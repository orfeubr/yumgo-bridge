<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use App\Services\PagarMeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Assinaturas';

    protected static ?string $modelLabel = 'Assinatura';

    protected static ?string $pluralModelLabel = 'Assinaturas';

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('Restaurante')
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\Select::make('plan_id')
                            ->label('Plano')
                            ->relationship('plan', 'name')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $plan = \App\Models\Plan::find($state);
                                if ($plan) {
                                    $set('amount', $plan->price_monthly);
                                }
                            })
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'trialing' => 'Trial (Teste Grátis)',
                                'active' => 'Ativo',
                                'past_due' => 'Atrasado',
                                'canceled' => 'Cancelado',
                            ])
                            ->default('trialing')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Datas')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Data de Início')
                            ->default(now())
                            ->required(),

                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('Fim do Trial')
                            ->default(now()->addDays(15))
                            ->helperText('15 dias de teste grátis'),

                        Forms\Components\DateTimePicker::make('next_billing_date')
                            ->label('Próxima Cobrança')
                            ->helperText('Data da próxima cobrança automática'),

                        Forms\Components\DateTimePicker::make('last_payment_date')
                            ->label('Último Pagamento')
                            ->helperText('Data do último pagamento confirmado'),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Data de Término'),

                        Forms\Components\DateTimePicker::make('canceled_at')
                            ->label('Data de Cancelamento'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Forms\Components\Section::make('Pagamento')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Valor Mensal')
                            ->prefix('R$')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(9999)
                            ->helperText('Valor cobrado mensalmente'),

                        Forms\Components\Select::make('payment_method')
                            ->label('Método de Pagamento')
                            ->options([
                                'credit_card' => 'Cartão de Crédito',
                                'boleto' => 'Boleto',
                            ])
                            ->helperText('Como o restaurante paga'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Pagar.me (Integração)')
                    ->schema([
                        Forms\Components\TextInput::make('pagarme_subscription_id')
                            ->label('ID da Assinatura (Pagar.me)')
                            ->maxLength(255)
                            ->disabled()
                            ->helperText('ID gerado pelo Pagar.me'),

                        Forms\Components\TextInput::make('pagarme_customer_id')
                            ->label('ID do Cliente (Pagar.me)')
                            ->maxLength(255)
                            ->disabled()
                            ->helperText('ID do cliente no Pagar.me'),

                        Forms\Components\TextInput::make('pagarme_status')
                            ->label('Status no Pagar.me')
                            ->maxLength(255)
                            ->disabled()
                            ->helperText('Status retornado pelo Pagar.me'),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Restaurante')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plano')
                    ->badge()
                    ->color(fn($record) => match($record->plan->name) {
                        'Starter' => 'gray',
                        'Pro' => 'success',
                        'Enterprise' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'trialing' => 'info',
                        'past_due' => 'danger',
                        'canceled' => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Ativo',
                        'trialing' => 'Trial',
                        'past_due' => 'Atrasado',
                        'canceled' => 'Cancelado',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_billing_date')
                    ->label('Próxima Cobrança')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('–'),

                Tables\Columns\TextColumn::make('last_payment_date')
                    ->label('Último Pagamento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Nunca')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('pagarme_subscription_id')
                    ->label('Pagar.me')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->pagarme_subscription_id ? 'Integrado' : 'Não integrado')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativo',
                        'trialing' => 'Trial',
                        'past_due' => 'Atrasado',
                        'canceled' => 'Cancelado',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('plan_id')
                    ->label('Plano')
                    ->relationship('plan', 'name')
                    ->multiple(),

                Tables\Filters\Filter::make('has_pagarme')
                    ->label('Integrado com Pagar.me')
                    ->query(fn ($query) => $query->whereNotNull('pagarme_subscription_id')),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Vence em 7 dias')
                    ->query(fn ($query) => $query
                        ->where('status', 'active')
                        ->whereBetween('next_billing_date', [now(), now()->addDays(7)])
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('sync')
                    ->label('Sincronizar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn ($record) => !empty($record->pagarme_subscription_id))
                    ->requiresConfirmation()
                    ->action(function (Subscription $record) {
                        try {
                            $service = new PagarMeService();
                            $info = $service->getSubscriptionInfo($record->pagarme_subscription_id);

                            if ($info) {
                                $record->update([
                                    'pagarme_status' => $info['status'],
                                    'next_billing_date' => $info['next_billing_at'] ?? null,
                                ]);

                                Notification::make()
                                    ->success()
                                    ->title('Sincronizado com sucesso!')
                                    ->body('Status atualizado do Pagar.me.')
                                    ->send();
                            } else {
                                throw new \Exception('Não foi possível obter informações do Pagar.me');
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Erro ao sincronizar')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['active', 'trialing', 'past_due']))
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Assinatura')
                    ->modalDescription('Tem certeza? O restaurante perderá acesso ao sistema.')
                    ->modalSubmitActionLabel('Sim, cancelar')
                    ->action(function (Subscription $record) {
                        try {
                            // Cancelar no Pagar.me se existir
                            if ($record->pagarme_subscription_id) {
                                $service = new PagarMeService();
                                $service->cancelSubscription($record->pagarme_subscription_id);
                            }

                            // Atualizar status local
                            $record->update([
                                'status' => 'canceled',
                                'canceled_at' => now(),
                                'ends_at' => now(),
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Assinatura cancelada')
                                ->body('O restaurante foi notificado.')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Erro ao cancelar')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('view_pagarme')
                    ->label('Ver no Pagar.me')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->visible(fn ($record) => !empty($record->pagarme_subscription_id))
                    ->url(fn ($record) => "https://dashboard.pagar.me/subscriptions/{$record->pagarme_subscription_id}", shouldOpenInNewTab: true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'past_due')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
