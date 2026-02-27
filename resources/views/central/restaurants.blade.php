<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YumGo - Restaurantes Parceiros</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); }
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
    </style>
</head>
<body class="antialiased bg-gradient-to-br from-gray-50 via-orange-50 to-gray-50">

    <!-- Header -->
    <nav class="bg-white/80 backdrop-blur-lg shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xl font-bold bg-gradient-to-r from-orange-600 to-orange-500 bg-clip-text text-transparent">YumGo</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/sobre" class="text-sm text-gray-600 hover:text-orange-600 transition">Sobre</a>
                    <a href="/admin" class="px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition">Painel Admin</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <div class="gradient-bg text-white py-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h1 class="text-4xl md:text-5xl font-black mb-3 tracking-tight">Restaurantes Parceiros</h1>
            <p class="text-lg text-white/90 max-w-xl mx-auto">
                Escolha seu restaurante favorito e ganhe cashback em cada pedido
            </p>
        </div>
    </div>

    <!-- Restaurantes -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($tenants->count() > 0)
            <!-- Stats -->
            <div class="mb-8">
                <p class="text-sm text-gray-600">
                    <span class="font-semibold text-gray-900">{{ $tenants->count() }}</span> {{ $tenants->count() == 1 ? 'restaurante disponível' : 'restaurantes disponíveis' }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($tenants as $tenant)
                    <a href="{{ $tenant['url'] }}" class="block group">
                        <div class="bg-white rounded-2xl shadow-sm overflow-hidden card-hover border border-gray-100">
                            <!-- Logo ou imagem -->
                            <div class="h-36 bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center relative overflow-hidden">
                                @if(!empty($tenant['logo']))
                                    <img src="{{ Storage::url($tenant['logo']) }}"
                                         alt="{{ $tenant['name'] }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <span class="text-5xl group-hover:scale-110 transition-transform">🍽️</span>
                                @endif
                                <div class="absolute top-2 right-2">
                                    <span class="px-2 py-1 bg-green-500 text-white text-xs font-semibold rounded-full flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                                        Aberto
                                    </span>
                                </div>
                            </div>

                            <!-- Conteúdo -->
                            <div class="p-4">
                                <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-orange-600 transition truncate">
                                    {{ $tenant['name'] }}
                                </h3>

                                @if(!empty($tenant['description']))
                                    <p class="text-xs text-gray-500 mb-3 line-clamp-2 h-8">
                                        {{ $tenant['description'] }}
                                    </p>
                                @else
                                    <p class="text-xs text-gray-400 mb-3 h-8">
                                        Delivery rápido com cashback
                                    </p>
                                @endif

                                <!-- Info compacta -->
                                <div class="flex items-center justify-between text-xs text-gray-600 mb-3 pb-3 border-b border-gray-100">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        30-45 min
                                    </span>
                                    <span class="flex items-center gap-1 text-orange-600 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                        </svg>
                                        Cashback
                                    </span>
                                </div>

                                <!-- Botão -->
                                <button class="w-full py-2 bg-orange-600 text-white text-sm font-semibold rounded-lg group-hover:bg-orange-700 transition flex items-center justify-center gap-2">
                                    Ver Cardápio
                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-20">
                <div class="w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Nenhum restaurante disponível</h3>
                <p class="text-gray-600 max-w-sm mx-auto">Em breve novos restaurantes parceiros estarão disponíveis na plataforma!</p>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <footer class="bg-gradient-to-br from-gray-900 to-gray-800 text-white py-12 mt-16 border-t border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Logo e descrição -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold">YumGo</span>
                    </div>
                    <p class="text-gray-400 text-sm max-w-sm">
                        A plataforma de delivery que revoluciona o mercado com comissões justas e cashback configurável.
                    </p>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="font-semibold mb-3 text-sm">Plataforma</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="/sobre" class="hover:text-orange-400 transition">Sobre Nós</a></li>
                        <li><a href="/como-funciona" class="hover:text-orange-400 transition">Como Funciona</a></li>
                        <li><a href="/parceiros" class="hover:text-orange-400 transition">Seja Parceiro</a></li>
                    </ul>
                </div>

                <!-- Contato -->
                <div>
                    <h4 class="font-semibold mb-3 text-sm">Suporte</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="/ajuda" class="hover:text-orange-400 transition">Central de Ajuda</a></li>
                        <li><a href="/contato" class="hover:text-orange-400 transition">Contato</a></li>
                        <li><a href="/admin" class="hover:text-orange-400 transition">Painel Admin</a></li>
                    </ul>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-700 pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-gray-400">
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
                            Cashback Configurável
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Dados Isolados
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
