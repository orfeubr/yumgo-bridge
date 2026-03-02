<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YumGo - Peça comida dos melhores restaurantes</title>
    <meta name="description" content="Peça delivery dos melhores restaurantes da região. Cashback em cada pedido, pagamento online e entrega rápida.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .btn-primary {
            background-color: #EA1D2C;
            color: white;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #d41a27;
            transform: translateY(-1px);
        }
        .text-primary { color: #EA1D2C; }
        .border-primary { border-color: #EA1D2C; }
        .bg-primary { background-color: #EA1D2C; }
    </style>
</head>
<body class="antialiased bg-gray-50">

    <!-- Header -->
    <nav class="border-b border-gray-200 sticky top-0 z-50 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span class="text-2xl font-semibold text-gray-900">YumGo</span>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="/" class="hidden md:block text-gray-700 hover:text-primary transition text-sm font-medium">Restaurantes</a>
                    <a href="/parceiro" class="hidden md:block text-gray-700 hover:text-primary transition text-sm font-medium">Seja Parceiro</a>
                    <a href="/admin/login" class="border-2 border-gray-300 text-gray-700 px-5 py-2 rounded-full text-sm font-medium hover:border-primary hover:text-primary transition">
                        Entrar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section com Busca -->
    <section class="bg-gradient-to-br from-red-50 to-orange-50 py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Peça comida dos melhores restaurantes
                </h1>
                <p class="text-lg lg:text-xl text-gray-600 max-w-2xl mx-auto mb-8">
                    Ganhe <span class="text-primary font-semibold">cashback</span> em cada pedido e acumule pontos para descontos futuros
                </p>

                <!-- Busca -->
                <form method="GET" action="/" class="max-w-2xl mx-auto">
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Buscar restaurantes..."
                            class="w-full px-6 py-4 rounded-full border-2 border-gray-200 focus:border-primary focus:outline-none text-lg shadow-sm"
                        >
                        <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 btn-primary px-6 py-2.5 rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Destaques -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                <div class="bg-white rounded-xl p-4 text-center shadow-sm border border-gray-100">
                    <div class="text-3xl mb-2">🎁</div>
                    <div class="text-sm font-medium text-gray-900">Cashback em cada pedido</div>
                </div>
                <div class="bg-white rounded-xl p-4 text-center shadow-sm border border-gray-100">
                    <div class="text-3xl mb-2">💳</div>
                    <div class="text-sm font-medium text-gray-900">PIX e Cartão</div>
                </div>
                <div class="bg-white rounded-xl p-4 text-center shadow-sm border border-gray-100">
                    <div class="text-3xl mb-2">🚀</div>
                    <div class="text-sm font-medium text-gray-900">Entrega rápida</div>
                </div>
                <div class="bg-white rounded-xl p-4 text-center shadow-sm border border-gray-100">
                    <div class="text-3xl mb-2">⭐</div>
                    <div class="text-sm font-medium text-gray-900">Avaliações reais</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Lista de Restaurantes -->
    <section class="py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">
                    @if(request('search'))
                        Resultados para "{{ request('search') }}"
                    @else
                        Restaurantes disponíveis
                    @endif
                </h2>
                <span class="text-gray-600">{{ $restaurants->count() }} restaurante(s)</span>
            </div>

            @if($restaurants->isEmpty())
                <!-- Vazio -->
                <div class="text-center py-16">
                    <div class="text-6xl mb-4">🔍</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Nenhum restaurante encontrado</h3>
                    <p class="text-gray-600 mb-6">Tente buscar por outro termo</p>
                    <a href="/" class="btn-primary px-6 py-3 rounded-full inline-block">
                        Ver todos restaurantes
                    </a>
                </div>
            @else
                <!-- Grid de Restaurantes -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($restaurants as $restaurant)
                        <a href="{{ $restaurant->url }}" target="_blank" class="group bg-white rounded-2xl overflow-hidden border-2 border-gray-100 hover:border-primary hover:shadow-xl transition-all duration-200">
                            <!-- Imagem/Logo -->
                            <div class="aspect-video bg-gradient-to-br from-red-100 to-orange-100 flex items-center justify-center overflow-hidden">
                                <img
                                    src="{{ $restaurant->logo_url }}"
                                    alt="{{ $restaurant->name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                >
                            </div>

                            <!-- Conteúdo -->
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-primary transition">
                                    {{ $restaurant->name }}
                                </h3>

                                @if($restaurant->description)
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                        {{ $restaurant->description }}
                                    </p>
                                @endif

                                <!-- Badges -->
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Aberto
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        🎁 Cashback
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        💳 PIX
                                    </span>
                                </div>

                                <!-- CTA -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-1 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                        <span class="font-medium">4.8</span>
                                        <span class="ml-1">(120+)</span>
                                    </div>
                                    <span class="text-primary font-semibold group-hover:underline">
                                        Ver cardápio →
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- CTA Seja Parceiro -->
    <section class="py-16 bg-gradient-to-br from-primary to-red-700">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">
                É dono de restaurante?
            </h2>
            <p class="text-lg text-red-100 mb-8">
                Comissão de apenas 1-3% por pedido. Muito mais justo que as outras plataformas.
            </p>
            <a href="/parceiro" class="bg-white text-primary px-8 py-4 rounded-full font-medium inline-block shadow-lg hover:bg-gray-50 transition">
                Quero ser parceiro
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-gray-200 py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <!-- Logo e Descrição -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span class="text-xl font-semibold text-gray-900">YumGo</span>
                    </div>
                    <p class="text-sm text-gray-600 max-w-md">
                        A melhor plataforma de delivery do Brasil. Ganhe cashback em cada pedido e apoie restaurantes locais.
                    </p>
                </div>

                <!-- Para Clientes -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">Para você</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/" class="text-gray-600 hover:text-primary transition">Restaurantes</a></li>
                        <li><a href="/como-funciona" class="text-gray-600 hover:text-primary transition">Como funciona</a></li>
                        <li><a href="/cashback" class="text-gray-600 hover:text-primary transition">Cashback</a></li>
                    </ul>
                </div>

                <!-- Para Restaurantes -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">Para restaurantes</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/parceiro" class="text-gray-600 hover:text-primary transition">Seja parceiro</a></li>
                        <li><a href="/planos" class="text-gray-600 hover:text-primary transition">Planos e preços</a></li>
                        <li><a href="/admin/login" class="text-gray-600 hover:text-primary transition">Área do parceiro</a></li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-200 pt-8 text-center">
                <p class="text-sm text-gray-500">
                    &copy; 2026 YumGo. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
