<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Planos e Preços - YumGo</title>
    <meta name="description" content="Escolha o plano ideal para o seu restaurante. A partir de R$ 79/mês com apenas 1-3% de comissão por pedido.">
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
                    <a href="/" class="flex items-center space-x-3">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span class="text-2xl font-semibold text-gray-900">YumGo</span>
                    </a>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="/" class="hidden md:block text-gray-700 hover:text-primary transition text-sm font-medium">Restaurantes</a>
                    <a href="/parceiro" class="hidden md:block text-gray-700 hover:text-primary transition text-sm font-medium">Seja Parceiro</a>
                    <a href="/admin/login" class="btn-primary px-5 py-2 rounded-full text-sm font-medium">
                        Entrar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="py-16 lg:py-20 bg-gradient-to-br from-red-50 to-orange-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                Planos simples e transparentes
            </h1>
            <p class="text-lg lg:text-xl text-gray-600 mb-6">
                Escolha o plano ideal para o seu restaurante. Muito mais justo que o iFood.
            </p>
            <div class="inline-flex items-center space-x-2 bg-white px-6 py-3 rounded-full border-2 border-primary">
                <span class="text-sm font-medium text-gray-900">✨ 15 dias de trial grátis</span>
                <span class="text-gray-400">•</span>
                <span class="text-sm text-gray-600">Sem cartão de crédito</span>
            </div>
        </div>
    </section>

    <!-- Comparação -->
    <section class="py-12 bg-white border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-2xl p-8 lg:p-12">
                <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-8 text-center">
                    Por que escolher o YumGo?
                </h2>
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- YumGo -->
                    <div class="bg-white rounded-xl p-6 border-2 border-primary">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900">YumGo</h3>
                            <span class="bg-primary text-white px-3 py-1 rounded-full text-xs font-semibold">Melhor escolha</span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-sm text-gray-700"><strong>1-3%</strong> de comissão por pedido</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Mensalidade a partir de <strong>R$ 79</strong></span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Cashback configurável para fidelizar clientes</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Seus dados são 100% seus</span>
                            </div>
                        </div>
                    </div>

                    <!-- Concorrente -->
                    <div class="bg-white rounded-xl p-6 border-2 border-gray-200 opacity-75">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Outras plataformas</h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Até <strong>30%</strong> de comissão</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Taxas adicionais escondidas</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Sem controle sobre cashback</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Dados dos clientes ficam com eles</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Planos -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Starter -->
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Starter</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900">R$ 79</span>
                        <span class="text-gray-600">/mês</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-6">+ 3% por pedido</p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Até 500 pedidos/mês</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Cashback configurável</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Pagamentos online (PIX + Cartão)</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Painel completo de gestão</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Suporte por email</span>
                        </li>
                    </ul>
                    <a href="/admin/login" class="block w-full text-center border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-full font-medium hover:border-gray-400 transition">
                        Começar trial grátis
                    </a>
                </div>

                <!-- Pro (Destacado) -->
                <div class="bg-primary border-2 border-primary rounded-2xl p-8 relative shadow-2xl scale-105">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-white text-primary px-4 py-1 rounded-full text-xs font-semibold border-2 border-primary">
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
                            <span class="text-sm text-white">Relatórios avançados</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-white mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-white">Suporte prioritário</span>
                        </li>
                    </ul>
                    <a href="/admin/login" class="block w-full text-center bg-white text-primary px-6 py-3 rounded-full font-medium hover:bg-gray-50 transition">
                        Começar trial grátis
                    </a>
                </div>

                <!-- Enterprise -->
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Enterprise</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900">R$ 299</span>
                        <span class="text-gray-600">/mês</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-6">+ 1% por pedido</p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Pedidos ilimitados</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Tudo do Pro +</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Múltiplas lojas</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">API personalizada</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Suporte 24/7</span>
                        </li>
                    </ul>
                    <a href="/admin/login" class="block w-full text-center border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-full font-medium hover:border-gray-400 transition">
                        Começar trial grátis
                    </a>
                </div>
            </div>

            <p class="text-center text-gray-600 mt-12">
                Todos os planos incluem 15 dias de trial gratuito • Sem cartão de crédito
            </p>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">Perguntas frequentes</h2>

            <div class="space-y-6">
                <details class="bg-white rounded-lg p-6 border border-gray-200">
                    <summary class="font-semibold text-gray-900 cursor-pointer">Como funciona o trial gratuito?</summary>
                    <p class="mt-3 text-gray-600">Você tem 15 dias para testar todos os recursos da plataforma sem compromisso. Não pedimos cartão de crédito no cadastro.</p>
                </details>

                <details class="bg-white rounded-lg p-6 border border-gray-200">
                    <summary class="font-semibold text-gray-900 cursor-pointer">Posso cancelar a qualquer momento?</summary>
                    <p class="mt-3 text-gray-600">Sim! Você pode cancelar sua assinatura a qualquer momento, sem multas ou taxas de cancelamento.</p>
                </details>

                <details class="bg-white rounded-lg p-6 border border-gray-200">
                    <summary class="font-semibold text-gray-900 cursor-pointer">Como funciona a comissão por pedido?</summary>
                    <p class="mt-3 text-gray-600">A comissão varia de 1% a 3% dependendo do plano escolhido. É descontada automaticamente de cada pedido pago. Muito mais justo que os 30% cobrados pelo iFood.</p>
                </details>

                <details class="bg-white rounded-lg p-6 border border-gray-200">
                    <summary class="font-semibold text-gray-900 cursor-pointer">Quais métodos de pagamento vocês aceitam?</summary>
                    <p class="mt-3 text-gray-600">Aceitamos PIX e cartão de crédito através do Pagar.me. As taxas do gateway são as menores do mercado: R$ 0,99 para PIX e 2,99% para cartão.</p>
                </details>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-20 bg-primary">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">
                Comece gratuitamente hoje
            </h2>
            <p class="text-lg text-red-100 mb-8">
                15 dias de trial grátis. Sem cartão de crédito. Configure em minutos.
            </p>
            <a href="/admin/login" class="bg-white text-primary px-8 py-4 rounded-full font-medium inline-block shadow-lg hover:bg-gray-50 transition">
                Criar minha conta grátis
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-gray-200 py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span class="text-xl font-semibold text-gray-900">YumGo</span>
                    </div>
                    <p class="text-sm text-gray-600 max-w-md">
                        A melhor plataforma de delivery para restaurantes. Comissão justa e total controle dos seus dados.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">Para você</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/" class="text-gray-600 hover:text-primary transition">Restaurantes</a></li>
                        <li><a href="/como-funciona" class="text-gray-600 hover:text-primary transition">Como funciona</a></li>
                        <li><a href="/cashback" class="text-gray-600 hover:text-primary transition">Cashback</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900 mb-4">Para restaurantes</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/parceiro" class="text-gray-600 hover:text-primary transition">Seja parceiro</a></li>
                        <li><a href="/planos" class="text-gray-600 hover:text-primary transition">Planos e preços</a></li>
                        <li><a href="/admin/login" class="text-gray-600 hover:text-primary transition">Área do parceiro</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-8 text-center">
                <p class="text-sm text-gray-500">
                    &copy; 2026 YumGo. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
