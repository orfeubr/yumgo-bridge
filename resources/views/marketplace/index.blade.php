<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YumGo - Delivery de Comida</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="/" class="flex items-center space-x-2">
                    <div class="bg-red-600 text-white px-3 py-2 rounded-lg font-bold text-xl">
                        YumGo
                    </div>
                </a>

                <!-- Busca -->
                <div class="flex-1 max-w-xl mx-8">
                    <form action="/" method="GET" class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Buscar restaurante..."
                            class="w-full px-4 py-2 pl-10 pr-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </form>
                </div>

                <!-- Menu -->
                <nav class="flex items-center space-x-4">
                    <a href="/para-restaurantes" class="flex items-center space-x-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-store"></i>
                        <span class="hidden md:inline">Sou Restaurante</span>
                    </a>

                    <a href="/admin" class="text-gray-600 hover:text-red-600 transition">
                        <i class="fas fa-user-circle text-2xl"></i>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-red-600 to-red-700 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    Peça comida dos melhores restaurantes
                </h1>
                <p class="text-xl text-red-100">
                    Delivery rápido, preços justos e cashback em cada pedido!
                </p>
            </div>
        </div>
    </section>

    <!-- Filtros rápidos -->
    <section class="bg-white border-b py-4">
        <div class="container mx-auto px-4">
            <div class="flex items-center space-x-4 overflow-x-auto">
                <button class="px-4 py-2 rounded-full bg-red-600 text-white whitespace-nowrap">
                    <i class="fas fa-fire"></i> Todos
                </button>
                <button class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap">
                    <i class="fas fa-pizza-slice"></i> Pizza
                </button>
                <button class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap">
                    <i class="fas fa-hamburger"></i> Hambúrguer
                </button>
                <button class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap">
                    <i class="fas fa-drumstick-bite"></i> Marmitex
                </button>
                <button class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap">
                    <i class="fas fa-ice-cream"></i> Sobremesas
                </button>
                <button class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap">
                    <i class="fas fa-coffee"></i> Bebidas
                </button>
            </div>
        </div>
    </section>

    <!-- Restaurantes -->
    <section class="py-8">
        <div class="container mx-auto px-4">
            @if($search)
                <h2 class="text-2xl font-bold mb-6">
                    Resultados para "{{ $search }}"
                    <span class="text-gray-500 text-lg">({{ $restaurants->total() }} restaurantes)</span>
                </h2>
            @else
                <h2 class="text-2xl font-bold mb-6">Restaurantes disponíveis</h2>
            @endif

            @if($restaurants->isEmpty())
                <div class="text-center py-16">
                    <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">Nenhum restaurante encontrado</h3>
                    <p class="text-gray-500">Tente outra busca ou navegue por todas as opções</p>
                    @if($search)
                        <a href="/" class="inline-block mt-4 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Ver todos os restaurantes
                        </a>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($restaurants as $restaurant)
                        <a href="{{ $restaurant->url }}" class="block bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden group">
                            <!-- Imagem do restaurante -->
                            <div class="relative h-40 bg-gray-200 overflow-hidden">
                                @if($restaurant->logo)
                                    <img
                                        src="{{ $restaurant->logo_url }}"
                                        alt="{{ $restaurant->name }}"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-red-500 to-red-600">
                                        <i class="fas fa-utensils text-white text-4xl"></i>
                                    </div>
                                @endif

                                <!-- Badge de status -->
                                @if($restaurant->is_open)
                                    <div class="absolute top-2 right-2 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-lg">
                                        <i class="fas fa-circle text-xs mr-1"></i> Aberto
                                    </div>
                                @else
                                    <div class="absolute top-2 right-2 bg-gray-800 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-lg">
                                        Fechado
                                    </div>
                                @endif
                            </div>

                            <!-- Informações -->
                            <div class="p-4">
                                <h3 class="font-bold text-lg mb-1 group-hover:text-red-600 transition">
                                    {{ $restaurant->name }}
                                </h3>

                                @if($restaurant->description)
                                    <p class="text-gray-600 text-sm mb-2 line-clamp-2">
                                        {{ $restaurant->description }}
                                    </p>
                                @endif

                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <div class="flex items-center space-x-4">
                                        <span>
                                            <i class="fas fa-star text-yellow-400"></i> 4.5
                                        </span>
                                        <span>
                                            <i class="fas fa-clock"></i> 30-40 min
                                        </span>
                                    </div>

                                    <!-- Badge de cashback -->
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-semibold">
                                        <i class="fas fa-gift"></i> Cashback
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Paginação -->
                @if($restaurants->hasPages())
                    <div class="mt-8">
                        {{ $restaurants->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 mt-16">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Sobre -->
                <div>
                    <h4 class="font-bold text-lg mb-4">YumGo</h4>
                    <p class="text-gray-400 text-sm">
                        Delivery com comissão justa e cashback para você!
                    </p>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="font-bold mb-4">Para Você</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="/" class="hover:text-white">Restaurantes</a></li>
                        <li><a href="/ajuda" class="hover:text-white">Ajuda</a></li>
                        <li><a href="/contato" class="hover:text-white">Contato</a></li>
                    </ul>
                </div>

                <!-- Para Restaurantes -->
                <div>
                    <h4 class="font-bold mb-4">Para Restaurantes</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="/para-restaurantes" class="hover:text-white">Seja Parceiro</a></li>
                        <li><a href="/para-restaurantes#planos" class="hover:text-white">Planos e Preços</a></li>
                        <li><a href="/admin" class="hover:text-white">Acessar Painel</a></li>
                    </ul>
                </div>

                <!-- Redes Sociais -->
                <div>
                    <h4 class="font-bold mb-4">Redes Sociais</h4>
                    <div class="flex space-x-4 text-2xl">
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400">
                <p>&copy; 2026 YumGo. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
