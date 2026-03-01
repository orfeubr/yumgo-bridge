<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Validar cupom
     *
     * POST /api/v1/coupons/validate
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'order_total' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->code));
        $orderTotal = (float) $request->order_total;

        // Buscar cupom no TENANT atual (isolado por restaurante)
        $coupon = Coupon::active()
            ->byCode($code)
            ->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Cupom inválido ou expirado'
            ], 404);
        }

        // Verificar valor mínimo do pedido
        if ($coupon->min_order_value && $orderTotal < $coupon->min_order_value) {
            return response()->json([
                'valid' => false,
                'message' => "Valor mínimo do pedido: R$ " . number_format($coupon->min_order_value, 2, ',', '.')
            ], 422);
        }

        // Verificar limite de uso geral
        if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
            return response()->json([
                'valid' => false,
                'message' => 'Cupom esgotado'
            ], 422);
        }

        // Verificar limite de uso por cliente (se autenticado)
        if ($request->user() && $coupon->usage_per_customer) {
            $customerUsageCount = \DB::table('orders')
                ->where('customer_id', $request->user()->id)
                ->where('coupon_code', $code)
                ->whereIn('payment_status', ['paid', 'pending'])
                ->count();

            if ($customerUsageCount >= $coupon->usage_per_customer) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Você já atingiu o limite de uso deste cupom'
                ], 422);
            }
        }

        // Calcular desconto
        $discountAmount = 0;
        if ($coupon->type === 'percentage') {
            $discountAmount = ($orderTotal * $coupon->value) / 100;
        } else {
            $discountAmount = $coupon->value;
        }

        // Desconto não pode ser maior que o total
        $discountAmount = min($discountAmount, $orderTotal);

        return response()->json([
            'valid' => true,
            'message' => 'Cupom aplicado com sucesso!',
            'coupon' => [
                'code' => $coupon->code,
                'description' => $coupon->description,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount_amount' => round($discountAmount, 2),
                'min_order_value' => $coupon->min_order_value,
            ]
        ]);
    }
}
