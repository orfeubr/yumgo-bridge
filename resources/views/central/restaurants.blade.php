<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YumGo - Restaurantes Parceiros</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .text-primary { color: #EA1D2C; }
        .bg-primary { background-color: #EA1D2C; }
        .border-primary { border-color: #EA1D2C; }
        .hover-lift:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="antialiased bg-white">

    <!-- Header -->
    <nav class="border-b border-gray-200 sticky top-0 z-50 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span class="text-2xl font-semibold text-gray-900">YumGo</span>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="/admin" class="bg-primary text-white px-5 py-2 rounded-full text-sm font-medium hover:bg-red-700 transition">
                        Área Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="py-16 bg-gray-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Restaurantes Parceiros</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Escolha seu restaurante favorito e ganhe cashback em cada pedido
            </p>
        </div>
    </section>

    <!-- Restaurantes -->
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($tenants->count() > 0)
                <!-- Stats -->
                <div class="mb-8">
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold text-gray-900">{{ $tenants->count() }}</span>
                        {{ $tenants->count() == 1 ? 'restaurante disponível' : 'restaurantes disponíveis' }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($tenants as $tenant)
                        <a href="{{ $tenant['url'] }}" class="block group">
                            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-200 hover-lift">
                                <!-- Imagem -->
                                <div class="h-40 bg-gray-100 relative overflow-hidden">
                                    @if(!empty($tenant['logo']))
                                        <img src="{{ Storage::url($tenant['logo']) }}"
                                             alt="{{ $tenant['name'] }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-50">
                                            <span class="text-6xl">🍽️</span>
                                        </div>
                                    @endif

                                    <!-- Badge Aberto -->
                                    <div class="absolute top-3 right-3">
                                        <span class="px-3 py-1 bg-green-500 text-white text-xs font-medium rounded-full flex items-center gap-1.5 shadow-sm">
                                            <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
                                            Aberto
                                        </span>
                                    </div>
                                </div>

                                <!-- Conteúdo -->
                                <div class="p-5">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-primary transition truncate">
                                        {{ $tenant['name'] }}
                                    </h3>

                                    @if(!empty($tenant['description']))
                                        <p class="text-sm text-gray-600 mb-4 line-clamp-2 min-h-[2.5rem]">
                                            {{ $tenant['description'] }}
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-400 mb-4 min-h-[2.5rem]">
                                            Delivery rápido com cashback
                                        </p>
                                    @endif

                                    <!-- Info -->
                                    <div class="flex items-center justify-between text-xs text-gray-500 pt-4 border-t border-gray-100">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            30-45 min
                                        </span>
                                        <span class="flex items-center gap-1.5 text-primary font-medium">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                            </svg>
                                            Cashback
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-20">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Nenhum restaurante disponível</h3>
                    <p class="text-gray-600 max-w-md mx-auto">
                        Em breve novos restaurantes parceiros estarão disponíveis na plataforma.
                    </p>
                </div>
            @endif
        </div>
    </section>

    <!-- Features -->
    <section class="py-16 bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl">💰</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Comissão Baixa</h3>
                    <p class="text-sm text-gray-600">
                        Apenas 1-3% de comissão. Mais vantajoso para restaurantes.
                    </p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl">🎁</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Cashback Configurável</h3>
                    <p class="text-sm text-gray-600">
                        Ganhe cashback em cada pedido e acumule para compras futuras.
                    </p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl">🔒</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Dados Seguros</h3>
                    <p class="text-sm text-gray-600">
                        Isolamento total de dados com tecnologia PostgreSQL.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-gray-200 py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Logo -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span class="text-xl font-semibold text-gray-900">YumGo</span>
                    </div>
                    <p class="text-sm text-gray-600 max-w-sm">
                        Plataforma de delivery com comissão justa e cashback configurável.
                    </p>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-3 text-sm">Plataforma</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="/admin" class="hover:text-primary transition">Painel Admin</a></li>
                        <li><a href="#" class="hover:text-primary transition">Sobre</a></li>
                        <li><a href="#" class="hover:text-primary transition">Seja Parceiro</a></li>
                    </ul>
                </div>

                <!-- Suporte -->
                <div>
                    <h4 class="font-semibold text-gray-900 mb-3 text-sm">Suporte</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="#" class="hover:text-primary transition">Central de Ajuda</a></li>
                        <li><a href="#" class="hover:text-primary transition">Contato</a></li>
                        <li><a href="#" class="hover:text-primary transition">Termos de Uso</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom -->
            <div class="border-t border-gray-200 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-gray-500">
                        © {{ date('Y') }} YumGo. Todos os direitos reservados.
                    </p>
                    <div class="flex items-center gap-6 text-xs text-gray-500">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Comissão 1-3%
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Cashback
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Seguro
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
