<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#EA1D2C',
                        secondary: '#717171',
                        accent: '#3E3E3E'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div x-data="orderConfirmedApp()" x-init="init()" x-cloak class="min-h-screen">

        <!-- Header Clean -->
        <header class="bg-white sticky top-0 z-50 shadow-md">
            <div class="max-w-2xl mx-auto px-4 py-4">
                <a href="/" class="text-gray-700 hover:text-primary text-sm mb-3 inline-flex items-center gap-2 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar ao cardápio
                </a>
                <div class="flex items-center gap-3 mt-2">
                    <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Pedido Confirmado</h1>
                        <p class="text-sm text-gray-600">{{ $tenant->name }}</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-2xl mx-auto px-4 py-6">

            <!-- Loading -->
            <div x-show="loading" class="bg-white rounded-xl shadow-sm p-12 text-center">
                <x-loading-spinner size="lg" />
                <p class="text-gray-600 text-sm mt-4">Carregando detalhes do pedido...</p>
            </div>

            <!-- Conteúdo -->
            <div x-show="!loading && order" class="space-y-4">

                <!-- Card de Sucesso -->
                <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Pedido Realizado!</h2>
                    <p class="text-gray-600 mb-1">Pedido <span class="font-bold text-green-600" x-text="'#' + orderNumber"></span></p>
                    <p class="text-sm text-gray-500 mb-6">Recebido com sucesso</p>

                    <!-- Tempo Estimado -->
                    <div class="inline-flex items-center gap-3 px-5 py-3 bg-green-50 border border-green-200 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-left">
                            <p class="text-xs text-green-700 font-medium">Tempo estimado</p>
                            <p class="text-base font-bold text-green-800">30-45 minutos</p>
                        </div>
                    </div>
                </div>

                <!-- Detalhes do Pedido -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h3 class="font-semibold text-gray-900 mb-4 text-sm flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Itens do Pedido
                    </h3>

                    <div class="space-y-3 mb-4">
                        <template x-for="item in order.items" :key="item.id">
                            <div class="flex justify-between items-start py-2 border-b border-gray-100 last:border-0">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 text-sm" x-text="item.quantity + 'x ' + item.product_name"></p>
                                    <p class="text-xs text-gray-500 mt-1" x-show="item.notes" x-text="item.notes"></p>
                                </div>
                                <p class="font-semibold text-gray-900 text-sm" x-text="'R$ ' + parseFloat(item.subtotal).toFixed(2).replace('.', ',')"></p>
                            </div>
                        </template>
                    </div>

                    <!-- Totais -->
                    <div class="space-y-2 pt-3 border-t border-gray-200">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span x-text="'R$ ' + parseFloat(order.subtotal || 0).toFixed(2).replace('.', ',')"></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Taxa de entrega</span>
                            <span x-text="'R$ ' + parseFloat(order.delivery_fee || 0).toFixed(2).replace('.', ',')"></span>
                        </div>
                        <div x-show="order.cashback_used > 0" class="flex justify-between text-sm text-green-600">
                            <span>Cashback usado</span>
                            <span x-text="'- R$ ' + parseFloat(order.cashback_used || 0).toFixed(2).replace('.', ',')"></span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t border-gray-200">
                            <span>Total</span>
                            <span class="text-primary" x-text="'R$ ' + parseFloat(order.total || 0).toFixed(2).replace('.', ',')"></span>
                        </div>
                    </div>
                </div>

                <!-- Endereço de Entrega -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h3 class="font-semibold text-gray-900 mb-3 text-sm flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Endereço de Entrega
                    </h3>
                    <p class="text-sm text-gray-700 mb-3" x-text="order.delivery_address"></p>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span x-text="formatPaymentMethod(order.payment_method)"></span>
                    </div>
                </div>

                <!-- Cashback Ganho -->
                <div x-show="order.cashback_earned > 0" class="bg-gradient-to-r from-amber-50 to-orange-50 border border-orange-200 rounded-xl p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-600 font-medium">Você ganhou</p>
                            <p class="text-xl font-bold text-orange-600" x-text="'R$ ' + parseFloat(order.cashback_earned || 0).toFixed(2).replace('.', ',')"></p>
                            <p class="text-xs text-gray-600">de cashback neste pedido!</p>
                        </div>
                    </div>
                </div>

                <!-- Status do Pedido -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h3 class="font-semibold text-gray-900 mb-4 text-sm flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Acompanhar Pedido
                    </h3>

                    <div class="space-y-3">
                        <!-- Passo 1 - Confirmado -->
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-green-700 text-sm">Pedido Confirmado</p>
                                <p class="text-xs text-gray-500">Recebemos seu pedido</p>
                            </div>
                        </div>

                        <!-- Passo 2 - Em Preparo -->
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm text-gray-400 font-semibold">2</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-400 text-sm">Em Preparo</p>
                                <p class="text-xs text-gray-400">Aguardando...</p>
                            </div>
                        </div>

                        <!-- Passo 3 - Saiu para Entrega -->
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm text-gray-400 font-semibold">3</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-400 text-sm">Saiu para Entrega</p>
                                <p class="text-xs text-gray-400">Aguardando...</p>
                            </div>
                        </div>

                        <!-- Passo 4 - Entregue -->
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm text-gray-400 font-semibold">4</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-400 text-sm">Pedido Entregue</p>
                                <p class="text-xs text-gray-400">Aguardando...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <a href="/" class="text-center px-4 py-3 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition text-sm">
                        Voltar ao Cardápio
                    </a>
                    <a href="/meus-pedidos" class="text-center px-4 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-red-700 transition text-sm">
                        Meus Pedidos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function orderConfirmedApp() {
        return {
            orderNumber: '{{ $orderNumber }}',
            loading: true,
            order: null,
            displayOrderNumber: '',

            async init() {
                await this.loadOrder();
            },

            async loadOrder() {
                try {
                    const token = localStorage.getItem('auth_token');

                    const response = await fetch(`/api/v1/orders/number/${this.orderNumber}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Erro ao carregar pedido');
                    }

                    this.order = data;
                    this.displayOrderNumber = data.order_number;
                    this.loading = false;

                } catch (error) {
                    console.error('Erro:', error);
                    this.loading = false;
                    alert('Erro ao carregar detalhes do pedido: ' + error.message);
                }
            },

            formatPaymentMethod(method) {
                const methods = {
                    'pix': 'PIX',
                    'credit_card': 'Cartão de Crédito',
                    'debit_card': 'Cartão de Débito',
                    'cash': 'Dinheiro'
                };
                return methods[method] || method;
            }
        }
    }
    </script>
</body>
</html>
