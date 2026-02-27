<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acompanhar Pedido - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <div x-data="trackingApp()" x-init="init({{ $orderId }})" class="min-h-screen">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white py-6 shadow-lg">
            <div class="max-w-2xl mx-auto px-4">
                <h1 class="text-3xl font-black">📍 Acompanhar Pedido</h1>
                <p class="text-orange-100">{{ $tenant->name }}</p>
            </div>
        </div>

        <div class="max-w-2xl mx-auto px-4 py-8">
            <!-- Loading -->
            <div x-show="loading" class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="animate-spin w-16 h-16 border-4 border-orange-500 border-t-transparent rounded-full mx-auto mb-4"></div>
                <p class="text-gray-600">Carregando informações do pedido...</p>
            </div>

            <!-- Erro -->
            <div x-show="error" class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="text-6xl mb-4">❌</div>
                <h2 class="text-2xl font-bold mb-2">Erro ao carregar pedido</h2>
                <p class="text-gray-600 mb-6" x-text="error"></p>
                <a href="/meus-pedidos" class="inline-block px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl font-bold hover:shadow-lg transition">
                    📦 Meus Pedidos
                </a>
            </div>

            <!-- Conteúdo -->
            <div x-show="!loading && !error" class="space-y-6">
                <!-- Header do Pedido -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm text-gray-600">Pedido</p>
                            <p class="text-3xl font-black text-gray-900" x-text="'#' + order.order_number"></p>
                            <p class="text-sm text-gray-500" x-text="formatDate(order.created_at)"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-black text-orange-600" x-text="'R$ ' + parseFloat(order.total).toFixed(2)"></p>
                            <span :class="getStatusColor(order.status)" class="inline-block px-4 py-2 rounded-full text-sm font-bold mt-2" x-text="getStatusLabel(order.status)"></span>
                        </div>
                    </div>
                </div>

                <!-- Timeline de Status -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-xl font-black mb-6">📊 Status do Pedido</h2>

                    <div class="space-y-4">
                        <!-- Pendente -->
                        <div class="flex items-start gap-4">
                            <div :class="isStatusActive('pending') ? 'bg-orange-500' : 'bg-gray-300'" class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                                <span x-show="isStatusActive('pending')">✓</span>
                                <span x-show="!isStatusActive('pending')">1</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-gray-900">Pedido Recebido</p>
                                <p class="text-sm text-gray-600">Aguardando confirmação de pagamento</p>
                            </div>
                        </div>

                        <div :class="isStatusActive('pending') ? 'bg-orange-300' : 'bg-gray-200'" class="w-1 h-8 ml-4"></div>

                        <!-- Confirmado -->
                        <div class="flex items-start gap-4">
                            <div :class="isStatusActive('confirmed') || isStatusActive('preparing') || isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-orange-500' : 'bg-gray-300'" class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                                <span x-show="isStatusActive('confirmed') || isStatusActive('preparing') || isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered')">✓</span>
                                <span x-show="!isStatusActive('confirmed') && !isStatusActive('preparing') && !isStatusActive('ready') && !isStatusActive('delivering') && !isStatusActive('delivered')">2</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-gray-900">Pagamento Confirmado</p>
                                <p class="text-sm text-gray-600">Pedido enviado para a cozinha</p>
                            </div>
                        </div>

                        <div :class="isStatusActive('preparing') || isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-orange-300' : 'bg-gray-200'" class="w-1 h-8 ml-4"></div>

                        <!-- Preparando -->
                        <div class="flex items-start gap-4">
                            <div :class="isStatusActive('preparing') || isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-orange-500' : 'bg-gray-300'" class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                                <span x-show="isStatusActive('preparing') || isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered')">✓</span>
                                <span x-show="!isStatusActive('preparing') && !isStatusActive('ready') && !isStatusActive('delivering') && !isStatusActive('delivered')">3</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-gray-900">Preparando</p>
                                <p class="text-sm text-gray-600">Seu pedido está sendo preparado</p>
                            </div>
                        </div>

                        <div :class="isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-orange-300' : 'bg-gray-200'" class="w-1 h-8 ml-4"></div>

                        <!-- Pronto -->
                        <div class="flex items-start gap-4">
                            <div :class="isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-orange-500' : 'bg-gray-300'" class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                                <span x-show="isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered')">✓</span>
                                <span x-show="!isStatusActive('ready') && !isStatusActive('delivering') && !isStatusActive('delivered')">4</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-gray-900">Pronto para Entrega</p>
                                <p class="text-sm text-gray-600">Pedido saindo para entrega</p>
                            </div>
                        </div>

                        <div :class="isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-orange-300' : 'bg-gray-200'" class="w-1 h-8 ml-4"></div>

                        <!-- Saiu para Entrega -->
                        <div class="flex items-start gap-4">
                            <div :class="isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-orange-500' : 'bg-gray-300'" class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                                <span x-show="isStatusActive('delivering') || isStatusActive('delivered')">✓</span>
                                <span x-show="!isStatusActive('delivering') && !isStatusActive('delivered')">5</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-gray-900">Saiu para Entrega</p>
                                <p class="text-sm text-gray-600">Entregador a caminho</p>
                            </div>
                        </div>

                        <div :class="isStatusActive('delivered') ? 'bg-green-300' : 'bg-gray-200'" class="w-1 h-8 ml-4"></div>

                        <!-- Entregue -->
                        <div class="flex items-start gap-4">
                            <div :class="isStatusActive('delivered') ? 'bg-green-500' : 'bg-gray-300'" class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                                <span x-show="isStatusActive('delivered')">✓</span>
                                <span x-show="!isStatusActive('delivered')">6</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-gray-900">Entregue</p>
                                <p class="text-sm text-gray-600">Pedido concluído!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Atualização Automática -->
                    <div class="mt-6 p-4 bg-blue-50 border-2 border-blue-200 rounded-xl">
                        <div class="flex items-center gap-2">
                            <div class="animate-pulse text-2xl">🔄</div>
                            <div>
                                <p class="font-bold text-blue-800">Atualização Automática</p>
                                <p class="text-sm text-blue-700">Atualizando a cada 10 segundos...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items do Pedido -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-xl font-black mb-4">🍕 Itens do Pedido</h2>
                    <div class="space-y-3">
                        <template x-for="item in order.items" :key="item.id">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900" x-text="item.quantity + 'x ' + item.product_name"></p>
                                    <p x-show="item.notes" class="text-sm text-gray-600" x-text="item.notes"></p>
                                </div>
                                <p class="font-black text-orange-600" x-text="'R$ ' + parseFloat(item.subtotal).toFixed(2)"></p>
                            </div>
                        </template>
                    </div>

                    <div class="border-t-2 border-gray-200 mt-4 pt-4">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-700">Subtotal</span>
                            <span class="font-bold" x-text="'R$ ' + parseFloat(order.subtotal).toFixed(2)"></span>
                        </div>
                        <div x-show="order.delivery_fee > 0" class="flex justify-between text-sm mb-2">
                            <span class="text-gray-700">Taxa de Entrega</span>
                            <span class="font-bold" x-text="'R$ ' + parseFloat(order.delivery_fee).toFixed(2)"></span>
                        </div>
                        <div x-show="order.cashback_used > 0" class="flex justify-between text-sm mb-2 text-green-600">
                            <span>Cashback Usado</span>
                            <span class="font-bold" x-text="'- R$ ' + parseFloat(order.cashback_used).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-lg font-black border-t-2 border-gray-200 pt-2 mt-2">
                            <span>Total</span>
                            <span class="text-orange-600" x-text="'R$ ' + parseFloat(order.total).toFixed(2)"></span>
                        </div>
                    </div>
                </div>

                <!-- Cashback Ganho -->
                <div x-show="order.cashback_earned > 0" class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center gap-4">
                        <div class="text-6xl">💰</div>
                        <div>
                            <p class="text-green-100">Cashback Ganho</p>
                            <p class="text-4xl font-black" x-text="'R$ ' + parseFloat(order.cashback_earned).toFixed(2)"></p>
                            <p class="text-green-100 mt-1">Use em suas próximas compras!</p>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex gap-3">
                    <a href="/meus-pedidos" class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-xl font-bold text-center transition">
                        📦 Meus Pedidos
                    </a>
                    <a href="/" class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl font-bold text-center hover:shadow-lg transition">
                        🍕 Fazer Novo Pedido
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function trackingApp() {
            return {
                order: {},
                loading: true,
                error: null,
                pollInterval: null,

                async init(orderId) {
                    await this.loadOrder(orderId);

                    // Atualizar a cada 10 segundos
                    if (!this.error) {
                        this.pollInterval = setInterval(() => {
                            this.loadOrder(orderId, false);
                        }, 10000);
                    }
                },

                async loadOrder(orderId, showLoading = true) {
                    if (showLoading) {
                        this.loading = true;
                    }

                    try {
                        const response = await fetch(`/api/v1/orders/${orderId}/track`);

                        if (!response.ok) {
                            throw new Error('Pedido não encontrado');
                        }

                        const data = await response.json();
                        this.order = data;
                    } catch (error) {
                        this.error = error.message;
                        if (this.pollInterval) {
                            clearInterval(this.pollInterval);
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('pt-BR') + ' às ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                },

                getStatusLabel(status) {
                    const labels = {
                        'pending': '⏳ Aguardando Pagamento',
                        'confirmed': '✅ Confirmado',
                        'preparing': '👨‍🍳 Preparando',
                        'ready': '📦 Pronto',
                        'delivering': '🚚 A Caminho',
                        'delivered': '✅ Entregue',
                        'cancelled': '❌ Cancelado',
                    };
                    return labels[status] || status;
                },

                getStatusColor(status) {
                    const colors = {
                        'pending': 'bg-yellow-100 text-yellow-800',
                        'confirmed': 'bg-blue-100 text-blue-800',
                        'preparing': 'bg-purple-100 text-purple-800',
                        'ready': 'bg-indigo-100 text-indigo-800',
                        'delivering': 'bg-orange-100 text-orange-800',
                        'delivered': 'bg-green-100 text-green-800',
                        'cancelled': 'bg-red-100 text-red-800',
                    };
                    return colors[status] || 'bg-gray-100 text-gray-800';
                },

                isStatusActive(checkStatus) {
                    const statusOrder = ['pending', 'confirmed', 'preparing', 'ready', 'delivering', 'delivered'];
                    const currentIndex = statusOrder.indexOf(this.order.status);
                    const checkIndex = statusOrder.indexOf(checkStatus);
                    return checkIndex <= currentIndex;
                }
            }
        }
    </script>
</body>
</html>
