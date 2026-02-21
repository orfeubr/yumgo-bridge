<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationGroup = 'Plataforma';
    
    protected static ?string $modelLabel = 'Assinatura';
    
    protected static ?string $pluralModelLabel = 'Assinaturas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->label('Restaurante')
                    ->relationship('tenant', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->data['name'] ?? $record->id)
                    ->required()
                    ->searchable(),
                
                Forms\Components\Select::make('plan_id')
                    ->label('Plano')
                    ->relationship('plan', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativa',
                        'canceled' => 'Cancelada',
                        'past_due' => 'Atrasada',
                        'trialing' => 'Trial',
                    ])
                    ->required()
                    ->default('trialing'),
                
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('Início')
                    ->required()
                    ->default(now()),
                
                Forms\Components\DateTimePicker::make('ends_at')
                    ->label('Término')
                    ->nullable(),
                
                Forms\Components\DateTimePicker::make('trial_ends_at')
                    ->label('Trial até')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.data.name')
                    ->label('Restaurante')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plano')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'trialing' => 'warning',
                        'canceled' => 'gray',
                        'past_due' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Ativa',
                        'trialing' => 'Trial',
                        'canceled' => 'Cancelada',
                        'past_due' => 'Atrasada',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Início')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Término')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->placeholder('Ativa'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativa',
                        'canceled' => 'Cancelada',
                        'past_due' => 'Atrasada',
                        'trialing' => 'Trial',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
