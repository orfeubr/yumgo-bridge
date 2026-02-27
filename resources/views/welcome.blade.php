<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YumGo - Plataforma de Delivery para Restaurantes</title>
    <meta name="description" content="A melhor plataforma de delivery para restaurantes. Comissão baixa, cashback configurável e total controle sobre seus dados.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                    <a href="#features" class="hidden md:block text-gray-700 hover:text-primary transition text-sm font-medium">Recursos</a>
                    <a href="#pricing" class="hidden md:block text-gray-700 hover:text-primary transition text-sm font-medium">Planos</a>
                    <a href="/admin/login" class="btn-primary px-5 py-2 rounded-full text-sm font-medium">
                        Entrar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl lg:text-5xl xl:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        Delivery com comissão justa
                    </h1>
                    <p class="text-lg lg:text-xl text-gray-600 mb-8 leading-relaxed">
                        Plataforma completa de delivery para restaurantes. Pague apenas <span class="text-primary font-semibold">1-3% de comissão</span>, defina seu próprio programa de cashback e tenha total controle sobre seus dados.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 mb-10">
                        <a href="/admin/login" class="btn-primary px-8 py-4 rounded-full font-medium text-center shadow-sm">
                            Começar gratuitamente
                        </a>
                        <a href="#features" class="border-2 border-gray-300 text-gray-700 px-8 py-4 rounded-full font-medium hover:border-gray-400 transition text-center">
                            Saiba mais
                        </a>
                    </div>
                    <div class="grid grid-cols-3 gap-6 pt-6 border-t border-gray-200">
                        <div>
                            <div class="text-3xl font-bold text-gray-900">1-3%</div>
                            <div class="text-sm text-gray-600 mt-1">Comissão</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-gray-900">15 dias</div>
                            <div class="text-sm text-gray-600 mt-1">Trial grátis</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-gray-900">24/7</div>
                            <div class="text-sm text-gray-600 mt-1">Disponível</div>
                        </div>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <div class="bg-gray-50 rounded-2xl p-12 text-center">
                        <div class="text-9xl mb-4">📱</div>
                        <p class="text-gray-600">Interface moderna e intuitiva</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Recursos principais</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Tudo que você precisa para gerenciar seu delivery de forma profissional</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-xl border border-gray-200 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                        <span class="text-2xl">💰</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Comissão Baixa</h3>
                    <p class="text-gray-600 text-sm">Pague apenas 1-3% por pedido. Muito mais justo que os 30% cobrados por outras plataformas.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-xl border border-gray-200 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                        <span class="text-2xl">🎁</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Cashback Configurável</h3>
                    <p class="text-gray-600 text-sm">Crie seu próprio programa de fidelidade com níveis Bronze, Prata, Ouro e Platina.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-xl border border-gray-200 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                        <span class="text-2xl">📱</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Painel Completo</h3>
                    <p class="text-gray-600 text-sm">Gerencie produtos, pedidos, entregas e relatórios em um único lugar.</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-8 rounded-xl border border-gray-200 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                        <span class="text-2xl">💳</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Pagamentos Online</h3>
                    <p class="text-gray-600 text-sm">Aceite PIX e cartão de crédito com split automático via Pagar.me.</p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white p-8 rounded-xl border border-gray-200 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                        <span class="text-2xl">🔒</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Dados Isolados</h3>
                    <p class="text-gray-600 text-sm">PostgreSQL com schemas separados. Seus dados nunca se misturam.</p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white p-8 rounded-xl border border-gray-200 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                        <span class="text-2xl">📊</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Relatórios</h3>
                    <p class="text-gray-600 text-sm">Dashboards com gráficos em tempo real de vendas, pedidos e receita.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Planos simples e transparentes</h2>
                <p class="text-lg text-gray-600">Escolha o plano ideal para o seu restaurante</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Starter -->
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-8">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Starter</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900">R$ 79</span>
                        <span class="text-gray-600">/mês</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-6">+ 3% por pedido</p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-primary mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Até 500 pedidos/mês</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-primary mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Cashback configurável</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-primary mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Pagamentos online</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-primary mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Suporte por email</span>
                        </li>
                    </ul>
                    <a href="/admin/login" class="block w-full text-center border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-full font-medium hover:border-gray-400 transition">
                        Começar trial
                    </a>
                </div>

                <!-- Pro (Destacado) -->
                <div class="bg-primary border-2 border-primary rounded-2xl p-8 relative shadow-lg scale-105">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-white text-primary px-4 py-1 rounded-full text-xs font-semibold border border-primary">
                        Mais Popular
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">Pro</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-white">R$ 149</span>
                        <span class="text-red-100">/mês</span>
                    </div>
                    <p class="text-red-100 text-sm mb-6">+ 2% por pedido</p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-white mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-white">Até 2000 pedidos/mês</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-white mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-white">Tudo do Starter +</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-white mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-white">Emissão de NFC-e</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-white mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-white">Suporte prioritário</span>
                        </li>
                    </ul>
                    <a href="/admin/login" class="block w-full text-center bg-white text-primary px-6 py-3 rounded-full font-medium hover:bg-gray-50 transition">
                        Começar trial
                    </a>
                </div>

                <!-- Enterprise -->
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-8">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Enterprise</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900">R$ 299</span>
                        <span class="text-gray-600">/mês</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-6">+ 1% por pedido</p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-primary mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Pedidos ilimitados</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-primary mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Tudo do Pro +</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-primary mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Múltiplas lojas</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-primary mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Suporte 24/7</span>
                        </li>
                    </ul>
                    <a href="/admin/login" class="block w-full text-center border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-full font-medium hover:border-gray-400 transition">
                        Começar trial
                    </a>
                </div>
            </div>

            <p class="text-center text-gray-600 mt-12">
                Todos os planos incluem 15 dias de trial gratuito • Sem cartão de crédito
            </p>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                Comece gratuitamente hoje
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                15 dias de trial grátis. Sem cartão de crédito. Configure em minutos.
            </p>
            <a href="/admin/login" class="btn-primary px-8 py-4 rounded-full font-medium inline-block shadow-sm">
                Criar minha conta grátis
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-gray-200 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-3 mb-4">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span class="text-xl font-semibold text-gray-900">YumGo</span>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Plataforma de delivery para restaurantes
                </p>
                <p class="text-sm text-gray-500">
                    &copy; 2026 YumGo. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
