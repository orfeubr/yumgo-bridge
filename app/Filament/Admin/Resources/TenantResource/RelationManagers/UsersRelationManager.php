<?php

namespace App\Filament\Admin\Resources\TenantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Usuários do Restaurante';

    protected static ?string $label = 'usuário';

    protected static ?string $pluralLabel = 'usuários';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->helperText('Deixe em branco para manter a senha atual'),

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
                            ->default('admin'),

                        Forms\Components\Toggle::make('active')
                            ->label('Ativo')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),

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
                    ]),

                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Garantir que email_verified_at seja preenchido
                        $data['email_verified_at'] = now();
                        return $data;
                    })
                    ->before(function ($livewire) {
                        // Inicializar tenancy antes de criar
                        $tenant = $livewire->getOwnerRecord();
                        tenancy()->initialize($tenant);
                    })
                    ->after(function () {
                        // Finalizar tenancy após criar
                        tenancy()->end();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->before(function ($livewire) {
                        $tenant = $livewire->getOwnerRecord();
                        tenancy()->initialize($tenant);
                    })
                    ->after(function () {
                        tenancy()->end();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function ($livewire) {
                        $tenant = $livewire->getOwnerRecord();
                        tenancy()->initialize($tenant);
                    })
                    ->after(function () {
                        tenancy()->end();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Inicializar tenancy ao montar o componente
     */
    public function mount(): void
    {
        parent::mount();

        $tenant = $this->getOwnerRecord();
        tenancy()->initialize($tenant);
    }

    /**
     * Customizar query para buscar usuários do tenant correto
     */
    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        // Tenancy já foi inicializada no mount()
        return \App\Models\User::query();
    }
}
