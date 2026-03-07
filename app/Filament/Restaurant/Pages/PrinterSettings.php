<?php

namespace App\Filament\Restaurant\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class PrinterSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-printer';

    protected static string $view = 'filament.restaurant.pages.printer-settings';

    protected static ?string $navigationLabel = 'Impressão Automática';

    protected static ?string $title = 'Impressão Automática';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationGroup = 'Configurações';

    public function mount(): void
    {
        //
    }

    public function getPrinterTokenProperty(): string
    {
        $token = Auth::user()->tokens()->where('name', 'bridge-app')->first();

        if (!$token) {
            return '(Nenhum token gerado)';
        }

        // Mostra apenas os últimos 8 caracteres (segurança)
        $tokenValue = $token->token;
        return '••••••••' . substr($tokenValue, -8);
    }

    public function generateToken(): void
    {
        $user = Auth::user();

        // Revoga tokens antigos
        $user->tokens()->where('name', 'bridge-app')->delete();

        // Cria novo token com validade de 1 ano
        $token = $user->createToken(
            'bridge-app',
            ['*'],
            now()->addYear()
        )->plainTextToken;

        // Armazena temporariamente na sessão (só mostra uma vez)
        session()->flash('new_token', $token);

        Notification::make()
            ->title('Token gerado com sucesso!')
            ->success()
            ->body('Copie o token agora. Ele só será exibido uma vez.')
            ->persistent()
            ->send();
    }

    public function revokeToken(): void
    {
        Auth::user()->tokens()->where('name', 'bridge-app')->delete();

        Notification::make()
            ->title('Token revogado')
            ->success()
            ->body('O app Bridge será desconectado.')
            ->send();
    }

    public function copyToClipboard(string $text): void
    {
        Notification::make()
            ->title('Copiado!')
            ->success()
            ->body('ID copiado para a área de transferência.')
            ->send();
    }

    public function getRestaurantId(): string
    {
        return tenancy()->tenant->id;
    }

    public function getRestaurantName(): string
    {
        return tenancy()->tenant->name;
    }

    public function hasActiveToken(): bool
    {
        return Auth::user()->tokens()->where('name', 'bridge-app')->exists();
    }

    public function getTokenCount(): int
    {
        return Auth::user()->tokens()->where('name', 'bridge-app')->count();
    }

    public function getTokenCreatedAt(): ?string
    {
        $token = Auth::user()->tokens()->where('name', 'bridge-app')->first();

        if (!$token) {
            return null;
        }

        return $token->created_at->diffForHumans();
    }
}
