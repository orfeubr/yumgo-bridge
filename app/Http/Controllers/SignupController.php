<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\PlatformUser;
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
            // Criar tenant
            $tenant = Tenant::create([
                'id' => $request->restaurant_slug,
                'name' => $request->restaurant_name,
                'slug' => $request->restaurant_slug,
                'email' => $request->restaurant_email,
                'phone' => $request->restaurant_phone,
                'plan_id' => $request->plan_id,
                'status' => 'trial', // Aguardando configuração de pagamento
                'approval_status' => 'pending_approval', // Aguardando aprovação manual
                'payment_gateway' => 'pagarme',
                // Dados bancários (se fornecidos)
                'bank_code' => $request->bank_code,
                'bank_agency' => $request->bank_agency,
                'bank_account' => $request->bank_account,
                'bank_account_digit' => $request->bank_account_digit,
                'bank_account_type' => $request->bank_account_type ?? 'checking',
            ]);

            // O TenantObserver já vai criar:
            // - Domínio ({slug}.yumgo.com.br)
            // - Schema do banco
            // - Usuário admin no schema do tenant

            // Criar usuário da plataforma (para acesso ao painel central se necessário)
            PlatformUser::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'password' => Hash::make($request->owner_password),
                'role' => 'support', // Não é admin da plataforma, apenas suporte
                'active' => true,
            ]);

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
