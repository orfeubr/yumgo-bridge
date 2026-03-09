<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TenantResource\Pages;
use App\Filament\Admin\Resources\TenantResource\RelationManagers;
use App\Models\Tenant;
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

    protected static ?string $navigationLabel = 'Restaurantes';

    protected static ?string $modelLabel = 'Restaurante';

    protected static ?string $pluralModelLabel = 'Restaurantes';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Restaurante')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Restaurante')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                if (!$get('slug')) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (Subdomínio)')
                            ->required()
                            ->rules([
                                'required',
                                'max:255',
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        $exists = \App\Models\Tenant::where('slug', $value)
                                            ->when(request()->route('record'), function ($query, $record) {
                                                $query->where('id', '!=', $record);
                                            })
                                            ->exists();

                                        if ($exists) {
                                            $fail('Este slug já está em uso.');
                                        }
                                    };
                                },
                            ])
                            ->maxLength(255)
                            ->helperText('Será usado como subdomínio: slug.yumgo.com.br')
                            ->suffixIcon('heroicon-o-link')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                $set('slug', Str::slug($state));
                            }),

                        Forms\Components\Placeholder::make('domain_preview')
                            ->label('URL do Painel')
                            ->content(function (Forms\Get $get) {
                                $slug = $get('slug') ?: 'seu-restaurante';
                                return "https://{$slug}.yumgo.com.br/painel";
                            })
                            ->visible(fn (Forms\Get $get) => filled($get('slug'))),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail de Contato')
                            ->email()
                            ->required()
                            ->rules([
                                'required',
                                'email',
                                'max:255',
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        $exists = \App\Models\Tenant::where('email', $value)
                                            ->when(request()->route('record'), function ($query, $record) {
                                                $query->where('id', '!=', $record);
                                            })
                                            ->exists();

                                        if ($exists) {
                                            $fail('Este email já está em uso.');
                                        }
                                    };
                                },
                            ])
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('address')
                            ->label('Endereço')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->helperText('Descrição breve do restaurante (máx. 500 caracteres)'),

                        Forms\Components\CheckboxList::make('cuisine_types')
                            ->label('Tipos de Culinária')
                            ->options([
                                'brasileira' => '🇧🇷 Brasileira',
                                'pizza' => '🍕 Pizza',
                                'hamburguer' => '🍔 Hambúrguer',
                                'japonesa' => '🍱 Japonesa',
                                'italiana' => '🍝 Italiana',
                                'lanches' => '🥪 Lanches',
                                'marmitex' => '🍲 Marmitex',
                                'bebidas' => '🥤 Bebidas',
                                'sobremesas' => '🍰 Sobremesas',
                                'saudavel' => '🥗 Saudável',
                                'vegetariana' => '🌱 Vegetariana/Vegana',
                                'frutos-mar' => '🦞 Frutos do Mar',
                                'churrasco' => '🥩 Churrasco',
                                'arabe' => '🥙 Árabe',
                                'chinesa' => '🥡 Chinesa',
                                'mexicana' => '🌮 Mexicana',
                            ])
                            ->columns(3)
                            ->gridDirection('row')
                            ->columnSpanFull()
                            ->helperText('Selecione os tipos de comida que seu restaurante serve'),

                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo do Restaurante')
                            ->image()
                            ->disk('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                                '16:9',
                            ])
                            ->maxSize(2048)
                            ->directory('tenants/logos')
                            ->visibility('public')
                            ->helperText('Imagem do logo (máx. 2MB, formatos: JPG, PNG)')
                            ->imagePreviewHeight('150')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Dados da Empresa e Endereço')
                    ->schema([
                        Forms\Components\TextInput::make('cnpj')
                            ->label('CNPJ')
                            ->mask('99.999.999/9999-99')
                            ->placeholder('00.000.000/0000-00')
                            ->maxLength(18)
                            ->helperText('CNPJ da empresa (obrigatório para emissão de NFC-e)'),

                        Forms\Components\TextInput::make('razao_social')
                            ->label('Razão Social')
                            ->maxLength(255)
                            ->helperText('Nome empresarial registrado na Receita Federal'),

                        Forms\Components\TextInput::make('address_zipcode')
                            ->label('CEP')
                            ->mask('99999-999')
                            ->placeholder('00000-000')
                            ->maxLength(9)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                if (!$state) {
                                    return;
                                }

                                $viaCep = app(\App\Services\ViaCepService::class);
                                $endereco = $viaCep->buscarCep($state);

                                if ($endereco) {
                                    $set('address_street', $endereco['logradouro']);
                                    $set('address_neighborhood', $endereco['bairro']);
                                    $set('address_city', $endereco['localidade']);
                                    $set('address_state', $endereco['uf']);

                                    \Filament\Notifications\Notification::make()
                                        ->title('CEP encontrado!')
                                        ->body('Endereço preenchido automaticamente.')
                                        ->success()
                                        ->send();
                                }
                            })
                            ->helperText('Digite o CEP e pressione Tab para buscar automaticamente')
                            ->suffixIcon('heroicon-o-magnifying-glass'),

                        Forms\Components\TextInput::make('address_street')
                            ->label('Rua/Avenida')
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('address_number')
                                    ->label('Número')
                                    ->maxLength(20)
                                    ->required(),

                                Forms\Components\TextInput::make('address_complement')
                                    ->label('Complemento')
                                    ->maxLength(100)
                                    ->placeholder('Apto, Sala, Bloco...'),

                                Forms\Components\TextInput::make('address_neighborhood')
                                    ->label('Bairro')
                                    ->maxLength(100),
                            ]),

                        Forms\Components\TextInput::make('address_city')
                            ->label('Cidade')
                            ->maxLength(100)
                            ->required(),

                        Forms\Components\Select::make('address_state')
                            ->label('Estado')
                            ->options([
                                'AC' => 'Acre',
                                'AL' => 'Alagoas',
                                'AP' => 'Amapá',
                                'AM' => 'Amazonas',
                                'BA' => 'Bahia',
                                'CE' => 'Ceará',
                                'DF' => 'Distrito Federal',
                                'ES' => 'Espírito Santo',
                                'GO' => 'Goiás',
                                'MA' => 'Maranhão',
                                'MT' => 'Mato Grosso',
                                'MS' => 'Mato Grosso do Sul',
                                'MG' => 'Minas Gerais',
                                'PA' => 'Pará',
                                'PB' => 'Paraíba',
                                'PR' => 'Paraná',
                                'PE' => 'Pernambuco',
                                'PI' => 'Piauí',
                                'RJ' => 'Rio de Janeiro',
                                'RN' => 'Rio Grande do Norte',
                                'RS' => 'Rio Grande do Sul',
                                'RO' => 'Rondônia',
                                'RR' => 'Roraima',
                                'SC' => 'Santa Catarina',
                                'SP' => 'São Paulo',
                                'SE' => 'Sergipe',
                                'TO' => 'Tocantins',
                            ])
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->placeholder('-23.550520')
                            ->helperText('Coordenada geográfica (opcional)'),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->placeholder('-46.633308')
                            ->helperText('Coordenada geográfica (opcional)'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Plano e Status')
                    ->schema([
                        Forms\Components\Select::make('plan_id')
                            ->label('Plano')
                            ->relationship('plan', 'name')
                            ->required()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'trial' => 'Trial (Teste)',
                                'active' => 'Ativo',
                                'inactive' => 'Inativo',
                                'suspended' => 'Suspenso',
                            ])
                            ->default('trial')
                            ->required(),

                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('Trial termina em')
                            ->default(now()->addDays(15))
                            ->visible(fn (Forms\Get $get) => $get('status') === 'trial'),
                    ])->columns(3),

                Forms\Components\Section::make('Gateway de Pagamento')
                    ->schema([
                        Forms\Components\Select::make('payment_gateway')
                            ->label('Gateway de Pagamento')
                            ->options([
                                'pagarme' => 'Pagar.me',
                            ])
                            ->default('pagarme')
                            ->required()
                            ->helperText('Gateway usado para processar pagamentos'),

                        Forms\Components\TextInput::make('pagarme_recipient_id')
                            ->label('ID do Recebedor Pagar.me')
                            ->helperText('Será preenchido automaticamente após criar recebedor')
                            ->disabled()
                            ->dehydrated(true),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('bank_code')
                                    ->label('Código do Banco')
                                    ->placeholder('001, 237, 341...')
                                    ->helperText('Ex: 001 (BB), 237 (Bradesco), 341 (Itaú)')
                                    ->maxLength(3),

                                Forms\Components\TextInput::make('bank_agency')
                                    ->label('Agência')
                                    ->placeholder('0001')
                                    ->maxLength(10),

                                Forms\Components\TextInput::make('bank_branch_digit')
                                    ->label('Dígito Agência')
                                    ->placeholder('0')
                                    ->maxLength(2),
                            ])
                            ->visible(fn (Forms\Get $get) => $get('payment_gateway') === 'pagarme'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('bank_account')
                                    ->label('Conta')
                                    ->placeholder('00000001')
                                    ->maxLength(20),

                                Forms\Components\TextInput::make('bank_account_digit')
                                    ->label('Dígito Conta')
                                    ->placeholder('0')
                                    ->maxLength(2),

                                Forms\Components\Select::make('bank_account_type')
                                    ->label('Tipo de Conta')
                                    ->options([
                                        'checking' => 'Conta Corrente',
                                        'savings' => 'Conta Poupança',
                                    ])
                                    ->default('checking'),
                            ])
                            ->visible(fn (Forms\Get $get) => $get('payment_gateway') === 'pagarme'),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('create_pagarme_recipient')
                                ->label('Criar Recebedor Pagar.me')
                                ->icon('heroicon-o-building-library')
                                ->color('success')
                                ->visible(fn (Forms\Get $get) => empty($get('pagarme_recipient_id')) && $get('payment_gateway') === 'pagarme')
                                ->requiresConfirmation()
                                ->action(function (Forms\Set $set, Forms\Get $get, $record) {
                                    try {
                                        $pagarmeService = app(\App\Services\PagarMeService::class);
                                        $result = $pagarmeService->createRecipient($record);

                                        if ($result && isset($result['id'])) {
                                            $record->update(['pagarme_recipient_id' => $result['id']]);
                                            $set('pagarme_recipient_id', $result['id']);

                                            \Filament\Notifications\Notification::make()
                                                ->title('Recebedor criado com sucesso!')
                                                ->success()
                                                ->send();
                                        } else {
                                            throw new \Exception('Erro ao criar recebedor');
                                        }
                                    } catch (\Exception $e) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Erro ao criar recebedor')
                                            ->body($e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                }),
                        ]),
                    ])->collapsible(),

                Forms\Components\Section::make('Domínios Personalizados')
                    ->schema([
                        Forms\Components\Repeater::make('custom_domains')
                            ->label('Domínios Extras')
                            ->relationship('domains')
                            ->schema([
                                Forms\Components\TextInput::make('domain')
                                    ->label('Domínio')
                                    ->required()
                                    ->helperText('Ex: pizzaria-bella.yumgo.com.br ou dominio-proprio.com.br'),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Adicionar domínio')
                            ->helperText('O domínio principal (slug.yumgo.com.br) é criado automaticamente')
                            ->collapsible(),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-restaurant.svg'))
                    ->size(50)
                    ->extraImgAttributes(['loading' => 'lazy', 'class' => 'object-cover']),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Tenant $record): string => $record->description ?? $record->slug),

                Tables\Columns\TextColumn::make('domains.domain')
                    ->label('Domínio')
                    ->badge()
                    ->separator(',')
                    ->url(fn (Tenant $record) => 'https://' . $record->domains->first()?->domain . '/painel')
                    ->openUrlInNewTab()
                    ->copyable()
                    ->copyMessage('Domínio copiado!'),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plano')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'trial' => 'info',
                        'inactive' => 'warning',
                        'suspended' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Ativo',
                        'trial' => 'Trial',
                        'inactive' => 'Inativo',
                        'suspended' => 'Suspenso',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label('Trial até')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn ($record) => $record && $record->status === 'trial'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('plan')
                    ->relationship('plan', 'name')
                    ->label('Plano'),
            ])
            ->actions([
                Tables\Actions\Action::make('access')
                    ->label('Acessar Painel')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn (Tenant $record) => 'https://' . $record->domains->first()?->domain . '/painel')
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('Isso vai DELETAR PERMANENTEMENTE todos os dados do restaurante!'),
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
            // Temporariamente desabilitado devido a conflito com multi-tenancy cross-schema
            // Usuários devem ser gerenciados pelo painel do restaurante: {slug}.yumgo.com.br/painel/users
            // RelationManagers\UsersRelationManager::class,
        ];
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
