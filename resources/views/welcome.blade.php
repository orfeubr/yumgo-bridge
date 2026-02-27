<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YumGo - Sistema de Delivery Multi-Tenant</title>
    <meta name="description" content="A melhor plataforma de delivery para restaurantes. Comissão baixa de 1-3%, cashback configurável e isolamento total de dados.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); }
        .gradient-text { background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
        .float-animation { animation: float 3s ease-in-out infinite; }
    </style>
</head>
<body class="antialiased bg-gray-50">

    <!-- Header/Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="text-2xl font-bold gradient-text">YumGo</span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-700 hover:text-orange-600 transition">Recursos</a>
                    <a href="#pricing" class="text-gray-700 hover:text-orange-600 transition">Planos</a>
                    <a href="#comparison" class="text-gray-700 hover:text-orange-600 transition">Comparação</a>
                    <a href="/admin/login" class="bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700 transition">
                        Área Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                        O Sistema de Delivery que o iFood não quer que você conheça
                    </h1>
                    <p class="text-xl mb-8 text-purple-100">
                        Comissão de apenas <span class="font-bold text-yellow-300">1-3%</span> (vs 30% do iFood).
                        Cashback configurável. Seu restaurante, suas regras.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#pricing" class="bg-white text-orange-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition text-center">
                            Ver Planos
                        </a>
                        <a href="#comparison" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-orange-600 transition text-center">
                            Por que somos melhores?
                        </a>
                    </div>
                    <div class="mt-8 flex items-center space-x-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold">97%</div>
                            <div class="text-sm text-orange-100">Fica com você</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold">R$ 0,99</div>
                            <div class="text-sm text-orange-100">Por PIX</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold">100%</div>
                            <div class="text-sm text-orange-100">Seus dados</div>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block float-animation">
                    <div class="text-9xl text-center">🍕</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Por que YumGo?</h2>
                <p class="text-xl text-gray-600">Tecnologia de ponta com economia real</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-lg card-hover border border-gray-100">
                    <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <span class="text-3xl">💰</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Comissão Justa</h3>
                    <p class="text-gray-600 mb-4">Apenas 1-3% de comissão vs 30% do iFood. Mais dinheiro no seu bolso!</p>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-sm text-gray-600">Economia em 1000 pedidos/mês:</div>
                        <div class="text-2xl font-bold text-green-600">R$ 1.500+</div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg card-hover border border-gray-100">
                    <div class="w-14 h-14 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <span class="text-3xl">🎁</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Cashback Configurável</h3>
                    <p class="text-gray-600 mb-4">Você define as regras! Bronze, Prata, Ouro, Platina. Bônus de aniversário e muito mais.</p>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg card-hover border border-gray-100">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <span class="text-3xl">🔒</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Isolamento Total</h3>
                    <p class="text-gray-600 mb-4">PostgreSQL com schemas separados. Seus dados nunca se misturam com outros restaurantes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="gradient-bg py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                Pronto para Economizar Milhares de Reais?
            </h2>
            <p class="text-xl mb-8 text-purple-100">
                Junte-se aos restaurantes que já economizam com YumGo
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/admin/login" class="bg-white text-purple-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition text-lg">
                    Começar Agora
                </a>
            </div>
            <p class="mt-6 text-purple-200 text-sm">
                ✨ Sistema funcionando • 🚀 Acesse o painel admin
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-2 mb-4">
                    <span class="text-3xl">🍕</span>
                    <span class="text-xl font-bold text-white">YumGo</span>
                </div>
                <p class="text-sm mb-4">
                    A plataforma de delivery que coloca mais dinheiro no seu bolso.
                </p>
                <p class="text-sm">
                    &copy; 2026 YumGo. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
