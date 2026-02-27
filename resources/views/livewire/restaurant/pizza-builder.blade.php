<div>
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(0, 0, 0, 0.5);">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-4xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl">

                    <!-- Header -->
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <span class="text-4xl">🍕</span>
                            <div>
                                <h2 class="text-2xl font-bold">Monte sua Pizza</h2>
                                <p class="text-sm text-gray-600">Personalize do seu jeito!</p>
                            </div>
                        </div>
                        <button
                            wire:click="$set('showModal', false)"
                            class="text-gray-400 hover:text-gray-600 text-2xl"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">

                        <!-- Tipo de Pizza -->
                        <div>
                            <label class="block text-sm font-semibold mb-3">🍕 Tipo de Pizza</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    wire:click="$set('pizzaType', 'whole')"
                                    class="p-4 border-2 rounded-lg transition {{ $pizzaType === 'whole' ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-300' }}"
                                >
                                    <div class="text-center">
                                        <div class="text-3xl mb-2">🍕</div>
                                        <div class="font-semibold">Pizza Inteira</div>
                                        <div class="text-xs text-gray-600">Um sabor só</div>
                                    </div>
                                </button>

                                <button
                                    wire:click="$set('pizzaType', 'half')"
                                    class="p-4 border-2 rounded-lg transition {{ $pizzaType === 'half' ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-300' }}"
                                >
                                    <div class="text-center">
                                        <div class="text-3xl mb-2">🍕🍕</div>
                                        <div class="font-semibold">Meio a Meio</div>
                                        <div class="text-xs text-gray-600">Dois sabores</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Tamanho -->
                        <div>
                            <label class="block text-sm font-semibold mb-3">📏 Tamanho</label>
                            <div class="grid grid-cols-4 gap-2">
                                <button
                                    wire:click="$set('size', 'small')"
                                    class="p-3 border-2 rounded-lg transition {{ $size === 'small' ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-300' }}"
                                >
                                    <div class="text-center">
                                        <div class="text-2xl mb-1">🍕</div>
                                        <div class="text-xs font-semibold">Pequena</div>
                                        <div class="text-xs text-gray-600">6 fatias</div>
                                    </div>
                                </button>

                                <button
                                    wire:click="$set('size', 'medium')"
                                    class="p-3 border-2 rounded-lg transition {{ $size === 'medium' ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-300' }}"
                                >
                                    <div class="text-center">
                                        <div class="text-3xl mb-1">🍕</div>
                                        <div class="text-xs font-semibold">Média</div>
                                        <div class="text-xs text-gray-600">8 fatias</div>
                                    </div>
                                </button>

                                <button
                                    wire:click="$set('size', 'large')"
                                    class="p-3 border-2 rounded-lg transition {{ $size === 'large' ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-300' }}"
                                >
                                    <div class="text-center">
                                        <div class="text-4xl mb-1">🍕</div>
                                        <div class="text-xs font-semibold">Grande</div>
                                        <div class="text-xs text-gray-600">10 fatias</div>
                                    </div>
                                </button>

                                <button
                                    wire:click="$set('size', 'family')"
                                    class="p-3 border-2 rounded-lg transition {{ $size === 'family' ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-300' }}"
                                >
                                    <div class="text-center">
                                        <div class="text-5xl mb-1">🍕</div>
                                        <div class="text-xs font-semibold">Família</div>
                                        <div class="text-xs text-gray-600">12 fatias</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Sabor 1 -->
                        <div>
                            <label class="block text-sm font-semibold mb-3">
                                {{ $pizzaType === 'half' ? '🍕 Primeiro Sabor (1/2)' : '🍕 Sabor' }}
                            </label>
                            <div class="grid grid-cols-2 gap-3 max-h-[200px] overflow-y-auto">
                                @foreach($flavors as $flavor)
                                    <button
                                        wire:click="$set('flavor1', {{ $flavor->id }})"
                                        class="p-3 border-2 rounded-lg transition text-left {{ $flavor1 == $flavor->id ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-300' }}"
                                    >
                                        <div class="font-semibold">{{ $flavor->name }}</div>
                                        <div class="text-sm text-gray-600">R$ {{ number_format($flavor->price, 2, ',', '.') }}</div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Sabor 2 (se meio a meio) - COM SCROLL E INGREDIENTES -->
                        @if($pizzaType === 'half')
                            <div>
                                <label class="block text-sm font-semibold mb-3">🍕 Segundo Sabor (1/2)</label>

                                <!-- SCROLL COM INGREDIENTES VISÍVEIS -->
                                <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2 scrollbar-thin">
                                    @foreach($flavors as $flavor)
                                        <button
                                            wire:click="$set('flavor2', {{ $flavor->id }})"
                                            class="w-full p-3 border-2 rounded-lg transition text-left flex items-start gap-3 {{ $flavor2 == $flavor->id ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-300 hover:shadow-md' }}"
                                        >
                                            @if($flavor->image)
                                                <img src="{{ Storage::url($flavor->image) }}"
                                                     alt="{{ $flavor->name }}"
                                                     class="w-16 h-16 rounded-lg object-cover flex-shrink-0">
                                            @else
                                                <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-orange-400 to-red-400 flex items-center justify-center text-2xl flex-shrink-0">
                                                    🍕
                                                </div>
                                            @endif

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="font-semibold text-gray-900">{{ $flavor->name }}</div>
                                                    @if($flavor2 == $flavor->id)
                                                        <span class="inline-block px-2 py-1 bg-primary-500 text-white text-xs rounded-full whitespace-nowrap">
                                                            ✓ Selecionado
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- INGREDIENTES VISÍVEIS -->
                                                @if($flavor->filling)
                                                    <div class="text-xs text-gray-600 mt-1 line-clamp-2">
                                                        <strong class="text-primary-600">🍕 Ingredientes:</strong> {{ $flavor->filling }}
                                                    </div>
                                                @endif

                                                <div class="text-sm font-bold text-primary-600 mt-2">
                                                    R$ {{ number_format($flavor->price, 2, ',', '.') }}
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Borda -->
                        <div>
                            <label class="block text-sm font-semibold mb-3">🎨 Borda Recheada (Opcional)</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <button
                                    wire:click="$set('border', 'none')"
                                    class="p-3 border-2 rounded-lg transition {{ $border === 'none' ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-300' }}"
                                >
                                    <div class="text-center">
                                        <div class="text-xs font-semibold">Sem Borda</div>
                                        <div class="text-xs text-gray-600">Grátis</div>
                                    </div>
                                </button>

                                <button
                                    wire:click="$set('border', 'catupiry')"
                                    class="p-3 border-2 rounded-lg transition {{ $border === 'catupiry' ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-300' }}"
                                >
                                    <div class="text-center">
                                        <div class="text-xs font-semibold">Catupiry</div>
                                        <div class="text-xs text-gray-600">+ R$ 8,00</div>
                                    </div>
                                </button>

                                <button
                                    wire:click="$set('border', 'cheddar')"
                                    class="p-3 border-2 rounded-lg transition {{ $border === 'cheddar' ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-300' }}"
                                >
                                    <div class="text-center">
                                        <div class="text-xs font-semibold">Cheddar</div>
                                        <div class="text-xs text-gray-600">+ R$ 8,00</div>
                                    </div>
                                </button>

                                <button
                                    wire:click="$set('border', 'chocolate')"
                                    class="p-3 border-2 rounded-lg transition {{ $border === 'chocolate' ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-300' }}"
                                >
                                    <div class="text-center">
                                        <div class="text-xs font-semibold">Chocolate</div>
                                        <div class="text-xs text-gray-600">+ R$ 10,00</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Resumo -->
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                            <h3 class="font-semibold mb-2">📋 Resumo da Pizza</h3>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $this->buildDescription() }}</p>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="text-sm text-gray-600">Preço Total:</div>
                            <div class="text-3xl font-bold text-primary-600">
                                R$ {{ number_format($calculatedPrice, 2, ',', '.') }}
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button
                                wire:click="$set('showModal', false)"
                                class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                            >
                                Cancelar
                            </button>
                            <button
                                wire:click="addToCart"
                                class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-semibold"
                            >
                                🛒 Adicionar ao Carrinho
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>
