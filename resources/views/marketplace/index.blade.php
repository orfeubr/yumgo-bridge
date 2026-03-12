<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $platformSettings->platform_name ?? 'YumGo' }} - Delivery de Comida</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF4D2D',
                    }
                }
            }
        }
    </script>
    <style>
        /* Smooth scroll */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Card hover effect */
        .restaurant-card {
            transition: all 0.3s ease;
        }
        .restaurant-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        /* Hero background - High quality food photography */
        .hero-bg {
            background: linear-gradient(135deg, rgba(255,77,45,0.92) 0%, rgba(255,77,45,0.82) 100%),
                        url('https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=1920&h=800&fit=crop&q=90') center/cover;
            background-attachment: fixed;
        }
    </style>
</head>
<body class="bg-white">

    <!-- ========== HEADER (Sticky) ========== -->
    <header class="bg-white shadow-sm sticky top-0 z-50 border-b border-gray-100">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="/" class="flex items-center">
                    @if(isset($platformSettings) && $platformSettings->platform_logo)
                        <img src="{{ url('storage/' . $platformSettings->platform_logo) }}"
                             alt="{{ $platformSettings->platform_name }}"
                             class="h-16 md:h-20 max-w-[280px] object-contain">
                    @else
                        <div class="bg-primary text-white px-4 py-2 rounded-lg font-bold text-xl">
                            {{ $platformSettings->platform_name ?? 'YumGo' }}
                        </div>
                    @endif
                </a>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <a href="/para-restaurantes"
                       class="hidden md:flex items-center gap-2 px-4 py-2 text-primary font-semibold hover:bg-red-50 rounded-lg transition">
                        <i class="fas fa-store"></i>
                        <span>Sou Restaurante</span>
                    </a>
                    <a href="/admin"
                       class="flex items-center justify-center w-10 h-10 bg-gray-100 rounded-full hover:bg-gray-200 transition">
                        <i class="fas fa-user text-gray-700"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- ========== HERO SECTION (Food Photography Background) ========== -->
    <section class="hero-bg text-white py-16 md:py-20">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <!-- Main Heading -->
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
                    O que você quer<br class="md:hidden"> comer hoje?
                </h1>

                <!-- Search Bar (Centered, Simple) -->
                <div class="bg-white rounded-2xl shadow-2xl p-2">
                    <form action="/" method="GET" class="flex items-center gap-2">
                        <div class="flex items-center gap-3 px-6 py-4 flex-1">
                            <i class="fas fa-search text-gray-400 text-xl"></i>
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Buscar restaurante ou prato..."
                                class="flex-1 outline-none bg-transparent text-gray-700 placeholder-gray-400 text-lg"
                            >
                        </div>
                        <button type="submit"
                                class="bg-primary hover:bg-red-600 text-white px-8 py-4 rounded-xl font-semibold transition shadow-lg">
                            Buscar
                        </button>
                    </form>
                </div>

                <!-- Stats -->
                <div class="flex items-center justify-center gap-6 mt-8 text-white/90 text-sm flex-wrap">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-utensils"></i>
                        <span>{{ $restaurants->total() }}+ restaurantes</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-clock"></i>
                        <span>Entrega rápida</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-gift"></i>
                        <span>Cashback em pedidos</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== CATEGORY NAVIGATION (Horizontal Scroll, Sticky) ========== -->
    <section class="bg-white border-b border-gray-100 sticky top-16 z-40 shadow-sm">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex items-center gap-3 overflow-x-auto no-scrollbar py-4">
                <!-- All (Active) -->
                <a href="/" class="flex flex-col items-center gap-2 px-5 py-3 rounded-2xl bg-primary text-white shadow-lg min-w-[90px] hover:shadow-xl transition">
                    <div class="text-2xl">🔥</div>
                    <span class="text-xs font-semibold whitespace-nowrap">Todos</span>
                </a>

                <!-- Pizza -->
                <a href="/?category=pizza" class="flex flex-col items-center gap-2 px-5 py-3 rounded-2xl bg-gray-50 hover:bg-gray-100 text-gray-700 min-w-[90px] transition">
                    <div class="text-2xl">🍕</div>
                    <span class="text-xs font-semibold whitespace-nowrap">Pizza</span>
                </a>

                <!-- Burger -->
                <a href="/?category=burger" class="flex flex-col items-center gap-2 px-5 py-3 rounded-2xl bg-gray-50 hover:bg-gray-100 text-gray-700 min-w-[90px] transition">
                    <div class="text-2xl">🍔</div>
                    <span class="text-xs font-semibold whitespace-nowrap">Burger</span>
                </a>

                <!-- Japonesa -->
                <a href="/?category=japonesa" class="flex flex-col items-center gap-2 px-5 py-3 rounded-2xl bg-gray-50 hover:bg-gray-100 text-gray-700 min-w-[90px] transition">
                    <div class="text-2xl">🍱</div>
                    <span class="text-xs font-semibold whitespace-nowrap">Japonesa</span>
                </a>

                <!-- Saudável -->
                <a href="/?category=saudavel" class="flex flex-col items-center gap-2 px-5 py-3 rounded-2xl bg-gray-50 hover:bg-gray-100 text-gray-700 min-w-[90px] transition">
                    <div class="text-2xl">🥗</div>
                    <span class="text-xs font-semibold whitespace-nowrap">Saudável</span>
                </a>

                <!-- Sobremesa -->
                <a href="/?category=sobremesa" class="flex flex-col items-center gap-2 px-5 py-3 rounded-2xl bg-gray-50 hover:bg-gray-100 text-gray-700 min-w-[90px] transition">
                    <div class="text-2xl">🍰</div>
                    <span class="text-xs font-semibold whitespace-nowrap">Sobremesa</span>
                </a>

                <!-- Marmitex -->
                <a href="/?category=marmitex" class="flex flex-col items-center gap-2 px-5 py-3 rounded-2xl bg-gray-50 hover:bg-gray-100 text-gray-700 min-w-[90px] transition">
                    <div class="text-2xl">🍛</div>
                    <span class="text-xs font-semibold whitespace-nowrap">Marmitex</span>
                </a>

                <!-- Bebidas -->
                <a href="/?category=bebidas" class="flex flex-col items-center gap-2 px-5 py-3 rounded-2xl bg-gray-50 hover:bg-gray-100 text-gray-700 min-w-[90px] transition">
                    <div class="text-2xl">🥤</div>
                    <span class="text-xs font-semibold whitespace-nowrap">Bebidas</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ========== MAIN CONTENT ========== -->
    <div class="bg-gray-50 py-8">
        <div class="container mx-auto px-4 lg:px-8">

            @if($search)
                <!-- Search Results -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        Resultados para "{{ $search }}"
                    </h2>
                    <p class="text-gray-600">{{ $restaurants->total() }} restaurantes encontrados</p>
                </div>
            @else
                <!-- ========== 🔥 MAIS PEDIDOS ========== -->
                <section class="mb-10">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">🔥</div>
                            <h2 class="text-2xl font-bold text-gray-900">Mais pedidos</h2>
                        </div>
                        <a href="/?sort=popular" class="text-primary font-semibold hover:underline">Ver todos</a>
                    </div>

                    <!-- Horizontal Scroll Cards -->
                    <div class="flex gap-4 overflow-x-auto no-scrollbar pb-2">
                        @foreach($mostOrdered as $restaurant)
                            @if($restaurant->delivers)
                            <a href="{{ $restaurant->url }}" class="restaurant-card bg-white rounded-2xl overflow-hidden shadow-sm flex-shrink-0 w-[280px]">
                                <!-- Image -->
                                <div class="relative h-40 overflow-hidden bg-gray-100">
                                    @if($restaurant->logo)
                                        <img src="{{ $restaurant->logo_url }}" alt="{{ $restaurant->name }}"
                                             class="w-full h-full object-cover"
                                             onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop';">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop"
                                             alt="Food" class="w-full h-full object-cover">
                                    @endif

                                    <!-- Badges -->
                                    @if($restaurant->is_free_delivery)
                                        <span class="absolute top-3 left-3 px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full shadow-lg">
                                            GRÁTIS
                                        </span>
                                    @endif

                                    @if($restaurant->is_open)
                                        <span class="absolute top-3 right-3 px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full shadow-lg">
                                            ABERTO
                                        </span>
                                    @else
                                        <span class="absolute top-3 right-3 px-3 py-1 bg-gray-800 text-white text-xs font-bold rounded-full shadow-lg">
                                            FECHADO
                                        </span>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="p-4">
                                    <h3 class="font-bold text-gray-900 mb-2 line-clamp-1">{{ $restaurant->name }}</h3>

                                    <!-- Rating -->
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="flex items-center gap-1 bg-green-50 px-2 py-1 rounded-lg">
                                            <i class="fas fa-star text-yellow-500 text-xs"></i>
                                            <span class="text-xs font-bold text-green-700">4.8</span>
                                        </div>
                                        @if($restaurant->cuisine_types && count($restaurant->cuisine_types) > 0)
                                            <span class="text-xs text-gray-500">{{ ucfirst(str_replace('-', ' ', $restaurant->cuisine_types[0])) }}</span>
                                        @endif
                                    </div>

                                    <!-- Delivery Info -->
                                    <div class="flex items-center justify-between text-xs text-gray-600 border-t border-gray-100 pt-3">
                                        <span><i class="fas fa-clock text-gray-400"></i> 30-40 min</span>
                                        @if($restaurant->is_free_delivery)
                                            <span class="font-bold text-green-600">Grátis</span>
                                        @else
                                            <span class="font-bold text-gray-900">{{ $restaurant->delivery_fee_formatted }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            @endif
                        @endforeach
                    </div>
                </section>

                <!-- ========== ⭐ RESTAURANTES POPULARES ========== -->
                @if($restaurants->count() > 0)
                <section class="mb-10">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">⭐</div>
                            <h2 class="text-2xl font-bold text-gray-900">Restaurantes populares</h2>
                        </div>
                        <a href="/?sort=rating" class="text-primary font-semibold hover:underline">Ver todos</a>
                    </div>

                    <!-- Horizontal Scroll Cards -->
                    <div class="flex gap-4 overflow-x-auto no-scrollbar pb-2">
                        @foreach($restaurants->take(6) as $restaurant)
                            @if($restaurant->delivers)
                            <a href="{{ $restaurant->url }}" class="restaurant-card bg-white rounded-2xl overflow-hidden shadow-sm flex-shrink-0 w-[280px]">
                                <!-- Image -->
                                <div class="relative h-40 overflow-hidden bg-gray-100">
                                    @if($restaurant->logo)
                                        <img src="{{ $restaurant->logo_url }}" alt="{{ $restaurant->name }}"
                                             class="w-full h-full object-cover"
                                             onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop';">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop"
                                             alt="Food" class="w-full h-full object-cover">
                                    @endif

                                    <!-- Status -->
                                    @if($restaurant->is_open)
                                        <span class="absolute top-3 right-3 px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full shadow-lg">
                                            ABERTO
                                        </span>
                                    @else
                                        <span class="absolute top-3 right-3 px-3 py-1 bg-gray-800 text-white text-xs font-bold rounded-full shadow-lg">
                                            FECHADO
                                        </span>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="p-4">
                                    <h3 class="font-bold text-gray-900 mb-2 line-clamp-1">{{ $restaurant->name }}</h3>

                                    <!-- Rating -->
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg">
                                            <i class="fas fa-star text-yellow-500 text-xs"></i>
                                            <span class="text-xs font-bold text-yellow-700">4.9</span>
                                        </div>
                                        @if($restaurant->cuisine_types && count($restaurant->cuisine_types) > 0)
                                            <span class="text-xs text-gray-500">{{ ucfirst(str_replace('-', ' ', $restaurant->cuisine_types[0])) }}</span>
                                        @endif
                                    </div>

                                    <!-- Delivery Info -->
                                    <div class="flex items-center justify-between text-xs text-gray-600 border-t border-gray-100 pt-3">
                                        <span><i class="fas fa-clock text-gray-400"></i> 25-35 min</span>
                                        @if($restaurant->is_free_delivery)
                                            <span class="font-bold text-green-600">Grátis</span>
                                        @else
                                            <span class="font-bold text-gray-900">{{ $restaurant->delivery_fee_formatted }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            @endif
                        @endforeach
                    </div>
                </section>
                @endif

                <!-- ========== 💰 RESTAURANTES COM CASHBACK ========== -->
                @if($withCashback->count() > 0)
                <section class="mb-10">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">💰</div>
                            <h2 class="text-2xl font-bold text-gray-900">Ganhe cashback</h2>
                        </div>
                        <span class="text-sm text-gray-600">Compre e ganhe de volta!</span>
                    </div>

                    <!-- Horizontal Scroll Cards -->
                    <div class="flex gap-4 overflow-x-auto no-scrollbar pb-2">
                        @foreach($withCashback as $restaurant)
                            @if($restaurant->delivers)
                            <a href="{{ $restaurant->url }}" class="restaurant-card bg-white rounded-2xl overflow-hidden shadow-sm flex-shrink-0 w-[280px]">
                                <!-- Image -->
                                <div class="relative h-40 overflow-hidden bg-gray-100">
                                    @if($restaurant->logo)
                                        <img src="{{ $restaurant->logo_url }}" alt="{{ $restaurant->name }}"
                                             class="w-full h-full object-cover"
                                             onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop';">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop"
                                             alt="Food" class="w-full h-full object-cover">
                                    @endif

                                    <!-- Cashback Badge (Destaque) -->
                                    <div class="absolute top-3 left-3">
                                        <span class="px-3 py-1.5 bg-gradient-to-r from-yellow-400 to-orange-400 text-gray-900 text-sm font-bold rounded-full shadow-lg flex items-center gap-1">
                                            💰 {{ number_format($restaurant->cashback_percentage, 1) }}% cashback
                                        </span>
                                    </div>

                                    <!-- Status -->
                                    @if($restaurant->is_open)
                                        <span class="absolute top-3 right-3 px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full shadow-lg">
                                            ABERTO
                                        </span>
                                    @else
                                        <span class="absolute top-3 right-3 px-3 py-1 bg-gray-800 text-white text-xs font-bold rounded-full shadow-lg">
                                            FECHADO
                                        </span>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="p-4">
                                    <h3 class="font-bold text-gray-900 mb-2 line-clamp-1">{{ $restaurant->name }}</h3>

                                    <!-- Rating -->
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="flex items-center gap-1 bg-green-50 px-2 py-1 rounded-lg">
                                            <i class="fas fa-star text-yellow-500 text-xs"></i>
                                            <span class="text-xs font-bold text-green-700">4.8</span>
                                        </div>
                                        @if($restaurant->cuisine_types && count($restaurant->cuisine_types) > 0)
                                            <span class="text-xs text-gray-500">{{ ucfirst(str_replace('-', ' ', $restaurant->cuisine_types[0])) }}</span>
                                        @endif
                                    </div>

                                    <!-- Delivery Info -->
                                    <div class="flex items-center justify-between text-xs text-gray-600 border-t border-gray-100 pt-3">
                                        <span><i class="fas fa-clock text-gray-400"></i> 30-40 min</span>
                                        @if($restaurant->is_free_delivery)
                                            <span class="font-bold text-green-600">Grátis</span>
                                        @else
                                            <span class="font-bold text-gray-900">{{ $restaurant->delivery_fee_formatted }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            @endif
                        @endforeach
                    </div>
                </section>
                @endif

                <!-- ========== 🎟 PROMOÇÕES ========== -->
                @if($restaurants->where('is_free_delivery', true)->count() > 0)
                <section class="mb-10">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">🎟</div>
                            <h2 class="text-2xl font-bold text-gray-900">Promoções</h2>
                        </div>
                    </div>

                    <!-- Promo Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($restaurants->where('is_free_delivery', true)->take(2) as $restaurant)
                            @if($restaurant->delivers)
                            <a href="{{ $restaurant->url }}" class="restaurant-card bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl p-6 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold mb-1 opacity-90">🎉 ENTREGA GRÁTIS</div>
                                        <h3 class="text-xl font-bold mb-2">{{ $restaurant->name }}</h3>
                                        <p class="text-sm opacity-90">Aproveite sem taxa de entrega!</p>
                                    </div>
                                    @if($restaurant->logo)
                                        <img src="{{ $restaurant->logo_url }}" alt="{{ $restaurant->name }}"
                                             class="w-20 h-20 rounded-full bg-white/20 object-cover">
                                    @endif
                                </div>
                            </a>
                            @endif
                        @endforeach
                    </div>
                </section>
                @endif

                <!-- ========== TODOS OS RESTAURANTES ========== -->
                <section>
                    <h2 class="text-2xl font-bold mb-6 text-gray-900">Todos os restaurantes</h2>
                </section>
            @endif

            <!-- ========== RESTAURANT GRID ========== -->
            @if($restaurants->isEmpty())
                <div class="text-center py-20 bg-white rounded-2xl">
                    <div class="text-6xl mb-4">🔍</div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-2">Nenhum restaurante encontrado</h3>
                    <p class="text-gray-600 mb-6">Tente buscar por outra região ou categoria</p>
                    @if($search)
                        <a href="/" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-full font-semibold hover:bg-red-600 transition">
                            <i class="fas fa-arrow-left"></i>
                            Ver todos os restaurantes
                        </a>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($restaurants as $restaurant)
                        @if($restaurant->delivers)
                        <!-- Restaurant Card (Modern Design) -->
                        <a href="{{ $restaurant->url }}" class="restaurant-card bg-white rounded-2xl overflow-hidden shadow-sm">
                            <!-- Large Food Photo -->
                            <div class="relative h-48 overflow-hidden bg-gray-100">
                                @if($restaurant->logo)
                                    <img src="{{ $restaurant->logo_url }}" alt="{{ $restaurant->name }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop';">
                                @else
                                    <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop"
                                         alt="Food" class="w-full h-full object-cover">
                                @endif

                                <!-- Optional Badges -->
                                <div class="absolute top-3 left-3 flex flex-col gap-2">
                                    @if($restaurant->is_free_delivery)
                                        <span class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full shadow-lg">
                                            GRÁTIS
                                        </span>
                                    @endif

                                    {{-- Only show cashback badge if restaurant has cashback configured --}}
                                    {{-- Backend should pass: $restaurant->has_cashback or $restaurant->cashback_percentage --}}
                                    @if(isset($restaurant->cashback_percentage) && $restaurant->cashback_percentage > 0)
                                        <span class="px-3 py-1 bg-yellow-400 text-gray-900 text-xs font-bold rounded-full shadow-lg flex items-center gap-1">
                                            💰 {{ $restaurant->cashback_percentage }}% cashback
                                        </span>
                                    @endif
                                </div>

                                <!-- Status -->
                                <div class="absolute top-3 right-3">
                                    @if($restaurant->is_open)
                                        <span class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full shadow-lg">
                                            ABERTO
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-gray-800 text-white text-xs font-bold rounded-full shadow-lg">
                                            FECHADO
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Card Content -->
                            <div class="p-5">
                                <!-- Restaurant Name -->
                                <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-1">
                                    {{ $restaurant->name }}
                                </h3>

                                <!-- Rating & Cuisine -->
                                <div class="flex items-center gap-2 mb-3 text-sm flex-wrap">
                                    <div class="flex items-center gap-1 bg-green-50 px-2 py-1 rounded-lg">
                                        <i class="fas fa-star text-yellow-500 text-xs"></i>
                                        <span class="font-bold text-green-700">4.8</span>
                                        <span class="text-gray-500">(200+)</span>
                                    </div>

                                    @if($restaurant->cuisine_types && count($restaurant->cuisine_types) > 0)
                                        <span class="text-gray-400">•</span>
                                        <span class="text-gray-600">{{ ucfirst(str_replace('-', ' ', $restaurant->cuisine_types[0])) }}</span>
                                    @endif
                                </div>

                                <!-- Distance -->
                                @if($restaurant->distance_formatted)
                                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
                                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                                        <span>{{ $restaurant->distance_formatted }}</span>
                                    </div>
                                @endif

                                <!-- Delivery Info -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="fas fa-clock text-gray-400"></i>
                                        <span>30-40 min</span>
                                    </div>

                                    <div class="text-right">
                                        @if($restaurant->is_free_delivery)
                                            <span class="text-sm font-bold text-green-600">Entrega GRÁTIS</span>
                                        @else
                                            <div class="text-xs text-gray-500">Entrega</div>
                                            <div class="text-sm font-bold text-gray-900">{{ $restaurant->delivery_fee_formatted }}</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Minimum Order -->
                                <div class="mt-3 text-xs text-gray-500">
                                    Pedido mínimo: <span class="font-semibold text-gray-700">R$ 20,00</span>
                                </div>
                            </div>
                        </a>
                        @endif
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($restaurants->hasPages())
                    <div class="mt-12 flex justify-center">
                        {{ $restaurants->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- ========== FOOTER ========== -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h4 class="font-bold text-lg mb-4">{{ $platformSettings->platform_name ?? 'YumGo' }}</h4>
                    <p class="text-gray-400 text-sm">Delivery com comissão justa e cashback configurável!</p>
                </div>

                <div>
                    <h4 class="font-bold mb-4">Para Você</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="/" class="hover:text-white transition">Restaurantes</a></li>
                        <li><a href="/ajuda" class="hover:text-white transition">Central de Ajuda</a></li>
                        <li><a href="/contato" class="hover:text-white transition">Fale Conosco</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-4">Para Restaurantes</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="/para-restaurantes" class="hover:text-white transition">Seja Parceiro</a></li>
                        <li><a href="/para-restaurantes#planos" class="hover:text-white transition">Planos e Preços</a></li>
                        <li><a href="/admin" class="hover:text-white transition">Acessar Painel</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-4">Redes Sociais</h4>
                    <div class="flex gap-4 text-2xl">
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-400">
                <p>&copy; 2026 {{ $platformSettings->platform_name ?? 'YumGo' }}. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- ========== SCRIPTS ========== -->
    <script>
        // Geolocalização
        const savedLat = localStorage.getItem('client_lat');
        const savedLon = localStorage.getItem('client_lon');
        const urlParams = new URLSearchParams(window.location.search);
        const hasLocationInUrl = urlParams.has('lat') && urlParams.has('lon');

        if (!hasLocationInUrl && 'geolocation' in navigator) {
            if (savedLat && savedLon) {
                urlParams.set('lat', savedLat);
                urlParams.set('lon', savedLon);
                const newUrl = window.location.pathname + '?' + urlParams.toString();
                if (window.location.href !== window.location.origin + newUrl) {
                    window.location.href = newUrl;
                }
            } else {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        localStorage.setItem('client_lat', lat);
                        localStorage.setItem('client_lon', lon);
                        urlParams.set('lat', lat);
                        urlParams.set('lon', lon);
                        window.location.href = window.location.pathname + '?' + urlParams.toString();
                    },
                    function(error) {
                        console.log('Geolocalização negada:', error.message);
                    },
                    { enableHighAccuracy: false, timeout: 5000, maximumAge: 300000 }
                );
            }
        }

        if (window.location.search.includes('clear_location')) {
            localStorage.removeItem('client_lat');
            localStorage.removeItem('client_lon');
            window.location.href = window.location.pathname;
        }
    </script>
</body>
</html>
