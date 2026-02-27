<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TenantResource\Pages;
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

                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo do Restaurante')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                                '16:9',
                            ])
                            ->maxSize(2048)
                            ->directory('tenants/logos')
                            ->visibility('public')
                            ->helperText('Imagem do logo (máx. 2MB, formatos: JPG, PNG)')
                            ->columnSpanFull(),
                    ])->columns(2),

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

                Forms\Components\Section::make('Integração Asaas')
                    ->schema([
                        Forms\Components\TextInput::make('asaas_account_id')
                            ->label('ID da Conta Asaas')
                            ->helperText('Será preenchido automaticamente após criar sub-conta')
                            ->disabled()
                            ->dehydrated(true),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('create_asaas_account')
                                ->label('Criar Sub-conta Asaas')
                                ->icon('heroicon-o-building-library')
                                ->color('success')
                                ->visible(fn (Forms\Get $get) => empty($get('asaas_account_id')))
                                ->action(function (Forms\Set $set, Forms\Get $get) {
                                    // TODO: Integrar com Asaas API
                                    \Filament\Notifications\Notification::make()
                                        ->title('Funcionalidade em desenvolvimento')
                                        ->warning()
                                        ->send();
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
                    ->circular()
                    ->defaultImageUrl(asset('images/default-restaurant.png'))
                    ->size(50),

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
                    ->visible(fn ($record) => $record->status === 'trial'),

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
            //
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
