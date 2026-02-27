@extends('tenant.layouts.app')

@section('title', 'Meus Pedidos')

@section('content')
<div x-data="ordersApp()" x-init="init()" class="bg-gray-50 min-h-screen pb-24">
    <!-- Header Clean -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-2xl mx-auto px-4 py-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="/" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h1 class="text-xl font-semibold text-gray-900">Meus Pedidos</h1>
                </div>
                <a href="/perfil" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-6">
        <!-- Verificar Login -->
        <div x-show="!isAuthenticated && !loading" class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Login Necessário</h2>
            <p class="text-sm text-gray-500 mb-6">Faça login para ver seus pedidos</p>
            <a href="/" class="inline-block px-6 py-2.5 bg-gray-900 text-white rounded-lg font-medium text-sm hover:bg-gray-800 transition">
                Fazer Login
            </a>
        </div>

        <!-- Loading -->
        <div x-show="loading && isAuthenticated" class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <div class="animate-spin w-12 h-12 border-3 border-gray-300 border-t-gray-900 rounded-full mx-auto mb-4"></div>
            <p class="text-sm text-gray-500">Carregando pedidos...</p>
        </div>

        <!-- Lista de Pedidos -->
        <div x-show="!loading && isAuthenticated">
            <!-- Filtros Clean -->
            <div class="flex gap-2 mb-4 overflow-x-auto pb-2">
                <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200'" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition">
                    Todos
                </button>
                <button @click="filter = 'pending'" :class="filter === 'pending' ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200'" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition">
                    Pendentes
                </button>
                <button @click="filter = 'confirmed'" :class="filter === 'confirmed' ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200'" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition">
                    Confirmados
                </button>
                <button @click="filter = 'delivered'" :class="filter === 'delivered' ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200'" class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition">
                    Entregues
                </button>
            </div>

            <!-- Vazio -->
            <div x-show="filteredOrders.length === 0" class="bg-white rounded-lg border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Nenhum pedido</h2>
                <p class="text-sm text-gray-500 mb-6">Você ainda não fez nenhum pedido</p>
                <a href="/" class="inline-block px-6 py-2.5 bg-gray-900 text-white rounded-lg font-medium text-sm hover:bg-gray-800 transition">
                    Ver Cardápio
                </a>
            </div>

            <!-- Pedidos -->
            <div class="space-y-4">
                <template x-for="order in filteredOrders" :key="order.id">
                    <div class="bg-white rounded-lg border border-gray-200 p-5 hover:border-gray-300 transition">
                        <!-- Header do Pedido -->
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

                        <!-- Items -->
                        <div class="border-t border-gray-100 pt-3 mb-3 space-y-2">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">
                                        <span class="font-medium" x-text="item.quantity + 'x'"></span>
                                        <span x-text="item.product_name"></span>
                                    </span>
                                    <span class="font-medium text-gray-900" x-text="'R$ ' + parseFloat(item.subtotal).toFixed(2).replace('.', ',')"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Ações -->
                        <div class="space-y-2">
                            <!-- Aviso se não pode pagar -->
                            <div x-show="order.payment_status === 'pending' && !order.can_pay" class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <div class="flex gap-2">
                                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-medium text-yellow-800" x-text="order.payment_blocked_reason"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a :href="'/pedido/' + order.order_number + '/acompanhar'" class="flex-1 px-4 py-2.5 bg-gray-50 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-100 transition text-center">
                                    Acompanhar
                                </a>
                                <!-- Botão Pagar: só mostra se can_pay === true -->
                                <button x-show="order.can_pay" @click="viewPayment(order.order_number)"
                                        class="flex-1 px-4 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                                    Pagar Agora
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function ordersApp() {
    return {
        orders: [],
        loading: true,
        isAuthenticated: false,
        filter: 'all',

        get filteredOrders() {
            if (this.filter === 'all') return this.orders;
            return this.orders.filter(order => order.status === this.filter);
        },

        async init() {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                this.loading = false;
                this.isAuthenticated = false;
                return;
            }

            this.isAuthenticated = true;

            try {
                const response = await fetch('/api/v1/orders', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.orders = data.data || [];
                } else if (response.status === 401) {
                    this.isAuthenticated = false;
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('customer');
                }
            } catch (error) {
                console.error(error);
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
                'delivering': 'Saiu para entrega',
                'delivered': 'Entregue',
                'cancelled': 'Cancelado'
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
                'cancelled': 'bg-red-100 text-red-700'
            };
            return classes[status] || 'bg-gray-100 text-gray-700';
        },

        viewPayment(orderNumber) {
            window.location.href = '/pedido/' + orderNumber + '/pagamento';
        }
    }
}
</script>
@endsection
