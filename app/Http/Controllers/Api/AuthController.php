<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

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
     *
     * Gera token seguro e envia email (ou retorna token em dev)
     * Sempre retorna sucesso para não expor se email existe (anti-enumeração)
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'Digite um email válido.',
        ]);

        $email = $request->email;

        // Buscar customer NO SCHEMA CENTRAL (não no tenant)
        // ⚠️ Importante: senha é única entre todos os restaurantes
        $customer = Customer::on('pgsql')->where('email', $email)->first();

        if ($customer) {
            // Deletar tokens antigos deste email (CENTRAL)
            DB::connection('pgsql')->table('password_reset_tokens')
                ->where('email', $email)
                ->delete();

            // Gerar token seguro (6 dígitos numéricos - mais fácil de digitar)
            $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Salvar token hasheado no schema CENTRAL (segurança)
            DB::connection('pgsql')->table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]);

            // TODO: Enviar email com token
            // Mail::to($email)->send(new ResetPasswordMail($token));

            // Em desenvolvimento, retorna o token (REMOVER EM PRODUÇÃO)
            if (config('app.debug')) {
                return response()->json([
                    'message' => 'Token gerado com sucesso (DEV MODE).',
                    'token' => $token, // ⚠️ APENAS EM DEV!
                    'email' => $email,
                ]);
            }
        }

        // Sempre retorna sucesso (não expõe se email existe - segurança)
        return response()->json([
            'message' => 'Se o email existir, você receberá um código para redefinir sua senha.',
        ]);
    }

    /**
     * Verificar se token de reset é válido
     *
     * Útil para validar token antes de mostrar tela de nova senha
     */
    public function verifyResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string|size:6',
        ]);

        $resetRecord = DB::connection('pgsql')->table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'valid' => false,
                'message' => 'Token inválido ou expirado.',
            ], 400);
        }

        // Verificar se token expirou (15 minutos)
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            // Token expirado, deletar
            DB::connection('pgsql')->table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            return response()->json([
                'valid' => false,
                'message' => 'Token expirado. Solicite um novo código.',
            ], 400);
        }

        // Verificar se token está correto
        if (!Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'valid' => false,
                'message' => 'Código incorreto.',
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Token válido!',
        ]);
    }

    /**
     * Redefinir senha com token
     *
     * Valida token e atualiza senha do customer
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'Digite um email válido.',
            'token.required' => 'O código é obrigatório.',
            'token.size' => 'O código deve ter 6 dígitos.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'password.confirmed' => 'As senhas não coincidem.',
        ]);

        // Buscar registro de reset (CENTRAL)
        $resetRecord = DB::connection('pgsql')->table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            throw ValidationException::withMessages([
                'token' => ['Token inválido ou expirado.'],
            ]);
        }

        // Verificar se token expirou (15 minutos)
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            // Token expirado, deletar
            DB::connection('pgsql')->table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            throw ValidationException::withMessages([
                'token' => ['Token expirado. Solicite um novo código.'],
            ]);
        }

        // Verificar se token está correto
        if (!Hash::check($request->token, $resetRecord->token)) {
            throw ValidationException::withMessages([
                'token' => ['Código incorreto.'],
            ]);
        }

        // Buscar customer NO SCHEMA CENTRAL
        $customer = Customer::on('pgsql')->where('email', $request->email)->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'email' => ['Email não encontrado.'],
            ]);
        }

        // Atualizar senha
        $customer->update([
            'password' => Hash::make($request->password),
        ]);

        // Deletar token usado (CENTRAL)
        DB::connection('pgsql')->table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Revogar todos os tokens de acesso antigos (por segurança)
        $customer->tokens()->delete();

        return response()->json([
            'message' => 'Senha redefinida com sucesso! Faça login com sua nova senha.',
        ]);
    }
}
