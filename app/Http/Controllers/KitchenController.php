<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function index(Request $request)
    {
        // Buscar pedidos ativos da cozinha
        $orders = Order::whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->where('payment_status', 'paid')
            ->with(['customer', 'items.product'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kitchen.index', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:confirmed,preparing,ready,out_for_delivery',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado!',
            'order' => $order->fresh(['customer', 'items.product']),
        ]);
    }
}
