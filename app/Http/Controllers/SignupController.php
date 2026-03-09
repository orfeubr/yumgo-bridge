<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\PlatformUser;
use App\Models\Subscription;
use App\Services\PagarMeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SignupController extends Controller
{
    /**
     * Exibe wizard de cadastro
     */
    public function index()
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('price_monthly', 'asc')
            ->get();

        return view('signup.index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Processa cadastro do restaurante
     */
    public function store(Request $request)
    {
        \Log::info('=== CADASTRO INICIADO ===', [
            'ip' => $request->ip(),
            'data' => $request->except(['owner_password', 'owner_password_confirmation'])
        ]);

        $validator = Validator::make($request->all(), [
            // Dados do restaurante
            'restaurant_name' => 'required|string|max:255',
            'restaurant_email' => 'required|email|unique:tenants,email',
            'restaurant_phone' => 'required|string|max:20',
            'restaurant_slug' => 'required|string|max:255|unique:tenants,slug|alpha_dash',

            // Dados do responsável
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:platform_users,email',
            'owner_password' => 'required|string|min:6|confirmed',

            // Plano
            'plan_id' => 'required|exists:plans,id',

            // Pagamento
            'card_token' => 'required|string', // Token do cartão (Pagar.me)

            // Dados bancários (opcional no cadastro inicial)
            'bank_code' => 'nullable|string|max:3',
            'bank_agency' => 'nullable|string|max:10',
            'bank_account' => 'nullable|string|max:20',
            'bank_account_digit' => 'nullable|string|max:2',
            'bank_account_type' => 'nullable|in:checking,savings',
        ], [
            'restaurant_email.unique' => 'Este email de restaurante já está cadastrado.',
            'owner_email.unique' => 'Este email de responsável já está cadastrado.',
            'restaurant_slug.unique' => 'Este nome de URL já está em uso.',
            'restaurant_slug.alpha_dash' => 'A URL só pode conter letras, números, hífens e underscores.',
            'owner_password.confirmed' => 'As senhas não conferem.',
        ]);

        if ($validator->fails()) {
            \Log::warning('=== ERROS DE VALIDAÇÃO ===', [
                'errors' => $validator->errors()->toArray()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // ⭐ Criar tenant SEM disparar observers (evita criação de user com senha hardcoded)
            $tenant = Tenant::withoutEvents(function () use ($request) {
                return Tenant::create([
                    'id' => $request->restaurant_slug,
                    'name' => $request->restaurant_name,
                    'slug' => $request->restaurant_slug,
                    'email' => $request->restaurant_email,
                    'phone' => $request->restaurant_phone,
                    'plan_id' => $request->plan_id,
                    'status' => 'trial',
                    'approval_status' => 'pending_approval',
                    'payment_gateway' => 'pagarme',
                    'bank_code' => $request->bank_code,
                    'bank_agency' => $request->bank_agency,
                    'bank_account' => $request->bank_account,
                    'bank_account_digit' => $request->bank_account_digit,
                    'bank_account_type' => $request->bank_account_type ?? 'checking',
                ]);
            });

            // Criar estrutura manualmente (já que Observer não rodou)
            $observer = new \App\Observers\TenantObserver();

            // 1. Criar storage
            $reflection = new \ReflectionClass($observer);
            $method = $reflection->getMethod('createStorageStructure');
            $method->setAccessible(true);
            $method->invoke($observer, $tenant);

            // 2. Criar domínio
            $method = $reflection->getMethod('createDomain');
            $method->setAccessible(true);
            $method->invoke($observer, $tenant);

            // 3. ✅ CRIAR USUÁRIO ADMIN COM A SENHA DO FORMULÁRIO
            tenancy()->initialize($tenant);

            \App\Models\User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'password' => Hash::make($request->owner_password), // ⭐ USA SENHA DO FORMULÁRIO
                'role' => 'admin',
                'active' => true,
                'email_verified_at' => now(),
            ]);

            \Log::info("✅ Usuário admin criado para tenant {$tenant->name} com senha do formulário");

            tenancy()->end();

            // Criar usuário da plataforma (para acesso ao painel central se necessário)
            PlatformUser::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'password' => Hash::make($request->owner_password),
                'role' => 'support', // Não é admin da plataforma, apenas suporte
                'active' => true,
            ]);

            // ⭐ CRIAR SUBSCRIPTION COM TRIAL DE 7 DIAS
            $plan = Plan::find($request->plan_id);

            // Criar subscription no banco primeiro
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'trialing', // Status inicial: em trial
                'trial_ends_at' => now()->addDays(7),
                'current_period_start' => now(),
                'current_period_end' => now()->addDays(7),
            ]);

            // Se o plano tem pagarme_plan_id, criar subscription no Pagar.me
            if ($plan->pagarme_plan_id) {
                try {
                    $pagarMeService = new PagarMeService();

                    $pagarMeResponse = $pagarMeService->createSubscription($subscription, [
                        'card_id' => $request->card_token,
                        'payment_method' => 'credit_card',
                    ]);

                    if ($pagarMeResponse && isset($pagarMeResponse['id'])) {
                        // Atualizar subscription com dados do Pagar.me
                        $subscription->update([
                            'pagarme_subscription_id' => $pagarMeResponse['id'],
                            'status' => $pagarMeResponse['status'] ?? 'trialing',
                        ]);

                        \Log::info('✅ Subscription criada no Pagar.me', [
                            'subscription_id' => $pagarMeResponse['id'],
                            'tenant_id' => $tenant->id,
                        ]);
                    } else {
                        \Log::warning('⚠️ Falha ao criar subscription no Pagar.me (continuando com trial local)', [
                            'tenant_id' => $tenant->id,
                        ]);
                    }

                } catch (\Exception $e) {
                    \Log::error('❌ Erro ao criar subscription no Pagar.me: ' . $e->getMessage());
                    // Continua mesmo se falhar - trial local funciona
                }
            }

            // Redirecionar para página de sucesso
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('signup.success', ['slug' => $tenant->slug])
                ]);
            }
            return redirect()->route('signup.success', ['slug' => $tenant->slug])
                ->with('success', 'Cadastro realizado com sucesso!');

        } catch (\Exception $e) {
            \Log::error('Erro ao criar tenant: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Erro ao criar cadastro. Por favor, tente novamente.'
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Erro ao criar cadastro. Por favor, tente novamente.')
                ->withInput();
        }
    }

    /**
     * Página de sucesso após cadastro
     */
    public function success($slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        return view('signup.success', [
            'tenant' => $tenant,
        ]);
    }
}
