@extends('tenant.layouts.app')

@section('title', 'Acompanhar Pedido')

@section('content')
<div x-data="trackingApp()" x-init="init('{{ $orderId }}')" class="bg-gray-50 min-h-screen pb-24">
    <!-- Header Clean -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-2xl mx-auto px-4 py-5">
            <div class="flex items-center gap-3">
                <a href="/meus-pedidos" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-xl font-semibold text-gray-900">Acompanhar Pedido</h1>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-6">
        <!-- Loading -->
        <div x-show="loading" class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <div class="animate-spin w-12 h-12 border-3 border-gray-300 border-t-gray-900 rounded-full mx-auto mb-4"></div>
            <p class="text-sm text-gray-500">Carregando...</p>
        </div>

        <!-- Erro -->
        <div x-show="error" class="bg-white rounded-lg border border-gray-200 p-8 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Erro ao carregar</h2>
            <p class="text-sm text-gray-500 mb-6" x-text="error"></p>
            <a href="/meus-pedidos" class="inline-block px-6 py-2.5 bg-gray-900 text-white rounded-lg font-medium text-sm hover:bg-gray-800 transition">
                Meus Pedidos
            </a>
        </div>

        <!-- Conteúdo -->
        <div x-show="!loading && !error" class="space-y-4">
            <!-- Header do Pedido -->
            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Pedido</p>
                        <p class="text-lg font-semibold text-gray-900" x-text="'#' + order.order_number"></p>
                        <p class="text-xs text-gray-500 mt-1" x-text="formatDate(order.created_at)"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-gray-900" x-text="'R$ ' + parseFloat(order.total).toFixed(2).replace('.', ',')"></p>
                        <span :class="getStatusClass(order.status)" class="inline-block px-2.5 py-1 rounded-full text-xs font-medium mt-2" x-text="getStatusLabel(order.status)"></span>
                    </div>
                </div>
            </div>

            <!-- Timeline de Status -->
            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-900 mb-5">Status</h2>

                <div class="space-y-4">
                    <!-- Pendente -->
                    <div class="flex items-start gap-3">
                        <div :class="isStatusActive('pending') ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0">
                            <span x-show="isStatusActive('pending')">✓</span>
                            <span x-show="!isStatusActive('pending')">1</span>
                        </div>
                        <div class="flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900">Pedido Recebido</p>
                            <p class="text-xs text-gray-500">Aguardando confirmação</p>
                        </div>
                    </div>

                    <div :class="isStatusActive('pending') ? 'bg-gray-900' : 'bg-gray-200'" class="w-0.5 h-6 ml-3.5"></div>

                    <!-- Confirmado -->
                    <div class="flex items-start gap-3">
                        <div :class="isStatusActive('confirmed') || isStatusActive('preparing') || isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0">
                            <span x-show="isStatusActive('confirmed') || isStatusActive('preparing') || isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered')">✓</span>
                            <span x-show="!isStatusActive('confirmed') && !isStatusActive('preparing') && !isStatusActive('ready') && !isStatusActive('delivering') && !isStatusActive('delivered')">2</span>
                        </div>
                        <div class="flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900">Confirmado</p>
                            <p class="text-xs text-gray-500">Enviado para cozinha</p>
                        </div>
                    </div>

                    <div :class="isStatusActive('preparing') || isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-gray-900' : 'bg-gray-200'" class="w-0.5 h-6 ml-3.5"></div>

                    <!-- Preparando -->
                    <div class="flex items-start gap-3">
                        <div :class="isStatusActive('preparing') || isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0">
                            <span x-show="isStatusActive('preparing') || isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered')">✓</span>
                            <span x-show="!isStatusActive('preparing') && !isStatusActive('ready') && !isStatusActive('delivering') && !isStatusActive('delivered')">3</span>
                        </div>
                        <div class="flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900">Preparando</p>
                            <p class="text-xs text-gray-500">Em produção</p>
                        </div>
                    </div>

                    <div :class="isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-gray-900' : 'bg-gray-200'" class="w-0.5 h-6 ml-3.5"></div>

                    <!-- Pronto -->
                    <div class="flex items-start gap-3">
                        <div :class="isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0">
                            <span x-show="isStatusActive('ready') || isStatusActive('delivering') || isStatusActive('delivered')">✓</span>
                            <span x-show="!isStatusActive('ready') && !isStatusActive('delivering') && !isStatusActive('delivered')">4</span>
                        </div>
                        <div class="flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900">Pronto</p>
                            <p class="text-xs text-gray-500">Saindo para entrega</p>
                        </div>
                    </div>

                    <div :class="isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-gray-900' : 'bg-gray-200'" class="w-0.5 h-6 ml-3.5"></div>

                    <!-- Saiu para Entrega -->
                    <div class="flex items-start gap-3">
                        <div :class="isStatusActive('delivering') || isStatusActive('delivered') ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0">
                            <span x-show="isStatusActive('delivering') || isStatusActive('delivered')">✓</span>
                            <span x-show="!isStatusActive('delivering') && !isStatusActive('delivered')">5</span>
                        </div>
                        <div class="flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900">Saiu para Entrega</p>
                            <p class="text-xs text-gray-500">A caminho</p>
                        </div>
                    </div>

                    <div :class="isStatusActive('delivered') ? 'bg-green-500' : 'bg-gray-200'" class="w-0.5 h-6 ml-3.5"></div>

                    <!-- Entregue -->
                    <div class="flex items-start gap-3">
                        <div :class="isStatusActive('delivered') ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500'" class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0">
                            <span x-show="isStatusActive('delivered')">✓</span>
                            <span x-show="!isStatusActive('delivered')">6</span>
                        </div>
                        <div class="flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900">Entregue</p>
                            <p class="text-xs text-gray-500">Pedido concluído</p>
                        </div>
                    </div>
                </div>

                <!-- Atualização Automática -->
                <div class="mt-5 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-center gap-2">
                        <div class="animate-pulse">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <p class="text-xs text-gray-600">Atualiza a cada 10 segundos</p>
                    </div>
                </div>
            </div>

            <!-- Items do Pedido -->
            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Itens</h2>
                <div class="space-y-3">
                    <template x-for="item in order.items" :key="item.id">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-sm text-gray-900">
                                    <span class="font-medium" x-text="item.quantity + 'x'"></span>
                                    <span x-text="item.product_name"></span>
                                </p>
                                <p x-show="item.notes" class="text-xs text-gray-500 mt-0.5" x-text="item.notes"></p>
                            </div>
                            <p class="text-sm font-medium text-gray-900 ml-3" x-text="'R$ ' + parseFloat(item.subtotal).toFixed(2).replace('.', ',')"></p>
                        </div>
                    </template>
                </div>

                <div class="border-t border-gray-200 mt-4 pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium text-gray-900" x-text="'R$ ' + parseFloat(order.subtotal).toFixed(2).replace('.', ',')"></span>
                    </div>
                    <div x-show="order.delivery_fee > 0" class="flex justify-between text-sm">
                        <span class="text-gray-600">Entrega</span>
                        <span class="font-medium text-gray-900" x-text="'R$ ' + parseFloat(order.delivery_fee).toFixed(2).replace('.', ',')"></span>
                    </div>
                    <div x-show="order.cashback_used > 0" class="flex justify-between text-sm text-green-600">
                        <span>Cashback</span>
                        <span class="font-medium" x-text="'- R$ ' + parseFloat(order.cashback_used).toFixed(2).replace('.', ',')"></span>
                    </div>
                    <div class="flex justify-between text-base font-bold border-t border-gray-200 pt-3 mt-3">
                        <span class="text-gray-900">Total</span>
                        <span class="text-gray-900" x-text="'R$ ' + parseFloat(order.total).toFixed(2).replace('.', ',')"></span>
                    </div>
                </div>
            </div>

            <!-- Cashback Ganho -->
            <div x-show="order.cashback_earned > 0" class="bg-green-50 border border-green-200 rounded-lg p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-green-700">Cashback ganho</p>
                        <p class="text-lg font-bold text-green-900" x-text="'R$ ' + parseFloat(order.cashback_earned).toFixed(2).replace('.', ',')"></p>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex gap-3">
                <a href="/meus-pedidos" class="flex-1 px-4 py-2.5 bg-gray-50 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-100 transition text-center border border-gray-200">
                    Meus Pedidos
                </a>
                <a href="/" class="flex-1 px-4 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition text-center">
                    Novo Pedido
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

                // Garantir que items existe (mesmo vazio)
                if (!data.items) {
                    data.items = [];
                }

                this.order = data;
                this.error = null; // Limpa erro anterior
            } catch (error) {
                console.error('Erro ao carregar pedido:', error);
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
            return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' às ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        },

        getStatusLabel(status) {
            const labels = {
                'pending': 'Pendente',
                'confirmed': 'Confirmado',
                'preparing': 'Preparando',
                'ready': 'Pronto',
                'delivering': 'Saiu para Entrega',
                'delivered': 'Entregue',
                'canceled': 'Cancelado'
            };
            return labels[status] || status;
        },

        getStatusClass(status) {
            const classes = {
                'pending': 'bg-yellow-100 text-yellow-700',
                'confirmed': 'bg-blue-100 text-blue-700',
                'preparing': 'bg-purple-100 text-purple-700',
                'ready': 'bg-green-100 text-green-700',
                'delivering': 'bg-indigo-100 text-indigo-700',
                'delivered': 'bg-gray-100 text-gray-700',
                'canceled': 'bg-red-100 text-red-700'
            };
            return classes[status] || 'bg-gray-100 text-gray-700';
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
@endsection
