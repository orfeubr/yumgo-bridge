<x-filament-panels::page>
    @livewire('restaurant.pizza-builder')

    {{-- ATALHOS REMOVIDOS: Causavam conflitos com navegador --}}
    {{-- Sistema otimizado para uso com mouse/touch --}}

    {{-- SOM DE FEEDBACK --}}
    <audio id="beep-sound" src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuFzvfVhzMHHGS56t2dQwwOVKzp67FoGAU7k9z1z4g0Bx1iuOngnkQNDlCp6uyxaRcFOY/Y8c6GOgcZX7Tp6aFSEQxMouH1v3AhBS6Gzfjtjj0IGV+1693CcSYFLojO9tOLOAcZXLPl6qNcFgxJneHytHEiBS2F0PbtjjwHGmG37OClUhIMRpzh8bVuIgUshdTx8IxBCBleue3fpFcSDkmf4PK9dCAELYfX8tGJPAcXYbTs7KRVEwxHnODvu3IcBSuFy/bbjjkII2C1593RiT0HHmS96+upUhEKSpzfuXYeBS2EyvbwkkEIFl636eShVhEMRp3e87VxIAUrlcz46YxBBxlft+viqVIRDEud3PO2ciMEK4NK8/OOMgYaXbbs5KdXEgtFn9vxunAjBSuBy/vrklIJFlux5/OgVBYLSJrb8r92IwUrgdT87I9CBRZds+XupFQVDE2c2fO8cSQEK4DO8e2RQQcWXLPm66JWFA1Mm97yt3UlBCuB0fXri0AJE1606+ikVhIOSp7d8LdyIgUrgdHz7I9DBRVbtOjqpFUSC0qb3PK5cSQELH/Q8+2RQQcUXLTl66JYEQxJnt/wvHEiBS2Bz/Xtj0AJFluz5euiVxILSp3c8bhwJQQrf9Dy9I9BBRVbtOjqpVYSC0mf3vC8ciMGK4DO8++RQQcWXbLm7KNXEg1Knt3ytnEiBSyAzvPtj0AJFluz5+uiVxIMSp3c8bhwJAQqf9Dz745ABRRds+rrpFYTDE2c3fG6cCQFK4DQ8e6PQQcXX7Pm66FZEQtInd3xu3EjBSuAz/Luj0EJFl2z5+qkVhINSZ7c8rhxIwQsf87y7o5BBRVbuenkpFYTDE2d3fG6cSQGK3/O8++OQggaXrPm66NYEg1Knt3wuXIjBCuAzvLuj0EIFl6z5uqkVxINSZ7c8rhwJAQqgM7z7o5ABRZes+fqpFYTDE2c3fG5cSQEK4DN8+6PQQgYXbPm66NYEgtKn9zxunAkBSyA0PPtkD8IGFyz5/OmVxIMR5/c8rpxIwUrgM7z7ZFBCBRcs+fso1cUDUqe3/C3ciQEKoDQ8eyPQQcXXbTm66VYEg1JntzwunIkBSyAzvPtkUAIE1y06POhWhALR5/b8rxxIwUsgNDy8I9ABxVbsujpolcSDUmd3fG6cyMHLIDO8+2PQQgTXLPm7KJZEg1Jntzxu3EjBiuAzvPuj0IJF12z5uujVxMMSJzd8bpxJQQsgc/z7o9AB"  preload="auto"></audio>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('play-beep', () => {
                document.getElementById('beep-sound')?.play();
            });
            Livewire.on('focusBarcode', () => {
                document.querySelector('[wire\\:model\\.live\\.debounce\\.300ms="barcode"]')?.focus();
            });
            Livewire.on('focusSearch', () => {
                document.querySelector('[wire\\:model\\.live\\.debounce\\.300ms="searchProduct"]')?.focus();
            });
        });
    </script>

    {{-- HEADER COM INDICADORES --}}
    <div class="mb-4 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-gray-800 dark:to-gray-700 rounded-lg p-3 border-2 border-primary-200 dark:border-primary-600">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🛒</span>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Frente de Caixa</h2>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Adicione produtos clicando nos cards ou usando o leitor de código de barras
                    </p>
                </div>
            </div>

            {{-- INDICADORES VISUAIS --}}
            <div class="flex items-center gap-2">
                @if($willPrint)
                    <div class="flex items-center gap-1 bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded-lg border border-green-300 dark:border-green-700">
                        <span class="text-lg">🖨️</span>
                        <span class="text-xs font-bold text-green-700 dark:text-green-300">Vai Imprimir</span>
                    </div>
                @endif

                @if($willEmitNfce)
                    <div class="flex items-center gap-1 bg-blue-100 dark:bg-blue-900/30 px-2 py-1 rounded-lg border border-blue-300 dark:border-blue-700">
                        <span class="text-lg">🧾</span>
                        <span class="text-xs font-bold text-blue-700 dark:text-blue-300">NFC-e Ativa</span>
                    </div>
                @else
                    <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700/30 px-2 py-1 rounded-lg border border-gray-300 dark:border-gray-600">
                        <span class="text-lg">📄</span>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400">NFC-e Inativa</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- PRODUTOS (8 colunas) -->
        <div class="lg:col-span-8 space-y-4">

            <!-- Busca MELHORADA com Código de Barras -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-2">
                <div class="flex gap-2 mb-2">
                    {{-- Código de Barras --}}
                    <div class="flex-1">
                        <x-filament::input.wrapper>
                            <x-filament::input
                                wire:model.live.debounce.300ms="barcode"
                                wire:keydown.enter="scanBarcode"
                                type="text"
                                placeholder="📷 Código de Barras"
                                class="text-sm py-1 font-mono"
                                autofocus
                            />
                        </x-filament::input.wrapper>
                    </div>

                    {{-- Busca por Nome --}}
                    <div class="flex-1">
                        <x-filament::input.wrapper>
                            <x-filament::input
                                wire:model.live.debounce.300ms="searchProduct"
                                type="text"
                                placeholder="🔍 Buscar Produto"
                                class="text-sm py-1"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    @if($searchProduct || $barcode)
                        <x-filament::button
                            wire:click="$set('searchProduct', ''); $set('barcode', '')"
                            color="gray"
                            outlined
                            size="xs"
                        >
                            ✕
                        </x-filament::button>
                    @endif
                </div>

                <!-- Categorias -->
                <div class="flex gap-1.5 overflow-x-auto pb-1">
                    <x-filament::button
                        wire:click="$set('selectedCategory', null)"
                        :color="!$selectedCategory ? 'primary' : 'gray'"
                        :outlined="$selectedCategory !== null"
                        size="xs"
                    >
                        Todas
                    </x-filament::button>

                    @foreach($this->getCategories() as $category)
                        <x-filament::button
                            wire:click="$set('selectedCategory', {{ $category->id }})"
                            :color="$selectedCategory == $category->id ? 'primary' : 'gray'"
                            :outlined="$selectedCategory != $category->id"
                            size="xs"
                        >
                            {{ $category->name }}
                        </x-filament::button>
                    @endforeach
                </div>
            </div>

            <!-- Produtos Organizados por Categoria (SEM IMAGENS) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 max-h-[calc(100vh-300px)] overflow-y-auto">
                @php
                    $productsByCategory = $this->getProductsByCategory();
                @endphp

                @if($productsByCategory->isEmpty())
                    <div class="text-center py-12">
                        <div class="text-5xl mb-3">🔍</div>
                        <p class="text-gray-500 text-sm font-semibold">Nenhum produto encontrado</p>
                        <p class="text-gray-400 text-xs mt-1">Tente ajustar os filtros ou busca</p>
                    </div>
                @else
                    @foreach($productsByCategory as $categoryName => $products)
                        {{-- Cabeçalho da Categoria (sticky) --}}
                        <div class="sticky top-0 z-10 bg-gradient-to-r from-primary-100 to-primary-50 dark:from-primary-900/40 dark:to-primary-800/30 px-3 py-2 mb-2 rounded-lg border-l-4 border-primary-500">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold text-primary-700 dark:text-primary-300 uppercase tracking-wide">
                                    {{ $categoryName }}
                                </h3>
                                <span class="text-xs bg-primary-500 text-white px-2 py-0.5 rounded-full font-semibold">
                                    {{ $products->count() }}
                                </span>
                            </div>
                        </div>

                        {{-- Grid de Produtos da Categoria --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 mb-4">
                            @foreach($products as $product)
                                <button
                                    wire:click="addToCart({{ $product->id }})"
                                    @if($product->has_stock_control && !$product->hasStock())
                                        disabled
                                        class="group relative flex flex-col bg-gray-100 dark:bg-gray-900/50 border-2 border-gray-300 dark:border-gray-700 rounded-lg p-2.5 min-h-[90px] opacity-50 cursor-not-allowed"
                                    @else
                                        class="group relative flex flex-col bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-2.5 min-h-[90px] hover:border-primary-500 hover:shadow-lg hover:scale-105 transition-all duration-150 cursor-pointer"
                                    @endif
                                >
                                    {{-- Nome e Badges --}}
                                    <div class="flex items-start justify-between gap-1 mb-2 min-h-[32px]">
                                        <h4 class="font-semibold text-xs leading-tight text-gray-900 dark:text-white line-clamp-2 flex-1 break-words">
                                            {{ $product->name }}
                                        </h4>

                                        {{-- Badges (Pizza, Estoque) --}}
                                        <div class="flex flex-col gap-1 items-end">
                                            @if($product->is_pizza)
                                                <span class="text-sm">🍕</span>
                                            @endif
                                            @if($product->has_stock_control && $product->stock_quantity <= 10 && $product->stock_quantity > 0)
                                                <span class="bg-yellow-500 text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold">
                                                    {{ $product->stock_quantity }}
                                                </span>
                                            @elseif($product->has_stock_control && $product->stock_quantity === 0)
                                                <span class="bg-red-500 text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold">
                                                    SEM
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Preço e Botão Add --}}
                                    <div class="flex items-center justify-between gap-1 mt-auto">
                                        <span class="text-xs font-bold text-primary-600 dark:text-primary-400">
                                            R$ {{ number_format($product->price, 2, ',', '.') }}
                                        </span>
                                        <span class="w-5 h-5 bg-primary-500 text-white rounded-full flex items-center justify-center font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                            +
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>

        </div>

        <!-- CARRINHO E FINALIZAÇÃO (4 colunas) -->
        <div class="lg:col-span-4 space-y-4">

            <!-- Cliente MELHORADO -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-bold mb-3 text-gray-700 dark:text-gray-300 flex items-center gap-2">
                    <span class="text-lg">👤</span> Cliente
                    <x-filament::button
                        wire:click="quickBalcaoMode"
                        color="warning"
                        size="xs"
                        title="Modo Balcão Rápido"
                    >
                        ⚡ Balcão
                    </x-filament::button>
                </h3>

                @if($selectedCustomer)
                    <div class="p-3 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 rounded-lg border-2 border-primary-200 dark:border-primary-700">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <p class="font-bold text-sm text-gray-900 dark:text-white">{{ $customerName }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $customerPhone }}</p>
                            </div>
                            <x-filament::button
                                wire:click="clearCustomer"
                                color="danger"
                                size="xs"
                                outlined
                            >
                                Trocar
                            </x-filament::button>
                        </div>

                        <div class="flex gap-2 mt-3">
                            <div class="flex-1 px-3 py-2 bg-white dark:bg-gray-900 rounded-lg border border-green-300 dark:border-green-700">
                                <p class="text-xs text-gray-600 dark:text-gray-400">Saldo Cashback</p>
                                <p class="font-bold text-sm text-green-600 dark:text-green-400">
                                    R$ {{ number_format($customerCashbackBalance, 2, ',', '.') }}
                                </p>
                            </div>
                            <div class="px-3 py-2 bg-white dark:bg-gray-900 rounded-lg border border-orange-300 dark:border-orange-700">
                                <p class="text-xs text-gray-600 dark:text-gray-400">Tier</p>
                                <p class="font-bold text-sm text-orange-600 dark:text-orange-400">
                                    {{ $this->getCashbackPercentage() }}%
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-2">
                        <x-filament::input.wrapper>
                            <x-filament::input
                                wire:model.live.debounce.300ms="searchCustomer"
                                type="text"
                                placeholder="🔍 Nome, telefone ou email"
                                class="text-sm"
                            />
                        </x-filament::input.wrapper>

                        {{-- Sugestões de clientes --}}
                        @if($this->getCustomerSuggestions()->count() > 0)
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-2 space-y-1 max-h-40 overflow-y-auto">
                                @foreach($this->getCustomerSuggestions() as $customer)
                                    <button
                                        wire:click="selectCustomer({{ $customer->id }})"
                                        class="w-full text-left px-3 py-2 bg-white dark:bg-gray-800 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors"
                                    >
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $customer->name }}</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $customer->phone }}</p>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <x-filament::button
                            wire:click="$set('showNewCustomerModal', true)"
                            color="gray"
                            outlined
                            size="sm"
                            class="w-full"
                        >
                            + Cadastrar Novo Cliente
                        </x-filament::button>
                    </div>
                @endif
            </div>

            <!-- Carrinho -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <span class="text-lg">🛒</span> Carrinho
                        @if(!empty($cart))
                            <span class="bg-primary-500 text-white text-xs px-2 py-0.5 rounded-full">
                                {{ count($cart) }}
                            </span>
                        @endif
                    </h3>
                    @if(!empty($cart))
                        <x-filament::button
                            wire:click="clearCart"
                            color="danger"
                            size="xs"
                            outlined
                        >
                            Limpar
                        </x-filament::button>
                    @endif
                </div>

                <div class="space-y-2 max-h-[calc(100vh-680px)] overflow-y-auto pr-2">
                    @forelse($cart as $key => $item)
                        <div class="flex gap-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                            <!-- Quantidade grande -->
                            <div class="flex flex-col items-center justify-center">
                                <span class="text-2xl font-black text-primary-600 dark:text-primary-400">
                                    {{ $item['quantity'] }}x
                                </span>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-gray-900 dark:text-white truncate">
                                    {{ $item['name'] }}
                                </p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    R$ {{ number_format($item['price'], 2, ',', '.') }} cada
                                </p>
                                <p class="text-sm font-bold text-primary-600 dark:text-primary-400 mt-1">
                                    R$ {{ number_format($item['subtotal'], 2, ',', '.') }}
                                </p>
                            </div>

                            <!-- Controles -->
                            <div class="flex flex-col gap-1">
                                <x-filament::button
                                    wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})"
                                    color="primary"
                                    size="xs"
                                >
                                    +
                                </x-filament::button>
                                <x-filament::button
                                    wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})"
                                    color="gray"
                                    size="xs"
                                >
                                    -
                                </x-filament::button>
                                <x-filament::button
                                    wire:click="removeFromCart('{{ $key }}')"
                                    color="danger"
                                    size="xs"
                                >
                                    🗑️
                                </x-filament::button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="text-5xl mb-3">🛒</div>
                            <p class="text-gray-500 text-sm">Carrinho vazio</p>
                            <p class="text-gray-400 text-xs mt-1">Adicione produtos para começar</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Finalização -->
            @if(!empty($cart))
                <div class="bg-gradient-to-br from-primary-50 to-primary-100 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-lg border-2 border-primary-200 dark:border-primary-700 p-4 space-y-4">

                    <!-- Tipo e Pagamento -->
                    <div>
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 block">Tipo de Pedido</label>
                        <div class="grid grid-cols-2 gap-2">
                            <x-filament::button
                                wire:click="$set('deliveryType', 'pickup')"
                                :color="$deliveryType === 'pickup' ? 'primary' : 'gray'"
                                :outlined="$deliveryType !== 'pickup'"
                                size="sm"
                                class="flex items-center justify-center gap-2"
                            >
                                <span class="text-lg">🏃</span> Retirada
                            </x-filament::button>
                            <x-filament::button
                                wire:click="$set('deliveryType', 'delivery')"
                                :color="$deliveryType === 'delivery' ? 'primary' : 'gray'"
                                :outlined="$deliveryType !== 'delivery'"
                                size="sm"
                                class="flex items-center justify-center gap-2"
                            >
                                <span class="text-lg">🚗</span> Delivery
                            </x-filament::button>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 block">Pagamento</label>
                        <div class="grid grid-cols-2 gap-2">
                            <x-filament::button
                                wire:click="$set('paymentMethod', 'cash')"
                                :color="$paymentMethod === 'cash' ? 'success' : 'gray'"
                                :outlined="$paymentMethod !== 'cash'"
                                size="sm"
                            >
                                💵 Dinheiro
                            </x-filament::button>
                            <x-filament::button
                                wire:click="$set('paymentMethod', 'pix')"
                                :color="$paymentMethod === 'pix' ? 'success' : 'gray'"
                                :outlined="$paymentMethod !== 'pix'"
                                size="sm"
                            >
                                💰 PIX
                            </x-filament::button>
                        </div>
                    </div>

                    <!-- Desconto MELHORADO -->
                    <div>
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 block flex items-center justify-between">
                            Desconto
                            @if($discount > 0)
                                <x-filament::button
                                    wire:click="clearDiscount"
                                    color="danger"
                                    size="xs"
                                >
                                    ✕ Limpar
                                </x-filament::button>
                            @endif
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="col-span-2">
                                <x-filament::input.wrapper>
                                    <x-filament::input
                                        wire:model.live="discountInput"
                                        type="number"
                                        step="0.01"
                                        placeholder="Valor"
                                        class="text-sm"
                                    />
                                </x-filament::input.wrapper>
                            </div>
                            <x-filament::button
                                wire:click="$toggle('discountType'); applyDiscount()"
                                :color="$discountType === 'percentage' ? 'warning' : 'gray'"
                                size="sm"
                            >
                                {{ $discountType === 'percentage' ? '%' : 'R$' }}
                            </x-filament::button>
                        </div>
                        @if($discountInput > 0)
                            <x-filament::button
                                wire:click="applyDiscount"
                                color="success"
                                size="xs"
                                class="w-full mt-2"
                            >
                                ✓ Aplicar Desconto
                            </x-filament::button>
                        @endif
                    </div>

                    @if($deliveryType === 'delivery')
                        <div>
                            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Taxa Entrega</label>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    wire:model.live="deliveryFee"
                                    type="number"
                                    step="0.01"
                                    placeholder="0,00"
                                    class="text-sm"
                                />
                            </x-filament::input.wrapper>
                        </div>
                    @endif

                    <!-- Resumo -->
                    <div class="bg-white dark:bg-gray-900 rounded-lg p-4 space-y-2 border-2 border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                            <span>Subtotal:</span>
                            <span class="font-semibold">R$ {{ number_format($this->getSubtotal(), 2, ',', '.') }}</span>
                        </div>

                        @if($deliveryFee > 0)
                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                <span>Taxa de Entrega:</span>
                                <span class="font-semibold text-green-600">+R$ {{ number_format($deliveryFee, 2, ',', '.') }}</span>
                            </div>
                        @endif

                        @if($discount > 0)
                            <div class="flex justify-between text-sm text-red-600 dark:text-red-400">
                                <span>Desconto:</span>
                                <span class="font-semibold">-R$ {{ number_format($discount, 2, ',', '.') }}</span>
                            </div>
                        @endif

                        @if($cashbackUsed > 0)
                            <div class="flex justify-between text-sm text-orange-600 dark:text-orange-400">
                                <span>Cashback Usado:</span>
                                <span class="font-semibold">-R$ {{ number_format($cashbackUsed, 2, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="border-t-2 border-gray-300 dark:border-gray-600 pt-2 flex justify-between items-center">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">TOTAL:</span>
                            <span class="text-2xl font-black text-primary-600 dark:text-primary-400">
                                R$ {{ number_format($this->getTotal(), 2, ',', '.') }}
                            </span>
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400 text-center pt-2">
                            Cliente ganhará <span class="font-bold text-green-600">R$ {{ number_format($this->getCashbackEarned(), 2, ',', '.') }}</span> de cashback
                        </div>
                    </div>

                    <!-- Botão Finalizar (F2) -->
                    <x-filament::button
                        wire:click="finishOrder"
                        color="success"
                        size="lg"
                        class="w-full text-lg font-bold"
                    >
                        ✅ FINALIZAR PEDIDO
                    </x-filament::button>
                </div>
            @endif

        </div>
    </div>

    {{-- ===== MODAL: AGUARDANDO PAGAMENTO (Versão Simples) ===== --}}
    @if($showPaymentWaitingModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         wire:key="payment-waiting-modal">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6">
            {{-- Cabeçalho --}}
            <div class="flex items-center gap-2 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                @if($paymentConfirmed)
                    <span class="text-green-600 text-2xl">✅</span>
                @else
                    <span class="animate-pulse text-2xl">⏳</span>
                @endif
                <span class="text-xl font-bold">Pedido #{{ $currentOrderNumber }}</span>
            </div>

        <div class="space-y-4">
            {{-- Total do Pedido --}}
            <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-sm text-gray-600 dark:text-gray-400">Total do Pedido</div>
                <div class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                    R$ {{ number_format($currentOrderTotal, 2, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Forma de Pagamento: <span class="font-semibold">{{ strtoupper($currentPaymentMethod ?? '') }}</span>
                </div>
            </div>

            {{-- QR Code PIX (se aplicável) --}}
            @if($currentPaymentMethod === 'pix' && $pixQrCode)
                <div class="bg-white dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-center space-y-3">
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            📱 QR Code PIX
                        </div>
                        <div class="flex justify-center">
                            <img src="{{ $pixQrCode }}" alt="QR Code PIX" class="w-48 h-48" />
                        </div>
                        @if($pixCopyPaste)
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Código Copia e Cola disponível
                            </div>
                        @endif
                        <x-filament::button
                            wire:click="reprintPixFromWaiting"
                            color="info"
                            size="sm"
                            outlined
                        >
                            🖨️ Reimprimir QR Code
                        </x-filament::button>
                    </div>
                </div>
            @endif

            {{-- Status do Pagamento --}}
            <div class="text-center">
                @if($paymentConfirmed)
                    <div class="text-green-600 dark:text-green-400 font-semibold text-lg">
                        ✅ Pagamento Confirmado!
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Cupom já foi impresso
                    </div>
                @else
                    <div class="text-orange-600 dark:text-orange-400 font-semibold">
                        ⏳ Aguardando Pagamento...
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        @if($currentPaymentMethod === 'pix')
                            Cliente pode escanear o QR Code ou digitar o código manualmente
                        @else
                            Marque como pago após receber o pagamento
                        @endif
                    </div>
                @endif
            </div>

            {{-- Ações --}}
            <div class="grid grid-cols-1 gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                @if(!$paymentConfirmed)
                    @if($currentPaymentMethod !== 'pix')
                        {{-- Botão: Marcar como Pago (Dinheiro, Débito, Crédito) --}}
                        <x-filament::button
                            wire:click="markOrderAsPaid"
                            color="success"
                            size="lg"
                            class="w-full"
                        >
                            ✅ MARCAR COMO PAGO
                        </x-filament::button>
                    @endif

                    {{-- Botão: Cancelar Pedido --}}
                    <x-filament::button
                        wire:click="cancelCurrentOrder"
                        wire:confirm="Tem certeza que deseja cancelar este pedido?"
                        color="danger"
                        size="sm"
                        outlined
                    >
                        ❌ Cancelar Pedido
                    </x-filament::button>
                @else
                    {{-- Botão: Próximo Pedido --}}
                    <x-filament::button
                        wire:click="finishAndStartNext"
                        color="primary"
                        size="lg"
                        class="w-full"
                    >
                        🔄 PRÓXIMO PEDIDO
                    </x-filament::button>
                @endif
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
