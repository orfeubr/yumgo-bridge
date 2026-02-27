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
        $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->get();

        return response()->json([
            'data' => $addresses,
        ]);
    }

    /**
     * Criar novo endereço
     */
    public function createAddress(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'complement' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        // Validar se o bairro está habilitado para delivery
        $neighborhood = \App\Models\Neighborhood::where('city', $validated['city'])
            ->where('name', $validated['neighborhood'])
            ->where('enabled', true)
            ->first();

        if (!$neighborhood) {
            return response()->json([
                'message' => 'Não atendemos este bairro. Por favor, selecione um bairro disponível.',
                'error' => 'neighborhood_not_available',
            ], 422);
        }

        $customer = $request->user();

        // Se for marcado como padrão, desmarca os outros
        if ($validated['is_default'] ?? false) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $address = $customer->addresses()->create($validated);

        return response()->json([
            'message' => 'Endereço criado com sucesso!',
            'data' => $address,
            'delivery_info' => [
                'fee' => (float) $neighborhood->delivery_fee,
                'time' => $neighborhood->delivery_time,
            ],
        ], 201);
    }

    /**
     * Atualizar endereço
     */
    public function updateAddress(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);

        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'city' => 'sometimes|string|max:255',
            'neighborhood' => 'sometimes|string|max:255',
            'street' => 'sometimes|string|max:255',
            'number' => 'sometimes|string|max:20',
            'complement' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        // Se cidade ou bairro foram alterados, validar se estão habilitados
        if (isset($validated['city']) || isset($validated['neighborhood'])) {
            $city = $validated['city'] ?? $address->city;
            $neighborhood = $validated['neighborhood'] ?? $address->neighborhood;

            $neighborhoodData = \App\Models\Neighborhood::where('city', $city)
                ->where('name', $neighborhood)
                ->where('enabled', true)
                ->first();

            if (!$neighborhoodData) {
                return response()->json([
                    'message' => 'Não atendemos este bairro. Por favor, selecione um bairro disponível.',
                    'error' => 'neighborhood_not_available',
                ], 422);
            }
        }

        // Se for marcado como padrão, desmarca os outros
        if ($validated['is_default'] ?? false) {
            $request->user()->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'message' => 'Endereço atualizado com sucesso!',
            'data' => $address,
        ]);
    }

    /**
     * Deletar endereço
     */
    public function deleteAddress(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $address->delete();

        return response()->json([
            'message' => 'Endereço deletado com sucesso!',
        ]);
    }
}
