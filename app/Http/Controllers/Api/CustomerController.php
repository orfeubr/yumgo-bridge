<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Mostrar perfil do cliente
     */
    public function show(Request $request)
    {
        $customer = $request->user();

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'birth_date' => $customer->birth_date?->format('d/m/Y'),
            'cpf' => $customer->cpf,
            'cashback_balance' => $customer->cashback_balance,
            'loyalty_tier' => $customer->loyalty_tier,
            'total_orders' => $customer->total_orders,
            'total_spent' => $customer->total_spent,
            'created_at' => $customer->created_at->format('d/m/Y'),
        ]);
    }

    /**
     * Atualizar perfil do cliente
     */
    public function update(Request $request)
    {
        $customer = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'birth_date' => 'nullable|date',
            'cpf' => 'nullable|string|max:14',
        ]);

        $customer->update($request->only(['name', 'phone', 'birth_date', 'cpf']));

        return response()->json([
            'message' => 'Perfil atualizado com sucesso!',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'birth_date' => $customer->birth_date?->format('d/m/Y'),
                'cpf' => $customer->cpf,
            ],
        ]);
    }

    /**
     * Listar endereços do cliente
     */
    public function addresses(Request $request)
    {
        // TODO: Implementar model de Address quando necessário
        // Por enquanto, retorna lista vazia
        return response()->json([
            'data' => [],
        ]);
    }

    /**
     * Criar novo endereço
     */
    public function createAddress(Request $request)
    {
        // TODO: Implementar quando model Address for criado
        return response()->json([
            'message' => 'Funcionalidade em desenvolvimento.',
        ], 501);
    }

    /**
     * Atualizar endereço
     */
    public function updateAddress(Request $request, $id)
    {
        // TODO: Implementar quando model Address for criado
        return response()->json([
            'message' => 'Funcionalidade em desenvolvimento.',
        ], 501);
    }

    /**
     * Deletar endereço
     */
    public function deleteAddress(Request $request, $id)
    {
        // TODO: Implementar quando model Address for criado
        return response()->json([
            'message' => 'Funcionalidade em desenvolvimento.',
        ], 501);
    }
}
