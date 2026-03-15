<?php

namespace App\Observers;

use App\Models\Order;
use Carbon\Carbon;

class OrderObserver
{
    /**
     * Handle the Order "creating" event.
     * Gera order_number antes de salvar no banco.
     */
    public function creating(Order $order): void
    {
        if (!$order->order_number) {
            $order->order_number = $this->generateOrderNumber();
        }
    }

    /**
     * Gera número único do pedido
     */
    private function generateOrderNumber(): string
    {
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));

        return "{$date}-{$random}";
    }
}
