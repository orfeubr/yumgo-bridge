<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

/**
 * Middleware de autenticação temporário (bypass Sanctum)
 * TODO: Voltar para Sanctum quando bug for resolvido
 */
class SimpleTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token não fornecido'], 401);
        }

        try {
            // Buscar token na tabela personal_access_tokens
            $accessToken = DB::connection('pgsql')
                ->table('personal_access_tokens')
                ->where('token', hash('sha256', $token))
                ->first();

            if (!$accessToken) {
                return response()->json(['message' => 'Token inválido'], 401);
            }

            // Buscar customer
            $customer = Customer::find($accessToken->tokenable_id);

            if (!$customer) {
                return response()->json(['message' => 'Usuário não encontrado'], 401);
            }

            // Setar usuário autenticado
            $request->setUserResolver(function () use ($customer) {
                return $customer;
            });

            return $next($request);

        } catch (\Exception $e) {
            \Log::error('Erro na autenticação: ' . $e->getMessage());
            return response()->json(['message' => 'Erro na autenticação'], 500);
        }
    }
}
