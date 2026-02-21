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
            'email' => 'required|string|email|max:255|unique:customers',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'birth_date' => 'nullable|date',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'birth_date' => $request->birth_date,
            'cashback_balance' => 0,
            'loyalty_tier' => 'bronze',
            'total_orders' => 0,
            'total_spent' => 0,
        ]);

        $token = $customer->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Cliente registrado com sucesso!',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'cashback_balance' => $customer->cashback_balance,
                'loyalty_tier' => $customer->loyalty_tier,
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
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        // Remover tokens antigos
        $customer->tokens()->delete();

        $token = $customer->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso!',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'cashback_balance' => $customer->cashback_balance,
                'loyalty_tier' => $customer->loyalty_tier,
                'total_orders' => $customer->total_orders,
                'total_spent' => $customer->total_spent,
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

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'birth_date' => $customer->birth_date,
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
