<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Listar endereços salvos do cliente
     */
    public function index(Request $request)
    {
        $customer = $request->user();

        $addresses = Address::where('customer_id', $customer->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $addresses
        ]);
    }

    /**
     * Salvar novo endereço
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:100',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'zipcode' => 'nullable|string|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        $customer = $request->user();

        // Se este endereço for marcado como padrão, desmarcar os outros
        if ($validated['is_default'] ?? false) {
            Address::where('customer_id', $customer->id)
                ->update(['is_default' => false]);
        }

        // Criar endereço
        $address = Address::create([
            'customer_id' => $customer->id,
            'label' => $validated['label'] ?? null,
            'street' => $validated['street'],
            'number' => $validated['number'],
            'complement' => $validated['complement'] ?? null,
            'neighborhood' => $validated['neighborhood'],
            'city' => $validated['city'],
            'zipcode' => $validated['zipcode'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return response()->json([
            'message' => 'Endereço salvo com sucesso',
            'data' => $address
        ], 201);
    }

    /**
     * Atualizar endereço
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:100',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'zipcode' => 'nullable|string|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        $customer = $request->user();

        $address = Address::where('customer_id', $customer->id)
            ->findOrFail($id);

        // Se este endereço for marcado como padrão, desmarcar os outros
        if ($validated['is_default'] ?? false) {
            Address::where('customer_id', $customer->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'message' => 'Endereço atualizado com sucesso',
            'data' => $address->fresh()
        ]);
    }

    /**
     * Excluir endereço
     */
    public function destroy(Request $request, $id)
    {
        $customer = $request->user();

        $address = Address::where('customer_id', $customer->id)
            ->findOrFail($id);

        $address->delete();

        return response()->json([
            'message' => 'Endereço excluído com sucesso'
        ]);
    }
}
