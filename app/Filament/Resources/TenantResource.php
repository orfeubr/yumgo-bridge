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

                Forms\Components\Section::make('Dados Bancários')
                    ->description('Informações necessárias para criar recebedor no Pagar.me e processar pagamentos com split automático')
                    ->schema([
                        Forms\Components\Select::make('payment_gateway')
                            ->label('Gateway de Pagamento Ativo')
                            ->options([
                                'asaas' => 'Asaas',
                                'pagarme' => 'Pagar.me',
                            ])
                            ->default('asaas')
                            ->required()
                            ->helperText('Escolha qual gateway será usado para processar pagamentos deste restaurante')
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('company_name')
                                    ->label('Razão Social / Nome Completo')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Nome da empresa ou nome completo do proprietário'),

                                Forms\Components\Select::make('company_type')
                                    ->label('Tipo de Pessoa')
                                    ->options([
                                        'company' => 'Pessoa Jurídica',
                                        'individual' => 'Pessoa Física',
                                        'mei' => 'MEI',
                                    ])
                                    ->required()
                                    ->default('company')
                                    ->reactive(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('cpf_cnpj')
                                    ->label(fn (Forms\Get $get) => $get('company_type') === 'company' ? 'CNPJ' : 'CPF')
                                    ->required()
                                    ->mask(fn (Forms\Get $get) => $get('company_type') === 'company' ? '99.999.999/9999-99' : '999.999.999-99')
                                    ->placeholder(fn (Forms\Get $get) => $get('company_type') === 'company' ? '00.000.000/0000-00' : '000.000.000-00')
                                    ->helperText('Sem pontuação ou com formatação'),

                                Forms\Components\TextInput::make('mobile_phone')
                                    ->label('Telefone/Celular')
                                    ->tel()
                                    ->mask('(99) 99999-9999')
                                    ->placeholder('(11) 99999-9999')
                                    ->required()
                                    ->helperText('Com DDD'),
                            ]),

                        Forms\Components\Fieldset::make('Dados da Conta Bancária')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make('bank_code')
                                            ->label('Banco')
                                            ->options([
                                                '001' => '001 - Banco do Brasil',
                                                '033' => '033 - Santander',
                                                '104' => '104 - Caixa Econômica',
                                                '237' => '237 - Bradesco',
                                                '341' => '341 - Itaú',
                                                '077' => '077 - Inter',
                                                '260' => '260 - Nubank',
                                                '212' => '212 - Banco Original',
                                                '336' => '336 - C6 Bank',
                                                '290' => '290 - Pagseguro',
                                            ])
                                            ->required()
                                            ->searchable()
                                            ->helperText('Código do banco'),

                                        Forms\Components\Select::make('bank_account_type')
                                            ->label('Tipo de Conta')
                                            ->options([
                                                'checking' => 'Conta Corrente',
                                                'savings' => 'Conta Poupança',
                                            ])
                                            ->required()
                                            ->default('checking'),

                                        Forms\Components\TextInput::make('pagarme_recipient_id')
                                            ->label('Recipient ID (Pagar.me)')
                                            ->disabled()
                                            ->helperText('Gerado automaticamente ao salvar')
                                            ->dehydrated(false),
                                    ]),

                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('bank_agency')
                                            ->label('Agência')
                                            ->required()
                                            ->numeric()
                                            ->maxLength(10)
                                            ->placeholder('0001')
                                            ->helperText('Sem dígito'),

                                        Forms\Components\TextInput::make('bank_branch_digit')
                                            ->label('Dígito')
                                            ->maxLength(1)
                                            ->placeholder('0')
                                            ->default('0')
                                            ->helperText('Dígito verificador da agência'),

                                        Forms\Components\TextInput::make('bank_account')
                                            ->label('Conta')
                                            ->required()
                                            ->numeric()
                                            ->maxLength(20)
                                            ->placeholder('12345678')
                                            ->helperText('Sem dígito'),

                                        Forms\Components\TextInput::make('bank_account_digit')
                                            ->label('Dígito')
                                            ->required()
                                            ->maxLength(2)
                                            ->placeholder('9')
                                            ->helperText('Dígito verificador da conta'),
                                    ]),
                            ]),

                        Forms\Components\Placeholder::make('recipient_status')
                            ->label('Status do Recebedor')
                            ->content(function ($record) {
                                if (!$record) {
                                    return '💡 Preencha os dados bancários e salve para criar o recebedor automaticamente';
                                }

                                if ($record->pagarme_recipient_id) {
                                    return '✅ Recebedor criado: ' . $record->pagarme_recipient_id;
                                }

                                if ($record->payment_gateway !== 'pagarme') {
                                    return 'ℹ️ Gateway ativo: ' . ($record->payment_gateway === 'asaas' ? 'Asaas' : 'Outro');
                                }

                                return '⏳ Recebedor será criado automaticamente ao salvar os dados bancários completos';
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(false),

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
