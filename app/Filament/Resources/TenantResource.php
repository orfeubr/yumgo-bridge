<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    
    protected static ?string $navigationGroup = 'Plataforma';
    
    protected static ?string $modelLabel = 'Restaurante';
    
    protected static ?string $pluralModelLabel = 'Restaurantes';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Restaurante')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('ID')
                            ->default(fn () => (string) Str::uuid())
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                if (!$get('slug')) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL amigável (ex: pizza-express)'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\Select::make('plan_id')
                            ->label('Plano')
                            ->relationship('plan', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'trial' => 'Trial',
                                'active' => 'Ativo',
                                'inactive' => 'Inativo',
                                'suspended' => 'Suspenso',
                            ])
                            ->required()
                            ->default('trial'),

                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('Trial termina em')
                            ->default(now()->addDays(15)),
                    ])->columns(2),
                
                Forms\Components\Section::make('Usuário Administrador')
                    ->description('Criar usuário para acessar o painel do restaurante')
                    ->schema([
                        Forms\Components\TextInput::make('admin_name')
                            ->label('Nome do Administrador')
                            ->required()
                            ->maxLength(255)
                            ->default(fn (Forms\Get $get) => $get('name') ? 'Admin ' . $get('name') : null),

                        Forms\Components\TextInput::make('admin_email')
                            ->label('Email do Administrador')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->default(fn (Forms\Get $get) => $get('slug') ? 'admin@' . $get('slug') . '.com.br' : null),

                        Forms\Components\Checkbox::make('admin_is_super')
                            ->label('Super Admin (pode gerenciar usuários e permissões)')
                            ->default(true)
                            ->helperText('Recomendado para o dono do restaurante'),

                        Forms\Components\Placeholder::make('password_info')
                            ->label('Senha')
                            ->content('Uma senha aleatória será gerada automaticamente e exibida após a criação do tenant.')
                            ->columnSpanFull(),
                    ])->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Integração Asaas')
                    ->schema([
                        Forms\Components\TextInput::make('asaas_account_id')
                            ->label('ID da Conta Asaas')
                            ->maxLength(255)
                            ->helperText('Será preenchido automaticamente após criação'),
                    ])->columns(1)
                    ->collapsible(),

                Forms\Components\Section::make('Domínios')
                    ->schema([
                        Forms\Components\Placeholder::make('domain_info')
                            ->label('')
                            ->content('Os domínios são criados automaticamente baseados no ID do tenant. Você pode adicionar domínios personalizados abaixo.')
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('domains')
                            ->label('Domínios Cadastrados')
                            ->relationship('domains')
                            ->schema([
                                Forms\Components\TextInput::make('domain')
                                    ->label('Domínio')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('exemplo.yumgo.com.br')
                                    ->helperText('Digite o domínio completo sem http:// ou https://'),
                            ])
                            ->addActionLabel('Adicionar domínio personalizado')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['domain'] ?? null)
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false)
                    ->visible(fn ($record) => $record !== null), // Só mostra ao editar, não ao criar
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plano')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Trial' => 'gray',
                        'Starter' => 'info',
                        'Pro' => 'success',
                        'Enterprise' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'trial' => 'warning',
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'trial' => 'Trial',
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        'suspended' => 'Suspenso',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('Trial até')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'trial' => 'Trial',
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        'suspended' => 'Suspenso',
                    ]),
                
                Tables\Filters\SelectFilter::make('plan_id')
                    ->label('Plano')
                    ->relationship('plan', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
