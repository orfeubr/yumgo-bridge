<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\OrderResource\Pages;
use App\Filament\Restaurant\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'pedidos';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Pedidos';
    protected static ?string $modelLabel = 'Pedido';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Pedido')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Nº do Pedido')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => '⏳ Pendente',
                                'confirmed' => '✅ Confirmado',
                                'preparing' => '👨‍🍳 Preparando',
                                'ready' => '📦 Pronto',
                                'out_for_delivery' => '🚗 Saiu para entrega',
                                'delivered' => '✅ Entregue',
                                'cancelled' => '❌ Cancelado',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\Select::make('payment_status')
                            ->label('Status do Pagamento')
                            ->options([
                                'pending' => 'Pendente',
                                'paid' => 'Pago',
                                'failed' => 'Falhou',
                                'refunded' => 'Reembolsado',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\Select::make('payment_method')
                            ->label('Método de Pagamento')
                            ->options([
                                'pix' => '💰 PIX',
                                'credit_card' => '💳 Cartão de Crédito',
                                'debit_card' => '💳 Cartão de Débito',
                                'cash' => '💵 Dinheiro',
                            ]),

                        Forms\Components\Select::make('delivery_type')
                            ->label('Tipo de Entrega')
                            ->options([
                                'delivery' => '🚗 Entrega',
                                'pickup' => '🏃 Retirada',
                            ])
                            ->required()
                            ->default('delivery'),
                    ])->columns(2),

                Forms\Components\Section::make('Valores')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('R$')
                            ->required(),

                        Forms\Components\TextInput::make('delivery_fee')
                            ->label('Taxa de Entrega')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        Forms\Components\TextInput::make('discount')
                            ->label('Desconto')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        Forms\Components\TextInput::make('cashback_used')
                            ->label('Cashback Utilizado')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),

                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->prefix('R$')
                            ->required(),

                        Forms\Components\TextInput::make('cashback_earned')
                            ->label('Cashback Ganho')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0),
                    ])->columns(3),

                Forms\Components\Section::make('Endereço de Entrega')
                    ->schema([
                        Forms\Components\Textarea::make('delivery_address')
                            ->label('Endereço')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('estimated_time')
                            ->label('Tempo Estimado (min)')
                            ->numeric()
                            ->suffix('min'),
                    ]),

                Forms\Components\Section::make('Observações')
                    ->schema([
                        Forms\Components\Textarea::make('customer_notes')
                            ->label('Observações do Cliente')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Observações Internas')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('#')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'confirmed',
                        'primary' => 'preparing',
                        'info' => 'ready',
                        'success' => ['delivered', 'out_for_delivery'],
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => '⏳ Pendente',
                        'confirmed' => '✅ Confirmado',
                        'preparing' => '👨‍🍳 Preparando',
                        'ready' => '📦 Pronto',
                        'out_for_delivery' => '🚗 Saindo',
                        'delivered' => '✅ Entregue',
                        'cancelled' => '❌ Cancelado',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Pagamento')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'secondary' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendente',
                        'paid' => 'Pago',
                        'failed' => 'Falhou',
                        'refunded' => 'Reembolsado',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Método')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pix' => '💰 PIX',
                        'credit_card' => '💳 Crédito',
                        'debit_card' => '💳 Débito',
                        'cash' => '💵 Dinheiro',
                        default => $state ?? 'N/A',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'confirmed' => 'Confirmado',
                        'preparing' => 'Preparando',
                        'ready' => 'Pronto',
                        'out_for_delivery' => 'Saindo',
                        'delivered' => 'Entregue',
                        'cancelled' => 'Cancelado',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Pagamento')
                    ->options([
                        'pending' => 'Pendente',
                        'paid' => 'Pago',
                        'failed' => 'Falhou',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('De'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Action para reimprimir pedido (v1.7.0)
                Tables\Actions\Action::make('reprint')
                    ->label('Reimprimir')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->visible(fn (Order $record) => $record->payment_status === 'paid')
                    ->requiresConfirmation()
                    ->modalHeading('Reimprimir Pedido')
                    ->modalDescription(fn (Order $record) => "Deseja reimprimir o pedido #{$record->order_number}? O cupom será enviado para as impressoras configuradas.")
                    ->modalSubmitActionLabel('Sim, Reimprimir')
                    ->action(function (Order $record) {
                        try {
                            // Dispara evento WebSocket para impressão
                            event(new \App\Events\NewOrderEvent($record));

                            \Filament\Notifications\Notification::make()
                                ->title('Pedido reenviado para impressão!')
                                ->success()
                                ->body("O pedido #{$record->order_number} foi enviado para as impressoras.")
                                ->send();

                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erro ao reimprimir')
                                ->danger()
                                ->body('Erro: ' . $e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /**
     * Verifica se pode criar pedido (limite mensal de plano)
     */
    public static function canCreate(): bool
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return false;
        }

        // Verifica se pode criar pedido baseado no plano
        if (!$tenant->canCreateOrder()) {
            // Notificar usuário sobre limite atingido
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('⚠️ Limite de Pedidos Atingido')
                ->body('Você atingiu o limite de pedidos deste mês. Faça upgrade para processar mais pedidos.')
                ->persistent()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('upgrade')
                        ->label('🚀 Fazer Upgrade')
                        ->url(route('filament.restaurant.pages.manage-subscription'))
                        ->markAsRead(),
                ])
                ->send();

            return false;
        }

        return true;
    }

    /**
     * Retorna badge com contador (exibe limite mensal)
     */
    public static function getNavigationBadge(): ?string
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return null;
        }

        $subscription = $tenant->activeSubscription();

        if (!$subscription) {
            return null;
        }

        $maxOrders = $subscription->plan->max_orders_per_month ?? null;

        // Se ilimitado, não exibe badge
        if ($maxOrders === null) {
            return null;
        }

        // Contar pedidos deste mês
        $currentCount = \App\Models\Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return "{$currentCount}/{$maxOrders}";
    }

    /**
     * Cor do badge baseado no uso mensal
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return null;
        }

        $subscription = $tenant->activeSubscription();

        if (!$subscription || !$subscription->plan->max_orders_per_month) {
            return null;
        }

        $maxOrders = $subscription->plan->max_orders_per_month;
        $currentCount = \App\Models\Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $percentage = ($currentCount / $maxOrders) * 100;

        return match (true) {
            $percentage >= 100 => 'danger',
            $percentage >= 80 => 'warning',
            default => 'info',
        };
    }
}
