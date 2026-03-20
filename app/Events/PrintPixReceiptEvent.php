<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento para imprimir comprovante PIX com QR Code
 * Enviado via WebSocket para YumGo Bridge
 */
class PrintPixReceiptEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public Payment $payment
    ) {}

    /**
     * Canal para broadcasting
     */
    public function broadcastOn(): Channel
    {
        $tenantId = tenancy()->tenant?->id ?? tenant('id');
        return new Channel("restaurant.{$tenantId}");
    }

    /**
     * Nome do evento
     */
    public function broadcastAs(): string
    {
        return 'print.pix.receipt';
    }

    /**
     * Dados enviados para o Bridge
     */
    public function broadcastWith(): array
    {
        return [
            'type' => 'pix_receipt',
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'customer_name' => $this->order->customer?->name ?? 'Cliente',
                'total' => $this->order->total,
                'created_at' => $this->order->created_at->format('d/m/Y H:i'),
            ],
            'payment' => [
                'pix_qrcode' => $this->payment->pix_qrcode, // Base64 do QR Code
                'pix_code' => $this->payment->pix_code, // Código copia e cola
                'expires_at' => $this->payment->expires_at?->format('d/m/Y H:i'),
            ],
            'tenant_id' => tenant('id'),
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
