<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationGroup = '👥 Clientes';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Avaliações';

    protected static ?string $modelLabel = 'Avaliação';

    protected static ?string $pluralModelLabel = 'Avaliações';

    public static function getNavigationBadge(): ?string
    {
        // Badge: quantidade de reviews sem resposta
        return static::getModel()::whereNull('response')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações da Avaliação')
                    ->schema([
                        Forms\Components\TextInput::make('customer.name')
                            ->label('Cliente')
                            ->disabled(),

                        Forms\Components\TextInput::make('order_id')
                            ->label('Pedido #')
                            ->disabled(),

                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('rating')
                                    ->label('Avaliação Geral')
                                    ->suffix('⭐')
                                    ->disabled(),

                                Forms\Components\TextInput::make('food_rating')
                                    ->label('Comida')
                                    ->suffix('⭐')
                                    ->disabled(),

                                Forms\Components\TextInput::make('delivery_rating')
                                    ->label('Entrega')
                                    ->suffix('⭐')
                                    ->disabled(),

                                Forms\Components\TextInput::make('service_rating')
                                    ->label('Atendimento')
                                    ->suffix('⭐')
                                    ->disabled(),
                            ]),

                        Forms\Components\Textarea::make('comment')
                            ->label('Comentário do Cliente')
                            ->disabled()
                            ->rows(3),

                        Forms\Components\Toggle::make('is_public')
                            ->label('Avaliação Pública')
                            ->helperText('Desative para ocultar esta avaliação do site'),
                    ]),

                Forms\Components\Section::make('Resposta do Restaurante')
                    ->schema([
                        Forms\Components\Textarea::make('response')
                            ->label('Sua Resposta')
                            ->placeholder('Escreva uma resposta para o cliente...')
                            ->rows(4)
                            ->maxLength(500),

                        Forms\Components\Placeholder::make('responded_at')
                            ->label('Respondido em')
                            ->content(fn ($record) => $record?->responded_at?->format('d/m/Y H:i') ?? 'Ainda não respondido'),
                    ])
                    ->collapsed(fn ($record) => !$record?->response),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_id')
                    ->label('Pedido')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Avaliação')
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Comentário')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('Pública')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('response')
                    ->label('Respondida')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->response))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->label('Avaliação')
                    ->options([
                        5 => '⭐⭐⭐⭐⭐ (5 estrelas)',
                        4 => '⭐⭐⭐⭐ (4 estrelas)',
                        3 => '⭐⭐⭐ (3 estrelas)',
                        2 => '⭐⭐ (2 estrelas)',
                        1 => '⭐ (1 estrela)',
                    ]),

                Tables\Filters\TernaryFilter::make('response')
                    ->label('Respondida')
                    ->nullable()
                    ->trueLabel('Com resposta')
                    ->falseLabel('Sem resposta')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('response'),
                        false: fn (Builder $query) => $query->whereNull('response'),
                    ),

                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Visibilidade')
                    ->nullable()
                    ->trueLabel('Públicas')
                    ->falseLabel('Privadas'),
            ])
            ->actions([
                Tables\Actions\Action::make('respond')
                    ->label('Responder')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->visible(fn ($record) => empty($record->response))
                    ->form([
                        Forms\Components\Textarea::make('response')
                            ->label('Sua Resposta')
                            ->required()
                            ->rows(4)
                            ->maxLength(500),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'response' => $data['response'],
                            'responded_at' => now(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Resposta enviada')
                            ->body('Sua resposta foi salva com sucesso!')
                            ->send();
                    }),

                Tables\Actions\Action::make('toggle_public')
                    ->label(fn ($record) => $record->is_public ? 'Tornar Privada' : 'Tornar Pública')
                    ->icon(fn ($record) => $record->is_public ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record->is_public ? 'warning' : 'success')
                    ->action(function ($record) {
                        $record->update(['is_public' => !$record->is_public]);

                        Notification::make()
                            ->success()
                            ->title('Visibilidade alterada')
                            ->body($record->is_public ? 'Avaliação agora é pública' : 'Avaliação agora é privada')
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('make_public')
                        ->label('Tornar Públicas')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_public' => true]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('make_private')
                        ->label('Tornar Privadas')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_public' => false]))
                        ->deselectRecordsAfterCompletion(),

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
            'index' => Pages\ListReviews::route('/'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['customer', 'order']);
    }
}
