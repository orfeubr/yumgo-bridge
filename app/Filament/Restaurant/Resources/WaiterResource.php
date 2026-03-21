<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\WaiterResource\Pages;
use App\Models\Waiter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WaiterResource extends Resource
{
    protected static ?string $model = Waiter::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = '👨‍🍳 Garçons';
    protected static ?string $modelLabel = 'Garçom';
    protected static ?string $pluralModelLabel = 'Garçons';
    protected static ?string $slug = 'garcons';
    protected static ?string $navigationGroup = '🏪 Pedidos Presenciais';
    protected static ?int $navigationSort = 2;

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
                            ->mask('(99) 99999-9999')
                            ->placeholder('(00) 00000-0000'),

                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto')
                            ->image()
                            ->avatar()
                            ->maxSize(2048)
                            ->directory('waiters')
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
                            ->helperText('Garçons inativos não aparecem nas opções de atendimento'),

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
                    ->description(fn (Waiter $record): string => $record->phone ?? ''),

                Tables\Columns\TextColumn::make('active_orders_count')
                    ->label('Pedidos Ativos')
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
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListWaiters::route('/'),
            'create' => Pages\CreateWaiter::route('/create'),
            'edit' => Pages\EditWaiter::route('/{record}/edit'),
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
