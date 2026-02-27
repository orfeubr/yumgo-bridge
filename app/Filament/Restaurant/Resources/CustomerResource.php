<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\CustomerResource\Pages;
use App\Filament\Restaurant\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $slug = 'clientes';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?int $navigationSort = 4;

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

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->required()
                            ->mask('(99) 99999-9999')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->maxLength(14),

                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Data de Nascimento')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Cliente Ativo?')
                            ->required()
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Endereço')
                    ->schema([
                        Forms\Components\TextInput::make('address_zipcode')
                            ->label('CEP')
                            ->mask('99999-999')
                            ->maxLength(9),

                        Forms\Components\TextInput::make('address_street')
                            ->label('Rua/Avenida')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address_number')
                            ->label('Número')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('address_complement')
                            ->label('Complemento')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address_neighborhood')
                            ->label('Bairro')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address_city')
                            ->label('Cidade')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address_state')
                            ->label('Estado')
                            ->maxLength(2)
                            ->mask('AA'),
                    ])->columns(3),

                Forms\Components\Section::make('Programa de Fidelidade')
                    ->schema([
                        Forms\Components\TextInput::make('cashback_balance')
                            ->label('Saldo de Cashback')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->default(0),

                        Forms\Components\Select::make('loyalty_tier')
                            ->label('Nível de Fidelidade')
                            ->options([
                                'bronze' => '🥉 Bronze',
                                'silver' => '🥈 Prata',
                                'gold' => '🥇 Ouro',
                                'platinum' => '💎 Platina',
                            ])
                            ->default('bronze')
                            ->required(),

                        Forms\Components\TextInput::make('total_orders')
                            ->label('Total de Pedidos')
                            ->numeric()
                            ->disabled()
                            ->default(0),

                        Forms\Components\TextInput::make('total_spent')
                            ->label('Total Gasto')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->default(0),
                    ])->columns(4),
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
                    ->label('E-mail')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('E-mail copiado!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Telefone copiado!')
                    ->copyMessageDuration(1500),

                Tables\Columns\BadgeColumn::make('loyalty_tier')
                    ->label('Nível')
                    ->colors([
                        'secondary' => 'bronze',
                        'info' => 'silver',
                        'warning' => 'gold',
                        'success' => 'platinum',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bronze' => '🥉 Bronze',
                        'silver' => '🥈 Prata',
                        'gold' => '🥇 Ouro',
                        'platinum' => '💎 Platina',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('cashback_balance')
                    ->label('Cashback')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Pedidos')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Gasto')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('loyalty_tier')
                    ->label('Nível de Fidelidade')
                    ->options([
                        'bronze' => '🥉 Bronze',
                        'silver' => '🥈 Prata',
                        'gold' => '🥇 Ouro',
                        'platinum' => '💎 Platina',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos'),

                Tables\Filters\Filter::make('with_balance')
                    ->label('Com saldo de cashback')
                    ->query(fn (Builder $query): Builder => $query->where('cashback_balance', '>', 0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Excluir selecionados'),
                ]),
            ])
            ->emptyStateHeading('Nenhum cliente encontrado')
            ->emptyStateDescription('Comece adicionando seu primeiro cliente.')
            ->emptyStateIcon('heroicon-o-users');
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
