<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\PagarMeService;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;

class ManageSubscription extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static string $view = 'filament.restaurant.pages.manage-subscription';

    protected static ?string $navigationLabel = 'Minha Assinatura';

    protected static ?string $title = 'Gerenciar Assinatura';

    protected static ?string $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 10;

    public ?Subscription $subscription = null;

    public function mount(): void
    {
        // Obter tenant atual (tenancy)
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return;
        }

        // Buscar assinatura ativa (model já configurado para usar conexão central)
        $this->subscription = Subscription::where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->with('plan') // Eager load do plano
            ->orderBy('created_at', 'desc')
            ->first();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Sincronizar Status')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn () => $this->subscription && $this->subscription->pagarme_subscription_id)
                ->action(function () {
                    try {
                        $service = new PagarMeService();
                        $info = $service->getSubscriptionInfo($this->subscription->pagarme_subscription_id);

                        if ($info) {
                            $this->subscription->update([
                                'pagarme_status' => $info['status'],
                                'next_billing_date' => $info['next_billing_at'] ?? null,
                            ]);

                            // Recarregar subscription
                            $this->subscription->refresh();

                            Notification::make()
                                ->success()
                                ->title('Sincronizado!')
                                ->body('Status atualizado com sucesso.')
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Erro ao sincronizar')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Action::make('cancel')
                ->label('Cancelar Assinatura')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->subscription && in_array($this->subscription->status, ['active', 'trialing']))
                ->requiresConfirmation()
                ->modalHeading('Cancelar Assinatura?')
                ->modalDescription('Você perderá acesso ao sistema. Tem certeza?')
                ->modalSubmitActionLabel('Sim, cancelar')
                ->action(function () {
                    try {
                        // Cancelar no Pagar.me
                        if ($this->subscription->pagarme_subscription_id) {
                            $service = new PagarMeService();
                            $service->cancelSubscription($this->subscription->pagarme_subscription_id);
                        }

                        // Atualizar local
                        $this->subscription->update([
                            'status' => 'canceled',
                            'canceled_at' => now(),
                            'ends_at' => now(),
                        ]);

                        // Recarregar
                        $this->subscription->refresh();

                        Notification::make()
                            ->success()
                            ->title('Assinatura cancelada')
                            ->body('Você foi deslogado. Entre em contato para renovar.')
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Erro ao cancelar')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }

    public function getStatusColor(): string
    {
        if (!$this->subscription) {
            return 'gray';
        }

        return match ($this->subscription->status) {
            'active' => 'success',
            'trialing' => 'info',
            'past_due' => 'danger',
            'canceled' => 'gray',
            default => 'warning',
        };
    }

    public function getStatusLabel(): string
    {
        if (!$this->subscription) {
            return 'Sem assinatura';
        }

        return match ($this->subscription->status) {
            'active' => 'Ativa',
            'trialing' => 'Em Trial (Teste Grátis)',
            'past_due' => 'Atrasada',
            'canceled' => 'Cancelada',
            default => $this->subscription->status,
        };
    }
}
