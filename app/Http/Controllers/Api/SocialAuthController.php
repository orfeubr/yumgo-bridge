<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redireciona para o provedor OAuth
     */
    public function redirect($provider)
    {
        $this->validateProvider($provider);

        // 🔥 CORRIGIDO: Usar URL do tenant atual, não APP_URL fixo
        $currentUrl = request()->getSchemeAndHttpHost();
        $redirectUrl = $currentUrl . '/auth/' . $provider . '/callback';

        return Socialite::driver($provider)
            ->redirectUrl($redirectUrl)
            ->redirect();
    }

    /**
     * Callback do provedor OAuth
     */
    public function callback($provider)
    {
        $this->validateProvider($provider);

        try {
            // 🔥 CORRIGIDO: Usar URL do tenant atual, não APP_URL fixo
            $currentUrl = request()->getSchemeAndHttpHost();
            $redirectUrl = $currentUrl . '/auth/' . $provider . '/callback';

            $socialUser = Socialite::driver($provider)
                ->redirectUrl($redirectUrl)
                ->user();

            // Buscar ou criar customer
            $customer = Customer::where('email', $socialUser->getEmail())->first();

            if (!$customer) {
                // Criar novo customer no schema central
                $customer = Customer::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'phone' => null, // Será solicitado depois
                    'password' => Hash::make(Str::random(32)), // Password aleatório
                    'email_verified_at' => now(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]);

                // Marcar que precisa validação de WhatsApp
                $needsWhatsappValidation = true;
            } else {
                // Atualizar provider se não existir
                if (!$customer->provider) {
                    $customer->update([
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                    ]);
                }

                // Verificar se precisa validação de WhatsApp
                $needsWhatsappValidation = empty($customer->phone) || empty($customer->phone_verified_at);
            }

            // Criar/obter relacionamento com tenant atual
            $tenantData = $customer->getOrCreateTenantRelation(tenant('id'));

            // Criar token
            $token = $customer->createToken('auth_token')->plainTextToken;

            // 🔥 REDIRECIONAR PARA HOMEPAGE COM TOKEN
            // Montar dados do customer para passar via URL
            $customerData = [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'avatar' => $customer->avatar,
                'cashback_balance' => $tenantData->cashback_balance ?? 0,
                'loyalty_tier' => $tenantData->loyalty_tier ?? 'bronze',
            ];

            // Redirecionar para homepage com dados do login
            return redirect('/')
                ->with('oauth_success', true)
                ->with('auth_token', $token)
                ->with('customer_data', $customerData)
                ->with('needs_whatsapp_validation', $needsWhatsappValidation);

        } catch (\Exception $e) {
            // Em caso de erro, redirecionar para home com mensagem de erro
            return redirect('/')
                ->with('oauth_error', true)
                ->with('error_message', 'Erro ao autenticar com ' . ucfirst($provider) . ': ' . $e->getMessage());
        }
    }

    /**
     * Valida se o provedor é suportado
     */
    private function validateProvider($provider)
    {
        $allowedProviders = ['google', 'facebook'];

        if (!in_array($provider, $allowedProviders)) {
            abort(404, 'Provedor não suportado');
        }
    }

    /**
     * Solicitar código de verificação via WhatsApp
     */
    public function requestWhatsAppCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
        ]);

        $customer = auth('sanctum')->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado'
            ], 401);
        }

        // Gerar código de 6 dígitos
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Salvar no customer com expiração de 10 minutos
        $customer->update([
            'phone' => $request->phone,
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(10),
        ]);

        // TODO: Integrar com API de WhatsApp
        // Opções sugeridas:
        // 1. Twilio: https://www.twilio.com/pt-br/messaging/whatsapp
        // 2. Maytapi: https://maytapi.com/
        // 3. Evolution API (gratuito, self-hosted): https://github.com/EvolutionAPI/evolution-api
        // 4. WPP Connect: https://wppconnect.io/

        // Por enquanto, apenas retornar o código (remover em produção)
        \Log::info("Código de verificação para {$request->phone}: {$code}");

        return response()->json([
            'success' => true,
            'message' => 'Código enviado via WhatsApp',
            // TODO: Remover em produção
            'debug_code' => config('app.debug') ? $code : null,
        ]);
    }

    /**
     * Verificar código do WhatsApp
     */
    public function verifyWhatsAppCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $customer = auth('sanctum')->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado'
            ], 401);
        }

        // Verificar se o código está correto e não expirou
        if (
            $customer->verification_code === $request->code &&
            $customer->verification_code_expires_at &&
            $customer->verification_code_expires_at->isFuture()
        ) {
            // Marcar como verificado
            $customer->update([
                'phone_verified_at' => now(),
                'verification_code' => null,
                'verification_code_expires_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp verificado com sucesso!',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'phone_verified_at' => $customer->phone_verified_at,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Código inválido ou expirado'
        ], 422);
    }
}
