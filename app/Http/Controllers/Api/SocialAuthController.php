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

        // 🔥 MULTI-TENANT: Guardar domínio do tenant em COOKIE (persiste entre domínios)
        $currentDomain = request()->getSchemeAndHttpHost();

        // Criar cookie seguro que funciona em todos subdomínios .yumgo.com.br
        $cookie = cookie(
            'oauth_return_domain',  // nome
            $currentDomain,         // valor
            15,                     // 15 minutos de validade
            '/',                    // path
            '.yumgo.com.br',       // domínio (funciona em todos subdomínios)
            true,                   // secure (apenas HTTPS)
            true                    // httpOnly
        );

        // Usar domínio central para OAuth (simplifica configuração Google)
        $redirectUrl = 'https://yumgo.com.br/auth/' . $provider . '/callback';

        return Socialite::driver($provider)
            ->redirectUrl($redirectUrl)
            ->stateless() // ⭐ Desabilita validação de state (sessão não funciona entre domínios)
            ->redirect()
            ->withCookie($cookie); // Adiciona cookie na resposta
    }

    /**
     * Callback do provedor OAuth
     */
    public function callback($provider)
    {
        $this->validateProvider($provider);

        try {
            // 🔥 MULTI-TENANT: Recuperar domínio do tenant do COOKIE
            $returnDomain = request()->cookie('oauth_return_domain');

            \Log::info('🔍 OAuth Callback - Iniciando', [
                'provider' => $provider,
                'return_domain' => $returnDomain,
                'has_code' => request()->has('code'),
                'state_param' => request()->input('state'), // state do OAuth (não mexer!)
            ]);

            // 🔥 MULTI-TENANT: Usar domínio central para OAuth
            $redirectUrl = 'https://yumgo.com.br/auth/' . $provider . '/callback';

            \Log::info('🔍 OAuth - Buscando user do ' . ucfirst($provider));

            $socialUser = Socialite::driver($provider)
                ->redirectUrl($redirectUrl)
                ->stateless() // ⭐ Desabilita validação de state (sessão não funciona entre domínios)
                ->user();

            \Log::info('✅ OAuth - User obtido com sucesso', [
                'email' => $socialUser->getEmail(),
                'name' => $socialUser->getName(),
                'provider_id' => $socialUser->getId(),
            ]);

            // ⚠️ PADRÃO MULTI-TENANT: Buscar/criar customer em DOIS schemas

            \Log::info('🔍 OAuth - Buscando customer no schema CENTRAL', [
                'email' => $socialUser->getEmail(),
            ]);

            // 1️⃣ SCHEMA CENTRAL: Login único (email/password global)
            $centralCustomer = \DB::connection('pgsql')->table('customers')
                ->where('email', $socialUser->getEmail())
                ->first();

            if (!$centralCustomer) {
                \Log::info('📝 OAuth - Customer não existe no CENTRAL, criando...');

                // Criar customer no schema CENTRAL
                $customerId = \DB::connection('pgsql')->table('customers')->insertGetId([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'phone' => null,
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $centralCustomer = (object)[
                    'id' => $customerId,
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'phone' => null,
                    'avatar' => $socialUser->getAvatar(),
                ];

                \Log::info('✅ OAuth - Customer criado no CENTRAL', [
                    'customer_id' => $customerId,
                    'email' => $socialUser->getEmail(),
                ]);

                $needsWhatsappValidation = true;
            } else {
                \Log::info('✅ OAuth - Customer já existe no CENTRAL', [
                    'customer_id' => $centralCustomer->id,
                    'email' => $centralCustomer->email,
                ]);

                // Atualizar provider se não existir
                if (!$centralCustomer->provider) {
                    \DB::connection('pgsql')->table('customers')
                        ->where('id', $centralCustomer->id)
                        ->update([
                            'provider' => $provider,
                            'provider_id' => $socialUser->getId(),
                            'avatar' => $socialUser->getAvatar(),
                            'updated_at' => now(),
                        ]);
                }

                $needsWhatsappValidation = empty($centralCustomer->phone) || empty($centralCustomer->phone_verified_at);
            }

            // 2️⃣ SCHEMA TENANT: Dados isolados (cashback, pedidos, etc)
            // ⚠️ IMPORTANTE: Inicializar tenancy ANTES de buscar customer do tenant
            $tenantDomain = parse_url($returnDomain, PHP_URL_HOST);
            $domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', $tenantDomain)->first();

            if (!$domain) {
                \Log::error('❌ OAuth - Domínio do tenant não encontrado', [
                    'return_domain' => $returnDomain,
                    'tenant_domain' => $tenantDomain,
                ]);
                throw new \Exception('Domínio do tenant não encontrado: ' . $tenantDomain);
            }

            // Inicializar tenancy manualmente
            tenancy()->initialize($domain->tenant);

            \Log::info('🔍 OAuth - Buscando customer no schema TENANT', [
                'email' => $centralCustomer->email,
                'tenant_id' => tenant('id'),
                'tenant_name' => tenant('name'),
            ]);

            $tenantCustomer = Customer::where('email', $centralCustomer->email)->first();

            if (!$tenantCustomer) {
                \Log::info('📝 OAuth - Customer não existe no TENANT, criando...');

                // Criar customer no schema TENANT
                $tenantCustomer = Customer::create([
                    'name' => $centralCustomer->name,
                    'email' => $centralCustomer->email,
                    'phone' => $centralCustomer->phone,
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $centralCustomer->avatar,
                    'cashback_balance' => 0,
                    'loyalty_tier' => 'bronze',
                    'is_active' => true,
                ]);

                \Log::info('✅ OAuth - Customer criado no TENANT', [
                    'tenant_customer_id' => $tenantCustomer->id,
                    'email' => $tenantCustomer->email,
                ]);
            } else {
                \Log::info('✅ OAuth - Customer já existe no TENANT', [
                    'tenant_customer_id' => $tenantCustomer->id,
                    'email' => $tenantCustomer->email,
                ]);
            }

            // 3️⃣ Criar token usando customer CENTRAL (para auth)
            \Log::info('🔑 OAuth - Buscando customer CENTRAL para criar token', [
                'central_customer_id' => $centralCustomer->id,
            ]);

            $customer = (new \App\Models\Customer)->setConnection('pgsql')->find($centralCustomer->id);
            if (!$customer) {
                throw new \Exception('Erro ao buscar customer para autenticação');
            }

            // 4️⃣ Criar token para autenticação
            $token = $customer->createToken('auth_token')->plainTextToken;

            \Log::info('✅ OAuth - Token criado com sucesso', [
                'customer_id' => $customer->id,
                'token_preview' => substr($token, 0, 20) . '...',
            ]);

            // 5️⃣ Montar dados do customer (usando dados do TENANT para cashback)
            // ⚠️ IMPORTANTE: Usar ID do CENTRAL mas cashback do TENANT (isolamento multi-tenant)
            $customerData = [
                'id' => $customer->id, // ID do CENTRAL (para auth)
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'avatar' => $customer->avatar,
                'cashback_balance' => number_format((float)($tenantCustomer->cashback_balance ?? 0), 2, '.', ''), // TENANT
                'loyalty_tier' => $tenantCustomer->loyalty_tier ?? 'bronze', // TENANT
            ];

            // 🔥 MULTI-TENANT: Usar domínio do cookie OU fallback para domínio atual
            if (!$returnDomain) {
                $returnDomain = request()->getSchemeAndHttpHost();
                \Log::warning('⚠️ OAuth - Cookie oauth_return_domain não encontrado, usando domínio atual', [
                    'fallback_domain' => $returnDomain,
                ]);
            }

            // Criar URL com token e dados via query params (sessão não funciona entre domínios)
            $redirectUrl = $returnDomain . '/?oauth_success=true'
                . '&auth_token=' . urlencode($token)
                . '&customer_data=' . urlencode(json_encode($customerData))
                . '&needs_whatsapp=' . ($needsWhatsappValidation ? '1' : '0');

            \Log::info('🚀 OAuth - Redirecionando com sucesso', [
                'redirect_domain' => $returnDomain,
                'customer_email' => $customerData['email'],
                'needs_whatsapp' => $needsWhatsappValidation,
            ]);

            return redirect($redirectUrl);

        } catch (\Exception $e) {
            // 🔥 Log detalhado da exception
            \Log::error('❌ OAuth - Erro no callback', [
                'provider' => $provider,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            // 🔥 MULTI-TENANT: Redirecionar de volta ao domínio do tenant em caso de erro
            $returnDomain = request()->cookie('oauth_return_domain') ?: request()->getSchemeAndHttpHost();

            $errorMessage = $e->getMessage() ?: 'Erro desconhecido';
            $redirectUrl = $returnDomain . '/?oauth_error=true'
                . '&error_message=' . urlencode('Erro ao autenticar com ' . ucfirst($provider) . ': ' . $errorMessage);

            return redirect($redirectUrl);
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
