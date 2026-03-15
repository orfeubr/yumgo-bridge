<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestPrintEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tenantId;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Canal para broadcasting (público - sem autenticação)
     */
    public function broadcastOn(): Channel
    {
        return new Channel("restaurant.{$this->tenantId}");
    }

    /**
     * Nome do evento
     */
    public function broadcastAs(): string
    {
        return 'order.created';
    }

    /**
     * Dados que serão enviados via WebSocket
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => 1,
            'order_number' => 'TESTE-' . rand(1000, 9999),
            'status' => 'pending',
            'payment_status' => 'paid',

            // Cliente
            'customer' => [
                'name' => 'Cliente Teste',
                'phone' => '(11) 99999-9999',
                'email' => 'teste@yumgo.com.br',
            ],

            // Entrega
            'delivery' => [
                'method' => 'delivery',
                'address' => 'Rua Teste, 123 - Apto 45',
                'neighborhood' => 'Centro',
                'reference' => 'Portão azul',
                'fee' => 5.00,
            ],

            // Items
            'items' => [
                [
                    'quantity' => 1,
                    'name' => 'Feijoada Completa (TESTE)',
                    'price' => 31.00,
                    'subtotal' => 31.00,
                    'variations' => [],
                    'addons' => [],
                    'notes' => 'Bem quentinha - IMPRESSÃO DE TESTE',
                    'print_location' => 'kitchen',
                ],
            ],

            // Totais
            'totals' => [
                'subtotal' => 31.00,
                'delivery_fee' => 5.00,
                'discount' => 0,
                'total' => 36.00,
            ],

            // Pagamento
            'payment' => [
                'method' => 'pix',
                'status' => 'paid',
            ],

            // Observações
            'notes' => '🧪 IMPRESSÃO DE TESTE - Ignore este pedido',

            // Onde imprimir
            'print_locations' => ['kitchen', 'counter'],

            // Timestamp
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
