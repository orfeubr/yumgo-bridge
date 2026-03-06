<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    /**
     * Canal para broadcasting
     */
    public function broadcastOn(): Channel
    {
        // Canal privado do restaurante (tenant)
        return new Channel("restaurant.{$this->order->tenant_id}.orders");
    }

    /**
     * Nome do evento
     */
    public function broadcastAs(): string
    {
        return 'new-order';
    }

    /**
     * Dados que serão enviados via WebSocket
     */
    public function broadcastWith(): array
    {
        $this->order->load(['items.product', 'customer']);

        // Determinar onde imprimir baseado nos produtos
        $printLocations = $this->determinePrintLocations();

        return [
            'order_id' => $this->order->id,
            'order_number' => str_pad($this->order->id, 4, '0', STR_PAD_LEFT),
            'status' => $this->order->status,
            'payment_status' => $this->order->payment_status,

            // Cliente
            'customer' => [
                'name' => $this->order->customer_name,
                'phone' => $this->order->customer_phone,
                'email' => $this->order->customer_email,
            ],

            // Entrega
            'delivery' => [
                'method' => $this->order->delivery_method,
                'address' => $this->order->delivery_address,
                'neighborhood' => $this->order->delivery_neighborhood,
                'reference' => $this->order->delivery_reference,
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
            })->values(),

            // Totais
            'totals' => [
                'subtotal' => (float) $this->order->subtotal,
                'delivery_fee' => (float) $this->order->delivery_fee,
                'discount' => (float) $this->order->discount,
                'total' => (float) $this->order->total,
            ],

            // Pagamento
            'payment' => [
                'method' => $this->order->payment_method,
                'status' => $this->order->payment_status,
            ],

            // Observações
            'notes' => $this->order->notes,

            // Onde imprimir
            'print_locations' => $printLocations,

            // Timestamp
            'created_at' => $this->order->created_at->toIso8601String(),
            'updated_at' => $this->order->updated_at->toIso8601String(),
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
