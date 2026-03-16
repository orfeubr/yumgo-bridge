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

        // Bridge espera dados dentro de 'order'
        return [
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->order->status,
                'payment_status' => $this->order->payment_status,

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
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,
                        'variations' => $item->variations,
                        'addons' => $item->addons,
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

        foreach ($this->order->items as $item) {
            $printLocation = $item->product->print_location ?? 'kitchen';
            $locations->push($printLocation);
        }

        // Sempre imprimir no balcão (recibo completo)
        $locations->push('counter');

        return $locations->unique()->values()->toArray();
    }
}
