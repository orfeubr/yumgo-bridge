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
                        
                        Forms\Components\TextInput::make('data.name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                if (!$get('data.slug')) {
                                    $set('data.slug', Str::slug($state));
                                }
                            }),
                        
                        Forms\Components\TextInput::make('data.slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL amigável (ex: pizza-express)'),
                        
                        Forms\Components\TextInput::make('data.email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('data.phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(20),
                        
                        Forms\Components\Select::make('data.plan_id')
                            ->label('Plano')
                            ->relationship('plan', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('data.status')
                            ->label('Status')
                            ->options([
                                'trial' => 'Trial',
                                'active' => 'Ativo',
                                'inactive' => 'Inativo',
                                'suspended' => 'Suspenso',
                            ])
                            ->required()
                            ->default('trial'),
                        
                        Forms\Components\DateTimePicker::make('data.trial_ends_at')
                            ->label('Trial termina em')
                            ->default(now()->addDays(15)),
                    ])->columns(2),
                
                Forms\Components\Section::make('Integração Asaas')
                    ->schema([
                        Forms\Components\TextInput::make('data.asaas_account_id')
                            ->label('ID da Conta Asaas')
                            ->maxLength(255)
                            ->helperText('Será preenchido automaticamente após criação'),
                    ])->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('data.name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('data.email')
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
                
                Tables\Columns\TextColumn::make('data.status')
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
                
                Tables\Columns\TextColumn::make('data.trial_ends_at')
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
                    ])
                    ->attribute('data->status'),
                
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
