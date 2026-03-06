<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Assinaturas';

    protected static ?string $modelLabel = 'Assinatura';

    protected static ?string $pluralModelLabel = 'Assinaturas';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações da Assinatura')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('Restaurante')
                            ->relationship('tenant', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('plan_id')
                            ->label('Plano')
                            ->relationship('plan', 'name')
                            ->required()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Ativa',
                                'cancelled' => 'Cancelada',
                                'expired' => 'Expirada',
                                'suspended' => 'Suspensa',
                            ])
                            ->required()
                            ->default('active'),

                        Forms\Components\DatePicker::make('starts_at')
                            ->label('Data de Início')
                            ->required()
                            ->default(now()),

                        Forms\Components\DatePicker::make('ends_at')
                            ->label('Data de Término')
                            ->helperText('Deixe vazio para assinatura sem data de término'),

                        Forms\Components\DatePicker::make('cancelled_at')
                            ->label('Data de Cancelamento')
                            ->helperText('Preenchido automaticamente ao cancelar'),
                    ])
                    ->columns(2),
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
                    ->description(fn (Subscription $record): string => $record->tenant->slug ?? ''),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plano')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Starter' => 'info',
                        'Pro' => 'success',
                        'Enterprise' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'cancelled' => 'danger',
                        'expired' => 'warning',
                        'suspended' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Ativa',
                        'cancelled' => 'Cancelada',
                        'expired' => 'Expirada',
                        'suspended' => 'Suspensa',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Término')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Sem término'),

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
                        'active' => 'Ativa',
                        'cancelled' => 'Cancelada',
                        'expired' => 'Expirada',
                        'suspended' => 'Suspensa',
                    ]),

                Tables\Filters\SelectFilter::make('plan')
                    ->relationship('plan', 'name')
                    ->label('Plano'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Subscription $record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(fn (Subscription $record) => $record->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                    ])),
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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
