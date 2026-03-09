<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Usuários';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $pluralModelLabel = 'Usuários';

    protected static ?string $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 92;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Usuário')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->helperText(fn (string $context): string =>
                                $context === 'edit'
                                    ? 'Deixe em branco para manter a senha atual'
                                    : 'Mínimo de 8 caracteres'
                            ),

                        Forms\Components\Select::make('role')
                            ->label('Função')
                            ->options([
                                'admin' => 'Administrador',
                                'manager' => 'Gerente',
                                'worker' => 'Funcionário',
                                'finance' => 'Financeiro',
                                'driver' => 'Entregador',
                            ])
                            ->required()
                            ->default('worker')
                            ->helperText('Defina o nível de acesso do usuário'),

                        Forms\Components\Toggle::make('active')
                            ->label('Ativo')
                            ->default(true)
                            ->required()
                            ->helperText('Usuários inativos não podem fazer login'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Permissões')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('Permissões Específicas')
                            ->options([
                                // Produtos
                                'products.view' => 'Ver produtos',
                                'products.create' => 'Criar produtos',
                                'products.edit' => 'Editar produtos',
                                'products.delete' => 'Deletar produtos',

                                // Pedidos
                                'orders.view' => 'Ver pedidos',
                                'orders.edit' => 'Editar pedidos',
                                'orders.cancel' => 'Cancelar pedidos',

                                // Cupons
                                'coupons.view' => 'Ver cupons',
                                'coupons.create' => 'Criar cupons',
                                'coupons.edit' => 'Editar cupons',
                                'coupons.delete' => 'Deletar cupons',

                                // Clientes
                                'customers.view' => 'Ver clientes',
                                'customers.edit' => 'Editar clientes',

                                // Configurações
                                'settings.view' => 'Ver configurações',
                                'settings.edit' => 'Editar configurações',

                                // Relatórios
                                'reports.view' => 'Ver relatórios',
                                'reports.export' => 'Exportar relatórios',

                                // Usuários
                                'users.view' => 'Ver usuários',
                                'users.create' => 'Criar usuários',
                                'users.edit' => 'Editar usuários',
                                'users.delete' => 'Deletar usuários',
                            ])
                            ->columns(3)
                            ->gridDirection('row')
                            ->helperText('Selecione permissões específicas para este usuário')
                            ->visible(fn (Forms\Get $get) => in_array($get('role'), ['manager', 'worker', 'finance'])),
                    ])
                    ->collapsed()
                    ->description('Permissões granulares por recurso (opcional)')
                    ->visible(fn (Forms\Get $get) => !empty($get('role')) && $get('role') !== 'admin'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record): string => $record->email),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Função')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Administrador',
                        'manager' => 'Gerente',
                        'worker' => 'Funcionário',
                        'finance' => 'Financeiro',
                        'driver' => 'Entregador',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'manager',
                        'success' => 'worker',
                        'primary' => 'finance',
                        'secondary' => 'driver',
                    ])
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Último acesso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Nunca'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Função')
                    ->options([
                        'admin' => 'Administrador',
                        'manager' => 'Gerente',
                        'worker' => 'Funcionário',
                        'finance' => 'Financeiro',
                        'driver' => 'Entregador',
                    ]),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('toggle_active')
                        ->label(fn (User $record) => $record->active ? 'Desativar' : 'Ativar')
                        ->icon(fn (User $record) => $record->active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn (User $record) => $record->active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->action(function (User $record) {
                            $record->update(['active' => !$record->active]);
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Tem certeza que deseja deletar este usuário? Esta ação não pode ser desfeita.'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Verifica se pode criar usuário (limite de plano)
     */
    public static function canCreate(): bool
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return false;
        }

        // Buscar plano atual
        $subscription = $tenant->activeSubscription();
        $maxUsers = $subscription?->plan->max_users ?? null;

        // Se não tem limite configurado, pode criar
        if ($maxUsers === null) {
            return true;
        }

        // Contar usuários atuais
        $currentCount = \App\Models\User::count();

        // Se atingiu o limite, bloquear e notificar
        if ($currentCount >= $maxUsers) {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('⚠️ Limite de Usuários Atingido')
                ->body("Você atingiu o limite de {$maxUsers} usuários do seu plano. Faça upgrade para adicionar mais usuários.")
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
