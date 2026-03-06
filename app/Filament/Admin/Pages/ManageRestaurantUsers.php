<?php

namespace App\Filament\Admin\Pages;

use App\Models\Tenant;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Hash;

class ManageRestaurantUsers extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static string $view = 'filament.admin.pages.manage-restaurant-users';

    protected static ?string $navigationLabel = 'Usuários dos Restaurantes';

    protected static ?string $title = 'Gerenciar Usuários dos Restaurantes';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 6;

    public ?string $selectedTenantId = null;

    public function mount(): void
    {
        // Seleciona o primeiro tenant por padrão
        $firstTenant = Tenant::first();
        if ($firstTenant) {
            $this->selectedTenantId = $firstTenant->id;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->label('Selecione o Restaurante')
                    ->options(Tenant::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->selectedTenantId = $state;
                    })
                    ->default($this->selectedTenantId),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Usuários do Restaurante')
            ->query(function () {
                if (!$this->selectedTenantId) {
                    return User::query()->whereRaw('false');
                }

                $tenant = Tenant::find($this->selectedTenantId);
                if (!$tenant) {
                    return User::query()->whereRaw('false');
                }

                // Inicializar tenancy
                tenancy()->initialize($tenant);

                return User::query();
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Função')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'worker' => 'success',
                        'finance' => 'primary',
                        'driver' => 'secondary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Administrador',
                        'manager' => 'Gerente',
                        'worker' => 'Funcionário',
                        'finance' => 'Financeiro',
                        'driver' => 'Entregador',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Último acesso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
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
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
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
                            ->label('Nova Senha')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
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
                            ->required(),

                        Forms\Components\Toggle::make('active')
                            ->label('Ativo')
                            ->default(true),
                    ])
                    ->before(function () {
                        if ($this->selectedTenantId) {
                            $tenant = Tenant::find($this->selectedTenantId);
                            tenancy()->initialize($tenant);
                        }
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function () {
                        if ($this->selectedTenantId) {
                            $tenant = Tenant::find($this->selectedTenantId);
                            tenancy()->initialize($tenant);
                        }
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Novo Usuário')
                    ->form([
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
                            ->required()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),

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
                            ->default(true),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['email_verified_at'] = now();
                        return $data;
                    })
                    ->before(function () {
                        if (!$this->selectedTenantId) {
                            \Filament\Notifications\Notification::make()
                                ->title('Selecione um restaurante primeiro')
                                ->danger()
                                ->send();
                            return;
                        }

                        $tenant = Tenant::find($this->selectedTenantId);
                        tenancy()->initialize($tenant);
                    })
                    ->after(function () {
                        \Filament\Notifications\Notification::make()
                            ->title('Usuário criado com sucesso!')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function () {
                            if ($this->selectedTenantId) {
                                $tenant = Tenant::find($this->selectedTenantId);
                                tenancy()->initialize($tenant);
                            }
                        }),
                ]),
            ]);
    }
}
