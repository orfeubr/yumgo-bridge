<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public bool $forceReprint = false // ⭐ Flag para reimpressão forçada
    ) {
    }

    /**
     * Canal para broadcasting
     */
    public function broadcastOn(): Channel
    {
        // TESTE: Canal PÚBLICO (sem autenticação)
        // Usar tenancy() para obter tenant_id do contexto (schema isolation)
        $tenantId = tenancy()->tenant?->id ?? tenant('id');
        return new Channel("restaurant.{$tenantId}");
    }

    /**
     * Nome do evento
     */
    public function broadcastAs(): string
    {
        // IMPORTANTE: Laravel Echo espera evento com ponto na frente
        return '.order.created';
    }

    /**
     * Dados que serão enviados via WebSocket
     */
    public function broadcastWith(): array
    {
        $this->order->load(['items.product', 'customer']);

        // Determinar onde imprimir baseado nos produtos
        $printLocations = $this->determinePrintLocations();

        // Pegar nome e domínio do restaurante (tenant)
        $tenant = tenancy()->tenant;
        $restaurantName = $tenant?->name ?? 'Restaurante';
        $restaurantDomain = $tenant?->id ?? 'restaurante';

        // Bridge espera dados dentro de 'order'
        return [
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->order->status,
                'payment_status' => $this->order->payment_status,

                // ⭐ Dados do restaurante
                'restaurant' => [
                    'name' => $restaurantName,
                    'domain' => "{$restaurantDomain}.yumgo.com.br",
                ],

                // Cliente
                'customer' => [
                    'name' => $this->order->customer->name ?? 'Cliente',
                    'phone' => $this->order->customer->phone ?? '',
                    'email' => $this->order->customer->email ?? '',
                ],

                // Entrega
                'delivery' => [
                    'type' => $this->order->delivery_type,
                    'address' => $this->order->delivery_address,
                    'neighborhood' => $this->order->delivery_neighborhood,
                    'city' => $this->order->delivery_city,
                    'fee' => (float) $this->order->delivery_fee,
                ],

                // Items
                'items' => $this->order->items->map(function($item) {
                    return [
                        'quantity' => $item->quantity,
                        'name' => $item->product_name,
                        'unit_price' => (float) $item->unit_price,  // ⭐ CORRIGIDO
                        'subtotal' => (float) $item->subtotal,
                        'variations' => $item->variations ?? [],
                        'addons' => is_array($item->addons) ? $item->addons : [], // ⭐ GARANTIR ARRAY
                        'notes' => $item->notes,
                        'print_location' => $item->product->print_location ?? 'kitchen',
                    ];
                })->values()->toArray(),

                // Totais
                'totals' => [
                    'subtotal' => (float) $this->order->subtotal,
                    'delivery_fee' => (float) $this->order->delivery_fee,
                    'discount' => (float) ($this->order->discount ?? 0),
                    'total' => (float) $this->order->total,
                ],

                // Pagamento
                'payment' => [
                    'method' => $this->order->payment_method,
                    'status' => $this->order->payment_status,
                ],

                // ⭐ PIX: QR Code e código copia-e-cola (se aplicável)
                'pix' => $this->getPixData(),

                // Observações
                'notes' => $this->order->customer_notes,

                // Onde imprimir
                'print_locations' => $printLocations,

                // ⭐ Reimpressão forçada (ignora cooldown)
                'force_reprint' => $this->forceReprint,

                // Timestamp
                'created_at' => $this->order->created_at->toIso8601String(),
                'updated_at' => $this->order->updated_at->toIso8601String(),
            ]
        ];
    }

    /**
     * Determinar em quais impressoras imprimir
     */
    private function determinePrintLocations(): array
    {
        $locations = collect();

        // ⭐ Adicionar locations específicas dos produtos (se definidas)
        foreach ($this->order->items as $item) {
            $printLocation = $item->product->print_location ?? null;

            // Apenas adiciona se explicitamente configurado (não usa padrão)
            if ($printLocation && $printLocation !== 'counter') {
                $locations->push($printLocation);
            }
        }

        // ⭐ Sempre imprimir no balcão por último (recibo completo)
        $locations->push('counter');

        return $locations->unique()->values()->toArray();
    }

    /**
     * ⭐ Buscar dados do PIX (QR Code) se pagamento for PIX
     */
    private function getPixData(): ?array
    {
        // Apenas retorna dados se for pagamento PIX
        if ($this->order->payment_method !== 'pix') {
            \Log::info('🚫 getPixData: Não é PIX', [
                'payment_method' => $this->order->payment_method,
            ]);
            return null;
        }

        // Buscar último pagamento PIX do pedido (coluna: method, não payment_method)
        $payment = $this->order->payments()
            ->where('method', 'pix')
            ->latest()
            ->first();

        \Log::info('🔍 getPixData: Buscando payment PIX', [
            'order_id' => $this->order->id,
            'payment_found' => $payment ? 'SIM' : 'NÃO',
            'has_qrcode' => $payment?->pix_qrcode ? 'SIM' : 'NÃO',
            'qrcode_length' => $payment?->pix_qrcode ? strlen($payment->pix_qrcode) : 0,
            'has_copy_paste' => $payment?->pix_copy_paste ? 'SIM' : 'NÃO',
        ]);

        if (!$payment || !$payment->pix_qrcode) {
            \Log::warning('⚠️ getPixData: QR Code não encontrado', [
                'order_id' => $this->order->id,
                'payment_exists' => $payment ? 'SIM' : 'NÃO',
            ]);
            return null;
        }

        $pixData = [
            'qrcode' => $payment->pix_qrcode, // Base64 da imagem QR Code
            'code' => $payment->pix_copy_paste, // Código copia-e-cola
        ];

        \Log::info('✅ getPixData: Dados PIX retornados', [
            'order_id' => $this->order->id,
            'has_qrcode' => !empty($pixData['qrcode']),
            'has_code' => !empty($pixData['code']),
        ]);

        return $pixData;
    }
}
