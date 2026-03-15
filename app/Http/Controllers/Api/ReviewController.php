<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Listar avaliações públicas do restaurante
     *
     * GET /api/v1/reviews
     */
    public function index(Request $request)
    {
        $reviews = Review::with(['customer', 'order'])
            ->public()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'reviews' => $reviews->items(),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'total_pages' => $reviews->lastPage(),
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
            ],
            'average_rating' => Review::public()->avg('rating') ?? 0,
            'total_reviews' => Review::public()->count(),
        ]);
    }

    /**
     * Criar nova avaliação
     *
     * POST /api/v1/reviews
     */
    public function store(Request $request)
    {
        // 1. Validar dados
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'food_rating' => 'nullable|integer|min:1|max:5',
            'delivery_rating' => 'nullable|integer|min:1|max:5',
            'service_rating' => 'nullable|integer|min:1|max:5',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 2. Buscar customer do TENANT (não central)
        $centralCustomer = $request->user();
        $customer = Customer::where('email', $centralCustomer->email)
            ->orWhere('phone', $centralCustomer->phone)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado neste restaurante',
            ], 404);
        }

        // 3. Verificar se o pedido pertence ao cliente
        $order = Order::find($request->order_id);

        if (!$order || $order->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido não encontrado ou não pertence a você',
            ], 403);
        }

        // 4. Verificar se pedido foi entregue
        if ($order->status !== 'delivered') {
            return response()->json([
                'success' => false,
                'message' => 'Você só pode avaliar pedidos que foram entregues',
            ], 400);
        }

        // 5. Verificar se já avaliou este pedido
        $existingReview = Review::where('order_id', $order->id)
            ->where('customer_id', $customer->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Você já avaliou este pedido',
            ], 400);
        }

        // 6. Criar avaliação
        $review = Review::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'food_rating' => $request->food_rating,
            'delivery_rating' => $request->delivery_rating,
            'service_rating' => $request->service_rating,
            'is_public' => $request->is_public ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Avaliação enviada com sucesso!',
            'review' => $review,
        ], 201);
    }

    /**
     * Verificar se pedido já foi avaliado
     *
     * GET /api/v1/orders/{id}/review
     */
    public function checkReview(Request $request, $orderId)
    {
        // Buscar customer do TENANT
        $centralCustomer = $request->user();
        $customer = Customer::where('email', $centralCustomer->email)
            ->orWhere('phone', $centralCustomer->phone)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado',
            ], 404);
        }

        // Verificar se existe review
        $review = Review::where('order_id', $orderId)
            ->where('customer_id', $customer->id)
            ->first();

        return response()->json([
            'success' => true,
            'has_review' => $review !== null,
            'review' => $review,
        ]);
    }

    /**
     * Estatísticas de avaliações
     *
     * GET /api/v1/reviews/stats
     */
    public function stats()
    {
        $publicReviews = Review::public();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_reviews' => $publicReviews->count(),
                'average_rating' => round($publicReviews->avg('rating') ?? 0, 1),
                'average_food_rating' => round($publicReviews->avg('food_rating') ?? 0, 1),
                'average_delivery_rating' => round($publicReviews->avg('delivery_rating') ?? 0, 1),
                'average_service_rating' => round($publicReviews->avg('service_rating') ?? 0, 1),
                'ratings_distribution' => [
                    '5' => $publicReviews->where('rating', 5)->count(),
                    '4' => $publicReviews->where('rating', 4)->count(),
                    '3' => $publicReviews->where('rating', 3)->count(),
                    '2' => $publicReviews->where('rating', 2)->count(),
                    '1' => $publicReviews->where('rating', 1)->count(),
                ],
            ],
        ]);
    }
}
