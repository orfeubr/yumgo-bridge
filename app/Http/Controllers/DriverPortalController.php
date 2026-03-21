<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryDriver;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DriverPortalController extends Controller
{
    /**
     * Autentica entregador via token e mostra portal
     */
    public function index(string $token)
    {
        $driver = DeliveryDriver::where('access_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        // Registra acesso
        $driver->recordAccess();

        // Salva na sessão
        Session::put('driver_id', $driver->id);
        Session::put('driver_token', $token);

        return view('driver-portal.index', [
            'driver' => $driver,
            'pendingDeliveries' => $this->getPendingDeliveries($driver),
            'inTransitDeliveries' => $this->getInTransitDeliveries($driver),
        ]);
    }

    /**
     * Busca entregas pendentes do entregador
     */
    protected function getPendingDeliveries(DeliveryDriver $driver)
    {
        return Delivery::query()
            ->with(['order.customer'])
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['driver_assigned'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Busca entregas em trânsito do entregador
     */
    protected function getInTransitDeliveries(DeliveryDriver $driver)
    {
        return Delivery::query()
            ->with(['order.customer'])
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['picked_up', 'in_transit'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Atualiza status da entrega
     */
    public function updateStatus(Request $request)
    {
        $driverId = Session::get('driver_id');
        if (!$driverId) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }

        $request->validate([
            'delivery_id' => 'required|exists:deliveries,id',
            'status' => 'required|in:picked_up,in_transit,delivered',
        ]);

        $delivery = Delivery::where('id', $request->delivery_id)
            ->where('driver_id', $driverId)
            ->firstOrFail();

        $delivery->update(['status' => $request->status]);

        // Atualizar timestamps
        if ($request->status === 'picked_up') {
            $delivery->picked_up_at = now();
            $delivery->save();
        } elseif ($request->status === 'delivered') {
            $delivery->delivered_at = now();
            $delivery->order->update(['status' => 'delivered']);
            $delivery->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado!',
            'delivery' => $delivery->load('order.customer'),
        ]);
    }

    /**
     * Busca pedido por número ou código
     * Permite buscar pedidos não atribuídos (driver_id = null) OU já atribuídos ao motorista
     */
    public function findOrder(Request $request)
    {
        $driverId = Session::get('driver_id');

        \Log::info('🔍 Driver Portal: Buscando pedido', [
            'driver_id' => $driverId,
            'session_id' => Session::getId(),
            'code' => $request->code,
        ]);

        if (!$driverId) {
            \Log::warning('⚠️ Driver não autenticado', [
                'session_id' => Session::getId(),
                'all_session' => Session::all(),
            ]);
            return response()->json(['error' => 'Não autenticado'], 401);
        }

        $request->validate([
            'code' => 'required|string',
        ]);

        // Buscar por número do pedido
        $order = Order::where('order_number', $request->code)
            ->whereHas('delivery', function ($query) use ($driverId) {
                // Pedidos disponíveis (sem motorista) OU já atribuídos a este motorista
                $query->where(function ($q) use ($driverId) {
                    $q->whereNull('driver_id')
                      ->orWhere('driver_id', $driverId);
                });
            })
            ->with(['delivery', 'customer'])
            ->first();

        if (!$order) {
            \Log::warning('❌ Pedido não encontrado', [
                'code' => $request->code,
                'driver_id' => $driverId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Pedido não encontrado ou já está com outro entregador.',
            ], 404);
        }

        \Log::info('✅ Pedido encontrado', [
            'order_number' => $order->order_number,
            'order_id' => $order->id,
            'delivery_id' => $order->delivery->id,
            'current_driver_id' => $order->delivery->driver_id,
        ]);

        // 🚚 AUTO-ATRIBUIÇÃO: Se pedido não tem motorista, atribui automaticamente
        if ($order->delivery->driver_id === null) {
            $order->delivery->update(['driver_id' => $driverId]);
            \Log::info('🚚 Motorista deu entrada no pedido', [
                'order_number' => $order->order_number,
                'driver_id' => $driverId,
            ]);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'delivery_address' => $order->delivery_address,
                'delivery_city' => $order->delivery_city,
                'delivery_neighborhood' => $order->delivery_neighborhood,
                'total' => $order->total,
                'customer' => [
                    'name' => $order->customer->name ?? 'Cliente',
                    'phone' => $order->customer->phone ?? null,
                ],
                'delivery' => [
                    'id' => $order->delivery->id,
                    'status' => $order->delivery->status,
                ],
            ],
            'delivery' => $order->delivery,
        ]);
    }
}
