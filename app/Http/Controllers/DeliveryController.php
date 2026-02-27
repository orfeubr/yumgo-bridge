<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        // Buscar pedidos prontos e em entrega
        $orders = Order::whereIn('status', ['ready', 'out_for_delivery'])
            ->where('payment_status', 'paid')
            ->with(['customer', 'items.product'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('delivery.index', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:out_for_delivery,delivered',
        ]);

        $order->update([
            'status' => $request->status,
            'delivered_at' => $request->status === 'delivered' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado!',
            'order' => $order->fresh(['customer', 'items.product']),
        ]);
    }
}
