<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ tenant('name') ?? 'Restaurante' }} - Cardápio Online</title>

    <!-- 🔥 TESTE: Arquivo atualizado em {{ now()->format('d/m/Y H:i:s') }} 🔥 -->

    <!-- PWA Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $settings->restaurant_name ?? tenant('name') }}">
    <meta name="theme-color" content="#EA1D2C">
    <link rel="manifest" href="/manifest.json">
    @if($settings && $settings->logo)
    <link rel="apple-touch-icon" href="{{ url('storage/' . $settings->logo) }}">
    @endif

    <!-- 🔥 OAuth Auto-Login - EXECUTA PRIMEIRO! -->
    <script>
        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('oauth_success')) {
                const authToken = urlParams.get('auth_token');
                const customerData = urlParams.get('customer_data');
                if (authToken && customerData) {
                    const customer = JSON.parse(decodeURIComponent(customerData));
                    // ⭐ Adicionar tenant_id aos dados do customer
                    customer.tenant_id = '{{ tenant("id") }}';
                    customer.tenant_domain = '{{ request()->getHost() }}';

                    localStorage.setItem('auth_token', authToken);
                    localStorage.setItem('customer', JSON.stringify(customer));
                    window.location.href = '/';
                }
            }
        })();

        // 🔒 VALIDADOR DE TENANT - Previne vazamento de dados entre restaurantes
        (function() {
            const currentTenantId = '{{ tenant("id") }}';
            const currentDomain = '{{ request()->getHost() }}';
            const savedCustomer = localStorage.getItem('customer');

            if (savedCustomer && currentTenantId) {
                try {
                    const customer = JSON.parse(savedCustomer);

                    // Se o tenant_id salvo for diferente do atual, LIMPAR dados!
                    if (customer.tenant_id && customer.tenant_id !== currentTenantId) {
                        console.warn('⚠️ Mudou de restaurante! Limpando dados do restaurante anterior...');
                        console.log('Anterior:', customer.tenant_domain, '→ Atual:', currentDomain);

                        // Limpar dados do restaurante anterior
                        localStorage.removeItem('customer');
                        localStorage.removeItem('cart'); // Limpar carrinho antigo (migração)
                        localStorage.removeItem('yumgo_cart'); // Limpar carrinho compartilhado antigo

                        // Buscar dados do customer neste restaurante via API
                        const authToken = localStorage.getItem('auth_token');
                        if (authToken) {
                            fetch('/api/v1/customer/profile', {
                                headers: {
                                    'Authorization': 'Bearer ' + authToken,
                                    'Accept': 'application/json'
                                }
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    // Adicionar tenant_id aos dados
                                    data.customer.tenant_id = currentTenantId;
                                    data.customer.tenant_domain = currentDomain;
                                    localStorage.setItem('customer', JSON.stringify(data.customer));
                                    console.log('✅ Dados atualizados para o novo restaurante:', data.customer);
                                    window.location.reload();
                                }
                            })
                            .catch(err => {
                                console.error('❌ Erro ao buscar perfil:', err);
                                // Se falhar, limpar token também
                                localStorage.clear();
                            });
                        }
                    } else if (!customer.tenant_id) {
                        // Se não tem tenant_id, adicionar agora
                        customer.tenant_id = currentTenantId;
                        customer.tenant_domain = currentDomain;
                        localStorage.setItem('customer', JSON.stringify(customer));
                    }
                } catch (e) {
                    console.error('Erro ao validar tenant:', e);
                }
            }
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body{font-family:'Poppins',sans-serif}
        [x-cloak]{display:none!important}
        .product-card{transition:all 0.2s ease}
        .product-card:hover{transform:translateY(-2px)}
        html{scroll-behavior:smooth}
        .scrollbar-hide::-webkit-scrollbar{display:none}
        .scrollbar-hide{-ms-overflow-style:none;scrollbar-width:none}

        /* Skeleton loading */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s ease-in-out infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Animação Minimalista - Estilo Uber Eats/WhatsApp */
        @keyframes yumgo-jump {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-12px);
            }
        }

        /* Header fixo no topo */
        header {
            position: sticky !important;
            top: 0 !important;
            z-index: 100 !important;
            background: white !important;
        }

        /* Info Banner sempre fixo logo abaixo do header */
        .sticky-info-banner {
            position: -webkit-sticky !important;
            position: sticky !important;
            top: 110px !important; /* Desktop: altura do header (~110px) */
            z-index: 90 !important;
            background: white !important;
            width: 100% !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
        }

        /* Mobile: ajustar top do banner (header maior no mobile) */
        @media (max-width: 768px) {
            .sticky-info-banner {
                top: 155px !important; /* Mobile: header ~155px */
            }
        }

        /* Tablet: valor intermediário */
        @media (min-width: 769px) and (max-width: 1023px) {
            .sticky-info-banner {
                top: 120px !important;
            }
        }

        /* Layout Desktop - Estilo iFood */
        @media (min-width: 1024px) {
            body {
                background: #f5f5f5;
            }
            /* Header ocupa toda largura */
            body > header {
                width: 100%;
                background: white;
                position: sticky;
                top: 0;
                z-index: 50;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            /* Conteúdo interno do header alinhado */
            body > header .max-w-7xl {
                max-width: 1280px !important;
                margin: 0 auto !important;
            }
            /* Body centralizado com max-width - APENAS conteúdo principal */
            body > div:not(#pwa-install-banner):not([x-show*="Modal"]):not([x-show*="show"]):not(.sticky-info-banner):not(.sticky-category-filters) {
                max-width: 1280px !important;
                margin-left: auto !important;
                margin-right: auto !important;
                background: white;
                padding: 24px !important;
            }

            /* FORÇAR sticky nos filtros e info banner */
            .sticky-info-banner,
            .sticky-category-filters {
                position: -webkit-sticky !important;
                position: sticky !important;
                left: 0 !important;
                right: 0 !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
            /* Grid de produtos - 2 colunas */
            .grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 20px !important;
            }
            /* Cards de produtos - Layout horizontal estilo iFood */
            .product-card {
                display: flex !important;
                flex-direction: row !important;
                height: auto !important;
                align-items: stretch !important;
            }
            /* Container da imagem - tamanho fixo pequeno */
            .product-card > div:first-child {
                width: 150px !important;
                min-width: 150px !important;
                height: 150px !important;
            }
            .product-card img {
                width: 150px !important;
                height: 150px !important;
                object-fit: cover !important;
            }
            /* Conteúdo do produto à direita */
            .product-card > div:last-child {
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }
            /* Typography no desktop */
            .product-card h4 {
                font-size: 1rem !important;
                font-weight: 700 !important;
            }
            .product-card p {
                font-size: 0.875rem !important;
                line-height: 1.4 !important;
            }
            /* Modais de login/registro no desktop - tamanho menor */
            [x-show*="LoginModal"] > div,
            [x-show*="RegisterModal"] > div,
            [x-show*="ForgotModal"] > div {
                max-width: 450px !important;
                width: 450px !important;
            }
            /* Modal de pizza no desktop - tamanho maior */
            [x-show*="pizzaModal.show"] > div {
                max-width: 900px !important;
                width: 90% !important;
            }
        }
    </style>
    <script>
        tailwind.config={theme:{extend:{colors:{primary:'#EA1D2C',secondary:'#717171',accent:'#3E3E3E'}}}}
        // Dados das pizzas do backend
        window.pizzaData = @json($pizzaConfigs);
    </script>
</head>
<body class="bg-gray-50 min-h-screen" x-data="restaurantApp()">

    <!-- 🔄 Loading Screen - Jumping Dots (Estilo Moderno) -->
    <div x-show="pageLoading" x-cloak x-transition.opacity class="fixed inset-0 bg-white z-[99999] flex items-center justify-center">
        <div class="text-center">
            <!-- 3 Bolinhas Pulando (Minimalista) -->
            <div class="flex items-center justify-center gap-2 mb-6">
                <div class="w-3 h-3 bg-red-500 rounded-full" style="animation: yumgo-jump 0.6s infinite ease-in-out; animation-delay: 0s;"></div>
                <div class="w-3 h-3 bg-red-500 rounded-full" style="animation: yumgo-jump 0.6s infinite ease-in-out; animation-delay: 0.1s;"></div>
                <div class="w-3 h-3 bg-red-500 rounded-full" style="animation: yumgo-jump 0.6s infinite ease-in-out; animation-delay: 0.2s;"></div>
            </div>

            <!-- Texto -->
            <h3 class="text-xl font-semibold text-gray-600">Preparando seu cardápio</h3>
        </div>
    </div>

    <!-- 📱 PWA Install Banner (Aparece após 3 segundos) -->
    <div x-show="showPWABanner"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-4 left-4 right-4 md:left-auto md:right-6 md:max-w-sm z-[9999] bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">

        <!-- Conteúdo -->
        <div class="p-5">
            <div class="flex items-start gap-4">
                <!-- Ícone do App -->
                <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>

                <!-- Texto -->
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">
                        📱 Instalar YumGo
                    </h3>
                    <p class="text-sm text-gray-600 mb-3">
                        Acesso rápido, notificações e pedidos offline!
                    </p>

                    <!-- Botões -->
                    <div class="flex gap-2">
                        <button @click="installPWA()"
                                class="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold text-sm rounded-lg transition-smooth">
                            Instalar Agora
                        </button>
                        <button @click="dismissPWABanner()"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-sm rounded-lg transition-smooth">
                            Agora não
                        </button>
                    </div>
                </div>

                <!-- Botão Fechar -->
                <button @click="dismissPWABanner()"
                        class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-smooth">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Barra de Cor -->
        <div class="h-1 bg-gradient-to-r from-red-500 to-red-600"></div>
    </div>

    <!-- Header -->
    <header class="bg-white sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-3 py-3">
            <!-- Primeira linha: Logo e Botões -->
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-4">
                    @if($settings?->logo)
                    <img src="{{ url('storage/' . $settings->logo) }}" class="h-20 w-20 md:h-24 md:w-24 rounded-2xl object-cover shadow-lg ring-2 ring-gray-100">
                    @else
                    <div class="h-20 w-20 md:h-24 md:w-24 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center text-4xl shadow-lg">🍽️</div>
                    @endif
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ tenant('name') ?? 'Restaurante' }}</h1>
                        <div class="flex items-center gap-2 text-sm text-gray-600 mt-1">
                            @if($isOpen)
                                <span class="flex items-center gap-1">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    <span class="text-green-600 font-semibold">Aberto agora</span>
                                </span>
                                <span class="hidden md:inline text-gray-400">•</span>
                                <span class="hidden md:inline">30-45 min</span>
                            @else
                                <span class="flex items-center gap-1">
                                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                    <span class="text-red-600 font-semibold">Fechado</span>
                                </span>
                                @if($openTime)
                                    <span class="hidden md:inline text-gray-400">•</span>
                                    <span class="hidden md:inline">Abre às {{ $openTime }}</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Botão Login Mobile (quando NÃO logado) -->
                    <button @click="showLoginModal = true" x-show="!isLoggedIn" class="md:hidden p-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </button>

                    <!-- Botão Login Desktop (quando NÃO logado) -->
                    <button @click="showLoginModal = true" x-show="!isLoggedIn" class="hidden md:block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        Entrar
                    </button>

                    <!-- Menu do Usuário (quando logado) -->
                    <div x-show="isLoggedIn" x-cloak class="relative hidden md:block" @click.away="showUserMenu = false">
                        <button @click="showUserMenu = !showUserMenu" class="px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 rounded-lg transition flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span x-text="customerName"></span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Dropdown Menu Clean -->
                        <div x-show="showUserMenu" x-transition class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden z-50">
                            <!-- Header Clean -->
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                <p class="font-semibold text-gray-900 text-sm" x-text="customerName"></p>
                                <p class="text-xs text-gray-500" x-text="customerEmail"></p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded font-medium">
                                        R$ <span x-text="cashbackBalance"></span>
                                    </span>
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded font-medium" x-text="loyaltyTier"></span>
                                </div>
                            </div>
                            <!-- Menu Items Clean -->
                            <div class="py-2">
                                <a href="/perfil" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Meu Perfil</span>
                                </a>
                                <a href="/meus-pedidos" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Meus Pedidos</span>
                                </a>
                                <a href="/perfil" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Meu Cashback</span>
                                </a>
                                <div class="border-t border-gray-100 mt-1 pt-1">
                                    <button @click="logout()" class="w-full flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span class="text-sm font-medium">Sair</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botão Carrinho -->
                    <button @click="showCart=!showCart" class="relative px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition flex items-center gap-2 text-sm font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span x-show="cartCount>0" x-text="cartCount" class="font-bold"></span>
                    </button>
                </div>
            </div>

            <!-- Segunda linha: Busca -->
            <div class="relative w-full">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input x-model="searchQuery" type="text" placeholder="Buscar no cardápio" class="w-full pl-10 pr-3 py-2 bg-gray-50 rounded-lg border border-gray-200 focus:border-red-500 focus:bg-white transition text-sm outline-none">
            </div>
        </div>
    </header>

    <!-- Modal de Login -->
    <div x-show="showLoginModal" @click.self="showLoginModal = false" x-cloak x-transition.opacity class="fixed inset-0 bg-black/30 z-[9999] flex items-end md:items-center justify-center p-0 md:p-4">
        <div @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-full md:translate-y-0 md:scale-95" x-transition:enter-end="opacity-100 translate-y-0 md:scale-100" class="bg-white w-full md:w-full md:max-w-md rounded-t-3xl md:rounded-2xl shadow-2xl">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-5 py-3 rounded-t-3xl md:rounded-t-2xl flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900">Entrar</h2>
                <button @click="showLoginModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-5">
                <!-- Sucesso -->
                <div x-show="loginSuccess" x-transition class="mb-3 p-2.5 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm font-semibold">
                    <span x-text="loginSuccess"></span>
                </div>

                <!-- Erro -->
                <div x-show="loginError" x-transition class="mb-3 p-2.5 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    <span x-text="loginError"></span>
                </div>

                <!-- Login Social -->
                <div class="mb-4 space-y-2.5">
                    <!-- Google -->
                    <a href="/auth/google/redirect" class="w-full py-3 px-4 bg-white border-2 border-gray-300 rounded-xl flex items-center justify-center gap-3 hover:bg-gray-50 transition font-semibold text-gray-700 text-sm">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span>Continuar com Google</span>
                    </a>

                    <!-- Facebook -->
                    <a href="/auth/facebook/redirect" class="w-full py-3 px-4 bg-[#1877F2] rounded-xl flex items-center justify-center gap-3 hover:bg-[#166FE5] transition font-semibold text-white text-sm">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span>Continuar com Facebook</span>
                    </a>
                </div>

                <!-- Divisor -->
                <div class="relative mb-4">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="px-3 bg-white text-gray-500 font-medium">ou</span>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="login()" class="space-y-3">
                    <!-- Celular ou Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Celular ou Email</label>
                        <input
                            x-model="loginEmail"
                            type="text"
                            required
                            placeholder="(00) 00000-0000 ou email"
                            class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition text-base"
                            :disabled="loginLoading">
                    </div>

                    <!-- Senha -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Senha</label>
                        <input
                            x-model="loginPassword"
                            type="password"
                            required
                            placeholder="••••••••"
                            class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition text-base"
                            :disabled="loginLoading">
                    </div>

                    <!-- Esqueci a senha -->
                    <div class="text-right">
                        <button type="button" @click="showLoginModal = false; showForgotModal = true" class="text-xs text-red-600 hover:text-red-700 font-semibold">
                            Esqueci minha senha
                        </button>
                    </div>

                    <!-- Botão Entrar -->
                    <button
                        type="submit"
                        :disabled="loginLoading"
                        :class="loginLoading ? 'bg-gray-400 cursor-not-allowed' : 'bg-gradient-to-r from-red-500 to-red-600 hover:shadow-lg'"
                        class="w-full py-3 text-white rounded-lg font-bold text-base transition-all">
                        <span x-show="!loginLoading">Entrar</span>
                        <span x-show="loginLoading" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Entrando...
                        </span>
                    </button>
                </form>

                <!-- Criar Conta -->
                <div class="text-center pb-2">
                    <p class="text-sm text-gray-600">
                        Não tem uma conta?
                        <button type="button" @click="showLoginModal = false; showRegisterModal = true" class="text-red-600 hover:text-red-700 font-bold">
                            Cadastre-se
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Cadastro -->
    <div x-show="showRegisterModal" @click.self="showRegisterModal = false" x-cloak x-transition.opacity class="fixed inset-0 bg-black/30 z-[9999] flex items-end md:items-center justify-center p-0 md:p-4">
        <div @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-full md:translate-y-0 md:scale-95" x-transition:enter-end="opacity-100 translate-y-0 md:scale-100" class="bg-white w-full md:w-full md:max-w-md rounded-t-3xl md:rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-5 py-3 rounded-t-3xl md:rounded-t-2xl flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900">Criar Conta</h2>
                <button @click="showRegisterModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-5">
                <!-- Sucesso -->
                <div x-show="registerSuccess" x-transition class="mb-3 p-2.5 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm font-semibold">
                    <span x-text="registerSuccess"></span>
                </div>

                <!-- Erro -->
                <div x-show="registerError" x-transition class="mb-3 p-2.5 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    <span x-text="registerError"></span>
                </div>

                <!-- Form -->
                <form @submit.prevent="register()" class="space-y-3">
                    <!-- Nome -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nome completo</label>
                        <input
                            x-model="registerName"
                            type="text"
                            required
                            placeholder="João Silva"
                            class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition text-base"
                            :disabled="registerLoading">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                        <input
                            x-model="registerEmail"
                            type="email"
                            required
                            placeholder="seu@email.com"
                            class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition text-base"
                            :disabled="registerLoading">
                    </div>

                    <!-- Telefone -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Telefone</label>
                        <input
                            x-model="registerPhone"
                            type="tel"
                            required
                            placeholder="(11) 98765-4321"
                            class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition text-base"
                            :disabled="registerLoading">
                    </div>

                    <!-- Senha -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Senha</label>
                        <input
                            x-model="registerPassword"
                            type="password"
                            required
                            placeholder="Mínimo 6 caracteres"
                            class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition text-base"
                            :disabled="registerLoading">
                    </div>

                    <!-- Confirmar Senha -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmar senha</label>
                        <input
                            x-model="registerPasswordConfirm"
                            type="password"
                            required
                            placeholder="Digite a senha novamente"
                            class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition text-base"
                            :disabled="registerLoading">
                    </div>

                    <!-- Botão Cadastrar -->
                    <button
                        type="submit"
                        :disabled="registerLoading"
                        :class="registerLoading ? 'bg-gray-400 cursor-not-allowed' : 'bg-gradient-to-r from-red-500 to-red-600 hover:shadow-lg'"
                        class="w-full py-3 text-white rounded-lg font-bold text-base transition-all">
                        <span x-show="!registerLoading">Criar Conta</span>
                        <span x-show="registerLoading" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Criando conta...
                        </span>
                    </button>
                </form>

                <!-- Já tem conta -->
                <div class="text-center mt-4 pb-2">
                    <p class="text-sm text-gray-600">
                        Já tem uma conta?
                        <button type="button" @click="showRegisterModal = false; showLoginModal = true" class="text-red-600 hover:text-red-700 font-bold">
                            Entrar
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 📱 Modal de Validação WhatsApp -->
    <div x-show="showWhatsAppValidationModal" @click.self="showWhatsAppValidationModal = false" x-cloak x-transition.opacity class="fixed inset-0 bg-black/30 z-[9999] flex items-end md:items-center justify-center p-0 md:p-4">
        <div @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-full md:translate-y-0 md:scale-95" x-transition:enter-end="opacity-100 translate-y-0 md:scale-100" class="bg-white w-full md:w-full md:max-w-md rounded-t-3xl md:rounded-2xl shadow-2xl">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-5 py-4 rounded-t-3xl md:rounded-t-2xl flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">Validar WhatsApp</h2>
                        <p class="text-xs text-white/80">Para continuar usando o app</p>
                    </div>
                </div>
                <button @click="showWhatsAppValidationModal = false" class="text-white/80 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-5">
                <!-- Sucesso -->
                <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-green-900">Login realizado com sucesso!</p>
                            <p class="text-sm text-green-700 mt-1">Agora valide seu WhatsApp para aproveitar todas as funcionalidades.</p>
                        </div>
                    </div>
                </div>

                <!-- Etapa 1: Enviar Código -->
                <div x-show="!whatsappCodeSent">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Seu WhatsApp</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            </div>
                            <input
                                type="tel"
                                x-model="whatsappValidationPhone"
                                placeholder="(11) 99999-9999"
                                class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:ring-4 focus:ring-green-100 transition"
                                @keydown.enter="sendWhatsAppCode()">
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex flex-col gap-3">
                        <button
                            @click="sendWhatsAppCode()"
                            :disabled="whatsappValidationLoading || !whatsappValidationPhone"
                            class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <svg x-show="!whatsappValidationLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <svg x-show="whatsappValidationLoading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="whatsappValidationLoading ? 'Enviando...' : 'Enviar Código'"></span>
                        </button>

                        <button
                            @click="showWhatsAppValidationModal = false"
                            class="w-full py-3 border-2 border-gray-200 hover:border-gray-300 text-gray-700 font-bold rounded-lg transition">
                            Fazer Isso Depois
                        </button>
                    </div>
                </div>

                <!-- Etapa 2: Validar Código -->
                <div x-show="whatsappCodeSent">
                    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm text-blue-700">
                                Enviamos um código de 6 dígitos para <strong x-text="whatsappValidationPhone"></strong> via WhatsApp.
                            </p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Código de Verificação</label>
                        <input
                            type="text"
                            x-model="whatsappValidationCode"
                            placeholder="000000"
                            maxlength="6"
                            class="w-full px-4 py-3 text-center text-2xl font-mono tracking-widest border-2 border-gray-200 rounded-lg focus:border-green-500 focus:ring-4 focus:ring-green-100 transition"
                            @keydown.enter="validateWhatsAppCode()">
                    </div>

                    <!-- Erro -->
                    <div x-show="whatsappValidationError" class="mb-4 bg-red-50 border border-red-200 rounded-lg p-3">
                        <p class="text-sm text-red-700" x-text="whatsappValidationError"></p>
                    </div>

                    <!-- Botões -->
                    <div class="flex flex-col gap-3">
                        <button
                            @click="validateWhatsAppCode()"
                            :disabled="whatsappValidationLoading || whatsappValidationCode.length !== 6"
                            class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <svg x-show="!whatsappValidationLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <svg x-show="whatsappValidationLoading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="whatsappValidationLoading ? 'Validando...' : 'Validar Código'"></span>
                        </button>

                        <button
                            @click="whatsappCodeSent = false; whatsappValidationCode = ''"
                            class="w-full py-2 text-sm text-gray-600 hover:text-gray-800 font-medium transition">
                            ← Voltar e Reenviar
                        </button>

                        <button
                            @click="showWhatsAppValidationModal = false"
                            class="w-full py-2 text-sm text-gray-500 hover:text-gray-700 transition">
                            Fazer Isso Depois
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Esqueci a Senha -->
    <div x-show="showForgotModal" @click.self="showForgotModal = false" x-cloak x-transition.opacity class="fixed inset-0 bg-black/30 z-[9999] flex items-end md:items-center justify-center p-0 md:p-4">
        <div @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-full md:translate-y-0 md:scale-95" x-transition:enter-end="opacity-100 translate-y-0 md:scale-100" class="bg-white w-full md:w-full md:max-w-md rounded-t-3xl md:rounded-2xl shadow-2xl">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-5 py-3 rounded-t-3xl md:rounded-t-2xl flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900">Recuperar Senha</h2>
                <button @click="showForgotModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-5">
                <!-- Sucesso -->
                <div x-show="forgotSuccess" x-transition class="mb-3 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                    ✅ Email enviado! Verifique sua caixa de entrada.
                </div>

                <!-- Erro -->
                <div x-show="forgotError" x-transition class="mb-3 p-2.5 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    <span x-text="forgotError"></span>
                </div>

                <div x-show="!forgotSuccess">
                    <p class="text-sm text-gray-600 mb-4">
                        Digite seu email cadastrado e enviaremos um link para redefinir sua senha.
                    </p>

                    <!-- Form -->
                    <form @submit.prevent="forgotPassword()" class="space-y-3">
                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                            <input
                                x-model="forgotEmail"
                                type="email"
                                required
                                placeholder="seu@email.com"
                                class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 transition text-base"
                                :disabled="forgotLoading">
                        </div>

                        <!-- Botão Enviar -->
                        <button
                            type="submit"
                            :disabled="forgotLoading"
                            :class="forgotLoading ? 'bg-gray-400 cursor-not-allowed' : 'bg-gradient-to-r from-red-500 to-red-600 hover:shadow-lg'"
                            class="w-full py-3 text-white rounded-lg font-bold text-base transition-all">
                            <span x-show="!forgotLoading">Enviar Link</span>
                            <span x-show="forgotLoading" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Enviando...
                            </span>
                        </button>
                    </form>
                </div>

                <!-- Voltar para login -->
                <div class="text-center mt-4 pb-2">
                    <button type="button" @click="showForgotModal = false; showLoginModal = true" class="text-sm text-gray-600 hover:text-gray-800">
                        ← Voltar para login
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Aviso de Fechado (Discreto) -->
    @if(!$isOpen)
    <div class="bg-gray-100 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-3 py-2 text-center">
            <p class="text-sm text-gray-600">
                🕐 Estamos fechados no momento
                @if($openTime && $closeTime)
                    • Abrimos às {{ $openTime }}
                @endif
            </p>
        </div>
    </div>
    @endif

    <!-- Info Banner + Categorias (estilo iFood - INLINE e STICKY) -->
    <div class="sticky-info-banner bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-3 py-3">
            <!-- Primeira Linha: Rating + Tempo + Categorias -->
            <div class="flex items-center gap-3 overflow-x-auto scrollbar-hide">
                <!-- Rating -->
                @if($averageRating && $totalReviews > 0)
                <div class="flex items-center gap-1 text-xs text-gray-600 shrink-0">
                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span class="font-semibold text-gray-900">{{ $averageRating }}</span>
                    <span class="text-gray-500">({{ $totalReviews }})</span>
                </div>
                @endif

                <!-- Tempo de Entrega -->
                <span class="text-xs text-gray-600 shrink-0">30-45 min</span>

                <!-- Divisor -->
                @if($categories->count() > 0)
                <div class="w-px h-5 bg-gray-300 shrink-0"></div>

                <!-- Categorias Inline -->
                <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                    @foreach($categories as $category)
                    <a href="#category-{{ $category->id }}"
                       @click.prevent="
                           selectedCategory='{{ $category->id }}';
                           document.getElementById('category-{{ $category->id }}').scrollIntoView({behavior: 'smooth', block: 'start'});
                       "
                       :class="selectedCategory==='{{ $category->id }}'?'bg-red-600 text-white':'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                       class="px-3 py-1.5 rounded-full font-semibold text-xs whitespace-nowrap transition">
                        {{ $category->name }}
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Produtos por Categoria -->
    <div class="max-w-7xl mx-auto px-3 py-4 md:py-6">
        @if($allProducts->count() > 0)
            <!-- Banner Preview Mode -->
            @if($previewMode ?? false)
            <div class="mb-4 bg-gradient-to-r from-gray-100 to-gray-200 rounded-xl p-3 border border-gray-300">
                <div class="flex items-center gap-2">
                    <div class="text-2xl">📋</div>
                    <div class="flex-1">
                        <h3 class="text-sm font-black text-gray-700 mb-0.5">Cardápio não disponível hoje</h3>
                        <p class="text-gray-600 text-xs">Veja abaixo nossos produtos. Eles estarão disponíveis em outros dias da semana!</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Mostrar produtos agrupados por categoria -->
            @foreach($categories as $category)
            <div id="category-{{ $category->id }}" class="mb-8 scroll-mt-40">
                <!-- Cabeçalho da Categoria -->
                <div class="mb-4 pb-2 border-b-2 border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">
                        {{ $category->name }}
                    </h3>
                    @if($category->description)
                        <p class="text-gray-600 text-sm mt-1">{{ $category->description }}</p>
                    @endif
                </div>

                <!-- Grid de Produtos da Categoria -->
                <!-- 1 Coluna Mobile | 2 Colunas Desktop -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($category->products as $product)
                    @php
                        // ✅ Produto indisponível se:
                        // 1. Restaurante fechado OU
                        // 2. Produto não está no cardápio de hoje
                        $isProductAvailableToday = in_array($product->id, $todayProductIds ?? []);
                        $isProductUnavailable = !$isOpen || !$isProductAvailableToday;
                    @endphp
                    <div
                        x-show="searchQuery === '' || '{{ addslashes(strtolower($product->name)) }}'.includes(searchQuery.toLowerCase())"
                        class="bg-white rounded-lg border border-gray-200 overflow-hidden {{ $isProductUnavailable ? 'opacity-60' : '' }}">

                        <!-- Container Horizontal -->
                        <div class="flex gap-3 p-3 {{ $isProductUnavailable ? 'pointer-events-none' : 'cursor-pointer' }}"
                            @if(!$isProductUnavailable)
                            @click="openProductModal({
                                id: {{ $product->id }},
                                name: '{{ addslashes($product->name) }}',
                                description: '{{ addslashes($product->description ?? '') }}',
                                price: {{ $product->price }},
                                image: {{ $product->image ? json_encode(str_starts_with($product->image, 'http') ? $product->image : '/storage/' . $product->image) : 'null' }},
                                isPizza: {{ $product->is_pizza ? 'true' : 'false' }},
                                hasVariations: {{ $product->variations && $product->variations->count() > 0 ? 'true' : 'false' }},
                                variations: {{ $product->variations && $product->variations->count() > 0 ? json_encode($product->variations->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => $v->price])->toArray()) : '[]' }}
                            })"
                            @endif>

                            <!-- Imagem (Esquerda - Tamanho Fixo) -->
                            <div class="relative w-24 h-24 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
                                @if($product->image)
                                    <img
                                        src="{{ str_starts_with($product->image, 'http') ? $product->image : '/storage/' . $product->image }}"
                                        alt="{{ $product->name }}"
                                        class="w-full h-full object-cover {{ $isProductUnavailable ? 'grayscale opacity-60' : '' }}"
                                        loading="lazy">
                                @else
                                @php
                                    $imageMap = [
                                        'pizza' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=500&h=500&fit=crop&crop=center&v=2',
                                        'mussarela' => 'https://images.unsplash.com/photo-1571997478779-2adcbbe9ab2f?w=500&h=500&fit=crop&crop=center&v=2',
                                        'calabresa' => 'https://images.unsplash.com/photo-1534308983496-4fabb1a015ee?w=500&h=500&fit=crop&crop=center&v=2',
                                        'portuguesa' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=500&h=500&fit=crop&crop=center&v=2',
                                        'marguerita' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=500&h=500&fit=crop&crop=center&v=2',
                                        'queijo' => 'https://images.unsplash.com/photo-1528137871618-79d2761e3fd5?w=500&h=500&fit=crop&crop=center&v=2',
                                        'frango' => 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=500&h=500&fit=crop&crop=center&v=2',
                                        'hamburger' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=500&h=500&fit=crop&crop=center&v=2',
                                        'burger' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=500&h=500&fit=crop&crop=center&v=2',
                                        'batata' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=500&h=500&fit=crop&crop=center&v=2',
                                        'refrigerante' => 'https://images.unsplash.com/photo-1629203851122-3726ecdf080e?w=500&h=500&fit=crop&crop=center&v=2',
                                        'coca' => 'https://images.unsplash.com/photo-1629203851122-3726ecdf080e?w=500&h=500&fit=crop&crop=center&v=2',
                                        'suco' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=500&h=500&fit=crop&crop=center&v=2',
                                        'sobremesa' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=500&h=500&fit=crop&crop=center&v=2',
                                        'pudim' => 'https://images.unsplash.com/photo-1624353365286-3f8d62daad51?w=500&h=500&fit=crop&crop=center&v=2',
                                        'brownie' => 'https://images.unsplash.com/photo-1607920591413-4ec007e70023?w=500&h=500&fit=crop&crop=center&v=2',
                                        'sorvete' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=500&h=500&fit=crop&crop=center&v=2',
                                        'pastel' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=500&h=500&fit=crop&crop=center&v=2',
                                        'coxinha' => 'https://images.unsplash.com/photo-1626200419199-391ae4be7a41?w=500&h=500&fit=crop&crop=center&v=2',
                                    ];
                                    $productName = strtolower($product->name);
                                    $defaultImage = 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=500&h=500&fit=crop&crop=center&v=2';
                                    foreach($imageMap as $key => $url) {
                                        if(str_contains($productName, $key)) {
                                            $defaultImage = $url;
                                            break;
                                        }
                                    }
                                @endphp
                                    <img
                                        src="{{ $defaultImage }}"
                                        alt="{{ $product->name }}"
                                        class="w-full h-full object-cover {{ $isProductUnavailable ? 'grayscale opacity-60' : '' }}"
                                        loading="lazy">
                                @endif

                                @if($product->is_featured && !$isProductUnavailable)
                                    <span class="absolute top-1 right-1 px-1.5 py-0.5 bg-yellow-400 text-yellow-900 text-xs font-bold rounded">⭐</span>
                                @endif
                            </div>

                            <!-- Conteúdo (Direita) -->
                            <div class="flex-1 flex flex-col justify-between min-w-0 {{ $isProductUnavailable ? 'opacity-60' : '' }}">
                                <div class="mb-2">
                                    <h4 class="font-semibold text-base mb-1 {{ $isProductUnavailable ? 'text-gray-500' : 'text-gray-900' }} line-clamp-1">{{ $product->name }}</h4>

                                    @if($product->description)
                                        <p class="text-sm {{ $isProductUnavailable ? 'text-gray-400' : 'text-gray-600' }} line-clamp-2">{{ $product->description }}</p>
                                    @endif
                                </div>

                                <!-- Preço e Botão -->
                                @if($product->variations && $product->variations->count() > 0)
                                    <div class="flex items-center justify-between gap-2">
                                        <div>
                                            <p class="text-xs text-gray-500">A partir de</p>
                                            <p class="font-bold text-lg text-gray-900">
                                                R$ {{ number_format($product->variations->min('price'), 2, ',', '.') }}
                                            </p>
                                        </div>
                                        <button
                                            @click.stop="openVariationModal(
                                                {{ $product->id }},
                                                {{ json_encode($product->name) }},
                                                {{ json_encode($product->variations->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => $v->price])->toArray()) }},
                                                {{ json_encode($product->image) }}
                                            )"
                                            {{ $isProductUnavailable ? 'disabled' : '' }}
                                            class="w-9 h-9 bg-red-500 text-white font-bold text-xl rounded-full flex items-center justify-center {{ $isProductUnavailable ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-600' }}">
                                            +
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-bold text-lg text-gray-900">
                                            R$ {{ number_format($product->price, 2, ',', '.') }}
                                        </p>
                                        @if($product->is_pizza)
                                            <button
                                                @click.stop="openPizzaModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }})"
                                                {{ $isProductUnavailable ? 'disabled' : '' }}
                                                class="w-9 h-9 bg-red-500 text-white font-bold text-xl rounded-full flex items-center justify-center {{ $isProductUnavailable ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-600' }}">
                                                +
                                            </button>
                                        @else
                                            <button
                                                @click.stop="addToCart({id:{{ $product->id }},name:'{{ addslashes($product->name) }}',price:{{ $product->price }}})"
                                                {{ $isProductUnavailable ? 'disabled' : '' }}
                                                class="w-9 h-9 bg-red-500 text-white font-bold text-xl rounded-full flex items-center justify-center {{ $isProductUnavailable ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-600' }}">
                                                +
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <!-- Fim dos Produtos -->
        @else
            <div class="text-center py-12">
                @if($emptyReason === 'closed')
                    {{-- Fora do horário de funcionamento --}}
                    <div class="w-20 h-20 bg-gradient-to-br from-red-100 to-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-800 mb-2">🔒 Estamos Fechados</h3>
                    <p class="text-gray-600 text-sm mb-2">Voltamos em breve!</p>
                    @if($openTime && $closeTime)
                        <div class="inline-block mt-3 px-4 py-2 bg-gradient-to-r from-red-100 to-red-100 rounded-xl border-2 border-red-200">
                            <p class="text-xs text-gray-600 mb-1">Horário de funcionamento hoje:</p>
                            <p class="text-lg font-black bg-gradient-to-r from-red-600 to-red-600 bg-clip-text text-transparent">{{ $openTime }} - {{ $closeTime }}</p>
                        </div>
                    @endif
                @elseif($emptyReason === 'no_weekly_menu')
                    {{-- Cardápio semanal não configurado --}}
                    <div class="w-20 h-20 bg-gradient-to-br from-yellow-100 to-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-800 mb-2">⚙️ Cardápio em Configuração</h3>
                    <p class="text-gray-600 text-sm mb-4">Estamos preparando nosso cardápio especial para você!</p>
                    <p class="text-gray-500 text-xs">Em breve você poderá fazer pedidos. Volte em alguns instantes! 🎉</p>
                @elseif($emptyReason === 'no_menu')
                    {{-- Cardápio não cadastrado para hoje --}}
                    <div class="w-20 h-20 bg-gradient-to-br from-red-100 to-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-800 mb-2">📋 Cardápio não disponível hoje</h3>
                    <p class="text-gray-600 text-sm">Não temos produtos cadastrados para hoje. Volte amanhã para conferir nossas delícias! 🎉</p>
                    @if($openTime && $closeTime)
                        <p class="text-xs text-gray-500 mt-3">Horário: {{ $openTime }} - {{ $closeTime }}</p>
                    @endif
                @else
                    {{-- Nenhum produto cadastrado --}}
                    <div class="w-20 h-20 bg-gradient-to-br from-red-100 to-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-800 mb-2">Cardápio em Preparação</h3>
                    <p class="text-gray-600 text-sm">Estamos preparando delícias incríveis para você! Volte em breve 🎉</p>
                @endif
            </div>
        @endif
    </div>

    <!-- Modal Pizza COM INGREDIENTES -->
    <div x-show="pizzaModal.show" x-cloak @click.self="closePizzaModal()" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[60] flex items-center justify-center p-4" x-transition>
        <div class="bg-white rounded-3xl max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl" @click.stop x-transition>
            <div class="relative bg-gradient-to-r bg-red-600 text-white p-8 rounded-t-3xl overflow-hidden">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS1vcGFjaXR5PSIwLjEiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-30"></div>
                <div class="relative flex justify-between items-start">
                    <div>
                        <h3 class="text-4xl font-black mb-2">🍕 Monte sua Pizza</h3>
                        <p class="text-xl text-white/90 font-medium" x-text="pizzaModal.productName"></p>
                    </div>
                    <button @click="closePizzaModal()" class="text-white hover:bg-white/20 rounded-2xl p-3 transition">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="p-8 space-y-8">
                <!-- INGREDIENTES DA PIZZA PRINCIPAL -->
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-500 rounded-2xl p-5">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">✓</div>
                        <div class="flex-1">
                            <div class="font-bold text-lg mb-1" x-text="pizzaModal.productName"></div>
                            <div class="text-sm text-gray-700 font-medium">🧀 Ingredientes:</div>
                            <div class="text-sm text-gray-600 leading-relaxed" x-text="pizzaModal.ingredients"></div>
                        </div>
                    </div>
                </div>

                <!-- Tamanho -->
                <div>
                    <label class="block text-xl font-black mb-4 text-gray-800">Escolha o Tamanho</label>
                    <div class="grid grid-cols-2 gap-4">
                        <button @click="pizzaModal.size='small'" :class="pizzaModal.size==='small'?'ring-4 ring-red-500 bg-gradient-to-br from-red-50 to-red-50':'bg-gray-50 hover:bg-gray-100'" class="p-5 rounded-2xl border-2 border-gray-200 text-left transition-all duration-300 hover:scale-105">
                            <div class="flex items-center justify-between mb-2"><div class="font-bold text-lg">Pequena</div><div :class="pizzaModal.size==='small'?'bg-red-500':'bg-gray-300'" class="w-3 h-3 rounded-full"></div></div>
                            <div class="text-sm text-gray-600 mb-2">25cm • 4 fatias</div><div class="text-red-600 font-black text-lg">× 0.7</div>
                        </button>
                        <button @click="pizzaModal.size='medium'" :class="pizzaModal.size==='medium'?'ring-4 ring-red-500 bg-gradient-to-br from-red-50 to-red-50':'bg-gray-50 hover:bg-gray-100'" class="p-5 rounded-2xl border-2 border-gray-200 text-left transition-all duration-300 hover:scale-105">
                            <div class="flex items-center justify-between mb-2"><div class="font-bold text-lg">Média</div><div :class="pizzaModal.size==='medium'?'bg-red-500':'bg-gray-300'" class="w-3 h-3 rounded-full"></div></div>
                            <div class="text-sm text-gray-600 mb-2">30cm • 6 fatias</div><div class="text-red-600 font-black text-lg">× 1.0</div>
                        </button>
                        <button @click="pizzaModal.size='large'" :class="pizzaModal.size==='large'?'ring-4 ring-red-500 bg-gradient-to-br from-red-50 to-red-50':'bg-gray-50 hover:bg-gray-100'" class="p-5 rounded-2xl border-2 border-gray-200 text-left transition-all duration-300 hover:scale-105">
                            <div class="flex items-center justify-between mb-2"><div class="font-bold text-lg">Grande</div><div :class="pizzaModal.size==='large'?'bg-red-500':'bg-gray-300'" class="w-3 h-3 rounded-full"></div></div>
                            <div class="text-sm text-gray-600 mb-2">35cm • 8 fatias</div><div class="text-red-600 font-black text-lg">× 1.3</div>
                        </button>
                        <button @click="pizzaModal.size='family'" :class="pizzaModal.size==='family'?'ring-4 ring-red-500 bg-gradient-to-br from-red-50 to-red-50':'bg-gray-50 hover:bg-gray-100'" class="p-5 rounded-2xl border-2 border-gray-200 text-left transition-all duration-300 hover:scale-105">
                            <div class="flex items-center justify-between mb-2"><div class="font-bold text-lg">Família</div><div :class="pizzaModal.size==='family'?'bg-red-500':'bg-gray-300'" class="w-3 h-3 rounded-full"></div></div>
                            <div class="text-sm text-gray-600 mb-2">40cm • 12 fatias</div><div class="text-red-600 font-black text-lg">× 1.6</div>
                        </button>
                    </div>
                </div>

                <!-- SEGUNDO SABOR COM INGREDIENTES -->
                <div>
                    <label class="block text-xl font-black mb-4 text-gray-800">Adicionar 2º Sabor? (Meio a Meio)</label>
                    <div class="p-5 border-2 rounded-2xl transition-all" :class="pizzaModal.secondFlavor?'border-red-500 bg-gradient-to-r from-red-50 to-red-50':'border-gray-200'">
                        <label class="flex items-center gap-3 cursor-pointer mb-3">
                            <input type="checkbox" x-model="pizzaModal.secondFlavor" class="w-5 h-5 text-red-600 rounded focus:ring-red-500">
                            <div class="flex-1">
                                <div class="font-bold text-lg">Sim, quero meio a meio!</div>
                                <div class="text-sm text-gray-600">Divida sua pizza em dois sabores deliciosos</div>
                            </div>
                        </label>

                        <!-- SELECT DE SABORES COM INGREDIENTES -->
                        <div x-show="pizzaModal.secondFlavor" x-transition class="mt-4 space-y-3">
                            <select x-model="pizzaModal.secondFlavorId" @change="updateSecondFlavorIngredients()" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-100 transition font-medium">
                                <option value="">Escolha o segundo sabor...</option>
                                <template x-for="(pizza, id) in pizzaData" :key="id">
                                    <option :value="id" x-text="pizza.name"></option>
                                </template>
                            </select>

                            <!-- MOSTRA INGREDIENTES DO 2º SABOR -->
                            <div x-show="pizzaModal.secondFlavorIngredients" class="bg-yellow-50 border-2 border-yellow-400 rounded-xl p-4">
                                <div class="text-sm text-gray-700 font-bold mb-1">🧀 Ingredientes do 2º sabor:</div>
                                <div class="text-sm text-gray-600" x-text="pizzaModal.secondFlavorIngredients"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Borda -->
                <div>
                    <label class="block text-xl font-black mb-4 text-gray-800">Borda Recheada (Opcional)</label>
                    <div class="grid grid-cols-2 gap-4">
                        <button @click="pizzaModal.border='none'" :class="pizzaModal.border==='none'?'ring-4 ring-red-500 bg-gradient-to-br from-red-50 to-red-50':'bg-gray-50 hover:bg-gray-100'" class="p-4 rounded-2xl border-2 border-gray-200 text-left transition-all duration-300 hover:scale-105">
                            <div class="font-bold text-lg mb-1">Sem borda</div><div class="text-green-600 font-black text-lg">Grátis ✨</div>
                        </button>
                        <button @click="pizzaModal.border='catupiry'" :class="pizzaModal.border==='catupiry'?'ring-4 ring-red-500 bg-gradient-to-br from-red-50 to-red-50':'bg-gray-50 hover:bg-gray-100'" class="p-4 rounded-2xl border-2 border-gray-200 text-left transition-all duration-300 hover:scale-105">
                            <div class="font-bold text-lg mb-1">Catupiry 🧀</div><div class="text-red-600 font-black text-lg">+ R$ 8,00</div>
                        </button>
                        <button @click="pizzaModal.border='cheddar'" :class="pizzaModal.border==='cheddar'?'ring-4 ring-red-500 bg-gradient-to-br from-red-50 to-red-50':'bg-gray-50 hover:bg-gray-100'" class="p-4 rounded-2xl border-2 border-gray-200 text-left transition-all duration-300 hover:scale-105">
                            <div class="font-bold text-lg mb-1">Cheddar 🧀</div><div class="text-red-600 font-black text-lg">+ R$ 8,00</div>
                        </button>
                        <button @click="pizzaModal.border='chocolate'" :class="pizzaModal.border==='chocolate'?'ring-4 ring-red-500 bg-gradient-to-br from-red-50 to-red-50':'bg-gray-50 hover:bg-gray-100'" class="p-4 rounded-2xl border-2 border-gray-200 text-left transition-all duration-300 hover:scale-105">
                            <div class="font-bold text-lg mb-1">Chocolate 🍫</div><div class="text-red-600 font-black text-lg">+ R$ 10,00</div>
                        </button>
                    </div>
                </div>

                <!-- Total -->
                <div class="bg-gradient-to-br from-red-50 to-red-50 p-6 rounded-2xl border-2 border-red-200">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-gray-700"><span class="font-medium">Preço base:</span><span class="font-bold" x-text="'R$ ' + pizzaModal.basePrice.toFixed(2).replace('.',',')"></span></div>
                        <div class="flex justify-between items-center text-gray-700"><span class="font-medium">Multiplicador tamanho:</span><span class="font-bold" x-text="'× ' + getSizeMultiplier()"></span></div>
                        <div class="flex justify-between items-center text-gray-700"><span class="font-medium">Borda recheada:</span><span class="font-bold" x-text="'+ R$ ' + getBorderPrice().toFixed(2).replace('.',',')"></span></div>
                        <div class="border-t-2 border-red-300 pt-3 mt-3">
                            <div class="flex justify-between items-center">
                                <span class="font-black text-2xl text-gray-800">Total:</span>
                                <span class="font-black text-4xl bg-gradient-to-r from-red-600 to-red-600 bg-clip-text text-transparent" x-text="'R$ ' + getPizzaTotal().toFixed(2).replace('.',',')"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex gap-4">
                    <button @click="closePizzaModal()" class="flex-1 py-4 border-2 border-gray-300 rounded-2xl font-black text-lg hover:bg-gray-50 transition">Cancelar</button>
                    <button @click="addPizzaToCart()" class="flex-1 py-4 bg-gradient-to-r from-red-500 to-red-500 text-white rounded-2xl font-black text-lg hover:shadow-lg hover:scale-105 transition-all duration-300">🛒 Adicionar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Variações/Tamanhos (Clean & Compacto) -->
    <div x-show="variationModal.show" x-cloak @click.self="closeVariationModal()" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] flex items-end md:items-center justify-center p-0 md:p-4" x-transition>
        <div class="bg-white rounded-t-3xl md:rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl" @click.stop x-transition>
            <div class="p-4 md:p-5 space-y-4">
                <!-- Header Compacto -->
                <div class="flex items-center gap-3">
                    <div x-show="variationModal.image" class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0">
                        <img :src="variationModal.image" :alt="variationModal.productName" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg md:text-xl font-bold text-gray-900 truncate" x-text="variationModal.productName"></h3>
                        <p class="text-xs text-gray-500">Escolha o tamanho</p>
                    </div>
                    <button @click="closeVariationModal()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Tamanhos (Compacto) -->
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        Tamanho <span class="text-red-500">*</span>
                    </label>
                    <template x-for="variation in variationModal.variations" :key="variation.id">
                        <button
                            @click="variationModal.selectedVariation = variation"
                            :class="variationModal.selectedVariation?.id === variation.id
                                ? 'border-red-500 bg-red-50'
                                : 'border-gray-200 hover:border-gray-300'"
                            class="w-full p-3 rounded-xl border-2 text-left transition-all active:scale-98">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="font-semibold text-sm text-gray-900" x-text="variation.name"></div>
                                    <div class="text-red-500 font-bold text-base mt-0.5" x-text="'R$ ' + parseFloat(variation.price).toFixed(2).replace('.', ',')"></div>
                                </div>
                                <div
                                    :class="variationModal.selectedVariation?.id === variation.id ? 'bg-red-500' : 'bg-gray-200'"
                                    class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 transition-all">
                                    <svg x-show="variationModal.selectedVariation?.id === variation.id" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>

                <!-- Quantidade (Inline Compacto) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Quantidade</label>
                    <div class="flex items-center gap-3">
                        <button @click="if(variationModal.quantity > 1) variationModal.quantity--" class="w-10 h-10 rounded-lg border border-gray-300 flex items-center justify-center font-bold text-lg hover:bg-gray-50 transition active:scale-95">−</button>
                        <div class="flex-1 text-center">
                            <span class="text-xl font-bold text-gray-900" x-text="variationModal.quantity"></span>
                        </div>
                        <button @click="variationModal.quantity++" class="w-10 h-10 rounded-lg border border-red-500 bg-red-50 flex items-center justify-center font-bold text-lg text-red-500 hover:bg-red-100 transition active:scale-95">+</button>
                    </div>
                </div>

                <!-- Observações (Compacto) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Observações</label>
                    <textarea
                        x-model="variationModal.notes"
                        rows="2"
                        placeholder="Ex: sem cebola, bem passado..."
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 resize-none transition"></textarea>
                </div>

                <!-- Botões (Compacto) -->
                <div class="flex gap-3 pt-2 border-t border-gray-100">
                    <button @click="closeVariationModal()" class="flex-1 py-3 border border-gray-300 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 transition active:scale-98">
                        Cancelar
                    </button>
                    <button
                        @click="addVariationToCartFromModal()"
                        :disabled="!variationModal.selectedVariation"
                        :class="variationModal.selectedVariation ? 'bg-red-500 hover:bg-red-600' : 'bg-gray-300 cursor-not-allowed'"
                        class="flex-1 py-3 text-white rounded-xl font-bold text-sm transition-all active:scale-98 disabled:active:scale-100">
                        <span x-show="!variationModal.selectedVariation">Escolha um tamanho</span>
                        <span x-show="variationModal.selectedVariation">Adicionar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Produto -->
    <div x-show="productModal.show" x-cloak @click.self="closeProductModal()" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-[9999] flex items-center justify-center p-4" x-transition>
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl" @click.stop x-transition>
            <!-- Imagem do Produto -->
            <div class="relative h-64 bg-gray-100 rounded-t-2xl overflow-hidden">
                <template x-if="productModal.image">
                    <img :src="productModal.image" :alt="productModal.productName" class="w-full h-full object-cover">
                </template>
                <template x-if="!productModal.image">
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-red-100 to-red-100">
                        <svg class="w-24 h-24 text-red-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                        </svg>
                    </div>
                </template>
                <button @click="closeProductModal()" class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 shadow-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Detalhes do Produto -->
            <div class="p-6">
                <h3 class="text-2xl font-black text-gray-900 mb-2" x-text="productModal.productName"></h3>
                <p class="text-gray-600 text-sm mb-4" x-text="productModal.description || 'Produto delicioso e fresquinho!'"></p>

                <div class="flex items-baseline gap-2 mb-6">
                    <span class="text-3xl font-black text-red-600" x-text="'R$ ' + parseFloat(productModal.price).toFixed(2).replace('.', ',')"></span>
                </div>

                <!-- Botão de Adicionar -->
                <button
                    @click="addProductToCartFromModal()"
                    class="w-full py-4 bg-gradient-to-r from-red-500 to-red-600 text-white font-black text-lg rounded-2xl hover:shadow-lg hover:scale-105 transition-all duration-300 flex items-center justify-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-text="productModal.isPizza ? 'Personalizar Pizza' : productModal.hasVariations ? 'Escolher Tamanho' : 'Adicionar ao Carrinho'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification Melhorado -->
    <div
        x-show="showToast"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        :class="{
            'bg-green-600': toastType === 'success',
            'bg-yellow-500': toastType === 'warning',
            'bg-red-600': toastType === 'error'
        }"
        class="fixed top-20 right-4 z-[9999] text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 min-w-[300px] max-w-md">
        <!-- Ícone baseado no tipo -->
        <div x-show="toastType === 'success'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div x-show="toastType === 'warning'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div x-show="toastType === 'error'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <span x-text="toastMessage" class="font-semibold flex-1"></span>
    </div>

    <!-- Carrinho -->
    <!-- CARRINHO CLEAN ESTILO IFOOD -->
    <div x-show="showCart" @click.away="showCart=false" x-cloak x-transition class="fixed inset-y-0 right-0 w-full md:w-[450px] bg-white shadow-2xl z-[150] flex flex-col">

        <!-- Header Clean -->
        <div class="bg-white border-b border-gray-200 p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-xl text-gray-900">Carrinho</h3>
                    <p class="text-sm text-gray-500" x-show="cart.length>0" x-text="cart.length + ' ' + (cart.length===1?'item':'itens')"></p>
                </div>
                <button @click="showCart=false" class="w-10 h-10 flex items-center justify-center hover:bg-gray-100 rounded-full transition">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Itens do Carrinho -->
        <div class="flex-1 overflow-auto p-4 bg-white">
            <template x-if="cart.length===0">
                <div class="text-center py-20">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h4 class="font-semibold text-lg text-gray-900 mb-1">Sua sacola está vazia</h4>
                    <p class="text-sm text-gray-500">Adicione itens para começar</p>
                </div>
            </template>

            <div class="space-y-3">
                <template x-for="item in cart" :key="item.cartId">
                    <div class="border-b border-gray-100 pb-3">
                        <!-- Linha 1: Quantidade x Nome do Produto -->
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <p class="text-gray-900 font-medium text-[15px]">
                                    <span class="text-gray-500" x-text="item.quantity"></span>
                                    <span class="text-gray-500">x</span>
                                    <span x-text="item.name"></span>
                                </p>
                                <p class="text-xs text-gray-400 mt-1" x-show="item.details" x-text="item.details"></p>
                            </div>
                            <p class="text-gray-900 font-semibold text-[15px] ml-3" x-text="'R$ '+(item.price*item.quantity).toFixed(2).replace('.',',')"></p>
                        </div>

                        <!-- Linha 2: Botões de Ação -->
                        <div class="flex items-center justify-between">
                            <!-- Controle de Quantidade -->
                            <div class="flex items-center gap-3">
                                <button @click="decrementQuantity(item.cartId)" class="w-7 h-7 border border-gray-300 rounded-full hover:bg-gray-50 flex items-center justify-center transition">
                                    <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/></svg>
                                </button>
                                <span x-text="item.quantity" class="text-sm font-medium text-gray-700 w-5 text-center"></span>
                                <button @click="incrementQuantity(item.cartId)" class="w-7 h-7 border border-red-500 bg-red-500 text-white rounded-full hover:bg-red-600 flex items-center justify-center transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>

                            <!-- Links Editar/Remover -->
                            <div class="flex items-center gap-4 text-xs">
                                <button @click="removeItemFromCart(item.cartId)" class="text-red-500 hover:text-red-700 font-medium transition">
                                    Remover
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Sugestão "Compre Junto" -->
            <template x-if="cart.length > 0 && suggestedProductsForCart.length > 0">
                <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-2xl">🛒</span>
                        <div>
                            <h4 class="font-semibold text-gray-900 text-sm">Compre Junto</h4>
                            <p class="text-xs text-gray-600">Complete seu pedido</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <template x-for="product in suggestedProductsForCart.slice(0, 6)" :key="product.id">
                            <button
                                @click="addToCart({id:product.id,name:product.name,price:product.price}); showToastNotification(product.name + ' adicionado!')"
                                class="flex items-center gap-2 p-2 bg-white border border-gray-200 rounded-lg hover:border-amber-400 hover:bg-amber-50 transition text-left">
                                <img
                                    x-show="product.image"
                                    :src="product.image"
                                    :alt="product.name"
                                    class="w-10 h-10 object-cover rounded">
                                <div
                                    x-show="!product.image"
                                    class="w-10 h-10 bg-amber-100 rounded flex items-center justify-center text-lg">
                                    🍴
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-900 truncate" x-text="product.name"></p>
                                    <p class="text-xs text-gray-600" x-text="'R$ ' + product.price.toFixed(2).replace('.', ',')"></p>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>
        <!-- Rodapé do Carrinho - Clean -->
        <div class="p-4 bg-white border-t border-gray-200">
            <!-- Resumo do Pedido - Clean -->
            <div x-show="cart.length > 0" class="space-y-2.5 mb-4">
                <!-- Subtotal -->
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 text-sm">Subtotal</span>
                    <span class="text-gray-700 text-sm" x-text="'R$ ' + cartTotal.toFixed(2).replace('.',',')"></span>
                </div>

                <!-- Taxa de Entrega -->
                <div x-show="deliveryType === 'delivery'" class="flex justify-between items-center">
                    <span class="text-gray-500 text-sm">Taxa de Entrega</span>
                    <span class="text-gray-700 text-sm" x-text="deliveryFee > 0 ? 'R$ ' + deliveryFee.toFixed(2).replace('.',',') : (selectedNeighborhood ? 'Grátis' : '--')"></span>
                </div>

                <!-- Tempo Estimado -->
                <div x-show="deliveryType === 'delivery' && deliveryTime > 0" class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <span class="text-gray-500 text-sm">⏱ Tempo Estimado</span>
                    <span class="text-gray-700 text-sm font-medium" x-text="deliveryTime + ' min'"></span>
                </div>

                <!-- Total Final - Destacado -->
                <div class="flex justify-between items-center pt-2">
                    <span class="text-gray-900 font-semibold text-base">Total</span>
                    <span class="text-red-600 font-bold text-xl" x-text="'R$ ' + finalTotal.toFixed(2).replace('.',',')"></span>
                </div>
            </div>

            <!-- Botão Finalizar - Clean -->
            <button
                @click="iniciarCheckout()"
                x-show="cart.length>0"
                class="w-full py-4 bg-red-500 hover:bg-red-600 text-white font-semibold text-base rounded-lg transition-colors">
                Ir para o Checkout →
            </button>
        </div>
    </div>

    <footer class="bg-white border-t mt-8 py-4">
        <div class="max-w-7xl mx-auto px-3 text-center">
            <div class="flex items-center justify-center gap-1 mb-1">
                <span class="text-gray-600 text-xs">Powered by</span>
                <a href="https://food.yumgo.com.br" class="font-black text-sm bg-gradient-to-r from-red-600 to-red-600 bg-clip-text text-transparent hover:scale-105 transition">YumGo</a>
                <span class="text-base">🚀</span>
            </div>
            <p class="text-xs text-gray-500">Feito com 💜 para restaurantes incríveis</p>
        </div>
    </footer>

    <script>
    function restaurantApp(){
        // 🔒 ISOLAMENTO POR TENANT - Evita vazamento de carrinho entre restaurantes
        const CART_KEY = 'yumgo_cart_{{ $tenant->slug }}';

        return{
            pageLoading:true,
            // PWA Install Banner
            showPWABanner: false,
            deferredPWAPrompt: null,
            cart:[],
            showCart:false,
            suggestedProductsForCart: [],
            searchQuery:'',
            selectedCategory:null,
            pizzaData: window.pizzaData || {},
            selectedVariations: {},
            // Auth state
            isLoggedIn: false,
            customerName: '',
            customerEmail: '',
            cashbackBalance: '0.00',
            loyaltyTier: 'Bronze',
            showUserMenu: false,
            // Toast notification
            toastMessage: '',
            toastType: 'success',
            showToast: false,
            // Login modal state
            showLoginModal: false,
            loginEmail: '',
            loginPassword: '',
            loginLoading: false,
            loginError: '',
            loginSuccess: '',
            // WhatsApp Validation modal state
            showWhatsAppValidationModal: false,
            whatsappValidationPhone: '',
            whatsappValidationCode: '',
            whatsappCodeSent: false,
            whatsappValidationLoading: false,
            whatsappValidationError: '',
            // Register modal state
            showRegisterModal: false,
            registerName: '',
            registerEmail: '',
            registerPhone: '',
            registerPassword: '',
            registerPasswordConfirm: '',
            registerLoading: false,
            registerError: '',
            registerSuccess: '',
            // Forgot password modal state
            showForgotModal: false,
            forgotEmail: '',
            forgotLoading: false,
            forgotError: '',
            forgotSuccess: false,
            // Delivery state
            deliveryType: @json($allowDelivery) ? 'delivery' : 'pickup',
            deliveryAddress: '',
            deliveryCity: '',
            selectedCity: '',
            selectedNeighborhood: '',
            deliveryFee: 0,
            deliveryTime: 0,
            pizzaModal:{
                show:false,
                productId:null,
                productName:'',
                ingredients:'',
                basePrice:0,
                size:'medium',
                secondFlavor:false,
                secondFlavorId:'',
                secondFlavorName:'',
                secondFlavorIngredients:'',
                border:'none'
            },
            variationModal:{
                show:false,
                productId:null,
                productName:'',
                image:null,
                variations:[],
                selectedVariation:null,
                quantity:1,
                notes:''
            },
            productModal:{
                show:false,
                productId:null,
                productName:'',
                description:'',
                price:0,
                image:null,
                isPizza:false,
                hasVariations:false,
                variations:[]
            },
            init(){
                // 🔥 DETECTAR RETORNO DO OAUTH
                this.handleOAuthCallback();

                // Verificar se está logado
                this.checkAuth();

                // Carregar carrinho do localStorage (isolado por tenant)
                const savedCart = localStorage.getItem(CART_KEY);
                if(savedCart){
                    try{
                        this.cart = JSON.parse(savedCart);
                        console.log('Carrinho carregado:', this.cart);
                    }catch(e){
                        console.error('Erro ao carregar carrinho:', e);
                        this.cart = [];
                    }
                }
                // Observar mudanças no carrinho e salvar
                this.$watch('cart', value => {
                    localStorage.setItem(CART_KEY, JSON.stringify(value));
                    console.log('Carrinho salvo:', value);
                    this.loadSuggestedProducts();
                });

                // 🧹 LIMPEZA: Remover carrinhos antigos compartilhados (migração)
                if (localStorage.getItem('yumgo_cart')) {
                    console.log('🧹 Removendo carrinho compartilhado antigo');
                    localStorage.removeItem('yumgo_cart');
                }
                if (localStorage.getItem('cart')) {
                    localStorage.removeItem('cart');
                }

                // Carregar sugestões iniciais
                this.loadSuggestedProducts();

                // Finalizar loading
                setTimeout(() => { this.pageLoading = false; }, 300);

                // 📱 PWA Install Prompt
                this.initPWA();
            },

            // PWA Methods
            initPWA() {
                // Capturar evento de instalação
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPWAPrompt = e;

                    // Mostrar banner após 3 segundos (se não instalou ainda)
                    const pwaInstalled = localStorage.getItem('pwa_installed');
                    const pwaDismissed = localStorage.getItem('pwa_dismissed');

                    if (!pwaInstalled && !pwaDismissed) {
                        setTimeout(() => {
                            this.showPWABanner = true;
                        }, 3000);
                    }
                });

                // Detectar se já foi instalado
                window.addEventListener('appinstalled', () => {
                    localStorage.setItem('pwa_installed', 'true');
                    this.showPWABanner = false;
                    this.showNotification('App instalado com sucesso! 🎉', 'success');
                });
            },

            async installPWA() {
                if (!this.deferredPWAPrompt) return;

                this.deferredPWAPrompt.prompt();
                const { outcome } = await this.deferredPWAPrompt.userChoice;

                if (outcome === 'accepted') {
                    localStorage.setItem('pwa_installed', 'true');
                    this.showNotification('Instalando app...', 'success');
                } else {
                    localStorage.setItem('pwa_dismissed', 'true');
                }

                this.deferredPWAPrompt = null;
                this.showPWABanner = false;
            },

            dismissPWABanner() {
                this.showPWABanner = false;
                localStorage.setItem('pwa_dismissed', 'true');
            },

            async loadSuggestedProducts() {
                if (this.cart.length === 0) {
                    this.suggestedProductsForCart = [];
                    return;
                }

                // Pegar IDs dos produtos no carrinho
                const productIds = this.cart.map(item => item.id);
                const cartProductIds = productIds;

                try {
                    const response = await fetch(`/api/v1/products/suggestions?product_ids=${productIds.join(',')}`);
                    if (response.ok) {
                        const data = await response.json();
                        // Filtrar produtos que já estão no carrinho
                        this.suggestedProductsForCart = data.filter(p => !cartProductIds.includes(p.id));
                    }
                } catch (error) {
                    console.error('Erro ao carregar produtos sugeridos:', error);
                }
            },
            checkAuth(){
                const token = localStorage.getItem('auth_token');
                const customer = localStorage.getItem('customer');

                if(token && customer){
                    try{
                        const data = JSON.parse(customer);
                        this.isLoggedIn = true;
                        this.customerName = data.name || 'Usuário';
                        this.customerEmail = data.email || '';
                        this.cashbackBalance = data.cashback_balance || '0.00';
                        this.loyaltyTier = this.formatTier(data.loyalty_tier);
                        console.log('✅ Usuário logado:', this.customerName);
                    }catch(e){
                        console.error('Erro ao verificar auth:', e);
                        this.isLoggedIn = false;
                        // Mostrar modal de login automaticamente se não estiver logado
                        setTimeout(() => { this.showLoginModal = true; }, 500);
                    }
                }else{
                    this.isLoggedIn = false;
                    // Mostrar modal de login automaticamente ao entrar no site
                    setTimeout(() => { this.showLoginModal = true; }, 500);
                }
            },
            formatTier(tier){
                const tiers = {
                    'bronze': 'Bronze',
                    'silver': 'Prata',
                    'gold': 'Ouro',
                    'platinum': 'Platina'
                };
                return tiers[tier] || 'Bronze';
            },
            handleOAuthCallback(){
                // Verificar se voltou do OAuth com sucesso
                @if(session('oauth_success'))
                    const token = @json(session('auth_token'));
                    const customerData = @json(session('customer_data'));
                    const needsWhatsapp = @json(session('needs_whatsapp_validation'));

                    // Salvar no localStorage
                    localStorage.setItem('auth_token', token);
                    localStorage.setItem('customer', JSON.stringify(customerData));

                    // Atualizar estado
                    this.isLoggedIn = true;
                    this.customerName = customerData.name;
                    this.customerEmail = customerData.email;
                    this.cashbackBalance = customerData.cashback_balance || '0.00';
                    this.loyaltyTier = this.formatTier(customerData.loyalty_tier);

                    // Fechar modal de login
                    this.showLoginModal = false;

                    // Mostrar mensagem de sucesso
                    if(needsWhatsapp){
                        this.showWhatsAppValidationModal = true;
                        this.whatsappValidationPhone = customerData.phone || '';
                    } else {
                        this.showToastNotification('✅ Login realizado com sucesso!', 'success');
                    }

                    console.log('✅ OAuth login successful:', customerData.name);
                @endif

                // Verificar se houve erro no OAuth
                @if(session('oauth_error'))
                    const errorMessage = @json(session('error_message'));
                    this.showToastNotification('❌ ' + errorMessage, 'error');
                    console.error('OAuth error:', errorMessage);
                @endif
            },
            async sendWhatsAppCode() {
                if (!this.whatsappValidationPhone) {
                    this.showToastNotification('Por favor, informe seu WhatsApp', 'error');
                    return;
                }

                this.whatsappValidationLoading = true;
                this.whatsappValidationError = '';

                try {
                    const response = await fetch('/api/v1/auth/whatsapp/request-code', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                        },
                        body: JSON.stringify({
                            phone: this.whatsappValidationPhone
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.whatsappCodeSent = true;
                        this.showToastNotification('✅ Código enviado via WhatsApp!', 'success');
                    } else {
                        this.whatsappValidationError = data.message || 'Erro ao enviar código';
                    }
                } catch (error) {
                    console.error('Erro ao enviar código WhatsApp:', error);
                    this.whatsappValidationError = 'Erro ao enviar código. Tente novamente.';
                } finally {
                    this.whatsappValidationLoading = false;
                }
            },
            async validateWhatsAppCode() {
                if (!this.whatsappValidationCode || this.whatsappValidationCode.length !== 6) {
                    this.showToastNotification('Digite o código de 6 dígitos', 'error');
                    return;
                }

                this.whatsappValidationLoading = true;
                this.whatsappValidationError = '';

                try {
                    const response = await fetch('/api/v1/auth/whatsapp/verify-code', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                        },
                        body: JSON.stringify({
                            phone: this.whatsappValidationPhone,
                            code: this.whatsappValidationCode
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showWhatsAppValidationModal = false;
                        this.showToastNotification('✅ WhatsApp validado com sucesso!', 'success');

                        // Atualizar dados do cliente
                        await this.loadCustomerData();
                    } else {
                        this.whatsappValidationError = data.message || 'Código inválido';
                    }
                } catch (error) {
                    console.error('Erro ao validar código WhatsApp:', error);
                    this.whatsappValidationError = 'Erro ao validar código. Tente novamente.';
                } finally {
                    this.whatsappValidationLoading = false;
                }
            },
            logout(){
                if(confirm('Deseja realmente sair?')){
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('customer');
                    this.isLoggedIn = false;
                    this.customerName = '';
                    this.customerEmail = '';
                    this.showUserMenu = false;
                    console.log('🚪 Logout realizado');
                    alert('Você saiu da sua conta!');
                }
            },
            async login(){
                this.loginError = '';
                this.loginSuccess = '';

                if(!this.loginEmail || !this.loginPassword){
                    this.loginError = 'Preencha email e senha';
                    return;
                }

                this.loginLoading = true;

                try{
                    const response = await fetch('/api/v1/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            email: this.loginEmail,
                            password: this.loginPassword
                        })
                    });

                    const data = await response.json();

                    if(response.ok && data.success){
                        // Salvar no localStorage
                        localStorage.setItem('auth_token', data.token);
                        localStorage.setItem('customer', JSON.stringify(data.customer));

                        // Atualizar estado
                        this.isLoggedIn = true;
                        this.customerName = data.customer.name;
                        this.customerEmail = data.customer.email;
                        this.cashbackBalance = data.customer.cashback_balance || '0.00';
                        this.loyaltyTier = this.formatTier(data.customer.loyalty_tier);

                        // Fechar modal e limpar campos
                        this.showLoginModal = false;
                        this.loginEmail = '';
                        this.loginPassword = '';

                        console.log('✅ Login realizado:', this.customerName);

                        // Se tem itens no carrinho, continuar checkout automaticamente
                        if(this.cart.length > 0){
                            this.showToastNotification('Login realizado! Finalizando pedido...');
                            setTimeout(() => {
                                this.checkout();
                            }, 1000);
                        }
                    }else{
                        this.loginError = data.message || 'Email ou senha incorretos';
                    }
                }catch(error){
                    console.error('Erro no login:', error);
                    this.loginError = 'Erro ao conectar com o servidor';
                }finally{
                    this.loginLoading = false;
                }
            },
            async register(){
                this.registerError = '';
                this.registerSuccess = '';

                if(!this.registerName || !this.registerEmail || !this.registerPhone || !this.registerPassword){
                    this.registerError = 'Preencha todos os campos';
                    return;
                }

                if(this.registerPassword !== this.registerPasswordConfirm){
                    this.registerError = 'As senhas não coincidem';
                    return;
                }

                if(this.registerPassword.length < 6){
                    this.registerError = 'A senha deve ter no mínimo 6 caracteres';
                    return;
                }

                this.registerLoading = true;

                try{
                    const response = await fetch('/api/v1/register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: this.registerName,
                            email: this.registerEmail,
                            phone: this.registerPhone,
                            password: this.registerPassword,
                            password_confirmation: this.registerPasswordConfirm
                        })
                    });

                    const data = await response.json();

                    if(response.ok && data.success){
                        // Salvar no localStorage
                        localStorage.setItem('auth_token', data.token);
                        localStorage.setItem('customer', JSON.stringify(data.customer));

                        // Atualizar estado
                        this.isLoggedIn = true;
                        this.customerName = data.customer.name;
                        this.customerEmail = data.customer.email;
                        this.cashbackBalance = data.customer.cashback_balance || '0.00';
                        this.loyaltyTier = this.formatTier(data.customer.loyalty_tier);

                        // Fechar modal e limpar campos
                        this.showRegisterModal = false;
                        this.registerName = '';
                        this.registerEmail = '';
                        this.registerPhone = '';
                        this.registerPassword = '';
                        this.registerPasswordConfirm = '';

                        console.log('✅ Cadastro realizado:', this.customerName);
                        this.showToastNotification('Bem-vindo! Cadastro realizado com sucesso! 🎉');

                        // Se tem itens no carrinho, continuar checkout
                        if(this.cart.length > 0){
                            setTimeout(() => {
                                this.checkout();
                            }, 1500);
                        }
                    }else{
                        this.registerError = data.message || 'Erro ao criar conta';
                    }
                }catch(error){
                    console.error('Erro no cadastro:', error);
                    this.registerError = 'Erro ao conectar com o servidor';
                }finally{
                    this.registerLoading = false;
                }
            },
            async forgotPassword(){
                this.forgotError = '';
                this.forgotSuccess = false;

                if(!this.forgotEmail){
                    this.forgotError = 'Digite seu email';
                    return;
                }

                this.forgotLoading = true;

                try{
                    const response = await fetch('/api/auth/forgot-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            email: this.forgotEmail
                        })
                    });

                    const data = await response.json();

                    if(response.ok && data.success){
                        this.forgotSuccess = true;
                        this.forgotEmail = '';
                    }else{
                        this.forgotError = data.message || 'Email não encontrado';
                    }
                }catch(error){
                    console.error('Erro ao recuperar senha:', error);
                    this.forgotError = 'Erro ao conectar com o servidor';
                }finally{
                    this.forgotLoading = false;
                }
            },
            openPizzaModal(id,name,price){
                const pizzaInfo = this.pizzaData[id] || {};
                this.pizzaModal={
                    show:true,
                    productId:id,
                    productName:name,
                    ingredients:pizzaInfo.ingredients || '',
                    basePrice:price,
                    size:'medium',
                    secondFlavor:false,
                    secondFlavorId:'',
                    secondFlavorName:'',
                    secondFlavorIngredients:'',
                    border:'none'
                };
            },
            closePizzaModal(){this.pizzaModal.show=false},
            openProductModal(product){
                this.productModal = {
                    show: true,
                    productId: product.id,
                    productName: product.name,
                    description: product.description || '',
                    price: product.price,
                    image: product.image,
                    isPizza: product.isPizza || false,
                    hasVariations: product.hasVariations || false,
                    variations: product.variations || []
                };
            },
            closeProductModal(){
                this.productModal.show = false;
            },
            addProductToCartFromModal(){
                if(this.productModal.isPizza){
                    // Se for pizza, fechar modal de produto e abrir modal de pizza
                    this.closeProductModal();
                    this.openPizzaModal(this.productModal.productId, this.productModal.productName, this.productModal.price);
                }else if(this.productModal.hasVariations){
                    // Se tiver variações, fechar modal de produto e abrir modal de variações
                    this.closeProductModal();
                    this.openVariationModal(this.productModal.productId, this.productModal.productName, this.productModal.variations, this.productModal.image);
                }else{
                    // Produto simples, adicionar direto
                    this.addToCart({
                        id: this.productModal.productId,
                        name: this.productModal.productName,
                        price: this.productModal.price
                    });
                    this.closeProductModal();
                }
            },
            updateSecondFlavorIngredients(){
                const secondId = this.pizzaModal.secondFlavorId;
                if(secondId && this.pizzaData[secondId]){
                    this.pizzaModal.secondFlavorName = this.pizzaData[secondId].name;
                    this.pizzaModal.secondFlavorIngredients = this.pizzaData[secondId].ingredients || '';
                }else{
                    this.pizzaModal.secondFlavorName = '';
                    this.pizzaModal.secondFlavorIngredients = '';
                }
            },
            getSizeMultiplier(){const m={small:0.7,medium:1.0,large:1.3,family:1.6};return m[this.pizzaModal.size]||1.0},
            getBorderPrice(){const p={none:0,catupiry:8,cheddar:8,chocolate:10};return p[this.pizzaModal.border]||0},
            getPizzaTotal(){return (this.pizzaModal.basePrice * this.getSizeMultiplier()) + this.getBorderPrice()},
            addPizzaToCart(){
                const sizeNames={small:'Pequena',medium:'Média',large:'Grande',family:'Família'};
                const borderNames={none:'Sem borda',catupiry:'Borda Catupiry',cheddar:'Borda Cheddar',chocolate:'Borda Chocolate'};
                let details=sizeNames[this.pizzaModal.size];
                if(this.pizzaModal.secondFlavor && this.pizzaModal.secondFlavorName){
                    details+=' + ' + this.pizzaModal.secondFlavorName + ' (Meio a Meio)';
                }
                if(this.pizzaModal.border!=='none')details+=', '+borderNames[this.pizzaModal.border];
                this.cart.push({cartId:Date.now(),id:this.pizzaModal.productId,name:this.pizzaModal.productName,price:this.getPizzaTotal(),quantity:1,details:details,isPizza:true});
                this.closePizzaModal();
                this.showToastNotification('🍕 Pizza adicionada ao carrinho!', 'success');
                this.animateCart();
            },
            addToCart(p){
                this.cart.push({cartId:Date.now(),...p,quantity:1});
                this.showToastNotification('✅ Item adicionado ao carrinho!', 'success');
                // Animar o carrinho
                this.animateCart();
            },
            animateCart(){
                // Adicionar classe de animação ao botão do carrinho
                const cartBtn = document.querySelector('[\\@click*="showCart"]');
                if(cartBtn){
                    cartBtn.classList.add('animate-bounce');
                    setTimeout(() => {
                        cartBtn.classList.remove('animate-bounce');
                    }, 500);
                }
            },
            openVariationModal(productId, productName, variations, image){
                this.variationModal = {
                    show: true,
                    productId: productId,
                    productName: productName,
                    image: image ? (image.startsWith('http') ? image : `/tenancy/assets/${image}`) : null,
                    variations: variations,
                    selectedVariation: null,
                    quantity: 1,
                    notes: ''
                };
            },
            closeVariationModal(){
                this.variationModal.show = false;
            },
            addVariationToCartFromModal(){
                if(!this.variationModal.selectedVariation){
                    alert('Por favor, selecione um tamanho');
                    return;
                }

                const variation = this.variationModal.selectedVariation;
                const item = {
                    cartId: Date.now(),
                    id: this.variationModal.productId,
                    variationId: variation.id,
                    name: `${this.variationModal.productName} - ${variation.name}`,
                    price: parseFloat(variation.price),
                    quantity: this.variationModal.quantity,
                    details: this.variationModal.notes || null
                };

                this.cart.push(item);
                this.closeVariationModal();
                this.showToastNotification('✅ Item adicionado ao carrinho!', 'success');
                this.animateCart();
            },
            addVariationToCart(productId, productName) {
                const selected = this.selectedVariations[productId];
                if (!selected) {
                    alert('Por favor, selecione um tamanho');
                    return;
                }
                const variation = JSON.parse(selected);
                this.cart.push({
                    cartId: Date.now(),
                    id: productId,
                    variationId: variation.id,
                    name: productName + ' - ' + variation.name,
                    price: variation.price,
                    quantity: 1
                });
                this.showToastNotification('✅ Item adicionado ao carrinho!', 'success');
                this.animateCart();
            },
            incrementQuantity(cartId){let item=this.cart.find(i=>i.cartId===cartId);if(item)item.quantity++},
            decrementQuantity(cartId){let item=this.cart.find(i=>i.cartId===cartId);if(item&&item.quantity>1)item.quantity--},
            removeItemFromCart(cartId){this.cart=this.cart.filter(i=>i.cartId!==cartId)},
            iniciarCheckout(){
                // Verificar se está logado
                if(!this.isLoggedIn){
                    // Fechar carrinho e abrir modal de login
                    this.showCart = false;
                    this.showLoginModal = true;
                    this.showToastNotification('Faça login para finalizar seu pedido');
                    return;
                }

                // Redirecionar para página de checkout
                // O endereço e taxa de entrega serão definidos na página de checkout
                window.location.href = '/checkout';
            },
            async checkout(){
                if(this.cart.length === 0){
                    this.showToastNotification('Seu carrinho está vazio!');
                    return;
                }

                // Mostrar loading
                const originalText = event.target.textContent;
                event.target.disabled = true;
                event.target.textContent = '🔄 Processando...';

                try{
                    // Preparar items no formato da API
                    const items = this.cart.map(item => ({
                        product_id: item.id,
                        quantity: item.quantity,
                        variation_id: item.variationId || null,
                        addons: item.addons || [],
                        notes: item.details || ''
                    }));

                    // Pegar token de autenticação
                    const token = localStorage.getItem('auth_token');
                    console.log('🔑 Token encontrado:', token ? 'SIM' : 'NÃO');
                    if(!token){
                        this.showLoginModal = true;
                        throw new Error('Você precisa estar logado para fazer um pedido');
                    }

                    // Fazer requisição
                    const response = await fetch('/api/v1/orders', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            items: items,
                            delivery_address: this.deliveryAddress || 'Rua Teste, 123',
                            delivery_city: this.deliveryCity || 'São Paulo',
                            delivery_neighborhood: this.selectedNeighborhood || 'Centro',
                            payment_method: 'pix',
                            notes: ''
                        })
                    });

                    // Debug: ver o que veio da API
                    const responseText = await response.text();
                    console.log('📦 Resposta da API:', responseText);
                    console.log('📊 Status:', response.status);

                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch(e) {
                        console.error('❌ Erro ao fazer parse do JSON:', e);
                        console.error('📄 Resposta recebida:', responseText.substring(0, 500));
                        throw new Error('Resposta inválida do servidor. Verifique o console.');
                    }

                    if(!response.ok){
                        throw new Error(data.message || 'Erro ao criar pedido');
                    }

                    // Sucesso! Limpar carrinho (isolado por tenant)
                    this.cart = [];
                    localStorage.removeItem(CART_KEY);

                    // Redirecionar ou mostrar sucesso
                    if(data.payment?.qrcode_image){
                        window.location.href = `/pix?order=${data.order.id}`;
                    }else{
                        alert('✅ Pedido criado com sucesso! Número: ' + data.order.id);
                        this.showCart = false;
                    }

                }catch(error){
                    console.error('Erro no checkout:', error);
                    this.showToastNotification('❌ Erro ao finalizar pedido: ' + error.message, 'error');
                    event.target.disabled = false;
                    event.target.textContent = originalText;
                }
            },
            updateDeliveryFee(){
                if(this.deliveryType === 'pickup'){
                    this.deliveryFee = 0;
                    this.deliveryTime = 0;
                    return;
                }

                const select = document.querySelector('select[x-model="selectedNeighborhood"]');
                if(!select || !select.selectedOptions[0]) {
                    this.deliveryFee = 0;
                    this.deliveryTime = 0;
                    return;
                }

                const option = select.selectedOptions[0];
                this.selectedCity = option.dataset.city || this.selectedCity;
                this.deliveryFee = parseFloat(option.dataset.fee || 0);
                this.deliveryTime = parseInt(option.dataset.time || 0);

                console.log('Taxa de entrega atualizada:', {
                    city: this.selectedCity,
                    neighborhood: this.selectedNeighborhood,
                    fee: this.deliveryFee,
                    time: this.deliveryTime
                });
            },
            showToastNotification(message, type = 'success'){
                this.toastMessage = message;
                this.toastType = type;
                this.showToast = true;
                setTimeout(() => { this.showToast = false; }, 3000);
            },
            // Reviews
            showReviewModal: false,
            reviewData: {
                order_id: null,
                rating: 0,
                food_rating: null,
                delivery_rating: null,
                service_rating: null,
                comment: '',
                is_public: true
            },
            openReviewModal(orderId) {
                this.reviewData = {
                    order_id: orderId,
                    rating: 0,
                    food_rating: null,
                    delivery_rating: null,
                    service_rating: null,
                    comment: '',
                    is_public: true
                };
                this.showReviewModal = true;
            },
            async submitReview() {
                if (!this.reviewData.rating) {
                    this.showToastNotification('Por favor, dê uma avaliação geral', 'error');
                    return;
                }

                const token = localStorage.getItem('auth_token');
                if (!token) {
                    this.showToastNotification('Você precisa estar logado', 'error');
                    return;
                }

                try {
                    const response = await fetch('/api/v1/reviews', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${token}`
                        },
                        body: JSON.stringify(this.reviewData)
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showReviewModal = false;
                        this.showToastNotification('✅ Avaliação enviada! Obrigado pelo feedback!', 'success');

                        // Marcar pedido como avaliado
                        const order = this.myOrders.find(o => o.id === this.reviewData.order_id);
                        if (order) {
                            order.has_review = true;
                        }
                    } else {
                        this.showToastNotification('❌ ' + (data.message || 'Erro ao enviar avaliação'), 'error');
                    }
                } catch (error) {
                    console.error('Erro ao enviar avaliação:', error);
                    this.showToastNotification('❌ Erro ao enviar avaliação', 'error');
                }
            },
            get cartTotal(){return this.cart.reduce((s,i)=>s+i.price*i.quantity,0)},
            get cartCount(){return this.cart.reduce((s,i)=>s+i.quantity,0)},
            get finalTotal(){
                if(this.deliveryType === 'pickup'){
                    return this.cartTotal;
                }
                return this.cartTotal + this.deliveryFee;
            }
        }
    }
    </script>

    <!-- Banner de instalação PWA -->
    <div id="pwa-install-banner" style="display:none;" class="fixed bottom-0 left-0 right-0 bg-white shadow-2xl border-t-4 border-primary z-50 transform translate-y-full transition-transform duration-300">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 flex-1">
                    @if($settings && $settings->logo)
                    <img src="{{ url('storage/' . $settings->logo) }}" alt="Logo" class="w-12 h-12 rounded-lg object-cover">
                    @endif
                    <div>
                        <p class="font-bold text-gray-900">Instalar {{ $settings->restaurant_name ?? tenant('name') }}</p>
                        <p class="text-sm text-gray-600">Peça delivery direto do app!</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="dismissPWAPrompt()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        Agora não
                    </button>
                    <button onclick="installPWA()" class="px-6 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-red-700 transition shadow-lg">
                        Instalar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- PWA Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('✅ PWA: Service Worker registrado!');
                    })
                    .catch(error => {
                        console.log('❌ PWA: Erro ao registrar Service Worker:', error);
                    });
            });
        }

        // Prompt para instalar PWA
        let deferredPrompt;
        const installBanner = document.getElementById('pwa-install-banner');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;

            // Mostrar banner após 2 segundos
            setTimeout(() => {
                installBanner.style.display = 'block';
                setTimeout(() => {
                    installBanner.style.transform = 'translateY(0)';
                }, 100);
            }, 2000);
        });

        function installPWA() {
            if (!deferredPrompt) return;

            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('✅ App instalado!');
                }
                deferredPrompt = null;
                dismissPWAPrompt();
            });
        }

        function dismissPWAPrompt() {
            installBanner.style.transform = 'translateY(100%)';
            setTimeout(() => {
                installBanner.style.display = 'none';
            }, 300);
        }

        // Detectar se já está instalado
        window.addEventListener('appinstalled', () => {
            console.log('✅ PWA instalado com sucesso!');
            dismissPWAPrompt();
        });
    </script>

    <!-- Modal de Avaliação -->
    @include('components.review-modal')

</body>
</html>
