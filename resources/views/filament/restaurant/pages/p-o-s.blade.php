<x-filament-panels::page>
    @livewire('restaurant.pizza-builder')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <!-- PRODUTOS (8 colunas) -->
        <div class="lg:col-span-8 space-y-4">

            <!-- Busca -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex gap-3">
                    <div class="flex-1">
                        <x-filament::input.wrapper>
                            <x-filament::input
                                wire:model.live.debounce.300ms="searchProduct"
                                type="text"
                                placeholder="🔍 Buscar produto..."
                                class="text-base"
                            />
                        </x-filament::input.wrapper>
                    </div>
                    @if($searchProduct)
                        <x-filament::button
                            wire:click="$set('searchProduct', '')"
                            color="gray"
                            outlined
                        >
                            Limpar
                        </x-filament::button>
                    @endif
                </div>
            </div>

            <!-- Categorias -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex gap-2 overflow-x-auto pb-2">
                    <x-filament::button
                        wire:click="$set('selectedCategory', null)"
                        :color="!$selectedCategory ? 'primary' : 'gray'"
                        :outlined="$selectedCategory !== null"
                        size="sm"
                    >
                        🍽️ Todas
                    </x-filament::button>

                    @foreach($this->getCategories() as $category)
                        <x-filament::button
                            wire:click="$set('selectedCategory', {{ $category->id }})"
                            :color="$selectedCategory == $category->id ? 'primary' : 'gray'"
                            :outlined="$selectedCategory != $category->id"
                            size="sm"
                        >
                            {{ $category->name }}
                        </x-filament::button>
                    @endforeach
                </div>
            </div>

            <!-- Grid de Produtos -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-base font-bold mb-4 text-gray-700 dark:text-gray-300">Produtos Disponíveis</h3>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 max-h-[calc(100vh-350px)] overflow-y-auto pr-2">
                    @forelse($this->getProducts() as $product)
                        <button
                            wire:click="addToCart({{ $product->id }})"
                            class="group relative flex flex-col bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-3 hover:border-primary-500 hover:shadow-lg transition-all duration-200 cursor-pointer"
                            @if($product->has_stock_control && !$product->hasStock())
                                disabled
                                class="opacity-50 cursor-not-allowed"
                            @endif
                        >
                            <!-- Imagem -->
                            <div class="relative mb-3">
                                @if($product->image)
                                    <img
                                        src="{{ Storage::url($product->image) }}"
                                        alt="{{ $product->name }}"
                                        class="w-full h-24 object-cover rounded-lg"
                                    >
                                @else
                                    <div class="w-full h-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 rounded-lg flex items-center justify-center">
                                        <span class="text-4xl">🍕</span>
                                    </div>
                                @endif

                                @if($product->is_pizza)
                                    <span class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                                        🍕
                                    </span>
                                @endif

                                @if($product->has_stock_control && $product->stock_quantity <= 10 && $product->stock_quantity > 0)
                                    <span class="absolute -bottom-1 -right-1 bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full font-bold">
                                        {{ $product->stock_quantity }}un
                                    </span>
                                @elseif($product->has_stock_control && $product->stock_quantity === 0)
                                    <span class="absolute -bottom-1 -right-1 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full font-bold">
                                        ESGOTADO
                                    </span>
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="flex-1 flex flex-col justify-between">
                                <h4 class="font-semibold text-sm text-gray-900 dark:text-white line-clamp-2 mb-2">
                                    {{ $product->name }}
                                </h4>

                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                        R$ {{ number_format($product->price, 2, ',', '.') }}
                                    </span>
                                    <span class="w-8 h-8 bg-primary-500 text-white rounded-full flex items-center justify-center font-bold text-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                        +
                                    </span>
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <div class="text-6xl mb-4">🔍</div>
                            <p class="text-gray-500 text-sm">Nenhum produto encontrado</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- CARRINHO E FINALIZAÇÃO (4 colunas) -->
        <div class="lg:col-span-4 space-y-4">

            <!-- Cliente -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-bold mb-3 text-gray-700 dark:text-gray-300 flex items-center gap-2">
                    <span class="text-lg">👤</span> Cliente
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
                                wire:model.live.debounce.300ms="customerPhone"
                                type="text"
                                placeholder="Digite o telefone do cliente..."
                                class="text-sm"
                            />
                        </x-filament::input.wrapper>

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

                <div class="space-y-2 max-h-[calc(100vh-580px)] overflow-y-auto pr-2">
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
                                wire:click="$set('deliveryType', 'delivery')"
                                :color="$deliveryType === 'delivery' ? 'primary' : 'gray'"
                                :outlined="$deliveryType !== 'delivery'"
                                size="sm"
                                class="flex items-center justify-center gap-2"
                            >
                                <span class="text-lg">🚗</span> Delivery
                            </x-filament::button>
                            <x-filament::button
                                wire:click="$set('deliveryType', 'pickup')"
                                :color="$deliveryType === 'pickup' ? 'primary' : 'gray'"
                                :outlined="$deliveryType !== 'pickup'"
                                size="sm"
                                class="flex items-center justify-center gap-2"
                            >
                                <span class="text-lg">🏃</span> Retirada
                            </x-filament::button>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 block">Pagamento</label>
                        <div class="grid grid-cols-2 gap-2">
                            <x-filament::button
                                wire:click="$set('paymentMethod', 'pix')"
                                :color="$paymentMethod === 'pix' ? 'success' : 'gray'"
                                :outlined="$paymentMethod !== 'pix'"
                                size="sm"
                            >
                                💰 PIX
                            </x-filament::button>
                            <x-filament::button
                                wire:click="$set('paymentMethod', 'cash')"
                                :color="$paymentMethod === 'cash' ? 'success' : 'gray'"
                                :outlined="$paymentMethod !== 'cash'"
                                size="sm"
                            >
                                💵 Dinheiro
                            </x-filament::button>
                        </div>
                    </div>

                    <!-- Taxas -->
                    <div class="grid grid-cols-2 gap-2">
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
                        <div>
                            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1 block">Desconto</label>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    wire:model.live="discount"
                                    type="number"
                                    step="0.01"
                                    placeholder="0,00"
                                    class="text-sm"
                                />
                            </x-filament::input.wrapper>
                        </div>
                    </div>

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

                    <!-- Botão Finalizar -->
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
</x-filament-panels::page>
