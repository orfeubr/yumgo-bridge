<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar novo cliente
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|string|email|max:255|unique:customers,email',
            'password' => 'required|string|min:6|confirmed',
            'birth_date' => 'nullable|date',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'phone.required' => 'O celular é obrigatório.',
            'phone.unique' => 'Este celular já está cadastrado. Faça login ou use outro celular.',
            'email.email' => 'Digite um email válido.',
            'email.unique' => 'Este email já está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'password.confirmed' => 'As senhas não coincidem.',
        ]);

        // Criar customer no schema central
        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'birth_date' => $request->birth_date,
        ]);

        // Criar relacionamento com tenant atual
        $tenantData = $customer->getOrCreateTenantRelation(tenant('id'));

        $token = $customer->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Cliente registrado com sucesso!',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'cashback_balance' => $tenantData->cashback_balance ?? 0,
                'loyalty_tier' => $tenantData->loyalty_tier ?? 'bronze',
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * Login do cliente
     */
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string', // celular ou email
            'password' => 'required',
        ], [
            'identifier.required' => 'Digite seu celular ou email',
            'password.required' => 'Digite sua senha',
        ]);

        // Tentar login por celular ou email
        $identifier = $request->input('identifier');
        $customer = Customer::where('phone', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Celular/email ou senha incorretos.'],
            ]);
        }

        // Criar/obter relacionamento com tenant atual
        $tenantData = $customer->getOrCreateTenantRelation(tenant('id'));

        // Remover tokens antigos
        $customer->tokens()->delete();

        $token = $customer->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'cashback_balance' => $tenantData->cashback_balance ?? 0,
                'loyalty_tier' => $tenantData->loyalty_tier ?? 'bronze',
                'total_orders' => $tenantData->total_orders ?? 0,
                'total_spent' => $tenantData->total_spent ?? 0,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Logout do cliente
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso!',
        ]);
    }

    /**
     * Obter dados do cliente autenticado
     */
    public function me(Request $request)
    {
        $customer = $request->user();

        // Obter dados do tenant atual
        $tenantData = $customer->getTenantData(tenant('id'));

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'cpf' => $customer->cpf,
                'birth_date' => $customer->birth_date,
                'cashback_balance' => (float) ($tenantData->cashback_balance ?? 0),
                'loyalty_tier' => $tenantData->loyalty_tier ?? 'bronze',
                'total_orders' => (int) ($tenantData->total_orders ?? 0),
                'total_spent' => (float) ($tenantData->total_spent ?? 0),
                'created_at' => $customer->created_at->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Atualizar perfil do cliente
     */
    public function updateProfile(Request $request)
    {
        $customer = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'birth_date' => 'nullable|date',
        ]);

        $customer->update($request->only(['name', 'phone', 'birth_date']));

        return response()->json([
            'message' => 'Perfil atualizado com sucesso!',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'birth_date' => $customer->birth_date,
            ],
        ]);
    }

    /**
     * Solicitar reset de senha
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // TODO: Implementar envio de email com token de reset
        // Por enquanto, apenas confirma que o email existe

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            return response()->json([
                'message' => 'Se o email existir, você receberá instruções para resetar sua senha.',
            ]);
        }

        return response()->json([
            'message' => 'Email de recuperação enviado com sucesso!',
        ]);
    }
}
