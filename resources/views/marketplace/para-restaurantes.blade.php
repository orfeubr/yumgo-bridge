<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seja Parceiro YumGo | Comissão de 1% a 3% • Teste Grátis por 15 Dias</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Header Fixo -->
    <header class="bg-white shadow-sm sticky top-0 z-50 border-b border-gray-200">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center space-x-2">
                    <div class="bg-red-600 text-white px-3 py-2 rounded-lg font-bold text-xl">
                        YumGo
                    </div>
                </a>

                <nav class="hidden md:flex items-center space-x-6">
                    <a href="#vantagens" class="text-gray-600 hover:text-red-600 transition">Vantagens</a>
                    <a href="#planos" class="text-gray-600 hover:text-red-600 transition">Planos</a>
                    <a href="#depoimentos" class="text-gray-600 hover:text-red-600 transition">Depoimentos</a>
                    <a href="#como-funciona" class="text-gray-600 hover:text-red-600 transition">Como Funciona</a>
                    <a href="/admin" class="text-gray-600 hover:text-red-600 transition">Já Sou Parceiro</a>
                    <a href="/cadastro" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                        Comece Agora
                    </a>
                </nav>

                <!-- Mobile Menu Button -->
                <button class="md:hidden text-gray-600">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-red-50 to-orange-50 py-16 md:py-24 overflow-hidden">
        <!-- Badge Promocional Flutuante -->
        <div class="absolute top-8 right-8 bg-yellow-400 text-gray-900 px-6 py-3 rounded-full font-bold shadow-lg transform rotate-12 float-animation hidden md:block">
            <i class="fas fa-gift mr-2"></i>
            15 DIAS GRÁTIS!
        </div>

        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- Conteúdo -->
                <div>
                    <div class="inline-block bg-red-600 text-white px-4 py-1 rounded-full text-sm font-semibold mb-4">
                        🔥 COMISSÃO MAIS BAIXA DO MERCADO
                    </div>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        Aumente seu faturamento pagando até <span class="text-red-600">27x menos</span>
                    </h1>

                    <p class="text-xl text-gray-700 mb-8">
                        Enquanto outras plataformas cobram até <span class="font-bold text-red-600">30%</span>, no YumGo você paga apenas <span class="font-bold text-green-600">1% a 3%</span> por pedido.
                    </p>

                    <!-- Stats Rápidos -->
                    <div class="grid grid-cols-3 gap-4 mb-8">
                        <div class="bg-white rounded-lg p-4 shadow-md text-center">
                            <div class="text-3xl font-bold text-red-600">1-3%</div>
                            <div class="text-sm text-gray-600">comissão</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-md text-center">
                            <div class="text-3xl font-bold text-green-600">+50%</div>
                            <div class="text-sm text-gray-600">faturamento</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-md text-center">
                            <div class="text-3xl font-bold text-blue-600">15 dias</div>
                            <div class="text-sm text-gray-600">grátis</div>
                        </div>
                    </div>

                    <!-- CTAs -->
                    <div class="flex flex-wrap gap-4">
                        <a href="/cadastro" class="px-8 py-4 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold text-lg shadow-lg hover:shadow-xl transition inline-flex items-center">
                            Começar Teste Grátis
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <a href="#calculadora" class="px-8 py-4 bg-white border-2 border-red-600 text-red-600 rounded-lg hover:bg-red-50 font-bold text-lg transition inline-flex items-center">
                            <i class="fas fa-calculator mr-2"></i>
                            Calcular Economia
                        </a>
                    </div>

                    <p class="mt-4 text-sm text-gray-500">
                        <i class="fas fa-check-circle text-green-500 mr-1"></i> Sem cartão de crédito
                        <i class="fas fa-check-circle text-green-500 ml-4 mr-1"></i> Cancele quando quiser
                    </p>
                </div>

                <!-- Imagem/Visual -->
                <div class="hidden md:block">
                    <div class="relative">
                        <div class="bg-white rounded-2xl shadow-2xl p-8 transform hover:scale-105 transition duration-300">
                            <div class="text-center mb-6">
                                <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 rounded-full mb-4">
                                    <i class="fas fa-store text-5xl text-red-600"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Comece em 5 minutos</h3>
                                <p class="text-gray-600">Sem burocracia, 100% online</p>
                            </div>

                            <!-- Formulário Quick -->
                            <form action="/cadastro" method="GET" class="space-y-4">
                                <input type="email" placeholder="Seu melhor e-mail" required
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <button type="submit"
                                        class="w-full px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold transition">
                                    Criar Minha Conta Grátis
                                </button>
                            </form>

                            <p class="text-xs text-gray-500 text-center mt-4">
                                Ao continuar, você concorda com nossos <a href="#" class="underline">Termos de Uso</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Estatísticas -->
    <section class="py-12 bg-gray-900 text-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl md:text-5xl font-bold mb-2">R$ 27k</div>
                    <div class="text-gray-400">economia média/mês</div>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-bold mb-2">+150</div>
                    <div class="text-gray-400">restaurantes parceiros</div>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-bold mb-2">+10k</div>
                    <div class="text-gray-400">pedidos entregues</div>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-bold mb-2">98%</div>
                    <div class="text-gray-400">satisfação</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Calculadora de Economia -->
    <section id="calculadora" class="py-20 bg-white" x-data="calculator()">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold mb-4">Quanto você vai economizar?</h2>
                    <p class="text-xl text-gray-600">Descubra em 30 segundos</p>
                </div>

                <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-2xl shadow-xl p-8">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-lg font-semibold mb-3">Qual seu faturamento mensal em delivery?</label>
                            <input type="range" min="5000" max="200000" step="5000"
                                   x-model="revenue"
                                   class="w-full h-3 bg-gray-300 rounded-lg appearance-none cursor-pointer">
                            <div class="text-center mt-2">
                                <span class="text-3xl font-bold text-red-600">
                                    R$ <span x-text="formatMoney(revenue)"></span>
                                </span>
                                <span class="text-gray-600">/mês</span>
                            </div>
                        </div>

                        <!-- Comparação -->
                        <div class="grid md:grid-cols-2 gap-6 mt-8">
                            <!-- Outras Plataformas -->
                            <div class="bg-white rounded-xl p-6 border-2 border-red-300">
                                <div class="text-sm font-semibold text-gray-500 mb-2">Outras Plataformas</div>
                                <div class="text-2xl font-bold text-red-600 mb-4">
                                    - R$ <span x-text="formatMoney(competitorCost)"></span>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-times text-red-500 mr-2"></i> Comissão de 30%
                                </div>
                            </div>

                            <!-- YumGo -->
                            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white relative overflow-hidden">
                                <div class="absolute top-0 right-0 bg-yellow-400 text-gray-900 px-3 py-1 rounded-bl-lg text-xs font-bold">
                                    MELHOR ESCOLHA
                                </div>
                                <div class="text-sm font-semibold text-green-100 mb-2">YumGo</div>
                                <div class="text-2xl font-bold mb-4">
                                    - R$ <span x-text="formatMoney(yumgoCost)"></span>
                                </div>
                                <div class="text-sm">
                                    <i class="fas fa-check mr-2"></i> Comissão de 3%
                                </div>
                            </div>
                        </div>

                        <!-- Resultado -->
                        <div class="bg-green-600 text-white rounded-xl p-6 text-center">
                            <div class="text-sm font-semibold mb-2">Você economiza por mês:</div>
                            <div class="text-5xl font-bold mb-2">
                                R$ <span x-text="formatMoney(savings)"></span>
                            </div>
                            <div class="text-green-100">
                                Isso é <span x-text="formatMoney(savings * 12)"></span> por ano! 🎉
                            </div>
                        </div>

                        <div class="text-center">
                            <a href="/cadastro" class="inline-block px-8 py-4 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold text-lg shadow-lg">
                                Começar a Economizar Agora
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vantagens -->
    <section id="vantagens" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Por que restaurantes estão migrando para o YumGo?</h2>
                <p class="text-xl text-gray-600">Menos custos, mais lucro, total controle</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transition text-center">
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-percent text-4xl text-red-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Comissão Justa</h3>
                    <p class="text-gray-600 mb-4">De 1% a 3% por pedido. Compare com os 30% da concorrência!</p>
                    <div class="text-3xl font-bold text-red-600">27x menos</div>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transition text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-4xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Aumente Vendas</h3>
                    <p class="text-gray-600 mb-4">Nossos parceiros aumentam faturamento em até 50% no primeiro mês</p>
                    <div class="text-3xl font-bold text-green-600">+50%</div>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transition text-center">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-gift text-4xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Cashback Automático</h3>
                    <p class="text-gray-600 mb-4">Fideliza clientes com cashback configurável por você</p>
                    <div class="text-3xl font-bold text-blue-600">100%</div>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transition text-center">
                    <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-database text-4xl text-purple-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Seus Dados, Suas Regras</h3>
                    <p class="text-gray-600 mb-4">Acesso total aos dados dos clientes. Sem segredos.</p>
                    <div class="text-3xl font-bold text-purple-600">Total</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Depoimentos -->
    <section id="depoimentos" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Quem já faz parte conta:</h2>
                <p class="text-xl text-gray-600">Histórias reais de crescimento</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Depoimento 1 -->
                <div class="bg-gray-50 rounded-xl p-8 hover:shadow-xl transition">
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-red-600 rounded-full flex items-center justify-center text-white font-bold text-xl mr-4">
                            PM
                        </div>
                        <div>
                            <div class="font-bold text-lg">Pizza Master</div>
                            <div class="text-sm text-gray-600">São Paulo, SP</div>
                        </div>
                    </div>
                    <div class="text-yellow-400 mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-700 italic mb-4">
                        "Saímos da concorrência e economizamos R$ 15 mil por mês! Com o YumGo, finalmente conseguimos crescer sem perder tudo em comissão."
                    </p>
                    <div class="text-sm font-semibold text-red-600">
                        <i class="fas fa-chart-line mr-1"></i> +120 pedidos/dia
                    </div>
                </div>

                <!-- Depoimento 2 -->
                <div class="bg-gray-50 rounded-xl p-8 hover:shadow-xl transition">
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-xl mr-4">
                            MG
                        </div>
                        <div>
                            <div class="font-bold text-lg">Marmitex da Gi</div>
                            <div class="text-sm text-gray-600">Rio de Janeiro, RJ</div>
                        </div>
                    </div>
                    <div class="text-yellow-400 mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-700 italic mb-4">
                        "Em 3 meses consegui dobrar meu faturamento. O cashback faz os clientes voltarem sempre. Melhor decisão que tomei!"
                    </p>
                    <div class="text-sm font-semibold text-green-600">
                        <i class="fas fa-chart-line mr-1"></i> Faturamento 2x
                    </div>
                </div>

                <!-- Depoimento 3 -->
                <div class="bg-gray-50 rounded-xl p-8 hover:shadow-xl transition">
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xl mr-4">
                            BB
                        </div>
                        <div>
                            <div class="font-bold text-lg">Burguer Boss</div>
                            <div class="text-sm text-gray-600">Belo Horizonte, MG</div>
                        </div>
                    </div>
                    <div class="text-yellow-400 mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-700 italic mb-4">
                        "A diferença na comissão é absurda! Economizo mais de R$ 20 mil por mês. Consigo investir em marketing e crescer de verdade."
                    </p>
                    <div class="text-sm font-semibold text-blue-600">
                        <i class="fas fa-chart-line mr-1"></i> R$ 20k economia/mês
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Como Funciona -->
    <section id="como-funciona" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Comece em 3 passos simples</h2>
                <p class="text-xl text-gray-600">Você pode estar vendendo em menos de 24 horas</p>
            </div>

            <div class="grid md:grid-cols-3 gap-12 max-w-5xl mx-auto">
                <!-- Passo 1 -->
                <div class="text-center">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 bg-red-600 rounded-full flex items-center justify-center mx-auto">
                            <i class="fas fa-user-plus text-4xl text-white"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center font-bold text-xl">
                            1
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Cadastre-se</h3>
                    <p class="text-gray-600">
                        Preencha seus dados básicos e escolha seu plano. Leva apenas 5 minutos!
                    </p>
                </div>

                <!-- Passo 2 -->
                <div class="text-center">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 bg-green-600 rounded-full flex items-center justify-center mx-auto">
                            <i class="fas fa-utensils text-4xl text-white"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center font-bold text-xl">
                            2
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Configure seu Cardápio</h3>
                    <p class="text-gray-600">
                        Adicione produtos, fotos e preços pelo painel super simples.
                    </p>
                </div>

                <!-- Passo 3 -->
                <div class="text-center">
                    <div class="relative inline-block mb-6">
                        <div class="w-24 h-24 bg-blue-600 rounded-full flex items-center justify-center mx-auto">
                            <i class="fas fa-rocket text-4xl text-white"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center font-bold text-xl">
                            3
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Comece a Vender</h3>
                    <p class="text-gray-600">
                        Pronto! Seu restaurante já está no ar e pronto para receber pedidos.
                    </p>
                </div>
            </div>

            <div class="text-center mt-12">
                <a href="/cadastro" class="inline-block px-8 py-4 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold text-lg shadow-lg">
                    Começar Agora Grátis
                </a>
            </div>
        </div>
    </section>

    <!-- Planos -->
    <section id="planos" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Escolha o plano ideal para você</h2>
                <p class="text-xl text-gray-600">Todos com 15 dias de teste grátis!</p>
            </div>

            @if($plans->isEmpty())
                <div class="text-center py-12">
                    <p class="text-gray-600 text-lg">Em breve disponibilizaremos nossos planos!</p>
                </div>
            @else
                <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    @foreach($plans as $plan)
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border-2 {{ $plan->name === 'Pro' ? 'border-red-500 transform md:scale-105' : 'border-gray-200' }}">
                            @if($plan->name === 'Pro')
                                <div class="bg-gradient-to-r from-red-600 to-red-700 text-white text-center py-3 font-bold">
                                    ⭐ MAIS POPULAR
                                </div>
                            @endif

                            <div class="p-8">
                                <h3 class="text-2xl font-bold mb-2">{{ $plan->name }}</h3>
                                @if($plan->description)
                                    <p class="text-gray-600 mb-6">{{ $plan->description }}</p>
                                @endif

                                <div class="mb-6">
                                    <div class="text-5xl font-bold text-red-600 mb-1">
                                        R$ {{ number_format($plan->price_monthly, 0, ',', '.') }}
                                    </div>
                                    <div class="text-gray-600">por mês</div>
                                </div>

                                <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6 text-center">
                                    <div class="text-2xl font-bold">{{ $plan->commission_percentage }}%</div>
                                    <div class="text-sm">de comissão por pedido</div>
                                </div>

                                @if($plan->features && is_array($plan->features))
                                    <ul class="space-y-3 mb-8">
                                        @foreach($plan->features as $feature)
                                            <li class="flex items-start">
                                                <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                                                <span>{{ $feature }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                <a href="/cadastro" class="block w-full px-6 py-4 text-center {{ $plan->name === 'Pro' ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-800 hover:bg-gray-900' }} text-white rounded-lg font-bold transition text-lg">
                                    Começar Teste Grátis
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-20 bg-gray-50" x-data="{ open: null }">
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Perguntas Frequentes</h2>
                <p class="text-xl text-gray-600">Tudo que você precisa saber</p>
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50">
                        <span class="font-semibold text-lg">Como funciona o teste grátis?</span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open === 1 }"></i>
                    </button>
                    <div x-show="open === 1" x-collapse class="px-6 py-4 bg-gray-50">
                        <p class="text-gray-600">Você tem 15 dias de teste grátis em qualquer plano. Durante esse período, acesso total a todas as funcionalidades. Sem cobranças, sem cartão de crédito, sem pegadinhas!</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50">
                        <span class="font-semibold text-lg">Como vocês conseguem cobrar tão pouco?</span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open === 2 }"></i>
                    </button>
                    <div x-show="open === 2" x-collapse class="px-6 py-4 bg-gray-50">
                        <p class="text-gray-600">Não gastamos milhões em publicidade e mantemos uma estrutura enxuta. Nosso lucro vem do volume, não de margens absurdas. Você ganha, nós ganhamos!</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50">
                        <span class="font-semibold text-lg">Preciso de CNPJ?</span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open === 3 }"></i>
                    </button>
                    <div x-show="open === 3" x-collapse class="px-6 py-4 bg-gray-50">
                        <p class="text-gray-600">Sim, CNPJ ativo é necessário para receber pagamentos via Pagar.me. Mas você pode testar a plataforma antes de configurar os pagamentos!</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50">
                        <span class="font-semibold text-lg">Posso cancelar quando quiser?</span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open === 4 }"></i>
                    </button>
                    <div x-show="open === 4" x-collapse class="px-6 py-4 bg-gray-50">
                        <p class="text-gray-600">Claro! Sem fidelidade, sem multa, sem burocracia. Se não gostar, cancela com 1 clique no painel. Simples assim.</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button @click="open = open === 5 ? null : 5" class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50">
                        <span class="font-semibold text-lg">Vocês cobram taxa de setup?</span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open === 5 }"></i>
                    </button>
                    <div x-show="open === 5" x-collapse class="px-6 py-4 bg-gray-50">
                        <p class="text-gray-600">Não! Zero taxa de adesão, zero taxa de setup. Você paga apenas a mensalidade do plano + comissão por pedido. Transparência total.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-20 bg-gradient-to-r from-red-600 to-red-700 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                Pronto para economizar até R$ 27 mil por mês?
            </h2>
            <p class="text-xl mb-8 text-red-100 max-w-2xl mx-auto">
                Junte-se a centenas de restaurantes que já economizam milhares todos os meses com o YumGo!
            </p>
            <a href="/cadastro" class="inline-block px-10 py-5 bg-white text-red-600 rounded-lg font-bold text-xl hover:bg-gray-100 transition shadow-2xl">
                Começar Teste Grátis de 15 Dias
            </a>

            <p class="mt-6 text-red-100 text-sm">
                <i class="fas fa-shield-alt mr-2"></i> Sem cartão de crédito • Cancele quando quiser • Suporte dedicado
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h4 class="font-bold text-lg mb-4">YumGo</h4>
                    <p class="text-gray-400 text-sm">
                        A plataforma de delivery com comissão justa para restaurantes.
                    </p>
                </div>

                <div>
                    <h4 class="font-bold mb-4">Para Você</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="/" class="hover:text-white">Pedir Comida</a></li>
                        <li><a href="/ajuda" class="hover:text-white">Ajuda</a></li>
                        <li><a href="/contato" class="hover:text-white">Contato</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-4">Para Restaurantes</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="/cadastro" class="hover:text-white">Criar Conta</a></li>
                        <li><a href="#planos" class="hover:text-white">Planos e Preços</a></li>
                        <li><a href="/admin" class="hover:text-white">Acessar Painel</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-4">Redes Sociais</h4>
                    <div class="flex space-x-4 text-2xl">
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400">
                <p>&copy; 2026 YumGo. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        function calculator() {
            return {
                revenue: 50000,

                get competitorCost() {
                    return this.revenue * 0.30; // 30% comissão
                },

                get yumgoCost() {
                    return this.revenue * 0.03; // 3% comissão
                },

                get savings() {
                    return this.competitorCost - this.yumgoCost;
                },

                formatMoney(value) {
                    return parseFloat(value).toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                }
            }
        }
    </script>
</body>
</html>
