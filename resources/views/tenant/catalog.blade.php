@extends('tenant.layouts.app')

@section('title', 'Cardápio')

@section('content')
<div x-data="catalogApp()" x-init="init()" class="bg-gray-50">
    <!-- Header -->
    <div class="sticky top-0 bg-gradient-to-r from-primary-500 to-primary-600 border-b border-primary-700 z-40 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-4 md:py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-white">{{ $tenant->name }}</h1>
                    <p class="text-sm md:text-base text-white/90 mt-1">Marmitas fresquinhas todos os dias</p>
                </div>
                <!-- Busca Desktop -->
                <div class="hidden md:block">
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Buscar produtos..."
                           class="w-64 lg:w-80 px-4 py-2.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 focus:bg-white/20">
                </div>
            </div>
        </div>
    </div>

    <!-- Busca Mobile -->
    <div class="md:hidden sticky top-[88px] bg-white border-b border-gray-100 z-30 px-4 py-3 shadow-sm">
        <input type="text"
               x-model="searchQuery"
               placeholder="🔍 Buscar produtos..."
               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 focus:bg-white">
    </div>

    <!-- Categorias (fixo) -->
    <div class="sticky top-[148px] md:top-[100px] bg-white border-b border-gray-100 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-3">
            <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                <button @click="selectedCategory = null"
                        :class="selectedCategory === null ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap transition-smooth">
                    Todas
                </button>
                <template x-for="category in categories" :key="category.id">
                    <button @click="selectedCategory = category.id"
                            :class="selectedCategory === category.id ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap transition-smooth"
                            x-text="category.name"></button>
                </template>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="max-w-2xl mx-auto px-4 py-12">
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <div class="animate-spin w-12 h-12 border-3 border-gray-300 border-t-gray-900 rounded-full mx-auto mb-4"></div>
            <p class="text-sm text-gray-500">Carregando...</p>
        </div>
    </div>

    <!-- Produtos -->
    <div x-show="!loading" class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-6 pb-32 md:pb-8">
        <!-- Grid Responsivo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
            <template x-for="product in filteredProducts" :key="product.id">
                <div @click="openProduct(product)"
                     class="bg-white border border-gray-200 rounded-xl overflow-hidden card-hover transition-smooth cursor-pointer hover:shadow-lg group">
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
                        <div x-show="product.is_featured"
                             class="absolute top-3 left-3 bg-primary-500 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-md z-20">
                            ⭐ Destaque
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="p-4 md:p-5">
                        <h3 class="font-bold text-gray-900 mb-2 text-base md:text-lg" x-text="product.name"></h3>
                        <p class="text-xs md:text-sm text-gray-600 mb-4 line-clamp-2" x-text="product.description"></p>

                        <!-- Preços -->
                        <div class="flex items-center justify-between">
                            <div class="flex gap-3 md:gap-4">
                                <template x-for="variation in product.variations" :key="variation.id">
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500 font-medium mb-1" x-text="variation.name"></p>
                                        <p class="text-sm md:text-base font-bold text-primary-500"
                                           x-text="'R$ ' + (parseFloat(product.price) + parseFloat(variation.price_modifier)).toFixed(2)"></p>
                                    </div>
                                </template>
                            </div>
                            <button class="p-2.5 md:p-3 bg-primary-50 text-primary-500 rounded-lg hover:bg-primary-100 transition-smooth group-hover:bg-primary-500 group-hover:text-white">
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
                        <button @click="addToCart()"
                                :disabled="!selectedVariation"
                                :class="selectedVariation ? 'bg-primary-500 hover:bg-primary-600 active:bg-primary-700' : 'bg-gray-300 cursor-not-allowed'"
                                class="w-full py-3.5 text-white rounded-xl font-bold text-base transition-all active:scale-98 disabled:active:scale-100 shadow-sm">
                            <span x-show="selectedVariation">
                                Adicionar R$ <span x-text="((parseFloat(selectedProduct.price) + parseFloat(selectedVariation?.price_modifier || 0)) * quantity).toFixed(2)"></span>
                            </span>
                            <span x-show="!selectedVariation">Escolha um tamanho</span>
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

        async init() {
            await Promise.all([
                this.loadProducts(),
                this.loadCategories(),
                this.loadCart()
            ]);
            this.loading = false;
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
