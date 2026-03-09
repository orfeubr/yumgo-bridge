<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\ProductResource\Pages;
use App\Filament\Restaurant\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $slug = 'produtos';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Produtos';
    protected static ?string $modelLabel = 'Produto';
    protected static ?string $navigationGroup = 'Produtos';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Categoria')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome da Categoria')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Str::slug($state))),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('order')
                                    ->label('Ordem de Exibição')
                                    ->helperText('Número que define a posição da categoria (menor número = aparece primeiro)')
                                    ->required()
                                    ->numeric()
                                    ->default(99)
                                    ->minValue(0),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Categoria Ativa?')
                                    ->default(true),
                            ]),

                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Produto')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Preço e Estoque')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Preço Base')
                            ->required()
                            ->numeric()
                            ->prefix('R$')
                            ->minValue(0)
                            ->helperText('Preço padrão do produto (sem variação)'),

                        Forms\Components\TextInput::make('preparation_time')
                            ->label('Tempo de Preparo (min)')
                            ->numeric()
                            ->suffix('min')
                            ->minValue(0)
                            ->default(30),

                        Forms\Components\Toggle::make('has_stock_control')
                            ->label('Controlar Estoque?')
                            ->live()
                            ->default(false),

                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Quantidade em Estoque')
                            ->numeric()
                            ->visible(fn ($get) => $get('has_stock_control')),

                        Forms\Components\TextInput::make('min_stock_alert')
                            ->label('Alerta de Estoque Mínimo')
                            ->numeric()
                            ->visible(fn ($get) => $get('has_stock_control')),
                    ])->columns(2),

                Forms\Components\Section::make('Variações de Tamanho')
                    ->description('Adicione diferentes tamanhos/versões do produto (Ex: 350ml, 2L, Pequeno, Médio, Grande)')
                    ->schema([
                        Forms\Components\Repeater::make('variations')
                            ->label('Variações')
                            ->relationship('variations')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome')
                                    ->placeholder('Ex: 350ml, 2L, Pequeno, Grande')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('final_price')
                                    ->label('Preço')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->required()
                                    ->step(0.01)
                                    ->reactive()
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record && isset($record->price_modifier) && isset($record->product)) {
                                            $basePrice = $record->product->price ?? 0;
                                            $finalPrice = $basePrice + $record->price_modifier;
                                            $component->state($finalPrice);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, $livewire) {
                                        $basePrice = $livewire->data['price'] ?? 0;
                                        $finalPrice = floatval($state ?? 0);
                                        $modifier = $finalPrice - $basePrice;
                                        $set('price_modifier', $modifier);
                                    })
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('serves')
                                    ->label('Serve')
                                    ->numeric()
                                    ->suffix('pessoas')
                                    ->placeholder('Ex: 2')
                                    ->minValue(1),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Ativo?')
                                    ->default(true)
                                    ->inline(false),

                                Forms\Components\Hidden::make('price_modifier')
                                    ->default(0),

                                Forms\Components\Hidden::make('modifier_type')
                                    ->default('fixed'),

                                Forms\Components\Hidden::make('order')
                                    ->default(0),
                            ])
                            ->orderColumn('order')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Nova variação')
                            ->defaultItems(0)
                            ->addActionLabel('+ Adicionar Tamanho')
                            ->columns(4),
                    ])->collapsible(),

                Forms\Components\Section::make('Imagens')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Imagem Principal')
                            ->disk('public')
                            ->directory('products')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                                '16:9',
                            ])
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('800')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('image_url')
                            ->label('Link da Imagem Principal')
                            ->content(function ($record) {
                                if (!$record || !$record->image) {
                                    return 'Nenhuma imagem cadastrada';
                                }
                                $url = route('stancl.tenancy.asset', ['path' => $record->image]);
                                return new \Illuminate\Support\HtmlString(
                                    '<div class="space-y-2">'.
                                    '<code class="block p-2 bg-gray-100 rounded text-xs break-all">' . $url . '</code>' .
                                    '<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 text-sm font-medium">' .
                                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>' .
                                    'Abrir em nova aba' .
                                    '</a>' .
                                    '</div>'
                                );
                            })
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record && $record->image),

                        Forms\Components\FileUpload::make('images')
                            ->label('Galeria de Imagens')
                            ->disk('public')
                            ->directory('products')
                            ->multiple()
                            ->image()
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                            ->maxFiles(5)
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('1200')
                            ->imageResizeTargetHeight('1200')
                            ->columnSpanFull(),
                    ])->columns(1),

                Forms\Components\Section::make('Configurações')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Ordem de Exibição')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Produto Ativo?')
                            ->default(true),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Produto em Destaque?')
                            ->default(false),

                        Forms\Components\Toggle::make('is_pizza')
                            ->label('🍕 É Pizza?')
                            ->helperText('Se marcado, abrirá o modal de personalização')
                            ->live()
                            ->default(false),

                        Forms\Components\Toggle::make('is_alcoholic')
                            ->label('🍺 Bebida Alcoólica?')
                            ->helperText('Identifica bebidas alcoólicas')
                            ->default(false),

                        Forms\Components\Toggle::make('suggest_in_cart')
                            ->label('💡 Sugerir no Carrinho?')
                            ->helperText('Será sugerido para clientes no carrinho')
                            ->default(false),
                    ])->columns(4),

                // ========== INFORMAÇÕES FISCAIS ==========
                Forms\Components\Section::make('Informações Fiscais')
                    ->description('Classificação fiscal do produto para emissão de NFC-e')
                    ->schema([
                        Forms\Components\Select::make('categoria_tributaria')
                            ->label('🏷️ Categoria Tributária')
                            ->placeholder('Selecione para preencher automaticamente')
                            ->options([
                                'alimentos_produzidos' => '🍕 Alimentos Produzidos (Pizzas, Lanches, Marmitas)',
                                'bebidas_gerais' => '🥤 Bebidas Gerais (Refrigerantes, Sucos)',
                                'bebidas_alcoolicas' => '🍺 Bebidas Alcoólicas (Cervejas, Vinhos)',
                                'aguas' => '💧 Águas (Mineral, Gaseificada)',
                                'sorvetes' => '🍦 Sorvetes e Picolés',
                                'doces' => '🍰 Doces e Sobremesas',
                                'paes' => '🥖 Pães e Produtos de Padaria',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $classificacoes = [
                                    'alimentos_produzidos' => ['ncm' => '19059090', 'cfop' => '5405', 'cest' => null],
                                    'bebidas_gerais' => ['ncm' => '22029900', 'cfop' => '5405', 'cest' => '0300700'],
                                    'bebidas_alcoolicas' => ['ncm' => '22030000', 'cfop' => '5405', 'cest' => '0300500'],
                                    'aguas' => ['ncm' => '22021000', 'cfop' => '5405', 'cest' => '0300100'],
                                    'sorvetes' => ['ncm' => '21050000', 'cfop' => '5405', 'cest' => null],
                                    'doces' => ['ncm' => '19059090', 'cfop' => '5405', 'cest' => null],
                                    'paes' => ['ncm' => '19059010', 'cfop' => '5405', 'cest' => null],
                                ];

                                if ($state && isset($classificacoes[$state])) {
                                    $set('ncm', $classificacoes[$state]['ncm']);
                                    $set('cfop', $classificacoes[$state]['cfop']);
                                    $set('cest', $classificacoes[$state]['cest']);

                                    \Filament\Notifications\Notification::make()
                                        ->success()
                                        ->title('✅ Classificação aplicada!')
                                        ->body('NCM, CFOP e CEST foram preenchidos automaticamente.')
                                        ->send();
                                }
                            })
                            ->helperText('Selecione uma categoria para preencher NCM, CFOP e CEST automaticamente')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('ia_divider')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="flex items-center my-4">
                                    <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
                                    <span class="px-3 text-sm text-gray-500 dark:text-gray-400 font-medium">OU</span>
                                    <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
                                </div>'
                            ))
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('ia_button')
                            ->label('')
                            ->content(function ($get, $set, $livewire) {
                                $productName = $get('name') ?? '';
                                $categoryId = $get('category_id');

                                $buttonDisabled = empty($productName) ? 'disabled' : '';
                                $buttonClass = empty($productName)
                                    ? 'opacity-50 cursor-not-allowed bg-gray-400'
                                    : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 cursor-pointer';

                                $onClickAction = empty($productName)
                                    ? "alert('⚠️ Preencha o nome do produto primeiro!')"
                                    : "\$wire.mountFormComponentAction('data.ia_button', 'classificar_ia')";

                                return new \Illuminate\Support\HtmlString(
                                    "<button
                                        type=\"button\"
                                        {$buttonDisabled}
                                        onclick=\"{$onClickAction}\"
                                        class=\"w-full flex items-center justify-center gap-3 px-6 py-4 text-sm font-semibold text-white {$buttonClass} rounded-lg shadow-md hover:shadow-lg transition-all duration-200\"
                                    >
                                        <svg class=\"w-5 h-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13 10V3L4 14h7v7l9-11h-7z\"/>
                                        </svg>
                                        <span>🤖 Classificar com IA Personalizada (Tributa AI)</span>
                                        <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 5l7 7-7 7\"/>
                                        </svg>
                                    </button>
                                    <p class=\"mt-2 text-xs text-center text-gray-600 dark:text-gray-400\">
                                        💡 Use para produtos muito específicos que não se encaixam nas categorias acima
                                    </p>"
                                );
                            })
                            ->registerActions([
                                Forms\Components\Actions\Action::make('classificar_ia')
                                    ->modalHeading('🤖 Classificação Fiscal com IA')
                                    ->modalDescription('A IA do Tributa AI analisará seu produto e sugerirá a melhor classificação')
                                    ->modalWidth('3xl')
                                    ->form(function ($get) {
                                        // Buscar classificação da IA
                                        try {
                                            $tributaAi = app(\App\Services\TributaAiService::class);

                                            if (!$tributaAi->isAvailable()) {
                                                return [
                                                    Forms\Components\Placeholder::make('no_token')
                                                        ->content(new \Illuminate\Support\HtmlString(
                                                            '<div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700">
                                                                <p class="text-sm text-red-700 dark:text-red-300">
                                                                    ❌ <strong>Token Tributa AI não configurado</strong><br>
                                                                    Configure o token no Painel Central em:<br>
                                                                    <strong>Sistema > Configurações da Plataforma</strong>
                                                                </p>
                                                            </div>'
                                                        ))
                                                ];
                                            }

                                            $productName = $get('name');
                                            $categoryId = $get('category_id');
                                            $categoryName = null;

                                            if ($categoryId) {
                                                $category = \App\Models\Category::find($categoryId);
                                                $categoryName = $category?->name;
                                            }

                                            // Classificar com IA
                                            $result = $tributaAi->classificarProduto($productName, $categoryName);

                                            // Salvar na sessão para usar depois
                                            session()->put('tributaai_last_classification', $result);

                                            $confidence = $result['confianca'] ?? 0;
                                            $confidenceBadge = $confidence >= 80 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                                                               ($confidence >= 60 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' :
                                                               'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300');

                                            return [
                                                // Disclaimer
                                                Forms\Components\Placeholder::make('disclaimer')
                                                    ->content(new \Illuminate\Support\HtmlString(
                                                        '<div class="p-4 mb-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700">
                                                            <div class="flex items-start gap-3">
                                                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                                </svg>
                                                                <div class="flex-1">
                                                                    <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2">
                                                                        ⚠️ Importante: Classificação Fiscal
                                                                    </h3>
                                                                    <div class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                                                                        <p>• <strong>A IA é ferramenta de APOIO</strong> - Pode conter erros</p>
                                                                        <p>• <strong>Você é responsável</strong> - Revise cuidadosamente</p>
                                                                        <p>• <strong>Consulte contador</strong> - Para validar a classificação</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>'
                                                    )),

                                                // Sugestão da IA
                                                Forms\Components\Placeholder::make('ia_suggestion')
                                                    ->content(new \Illuminate\Support\HtmlString(
                                                        '<div class="p-4 mb-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700">
                                                            <div class="flex items-center justify-between mb-3">
                                                                <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200">
                                                                    🤖 Sugestão da IA Tributa AI
                                                                </h4>
                                                                <span class="px-2 py-1 text-xs font-medium rounded-full ' . $confidenceBadge . '">
                                                                    ' . $confidence . '% de confiança
                                                                </span>
                                                            </div>
                                                            <div class="grid grid-cols-3 gap-4 mb-3">
                                                                <div>
                                                                    <span class="text-xs font-medium text-blue-700 dark:text-blue-300">NCM:</span>
                                                                    <p class="text-sm font-mono font-bold text-blue-900 dark:text-blue-100">' . $result['ncm'] . '</p>
                                                                </div>
                                                                <div>
                                                                    <span class="text-xs font-medium text-blue-700 dark:text-blue-300">CFOP:</span>
                                                                    <p class="text-sm font-mono font-bold text-blue-900 dark:text-blue-100">' . $result['cfop'] . '</p>
                                                                </div>
                                                                <div>
                                                                    <span class="text-xs font-medium text-blue-700 dark:text-blue-300">CEST:</span>
                                                                    <p class="text-sm font-mono font-bold text-blue-900 dark:text-blue-100">' . ($result['cest'] ?: 'N/A') . '</p>
                                                                </div>
                                                            </div>
                                                            <div class="pt-3 border-t border-blue-200 dark:border-blue-700">
                                                                <span class="text-xs font-medium text-blue-700 dark:text-blue-300">Descrição NCM:</span>
                                                                <p class="text-sm text-blue-800 dark:text-blue-200 mt-1">' . $result['descricao_ncm'] . '</p>
                                                            </div>
                                                        </div>
                                                        <p class="text-xs text-gray-600 dark:text-gray-400 italic mb-4">
                                                            💡 Revise e ajuste os valores abaixo se necessário
                                                        </p>'
                                                    )),

                                                // Campos editáveis
                                                Forms\Components\TextInput::make('ncm_sugerido')
                                                    ->label('NCM (Revise e ajuste se necessário)')
                                                    ->default($result['ncm'])
                                                    ->required()
                                                    ->maxLength(8)
                                                    ->helperText('8 dígitos numéricos'),

                                                Forms\Components\TextInput::make('cfop_sugerido')
                                                    ->label('CFOP (Revise e ajuste se necessário)')
                                                    ->default($result['cfop'])
                                                    ->required()
                                                    ->maxLength(4)
                                                    ->helperText('4 dígitos numéricos'),

                                                Forms\Components\TextInput::make('cest_sugerido')
                                                    ->label('CEST (Opcional - Revise e ajuste)')
                                                    ->default($result['cest'])
                                                    ->maxLength(7)
                                                    ->helperText('7 dígitos (deixe vazio se não aplicável)'),

                                                // Checkbox obrigatório
                                                Forms\Components\Checkbox::make('confirmo_revisao')
                                                    ->label('Li e revisei as sugestões da IA')
                                                    ->helperText('Estou ciente que a IA é ferramenta de apoio e que sou responsável pela classificação')
                                                    ->accepted()
                                                    ->required()
                                                    ->validationMessages([
                                                        'accepted' => 'Você precisa confirmar que revisou as sugestões',
                                                    ]),

                                                // Recomendação final
                                                Forms\Components\Placeholder::make('recomendacao')
                                                    ->content(new \Illuminate\Support\HtmlString(
                                                        '<div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                                                💼 <strong>Recomendação:</strong> Consulte um contador para validar a classificação fiscal. A classificação incorreta pode resultar em problemas com a Receita Federal.
                                                            </p>
                                                        </div>'
                                                    )),
                                            ];

                                        } catch (\Exception $e) {
                                            return [
                                                Forms\Components\Placeholder::make('error')
                                                    ->content(new \Illuminate\Support\HtmlString(
                                                        '<div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700">
                                                            <p class="text-sm text-red-700 dark:text-red-300">
                                                                ❌ <strong>Erro ao obter classificação:</strong><br>
                                                                ' . $e->getMessage() . '
                                                            </p>
                                                        </div>'
                                                    ))
                                            ];
                                        }
                                    })
                                    ->modalSubmitActionLabel('✅ Aplicar Classificação')
                                    ->action(function ($data, $set) {
                                        // Aplicar classificação
                                        $set('ncm', $data['ncm_sugerido']);
                                        $set('cfop', $data['cfop_sugerido']);
                                        $set('cest', $data['cest_sugerido'] ?: null);

                                        // Limpar sessão
                                        session()->forget('tributaai_last_classification');

                                        // Notificação
                                        \Filament\Notifications\Notification::make()
                                            ->success()
                                            ->title('✅ Classificação aplicada!')
                                            ->body('NCM, CFOP e CEST foram preenchidos. Não esqueça de salvar o produto!')
                                            ->send();
                                    })
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('campos_divider')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="my-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        📋 Campos de Classificação Fiscal:
                                    </p>
                                </div>'
                            ))
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('ncm')
                            ->label('NCM (8 dígitos)')
                            ->placeholder('19059090')
                            ->maxLength(8)
                            ->helperText('Nomenclatura Comum do Mercosul'),

                        Forms\Components\TextInput::make('cfop')
                            ->label('CFOP (4 dígitos)')
                            ->placeholder('5405')
                            ->maxLength(4)
                            ->default('5405')
                            ->helperText('Código Fiscal de Operações'),

                        Forms\Components\TextInput::make('cest')
                            ->label('CEST (7 dígitos - Opcional)')
                            ->placeholder('1700100')
                            ->maxLength(7)
                            ->helperText('Código Especificador da ST'),
                    ])->columns(3)->collapsible()->collapsed(false),

                Forms\Components\Section::make('Customização de Pizza')
                    ->schema([
                        Forms\Components\Toggle::make('allows_half_and_half')
                            ->label('Permite Meio a Meio?')
                            ->helperText('Cliente poderá escolher 2 sabores')
                            ->default(true),

                        Forms\Components\CheckboxList::make('available_sizes')
                            ->label('Tamanhos Disponíveis')
                            ->options([
                                'small' => 'Pequena (25cm - 4 fatias)',
                                'medium' => 'Média (30cm - 6 fatias)',
                                'large' => 'Grande (35cm - 8 fatias)',
                                'family' => 'Família (40cm - 12 fatias)',
                            ])
                            ->default(['small', 'medium', 'large', 'family'])
                            ->columns(2)
                            ->helperText('Selecione os tamanhos disponíveis'),

                        Forms\Components\CheckboxList::make('available_borders')
                            ->label('Bordas Disponíveis')
                            ->options([
                                'none' => 'Sem borda (Grátis)',
                                'catupiry' => 'Catupiry (+R$ 8,00)',
                                'cheddar' => 'Cheddar (+R$ 8,00)',
                                'chocolate' => 'Chocolate (+R$ 10,00)',
                            ])
                            ->default(['none', 'catupiry', 'cheddar', 'chocolate'])
                            ->columns(2)
                            ->helperText('Selecione as bordas disponíveis'),

                        Forms\Components\KeyValue::make('size_prices')
                            ->label('Preços deste Sabor por Tamanho')
                            ->helperText('Defina quanto custa ESTE sabor em cada tamanho. Ex: small => 25.00, medium => 35.00')
                            ->keyLabel('Tamanho (small/medium/large/family)')
                            ->valueLabel('Preço (R$)')
                            ->reorderable(false),

                        Forms\Components\KeyValue::make('border_prices')
                            ->label('Preços Personalizados de Bordas (Opcional)')
                            ->helperText('Deixe vazio para usar valores padrão. Ex: catupiry => 10.00')
                            ->keyLabel('Borda (catupiry/cheddar/chocolate)')
                            ->valueLabel('Preço (R$)')
                            ->reorderable(false),
                    ])
                    ->visible(fn ($get) => $get('is_pizza'))
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagem')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): string => $record->category?->name ?? ''),

                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Estoque')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (Product $record): string => match (true) {
                        !$record->has_stock_control => 'gray',
                        $record->stock_quantity === 0 => 'danger',
                        $record->stock_quantity <= ($record->min_stock_alert ?? 0) => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn (Product $record): string =>
                        !$record->has_stock_control ? 'N/A' : $record->stock_quantity
                    ),

                Tables\Columns\TextColumn::make('preparation_time')
                    ->label('Preparo')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destaque')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_alcoholic')
                    ->label('🍺 Alcoólica')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativo')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Destaque')
                    ->placeholder('Todos')
                    ->trueLabel('Em destaque')
                    ->falseLabel('Sem destaque'),

                Tables\Filters\TernaryFilter::make('has_stock_control')
                    ->label('Controle de Estoque')
                    ->placeholder('Todos')
                    ->trueLabel('Com controle')
                    ->falseLabel('Sem controle'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    /**
     * Verifica se pode criar produto (limite de plano)
     */
    public static function canCreate(): bool
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return false;
        }

        // Verifica se pode criar produto baseado no plano
        if (!$tenant->canCreateProduct()) {
            // Notificar usuário sobre limite atingido
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('⚠️ Limite de Produtos Atingido')
                ->body('Você atingiu o limite de produtos do seu plano. Faça upgrade para adicionar mais produtos.')
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
     * Retorna badge com contador (exibe limite)
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

        $maxProducts = $subscription->plan->max_products ?? null;

        // Se ilimitado, não exibe badge
        if ($maxProducts === null) {
            return null;
        }

        // Contar produtos
        $currentCount = \App\Models\Product::count();

        return "{$currentCount}/{$maxProducts}";
    }

    /**
     * Cor do badge baseado no uso
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return null;
        }

        $subscription = $tenant->activeSubscription();

        if (!$subscription || !$subscription->plan->max_products) {
            return null;
        }

        $maxProducts = $subscription->plan->max_products;
        $currentCount = \App\Models\Product::count();
        $percentage = ($currentCount / $maxProducts) * 100;

        return match (true) {
            $percentage >= 100 => 'danger',
            $percentage >= 80 => 'warning',
            default => 'primary',
        };
    }
}
