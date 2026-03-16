<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BridgeStatus;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BridgeController extends Controller
{
    /**
     * Heartbeat - Bridge envia a cada 30s para indicar que está online
     *
     * POST /api/v1/bridge/heartbeat
     * {
     *   "version": "3.20.0",
     *   "printers": [
     *     {"name": "POS58", "location": "counter", "status": "ready"},
     *     {"name": "POS58-Kitchen", "location": "kitchen", "status": "ready"}
     *   ]
     * }
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'version' => 'required|string',
            'printers' => 'nullable|array',
        ]);

        // Marca Bridge como online
        $bridge = BridgeStatus::markOnline(
            $validated['version'],
            $validated['printers'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat recebido com sucesso',
            'data' => [
                'status' => $bridge->status,
                'last_heartbeat' => $bridge->last_heartbeat->toIso8601String(),
            ]
        ]);
    }

    /**
     * Reporta impressão bem-sucedida
     *
     * POST /api/v1/bridge/print-success
     * {
     *   "order_id": 123,
     *   "location": "counter",
     *   "timestamp": "2026-03-16T10:30:00Z"
     * }
     */
    public function printSuccess(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'location' => 'required|string',
            'timestamp' => 'nullable|date',
        ]);

        $order = Order::find($validated['order_id']);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido não encontrado',
            ], 404);
        }

        // Marca como impresso
        $order->markPrintSuccess($validated['location']);

        return response()->json([
            'success' => true,
            'message' => 'Impressão registrada com sucesso',
            'data' => [
                'order_number' => $order->order_number,
                'print_status' => $order->print_status,
                'printed_at' => $order->printed_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Reporta falha de impressão
     *
     * POST /api/v1/bridge/print-failed
     * {
     *   "order_id": 123,
     *   "location": "counter",
     *   "error": "Impressora sem papel",
     *   "attempts": 1,
     *   "timestamp": "2026-03-16T10:30:00Z"
     * }
     */
    public function printFailed(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'location' => 'required|string',
            'error' => 'required|string',
            'attempts' => 'nullable|integer',
            'timestamp' => 'nullable|date',
        ]);

        $order = Order::find($validated['order_id']);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido não encontrado',
            ], 404);
        }

        // Marca como falha
        $order->markPrintFailed($validated['location'], $validated['error']);

        return response()->json([
            'success' => true,
            'message' => 'Falha de impressão registrada',
            'data' => [
                'order_number' => $order->order_number,
                'print_status' => $order->print_status,
                'print_error' => $order->print_error,
                'print_attempts' => $order->print_attempts,
            ]
        ]);
    }

    /**
     * Retorna status atual do Bridge
     *
     * GET /api/v1/bridge/status
     */
    public function status(): JsonResponse
    {
        $bridge = BridgeStatus::first();

        if (!$bridge) {
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => 'offline',
                    'message' => 'Bridge nunca conectado',
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $bridge->isOnline() ? 'online' : 'offline',
                'version' => $bridge->version,
                'last_heartbeat' => $bridge->last_heartbeat?->toIso8601String(),
                'last_seen' => $bridge->last_seen,
                'printers' => $bridge->printers ?? [],
            ]
        ]);
    }

    /**
     * Lista pedidos não impressos ou com falha
     *
     * GET /api/v1/bridge/pending-prints
     */
    public function pendingPrints(): JsonResponse
    {
        $orders = Order::notPrinted()
            ->with(['customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer' => $order->customer->name,
                    'total' => $order->total,
                    'print_status' => $order->print_status,
                    'print_error' => $order->print_error,
                    'print_attempts' => $order->print_attempts,
                    'created_at' => $order->created_at->toIso8601String(),
                ];
            })
        ]);
    }
}
