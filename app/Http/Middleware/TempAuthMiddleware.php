<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Customer;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Middleware temporário para substituir auth:sanctum
 * enquanto investigamos o crash do PHP-FPM
 */
class TempAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                \Log::warning('🔒 TempAuth: Token não fornecido');
                return response()->json([
                    'message' => 'Token não fornecido'
                ], 401);
            }

            // Token no formato "ID|PLAINTEXT" - precisamos apenas da parte PLAINTEXT
            $tokenParts = explode('|', $token, 2);
            if (count($tokenParts) !== 2) {
                \Log::warning('🔒 TempAuth: Formato de token inválido', [
                    'token_format' => $token
                ]);
                return response()->json([
                    'message' => 'Formato de token inválido'
                ], 401);
            }

            // Buscar token manualmente (sem usar Sanctum guard que está crashando)
            // Hashear apenas a parte PLAINTEXT (sem o ID)
            $hashedToken = hash('sha256', $tokenParts[1]);

            // IMPORTANTE: PersonalAccessToken está no schema PUBLIC (conexão padrão)
            // Não usar tenant connection aqui!
            $accessToken = PersonalAccessToken::on('pgsql')
                ->where('token', $hashedToken)
                ->first();

            if (!$accessToken) {
                \Log::warning('🔒 TempAuth: Token inválido', [
                    'token_prefix' => substr($token, 0, 10) . '...',
                    'hashed_prefix' => substr($hashedToken, 0, 20) . '...'
                ]);
                return response()->json([
                    'message' => 'Token inválido'
                ], 401);
            }

            \Log::info('✅ TempAuth: Token encontrado', [
                'token_id' => $accessToken->id,
                'customer_id' => $accessToken->tokenable_id,
                'expires_at' => $accessToken->expires_at,
                'last_used_at' => $accessToken->last_used_at
            ]);

            // Verificar se token expirou (apenas se expires_at NÃO for NULL)
            if ($accessToken->expires_at !== null && $accessToken->expires_at != '') {
                try {
                    $expiresAt = \Carbon\Carbon::parse($accessToken->expires_at);
                    if ($expiresAt->isPast()) {
                        \Log::warning('🔒 TempAuth: Token expirado', [
                            'expires_at' => $accessToken->expires_at,
                            'now' => now()
                        ]);
                        return response()->json([
                            'message' => 'Token expirado'
                        ], 401);
                    }
                } catch (\Exception $e) {
                    \Log::error('❌ Erro ao verificar expiração', [
                        'expires_at' => $accessToken->expires_at,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Carregar customer (também está no schema PUBLIC)
            $customer = Customer::on('pgsql')->find($accessToken->tokenable_id);

            if (!$customer) {
                \Log::error('🔒 TempAuth: Cliente não encontrado', [
                    'customer_id' => $accessToken->tokenable_id
                ]);
                return response()->json([
                    'message' => 'Cliente não encontrado'
                ], 401);
            }

            // Atualizar last_used_at (sem disparar eventos)
            try {
                $accessToken->forceFill(['last_used_at' => now()])->save();
            } catch (\Exception $e) {
                // Ignora erro se não conseguir atualizar last_used_at
                \Log::warning('⚠️ Não conseguiu atualizar last_used_at', [
                    'error' => $e->getMessage()
                ]);
            }

            // Definir usuário autenticado
            $request->setUserResolver(function () use ($customer) {
                return $customer;
            });

            \Log::info('✅ TempAuth: Autenticação bem-sucedida', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name
            ]);

            return $next($request);

        } catch (\Exception $e) {
            \Log::error('❌ TempAuthMiddleware error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erro na autenticação',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
