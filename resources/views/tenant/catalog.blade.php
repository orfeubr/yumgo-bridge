@extends('tenant.layouts.app')

@section('title', 'Cardápio')

@section('content')
<!-- Header -->
<div class="sticky top-0 bg-gradient-to-r from-primary-500 to-primary-600 border-b border-primary-700 z-40 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-4 md:py-6">
        <div class="flex items-center justify-between gap-4">
            <div class="flex-1">
                <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-white">{{ $tenant->name }}</h1>
                <p class="text-sm md:text-base text-white/90 mt-1">Marmitas fresquinhas todos os dias</p>
            </div>

            <!-- Busca Desktop -->
            <div class="hidden md:block" x-data="{ searchQuery: '' }">
                <input type="text"
                       x-model="searchQuery"
                       @input="window.dispatchEvent(new CustomEvent('search-changed', { detail: $event.target.value }))"
                       placeholder="Buscar produtos..."
                       class="w-64 lg:w-80 px-4 py-2.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 focus:bg-white/20">
            </div>

            <!-- Perfil / Login -->
            <div x-data="{
                customer: null,
                init() {
                    console.log('🔍 Verificando localStorage...');
                    const saved = localStorage.getItem('customer');
                    console.log('📦 Customer no localStorage:', saved);
                    if (saved) {
                        this.customer = JSON.parse(saved);
                        console.log('✅ Customer carregado:', this.customer);
                    } else {
                        console.log('❌ Nenhum customer encontrado no localStorage');
                    }
                }
            }" class="flex items-center gap-3">
                <!-- Se estiver logado -->
                <template x-if="customer">
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold text-white" x-text="customer.name"></p>
                            <p class="text-xs text-white/80">Cashback: R$ <span x-text="parseFloat(customer.cashback_balance || 0).toFixed(2)"></span></p>
                        </div>
                        <img :src="customer.avatar || '/default-avatar.png'"
                             :alt="customer.name"
                             class="w-10 h-10 rounded-full border-2 border-white/30">
                    </div>
                </template>

                <!-- Se NÃO estiver logado -->
                <template x-if="!customer">
                    <a href="/auth/google/redirect"
                       class="flex items-center gap-2 px-4 py-2 bg-white text-primary-600 rounded-lg font-semibold text-sm hover:bg-white/90 transition-smooth">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span class="hidden sm:inline">Entrar</span>
                    </a>
                </template>
            </div>
        </div>
    </div>
</div>

<!-- Busca Mobile -->
<div class="md:hidden fixed top-[88px] left-0 right-0 bg-white border-b border-gray-100 z-40 px-4 py-3 shadow-sm" x-data="{ searchQuery: '' }">
    <input type="text"
           x-model="searchQuery"
           @input="window.dispatchEvent(new CustomEvent('search-changed', { detail: $event.target.value }))"
           placeholder="🔍 Buscar produtos..."
           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 focus:bg-white">
</div>

<!-- Categorias (fixo) -->
<div class="fixed top-[148px] md:top-[100px] left-0 right-0 bg-white border-b border-gray-100 z-50 shadow-sm" x-data="{ selectedCategory: null, categories: [] }" x-init="
    fetch('/api/v1/categories')
        .then(r => r.json())
        .then(data => categories = data.data || []);
">
    <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-3">
        <div class="flex gap-2 overflow-x-auto scrollbar-hide">
            <button @click="selectedCategory = null; window.dispatchEvent(new CustomEvent('category-changed', { detail: null }))"
                    :class="selectedCategory === null ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap transition-smooth">
                Todas
            </button>
            <template x-for="category in categories" :key="category.id">
                <button @click="selectedCategory = category.id; window.dispatchEvent(new CustomEvent('category-changed', { detail: category.id }))"
                        :class="selectedCategory === category.id ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap transition-smooth"
                        x-text="category.name"></button>
            </template>
        </div>
    </div>
</div>

<!-- Espaçador para compensar filtros fixos -->
<div class="h-[60px] md:hidden"></div>
<div class="h-[56px] hidden md:block"></div>

<!-- Área de Produtos com Alpine -->
<div x-data="catalogApp()" x-init="init()" class="bg-gray-50">

    <!-- Banner: Restaurante Fechado -->
    <div x-show="!isOpenNow"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-4">
        <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-4 md:p-5">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-amber-900 text-lg mb-2">🔒 Estamos fechados no momento</h3>
                    <p class="text-amber-800 text-sm mb-3">Você pode visualizar o cardápio, mas não é possível fazer pedidos agora.</p>

                    <!-- Horários de Funcionamento -->
                    <div class="bg-white rounded-lg p-3 border border-amber-100">
                        <p class="font-semibold text-amber-900 text-xs uppercase mb-2">⏰ Horários de Atendimento:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-xs">
                            <template x-for="(hours, day) in businessHours" :key="day">
                                <div class="flex justify-between py-1">
                                    <span class="font-medium text-gray-700" x-text="day"></span>
                                    <span class="text-gray-600" x-text="hours || 'Fechado'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Loading -->
    <div x-show="loading" class="max-w-2xl mx-auto px-4 py-12">
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <x-loading-spinner size="lg" />
            <p class="text-sm text-gray-500 mt-4">Carregando cardápio...</p>
        </div>
    </div>

    <!-- Produtos -->
    <div x-show="!loading" class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-6 pb-32 md:pb-8">
        <!-- Grid Responsivo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
            <template x-for="product in filteredProducts" :key="product.id">
                <div @click="isOpenNow && openProduct(product)"
                     :class="!isOpenNow && 'opacity-50 cursor-not-allowed'"
                     class="bg-white border border-gray-200 rounded-xl overflow-hidden card-hover transition-smooth hover:shadow-lg group"
                     :style="!isOpenNow && 'pointer-events: none; filter: grayscale(100%);'">
                    <!-- Imagem com Lazy Loading -->
                    <div class="relative h-48 md:h-52 lg:h-56 bg-gray-200 overflow-hidden">
                        <!-- Skeleton Loader -->
                        <div class="absolute inset-0 bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 animate-pulse"></div>

                        <!-- Imagem -->
                        <img :src="product.image"
                             :alt="product.name"
                             loading="lazy"
                             decoding="async"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 relative z-10"
                             @load="$el.previousElementSibling.style.display='none'"
                             onerror="this.src='https://via.placeholder.com/600x400?text=Sem+Foto'">

                        <!-- Badge Destaque -->
                        <div x-show="product.is_featured && isOpenNow"
                             class="absolute top-3 left-3 bg-primary-500 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-md z-20">
                            ⭐ Destaque
                        </div>

                        <!-- Badge Fechado -->
                        <div x-show="!isOpenNow"
                             class="absolute top-3 left-3 bg-gray-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-md z-20">
                            🔒 Fechado
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="p-4 md:p-5">
                        <h3 class="font-bold mb-2 text-base md:text-lg"
                            :class="isOpenNow ? 'text-gray-900' : 'text-gray-500'"
                            x-text="product.name"></h3>
                        <p class="text-xs md:text-sm mb-4 line-cloak-2"
                           :class="isOpenNow ? 'text-gray-600' : 'text-gray-400'"
                           x-text="product.description"></p>

                        <!-- Preços -->
                        <div class="flex items-center justify-between">
                            <div class="flex gap-3 md:gap-4">
                                <template x-for="variation in product.variations" :key="variation.id">
                                    <div class="text-center">
                                        <p class="text-xs font-medium mb-1"
                                           :class="isOpenNow ? 'text-gray-500' : 'text-gray-400'"
                                           x-text="variation.name"></p>
                                        <p class="text-sm md:text-base font-bold"
                                           :class="isOpenNow ? 'text-primary-500' : 'text-gray-400'"
                                           x-text="'R$ ' + (parseFloat(product.price) + parseFloat(variation.price_modifier)).toFixed(2)"></p>
                                    </div>
                                </template>
                            </div>
                            <button :disabled="!isOpenNow"
                                    :class="isOpenNow ? 'bg-primary-50 text-primary-500 hover:bg-primary-100 group-hover:bg-primary-500 group-hover:text-white cursor-pointer' : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                                    class="p-2.5 md:p-3 rounded-lg transition-smooth">
                                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="filteredProducts.length === 0" class="text-center py-16 md:py-24">
            <svg class="w-20 h-20 md:w-24 md:h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="text-gray-600 font-semibold text-base md:text-lg">Nenhum produto disponível</p>
            <p class="text-gray-500 text-sm mt-2">Tente mudar o filtro ou buscar por outro termo</p>
        </div>
    </div>

    <!-- Modal Produto (Clean & Minimalista) -->
    <div x-show="selectedProduct"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="closeProduct()"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-end md:items-center justify-center p-0 md:p-4"
         style="display: none;">

        <div x-show="selectedProduct"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="translate-y-full md:translate-y-0 md:scale-95 md:opacity-0"
             x-transition:enter-end="translate-y-0 md:scale-100 md:opacity-100"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="translate-y-0 md:scale-100 md:opacity-100"
             x-transition:leave-end="translate-y-full md:translate-y-0 md:scale-95 md:opacity-0"
             @click.stop
             class="bg-white w-full md:max-w-lg md:rounded-2xl rounded-t-3xl overflow-hidden shadow-2xl"
             style="max-height: 90vh;">

            <template x-if="selectedProduct">
                <div class="flex flex-col max-h-[90vh]">
                    <!-- Header com Imagem Compacta -->
                    <div class="relative flex-shrink-0">
                        <!-- Indicador arrastar mobile -->
                        <div class="absolute top-2 left-1/2 -translate-x-1/2 w-12 h-1 bg-gray-300 rounded-full md:hidden z-10"></div>

                        <!-- Imagem -->
                        <div class="relative h-44 md:h-48 bg-gray-100 overflow-hidden">
                            <!-- Skeleton Loader -->
                            <div class="absolute inset-0 bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 animate-pulse"></div>

                            <!-- Imagem com lazy loading -->
                            <img :src="selectedProduct.image" :alt="selectedProduct.name"
                                 loading="lazy"
                                 decoding="async"
                                 @load="$el.previousElementSibling.style.display='none'"
                                 class="w-full h-full object-cover relative z-10"
                                 onerror="this.src='https://via.placeholder.com/600x400?text=Sem+Foto'">

                            <!-- Botão Fechar -->
                            <button @click="closeProduct()"
                                    class="absolute top-3 right-3 w-9 h-9 bg-white/95 backdrop-blur rounded-full flex items-center justify-center shadow-md hover:bg-white transition-all">
                                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Info do Produto (sobre a imagem em gradiente) -->
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4 text-white">
                            <h2 class="text-lg md:text-xl font-bold mb-1" x-text="selectedProduct.name"></h2>
                            <p class="text-sm text-white/90 line-clamp-2" x-text="selectedProduct.description"></p>
                        </div>
                    </div>

                    <!-- Conteúdo Scrollável Compacto -->
                    <div class="flex-1 overflow-y-auto overscroll-contain px-4 py-3" style="-webkit-overflow-scrolling: touch;">
                        <!-- Tamanhos (Inline) -->
                        <div class="mb-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Tamanho</p>
                            <div class="flex gap-2">
                                <template x-for="variation in selectedProduct.variations" :key="variation.id">
                                    <button @click="selectedVariation = variation"
                                            :class="selectedVariation?.id === variation.id ? 'border-primary-500 bg-primary-50 text-primary-600' : 'border-gray-200 text-gray-700'"
                                            class="flex-1 border-2 rounded-xl px-3 py-2.5 text-center transition-all active:scale-95">
                                        <p class="text-xs font-medium mb-0.5" x-text="variation.name"></p>
                                        <p class="text-sm font-bold"
                                           x-text="'R$ ' + (parseFloat(selectedProduct.price) + parseFloat(variation.price_modifier)).toFixed(2)"></p>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Quantidade (Inline Compacto) -->
                        <div class="mb-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Quantidade</p>
                            <div class="flex items-center gap-3">
                                <button @click="quantity = Math.max(1, quantity - 1)"
                                        class="w-10 h-10 bg-gray-100 rounded-lg font-bold text-lg text-gray-700 active:bg-gray-200 transition-all active:scale-95">
                                    −
                                </button>
                                <span class="text-xl font-bold text-gray-900 min-w-[2.5rem] text-center" x-text="quantity"></span>
                                <button @click="quantity++"
                                        class="w-10 h-10 bg-primary-500 rounded-lg font-bold text-lg text-white active:bg-primary-600 transition-all active:scale-95">
                                    +
                                </button>
                            </div>
                        </div>

                        <!-- Observações (Compacto) -->
                        <div class="mb-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Observações</p>
                            <textarea x-model="notes" placeholder="Ex: Sem cebola, bem passado..." rows="2"
                                      class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Botão Fixo Compacto -->
                    <div class="flex-shrink-0 p-4 pt-2 bg-white border-t border-gray-100">
                        <!-- Mensagem quando fechado -->
                        <div x-show="!isOpenNow" class="text-center mb-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                            <p class="text-sm font-semibold text-amber-900">🔒 Não é possível adicionar itens</p>
                            <p class="text-xs text-amber-700 mt-1">Estamos fechados no momento</p>
                        </div>

                        <button @click="isOpenNow && addToCart()"
                                :disabled="!selectedVariation || !isOpenNow"
                                :class="selectedVariation && isOpenNow ? 'bg-primary-500 hover:bg-primary-600 active:bg-primary-700' : 'bg-gray-300 cursor-not-allowed'"
                                class="w-full py-3.5 text-white rounded-xl font-bold text-base transition-all active:scale-98 disabled:active:scale-100 shadow-sm">
                            <span x-show="selectedVariation && isOpenNow">
                                Adicionar R$ <span x-text="((parseFloat(selectedProduct.price) + parseFloat(selectedVariation?.price_modifier || 0)) * quantity).toFixed(2)"></span>
                            </span>
                            <span x-show="!selectedVariation && isOpenNow">Escolha um tamanho</span>
                            <span x-show="!isOpenNow">Restaurante Fechado</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Carrinho Flutuante -->
    <div x-show="cart.length > 0"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-[72px] md:bottom-6 left-4 right-4 md:left-auto md:right-6 lg:right-8 md:max-w-sm z-40">
        <div class="bg-primary-500 text-white rounded-2xl shadow-2xl p-4 md:p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm opacity-90">Carrinho</p>
                        <p class="font-bold text-lg" x-text="cart.length + ' ' + (cart.length === 1 ? 'item' : 'itens')"></p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm opacity-90">Total</p>
                    <p class="text-xl md:text-2xl font-bold" x-text="'R$ ' + cartTotal.toFixed(2)"></p>
                </div>
            </div>
            <a href="/checkout"
               class="block w-full py-3 md:py-4 bg-white text-primary-500 rounded-xl font-bold text-center hover:bg-gray-100 transition-smooth active:scale-98 shadow-lg text-base md:text-lg">
                Ver carrinho →
            </a>
        </div>
    </div>
</div>

<script>
function catalogApp() {
    return {
        products: [],
        categories: [],
        cart: [],
        loading: true,
        selectedCategory: null,
        selectedProduct: null,
        selectedVariation: null,
        quantity: 1,
        notes: '',
        searchQuery: '',
        isOpenNow: true, // Status do restaurante
        businessHours: {}, // Horários de funcionamento

        async init() {
            await Promise.all([
                this.loadProducts(),
                this.loadCategories(),
                this.loadSettings(), // Carrega status do horário
                this.loadCart()
            ]);
            this.loading = false;

            // Escutar eventos de busca e categoria
            window.addEventListener('search-changed', (e) => {
                this.searchQuery = e.detail || '';
            });

            window.addEventListener('category-changed', (e) => {
                this.selectedCategory = e.detail;
            });
        },

        async loadProducts() {
            try {
                const response = await fetch('/api/v1/products');
                if (response.ok) {
                    const data = await response.json();
                    this.products = data.data || [];
                }
            } catch (error) {
                console.error('Erro ao carregar produtos:', error);
            }
        },

        async loadSettings() {
            try {
                const response = await fetch('/api/v1/settings');
                if (response.ok) {
                    const data = await response.json();
                    this.isOpenNow = data.settings.is_open_now || false;
                    this.businessHours = data.settings.business_hours || {};
                }
            } catch (error) {
                console.error('Erro ao carregar configurações:', error);
            }
        },

        async loadCategories() {
            try {
                const response = await fetch('/api/v1/categories');
                if (response.ok) {
                    const data = await response.json();
                    this.categories = data.data || [];
                }
            } catch (error) {
                console.error('Erro ao carregar categorias:', error);
            }
        },

        loadCart() {
            const saved = localStorage.getItem('yumgo_cart');
            if (saved) {
                this.cart = JSON.parse(saved);
            }
        },

        saveCart() {
            localStorage.setItem('yumgo_cart', JSON.stringify(this.cart));
        },

        get filteredProducts() {
            let filtered = this.products;

            // Filtro por categoria
            if (this.selectedCategory) {
                filtered = filtered.filter(p => p.category_id === this.selectedCategory);
            }

            // Filtro por busca
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(p =>
                    p.name.toLowerCase().includes(query) ||
                    (p.description && p.description.toLowerCase().includes(query)) ||
                    (p.filling && p.filling.toLowerCase().includes(query))
                );
            }

            return filtered;
        },

        get cartTotal() {
            return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        },

        openProduct(product) {
            this.selectedProduct = product;
            this.selectedVariation = product.variations?.[0] || null;
            this.quantity = 1;
            this.notes = '';
            // Prevenir scroll do body quando modal aberto
            document.body.style.overflow = 'hidden';
        },

        closeProduct() {
            this.selectedProduct = null;
            this.selectedVariation = null;
            this.quantity = 1;
            this.notes = '';
            // Restaurar scroll do body
            document.body.style.overflow = '';
        },

        addToCart() {
            if (!this.selectedVariation) return;

            const price = parseFloat(this.selectedProduct.price) + parseFloat(this.selectedVariation.price_modifier);

            this.cart.push({
                id: this.selectedProduct.id,  // checkout espera 'id'
                name: this.selectedProduct.name,  // para exibição
                variationId: this.selectedVariation.id,  // checkout espera 'variationId'
                variationName: this.selectedVariation.name,  // para exibição
                price: price,
                quantity: this.quantity,
                details: this.notes,  // checkout espera 'details'
                image: this.selectedProduct.image,
                addons: []  // checkout espera 'addons'
            });

            this.saveCart();
            this.closeProduct();

            // Notificação mais elegante
            this.showNotification(`✅ ${this.quantity}x ${this.selectedProduct.name} (${this.selectedVariation.name}) adicionado!`);
        },

        showNotification(message) {
            // Criar notificação toast (clean)
            const toast = document.createElement('div');
            toast.className = 'fixed top-24 left-1/2 -translate-x-1/2 bg-green-500 text-white px-5 py-3 rounded-xl shadow-lg z-[60] text-sm font-medium';
            toast.style.transition = 'all 0.3s ease';
            toast.style.opacity = '0';
            toast.textContent = message;
            document.body.appendChild(toast);

            // Fade in
            setTimeout(() => { toast.style.opacity = '1'; }, 10);

            // Fade out
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(-10px)';
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        },

        vibrate(duration = 10) {
            if ('vibrate' in navigator) {
                navigator.vibrate(duration);
            }
        }
    }
}
</script>
@endsection
// Cache bust: 1772616380
