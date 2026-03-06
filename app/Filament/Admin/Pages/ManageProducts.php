<?php

namespace App\Filament\Admin\Pages;

use App\Models\Tenant;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Str;

class ManageProducts extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string $view = 'filament.admin.pages.manage-products';

    protected static ?string $navigationLabel = 'Produtos dos Restaurantes';

    protected static ?string $title = 'Gerenciar Produtos dos Restaurantes';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 7;

    public ?string $selectedTenantId = null;

    public function mount(): void
    {
        // Seleciona o primeiro tenant por padrão
        $firstTenant = Tenant::first();
        if ($firstTenant) {
            $this->selectedTenantId = $firstTenant->id;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->label('Selecione o Restaurante')
                    ->options(Tenant::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->selectedTenantId = $state;
                    })
                    ->default($this->selectedTenantId),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Produtos do Restaurante')
            ->query(function () {
                if (!$this->selectedTenantId) {
                    return Product::query()->whereRaw('false');
                }

                $tenant = Tenant::find($this->selectedTenantId);
                if (!$tenant) {
                    return Product::query()->whereRaw('false');
                }

                // Inicializar tenancy
                tenancy()->initialize($tenant);

                return Product::query()->with('category');
            })
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagem')
                    ->disk('public')
                    ->height(50),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_available')
                    ->label('Disponível')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Disponível')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('category_id')
                            ->label('Categoria')
                            ->options(function () {
                                if (!$this->selectedTenantId) return [];
                                $tenant = Tenant::find($this->selectedTenantId);
                                tenancy()->initialize($tenant);
                                return Category::pluck('name', 'id');
                            })
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3),

                        Forms\Components\TextInput::make('price')
                            ->label('Preço')
                            ->required()
                            ->numeric()
                            ->prefix('R$'),

                        Forms\Components\FileUpload::make('image')
                            ->label('Imagem')
                            ->image()
                            ->disk('public')
                            ->directory(fn () => 'tenant_' . $this->selectedTenantId . '/products'),

                        Forms\Components\Toggle::make('is_available')
                            ->label('Disponível')
                            ->default(true),
                    ])
                    ->before(function () {
                        if ($this->selectedTenantId) {
                            $tenant = Tenant::find($this->selectedTenantId);
                            tenancy()->initialize($tenant);
                        }
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function () {
                        if ($this->selectedTenantId) {
                            $tenant = Tenant::find($this->selectedTenantId);
                            tenancy()->initialize($tenant);
                        }
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Novo Produto')
                    ->form([
                        Forms\Components\Select::make('category_id')
                            ->label('Categoria')
                            ->options(function () {
                                if (!$this->selectedTenantId) return [];
                                $tenant = Tenant::find($this->selectedTenantId);
                                tenancy()->initialize($tenant);
                                return Category::pluck('name', 'id');
                            })
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3),

                        Forms\Components\TextInput::make('price')
                            ->label('Preço')
                            ->required()
                            ->numeric()
                            ->prefix('R$'),

                        Forms\Components\FileUpload::make('image')
                            ->label('Imagem')
                            ->image()
                            ->disk('public')
                            ->directory(fn () => 'tenant_' . $this->selectedTenantId . '/products'),

                        Forms\Components\Toggle::make('is_available')
                            ->label('Disponível')
                            ->default(true),
                    ])
                    ->before(function () {
                        if (!$this->selectedTenantId) {
                            \Filament\Notifications\Notification::make()
                                ->title('Selecione um restaurante primeiro')
                                ->danger()
                                ->send();
                            return;
                        }

                        $tenant = Tenant::find($this->selectedTenantId);
                        tenancy()->initialize($tenant);
                    })
                    ->after(function () {
                        \Filament\Notifications\Notification::make()
                            ->title('Produto criado com sucesso!')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function () {
                            if ($this->selectedTenantId) {
                                $tenant = Tenant::find($this->selectedTenantId);
                                tenancy()->initialize($tenant);
                            }
                        }),
                ]),
            ]);
    }
}
