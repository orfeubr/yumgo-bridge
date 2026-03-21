<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\DeliveryDriverResource\Pages;
use App\Filament\Restaurant\Resources\DeliveryDriverResource\RelationManagers;
use App\Models\DeliveryDriver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryDriverResource extends Resource
{
    protected static ?string $model = DeliveryDriver::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = '🚚 Entregadores';
    protected static ?string $modelLabel = 'Entregador';
    protected static ?string $pluralModelLabel = 'Entregadores';
    protected static ?string $slug = 'entregadores';
    protected static ?string $navigationGroup = '🚚 Entregas';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Pessoais')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->mask('(99) 99999-9999')
                            ->placeholder('(00) 00000-0000'),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->placeholder('000.000.000-00'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Veículo')
                    ->schema([
                        Forms\Components\Select::make('vehicle_type')
                            ->label('Tipo de Veículo')
                            ->options([
                                'moto' => '🏍️ Moto',
                                'carro' => '🚗 Carro',
                                'bicicleta' => '🚲 Bicicleta',
                                'a_pe' => '🚶 A pé',
                            ])
                            ->default('moto'),

                        Forms\Components\TextInput::make('vehicle_plate')
                            ->label('Placa do Veículo')
                            ->mask('AAA-9*99')
                            ->placeholder('ABC-1234'),

                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto do Entregador')
                            ->image()
                            ->avatar()
                            ->maxSize(2048)
                            ->directory('delivery-drivers')
                            ->visibility('public')
                            ->imageEditor()
                            ->circleCropper(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configurações')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true)
                            ->helperText('Entregadores inativos não recebem novas entregas'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->description(fn (DeliveryDriver $record): string => $record->phone),

                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Veículo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'moto' => '🏍️ Moto',
                        'carro' => '🚗 Carro',
                        'bicicleta' => '🚲 Bicicleta',
                        'a_pe' => '🚶 A pé',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('vehicle_plate')
                    ->label('Placa')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('active_deliveries_count')
                    ->label('Entregas Ativas')
                    ->badge()
                    ->color('warning')
                    ->default(0),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Apenas Ativos')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos'),

                Tables\Filters\SelectFilter::make('vehicle_type')
                    ->label('Tipo de Veículo')
                    ->options([
                        'moto' => '🏍️ Moto',
                        'carro' => '🚗 Carro',
                        'bicicleta' => '🚲 Bicicleta',
                        'a_pe' => '🚶 A pé',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Futuramente: RelationManager de entregas
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryDrivers::route('/'),
            'create' => Pages\CreateDeliveryDriver::route('/create'),
            'edit' => Pages\EditDeliveryDriver::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
