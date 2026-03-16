<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\OrderResource\Pages;
use App\Filament\Restaurant\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'pedidos';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Pedidos';
    protected static ?string $modelLabel = 'Pedido';
    protected static ?string $navigationGroup = '📦 Operações';
    protected static ?int $navigationSort = 1;

    // Forçar aparecer no menu
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    // Permitir visualização para todos usuários autenticados
    public static function canViewAny(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ===== CABEÇALHO: Cliente e Entrega =====
                Forms\Components\Section::make('📋 Cliente e Entrega')
                    ->description('Informações do cliente e endereço de entrega')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $customer = \App\Models\Customer::find($state);
                                    if ($customer) {
                                        // Preenche endereço
                                        $address = '';
                                        if ($customer->address) {
                                            $address = $customer->address . ', ' . $customer->number;
                                            if ($customer->complement) $address .= ' - ' . $customer->complement;
                                            if ($customer->neighborhood) $address .= ' - ' . $customer->neighborhood;
                                            if ($customer->city) $address .= ' - ' . $customer->city . '/' . $customer->state;
                                        }
                                        
                                        $set('delivery_address', $address);
                                        $set('delivery_neighborhood', $customer->neighborhood);
                                        
                                        // Busca taxa de entrega do bairro
                                        if ($customer->neighborhood) {
                                            $neighborhood = \App\Models\Neighborhood::where('name', $customer->neighborhood)
                                                ->where('is_active', true)
                                                ->first();

                                            if ($neighborhood) {
                                                $set('delivery_fee', $neighborhood->delivery_fee);
                                            }
                                        }
                                        
                                        // Aviso se não tem endereço
                                        if (!$customer->address) {
                                            \Filament\Notifications\Notification::make()
                                                ->warning()
                                                ->title('⚠️ Cliente sem endereço')
                                                ->body('Preencha o endereço abaixo ou clique no ✏️')
                                                ->send();
                                        }
                                    }
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('editAddress')
                                    ->icon('heroicon-o-pencil')
                                    ->tooltip('Editar Endereço')
                                    ->visible(fn (Forms\Get $get) => $get('customer_id'))
                                    ->modalHeading('Editar Endereço do Cliente')
                                    ->form([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('address')->label('Rua')->required(),
                                            Forms\Components\TextInput::make('number')->label('Nº')->required(),
                                            Forms\Components\TextInput::make('complement')->label('Compl.'),
                                            Forms\Components\TextInput::make('zipcode')->label('CEP')->mask('99999-999'),

                                            Forms\Components\Select::make('city')
                                                ->label('Cidade')
                                                ->options(function () {
                                                    return \App\Models\Neighborhood::where('is_active', true)
                                                        ->select('city')
                                                        ->distinct()
                                                        ->orderBy('city')
                                                        ->pluck('city', 'city');
                                                })
                                                ->searchable()
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    // Limpa o bairro ao trocar de cidade
                                                    $set('neighborhood', null);
                                                }),

                                            Forms\Components\Select::make('neighborhood')
                                                ->label('Bairro')
                                                ->options(function (callable $get) {
                                                    $city = $get('city');
                                                    if (!$city) {
                                                        return [];
                                                    }
                                                    return \App\Models\Neighborhood::where('is_active', true)
                                                        ->where('city', $city)
                                                        ->orderBy('name')
                                                        ->pluck('name', 'name');
                                                })
                                                ->searchable()
                                                ->required()
                                                ->disabled(fn (callable $get) => !$get('city'))
                                                ->helperText('Primeiro selecione a cidade'),
                                        ]),
                                    ])
                                    ->fillForm(fn (Forms\Get $get) => \App\Models\Customer::find($get('customer_id'))?->only(['address', 'number', 'complement', 'neighborhood', 'city', 'zipcode']) ?? [])
                                    ->action(function (array $data, Forms\Get $get, callable $set) {
                                        $customer = \App\Models\Customer::find($get('customer_id'));
                                        $customer->update($data);

                                        $address = $data['address'] . ', ' . $data['number'];
                                        if ($data['complement']) $address .= ' - ' . $data['complement'];
                                        if ($data['neighborhood']) $address .= ' - ' . $data['neighborhood'];
                                        if ($data['city']) $address .= ' - ' . $data['city'];

                                        $set('delivery_address', $address);
                                        $set('delivery_city', $data['city']);
                                        $set('delivery_neighborhood', $data['neighborhood']);

                                        // Atualiza taxa de entrega
                                        $neighborhood = \App\Models\Neighborhood::where('city', $data['city'])
                                            ->where('name', $data['neighborhood'])
                                            ->where('is_active', true)
                                            ->first();
                                        if ($neighborhood) {
                                            $set('delivery_fee', $neighborhood->delivery_fee);

                                            \Filament\Notifications\Notification::make()
                                                ->success()
                                                ->title('✅ Endereço atualizado')
                                                ->body('Taxa: R$ ' . number_format($neighborhood->delivery_fee, 2, ',', '.'))
                                                ->send();
                                        }
                                        
                                        \Filament\Notifications\Notification::make()->success()->title('✅ Salvo!')->send();
                                    })
                            )
                            ->createOptionForm([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('name')->label('Nome')->required()->columnSpan(2),
                                    Forms\Components\TextInput::make('phone')->label('Telefone')->mask('(99) 99999-9999')->required(),
                                    Forms\Components\TextInput::make('email')->label('Email')->email(),
                                    Forms\Components\TextInput::make('address')->label('Rua')->required(),
                                    Forms\Components\TextInput::make('number')->label('Nº')->required(),
                                    Forms\Components\TextInput::make('complement')->label('Complemento'),
                                    Forms\Components\TextInput::make('neighborhood')->label('Bairro')->required(),
                                    Forms\Components\TextInput::make('city')->label('Cidade')->required()->default(tenant()->address_city),
                                    Forms\Components\TextInput::make('zipcode')->label('CEP')->mask('99999-999'),
                                ]),
                            ])
                            ->createOptionUsing(fn (array $data) => \App\Models\Customer::create($data)->id)
                            ->createOptionModalHeading('Cadastrar Cliente')
                            ->columnSpan(2),
                        
                        Forms\Components\Textarea::make('delivery_address')
                            ->label('Endereço de Entrega')
                            ->rows(2)
                            ->columnSpan(2)
                            ->helperText('Preenche automaticamente ao selecionar cliente'),

                        Forms\Components\Select::make('delivery_city')
                            ->label('Cidade')
                            ->options(function () {
                                return \App\Models\Neighborhood::where('is_active', true)
                                    ->select('city')
                                    ->distinct()
                                    ->orderBy('city')
                                    ->pluck('city', 'city');
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Limpa o bairro ao trocar de cidade
                                $set('delivery_neighborhood', null);
                                $set('delivery_fee', 0);
                            })
                            ->helperText('Selecione a cidade (apenas cidades com bairros ativos)'),

                        Forms\Components\Select::make('delivery_neighborhood')
                            ->label('Bairro')
                            ->options(function (callable $get) {
                                $city = $get('delivery_city');
                                if (!$city) {
                                    return [];
                                }
                                return \App\Models\Neighborhood::where('is_active', true)
                                    ->where('city', $city)
                                    ->orderBy('name')
                                    ->pluck('name', 'name');
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->disabled(fn (callable $get) => !$get('delivery_city'))
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                if ($state) {
                                    $city = $get('delivery_city');
                                    $neighborhood = \App\Models\Neighborhood::where('city', $city)
                                        ->where('name', $state)
                                        ->where('is_active', true)
                                        ->first();
                                    if ($neighborhood) {
                                        $set('delivery_fee', $neighborhood->delivery_fee);

                                        \Filament\Notifications\Notification::make()
                                            ->success()
                                            ->title('✅ Taxa atualizada')
                                            ->body('R$ ' . number_format($neighborhood->delivery_fee, 2, ',', '.') . ' - ' . $neighborhood->delivery_time . ' min')
                                            ->send();
                                    }
                                }
                            })
                            ->helperText('Selecione o bairro de entrega (primeiro escolha a cidade)'),
                        
                        Forms\Components\Select::make('delivery_type')
                            ->label('Tipo de Entrega')
                            ->options([
                                'delivery' => '🚗 Entrega',
                                'pickup' => '🏃 Retirada',
                            ])
                            ->default('delivery')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'pickup') {
                                    $set('delivery_fee', 0);
                                }
                            }),
                        
                        Forms\Components\TextInput::make('estimated_time')
                            ->label('Tempo Estimado (min)')
                            ->numeric()
                            ->suffix('min')
                            ->default(30),
                    ])->columns(2)->collapsible(),
                
                // ===== Status e Pagamento =====
                Forms\Components\Section::make('📝 Status e Pagamento')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => '⏳ Pendente',
                                'confirmed' => '✅ Confirmado',
                                'preparing' => '👨‍🍳 Preparando',
                                'ready' => '📦 Pronto',
                                'out_for_delivery' => '🚗 Saiu para entrega',
                                'delivered' => '✅ Entregue',
                                'cancelled' => '❌ Cancelado',
                            ])
                            ->default('pending')
                            ->required(),
                        
                        Forms\Components\Select::make('payment_status')
                            ->label('Pagamento')
                            ->options([
                                'pending' => 'Pendente',
                                'paid' => 'Pago',
                                'failed' => 'Falhou',
                                'refunded' => 'Reembolsado',
                            ])
                            ->default('pending')
                            ->required(),
                        
                        Forms\Components\Select::make('payment_method')
                            ->label('Método')
                            ->options([
                                'pix' => '💰 PIX',
                                'credit_card' => '💳 Crédito',
                                'debit_card' => '💳 Débito',
                                'cash' => '💵 Dinheiro',
                            ]),
                        
                        Forms\Components\Textarea::make('customer_notes')
                            ->label('Observações do Cliente')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(3)->collapsible()->collapsed(),
                
                // ===== CORPO: Itens do Pedido =====
                Forms\Components\Section::make('🛒 Itens do Pedido')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produto')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $product = \App\Models\Product::find($state);
                                            if ($product) {
                                                $set('product_name', $product->name);
                                                $set('unit_price', $product->price);
                                                $set('quantity', 1);
                                                // Calcula subtotal inicial
                                                $set('subtotal', round($product->price * 1, 2));
                                            }
                                        }
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(3),
                                
                                Forms\Components\Hidden::make('product_name'),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qtd')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Calcula subtotal automaticamente
                                        $quantity = $state ?? 0;
                                        $unitPrice = $get('unit_price') ?? 0;
                                        $set('subtotal', round($quantity * $unitPrice, 2));
                                    })
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Preço Un.')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Calcula subtotal automaticamente
                                        $quantity = $get('quantity') ?? 0;
                                        $unitPrice = $state ?? 0;
                                        $set('subtotal', round($quantity * $unitPrice, 2));
                                    })
                                    ->columnSpan(2),

                                // Campo oculto que armazena o subtotal calculado
                                Forms\Components\Hidden::make('subtotal')
                                    ->default(0)
                                    ->dehydrated(),

                                Forms\Components\Placeholder::make('item_total')
                                    ->label('Total')
                                    ->content(fn (Forms\Get $get) => 'R$ ' . number_format(($get('quantity') ?? 0) * ($get('unit_price') ?? 0), 2, ',', '.'))
                                    ->columnSpan(1),
                                
                                Forms\Components\Textarea::make('notes')
                                    ->label('Observações')
                                    ->rows(1)
                                    ->columnSpanFull(),
                            ])
                            ->columns(7)
                            ->defaultItems(0)
                            ->addActionLabel('+ Adicionar Produto')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['product_name'] ?? 'Novo Item')
                            ->live()
                            ->columnSpanFull(),
                    ])->collapsible(),
                
                // ===== RODAPÉ: Valores =====
                Forms\Components\Section::make('💰 Valores')
                    ->schema([
                        Forms\Components\Placeholder::make('subtotal_calc')
                            ->label('Subtotal (Items)')
                            ->content(function (Forms\Get $get) {
                                $items = $get('items') ?? [];
                                $subtotal = 0;
                                foreach ($items as $item) {
                                    $subtotal += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                }
                                return 'R$ ' . number_format($subtotal, 2, ',', '.');
                            }),
                        
                        Forms\Components\TextInput::make('delivery_fee')
                            ->label('Taxa de Entrega')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0)
                            ->live()
                            ->helperText('Preenche automaticamente baseado no bairro'),

                        Forms\Components\TextInput::make('discount')
                            ->label('Desconto')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0)
                            ->live(),

                        Forms\Components\TextInput::make('cashback_used')
                            ->label('Cashback Usado')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0)
                            ->live(),
                        
                        Forms\Components\Placeholder::make('total_calc')
                            ->label('💵 TOTAL')
                            ->content(function (Forms\Get $get) {
                                $items = $get('items') ?? [];
                                $subtotal = 0;
                                foreach ($items as $item) {
                                    $subtotal += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                }
                                
                                $total = $subtotal 
                                    + ($get('delivery_fee') ?? 0) 
                                    - ($get('discount') ?? 0) 
                                    - ($get('cashback_used') ?? 0);
                                
                                return 'R$ ' . number_format(max(0, $total), 2, ',', '.');
                            })
                            ->extraAttributes(['class' => 'text-2xl font-bold text-green-600']),
                        
                        Forms\Components\Placeholder::make('cashback_calc')
                            ->label('Cashback a Ganhar')
                            ->content(function (Forms\Get $get) {
                                $customerId = $get('customer_id');
                                if (!$customerId) return 'R$ 0,00';
                                
                                $customer = \App\Models\Customer::find($customerId);
                                $settings = \App\Models\CashbackSettings::first();
                                
                                if (!$settings || !$settings->is_active) return 'R$ 0,00';
                                
                                $items = $get('items') ?? [];
                                $subtotal = 0;
                                foreach ($items as $item) {
                                    $subtotal += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                }
                                
                                // Busca % baseado no tier do cliente
                                $tier = $customer->loyalty_tier ?? 'bronze';
                                $percentage = match($tier) {
                                    'bronze' => $settings->bronze_percentage ?? 2,
                                    'silver' => $settings->silver_percentage ?? 3.5,
                                    'gold' => $settings->gold_percentage ?? 5,
                                    'platinum' => $settings->platinum_percentage ?? 7,
                                    default => 2,
                                };
                                
                                $cashback = ($subtotal * $percentage) / 100;
                                
                                return 'R$ ' . number_format($cashback, 2, ',', '.') . " ({$percentage}%)";
                            })
                            ->helperText('Calculado automaticamente baseado no nível do cliente'),
                        
                        // Hidden fields para salvar no banco
                        Forms\Components\Hidden::make('subtotal')
                            ->dehydrateStateUsing(function (Forms\Get $get) {
                                $items = $get('items') ?? [];
                                $subtotal = 0;
                                foreach ($items as $item) {
                                    $subtotal += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                }
                                return $subtotal;
                            }),
                        
                        Forms\Components\Hidden::make('total')
                            ->dehydrateStateUsing(function (Forms\Get $get) {
                                $items = $get('items') ?? [];
                                $subtotal = 0;
                                foreach ($items as $item) {
                                    $subtotal += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                }
                                
                                $total = $subtotal 
                                    + ($get('delivery_fee') ?? 0) 
                                    - ($get('discount') ?? 0) 
                                    - ($get('cashback_used') ?? 0);
                                
                                return max(0, $total);
                            }),
                        
                        Forms\Components\Hidden::make('cashback_earned')
                            ->dehydrateStateUsing(function (Forms\Get $get) {
                                $customerId = $get('customer_id');
                                if (!$customerId) return 0;
                                
                                $customer = \App\Models\Customer::find($customerId);
                                $settings = \App\Models\CashbackSettings::first();
                                
                                if (!$settings || !$settings->is_active) return 0;
                                
                                $items = $get('items') ?? [];
                                $subtotal = 0;
                                foreach ($items as $item) {
                                    $subtotal += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                }
                                
                                $tier = $customer->loyalty_tier ?? 'bronze';
                                $percentage = match($tier) {
                                    'bronze' => $settings->bronze_percentage ?? 2,
                                    'silver' => $settings->silver_percentage ?? 3.5,
                                    'gold' => $settings->gold_percentage ?? 5,
                                    'platinum' => $settings->platinum_percentage ?? 7,
                                    default => 2,
                                };
                                
                                return ($subtotal * $percentage) / 100;
                            }),
                    ])->columns(3),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('#')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'confirmed',
                        'primary' => 'preparing',
                        'info' => 'ready',
                        'success' => ['delivered', 'out_for_delivery'],
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => '⏳ Pendente',
                        'confirmed' => '✅ Confirmado',
                        'preparing' => '👨‍🍳 Preparando',
                        'ready' => '📦 Pronto',
                        'out_for_delivery' => '🚗 Saindo',
                        'delivered' => '✅ Entregue',
                        'cancelled' => '❌ Cancelado',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Pagamento')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'secondary' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendente',
                        'paid' => 'Pago',
                        'failed' => 'Falhou',
                        'refunded' => 'Reembolsado',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Método')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pix' => '💰 PIX',
                        'credit_card' => '💳 Crédito',
                        'debit_card' => '💳 Débito',
                        'cash' => '💵 Dinheiro',
                        default => $state ?? 'N/A',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'confirmed' => 'Confirmado',
                        'preparing' => 'Preparando',
                        'ready' => 'Pronto',
                        'out_for_delivery' => 'Saindo',
                        'delivered' => 'Entregue',
                        'cancelled' => 'Cancelado',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Pagamento')
                    ->options([
                        'pending' => 'Pendente',
                        'paid' => 'Pago',
                        'failed' => 'Falhou',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('De'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Action para reimprimir pedido (v1.7.0)
                Tables\Actions\Action::make('reprint')
                    ->label('Reimprimir')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->visible(fn (Order $record) => $record->payment_status === 'paid')
                    ->requiresConfirmation()
                    ->modalHeading('Reimprimir Pedido')
                    ->modalDescription(fn (Order $record) => "Deseja reimprimir o pedido #{$record->order_number}? O cupom será enviado para as impressoras configuradas.")
                    ->modalSubmitActionLabel('Sim, Reimprimir')
                    ->action(function (Order $record) {
                        try {
                            // Dispara evento WebSocket para impressão
                            // ⭐ forceReprint = true (ignora proteção de duplicação)
                            event(new \App\Events\NewOrderEvent($record, forceReprint: true));

                            \Filament\Notifications\Notification::make()
                                ->title('Pedido reenviado para impressão!')
                                ->success()
                                ->body("O pedido #{$record->order_number} foi enviado para as impressoras.")
                                ->send();

                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erro ao reimprimir')
                                ->danger()
                                ->body('Erro: ' . $e->getMessage())
                                ->send();
                        }
                    }),
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
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /**
     * Verifica se pode criar pedido (limite mensal de plano)
     */
    public static function canCreate(): bool
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return false;
        }

        // Verifica se pode criar pedido baseado no plano
        if (!$tenant->canCreateOrder()) {
            // Notificar usuário sobre limite atingido
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('⚠️ Limite de Pedidos Atingido')
                ->body('Você atingiu o limite de pedidos deste mês. Faça upgrade para processar mais pedidos.')
                ->persistent()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('upgrade')
                        ->label('🚀 Fazer Upgrade')
                        ->url(route('filament.restaurant.pages.manage-subscription'))
                        ->markAsRead(),
                ])
                ->send();

            return false;
        }

        return true;
    }

    /**
     * Retorna badge com contador (exibe limite mensal)
     */
    public static function getNavigationBadge(): ?string
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return null;
        }

        $subscription = $tenant->activeSubscription();

        if (!$subscription) {
            return null;
        }

        $maxOrders = $subscription->plan->max_orders_per_month ?? null;

        // Se ilimitado, não exibe badge
        if ($maxOrders === null) {
            return null;
        }

        // Contar pedidos deste mês
        $currentCount = \App\Models\Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return "{$currentCount}/{$maxOrders}";
    }

    /**
     * Cor do badge baseado no uso mensal
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return null;
        }

        $subscription = $tenant->activeSubscription();

        if (!$subscription || !$subscription->plan->max_orders_per_month) {
            return null;
        }

        $maxOrders = $subscription->plan->max_orders_per_month;
        $currentCount = \App\Models\Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $percentage = ($currentCount / $maxOrders) * 100;

        return match (true) {
            $percentage >= 100 => 'danger',
            $percentage >= 80 => 'warning',
            default => 'info',
        };
    }
}
