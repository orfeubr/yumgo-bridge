<?php

namespace App\Filament\Restaurant\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Senha')
            ->password()
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        $data = $this->form->getState();

        Log::info('=== TENTATIVA DE LOGIN ===', [
            'email' => $data['email'],
            'tenant_initialized' => tenancy()->initialized,
            'tenant_id' => tenancy()->initialized ? tenant('id') : 'N/A',
            'tenant_name' => tenancy()->initialized ? tenant('name') : 'N/A',
            'guard' => 'web',
            'guard_provider' => config('auth.guards.web.provider'),
            'provider_model' => config('auth.providers.users.model'),
            'current_connection' => \DB::connection()->getName(),
            'search_path' => \DB::select("SHOW search_path")[0]->search_path ?? 'N/A',
        ]);

        if (!tenancy()->initialized) {
            Log::error('TENANCY NÃO INICIALIZADA!');
            throw ValidationException::withMessages([
                'email' => 'Erro: tenant não identificado. Verifique o domínio.',
            ]);
        }

        // Verificar se usuário existe no schema correto
        $user = \App\Models\User::where('email', $data['email'])->first();
        Log::info('Busca de usuário', [
            'encontrado' => $user ? 'sim' : 'não',
            'user_id' => $user?->id,
            'user_email' => $user?->email,
        ]);

        try {
            Log::info('Chamando parent::authenticate()...');
            $response = parent::authenticate();
            Log::info('✅ parent::authenticate() SUCESSO!');

            // Verificar se realmente está logado
            $loggedUser = \Illuminate\Support\Facades\Auth::guard('web')->user();
            Log::info('Verificando sessão após login', [
                'is_authenticated' => \Illuminate\Support\Facades\Auth::guard('web')->check(),
                'user_id' => $loggedUser?->id,
                'user_email' => $loggedUser?->email,
                'session_id' => request()->session()->getId(),
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('❌ ERRO no parent::authenticate()', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
