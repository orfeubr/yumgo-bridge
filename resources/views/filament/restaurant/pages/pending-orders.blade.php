<x-filament-panels::page>
    <style>
        [x-cloak] { display: none !important; }

        /* Animação de pulso para novos pedidos */
        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.1); opacity: 0; }
        }
        .pulse-ring {
            animation: pulse-ring 1.5s ease-out infinite;
        }
    </style>

    <div x-data="pendingOrdersApp()" x-init="init()">

        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-primary-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                            <span x-text="orders.length"></span> pedido(s) pendente(s)
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Atualizado: <span x-text="lastUpdate"></span>
                        </p>
                    </div>
                </div>

                <!-- Toggle Mostrar Todos -->
                <x-filament::button
                    x-on:click="showAll = !showAll; fetchOrders()"
                    ::color="showAll ? 'primary' : 'gray'"
                    size="sm"
                >
                    <span x-show="!showAll">Mostrar Todos</span>
                    <span x-show="showAll">Apenas Pendentes</span>
                </x-filament::button>
            </div>
        </div>

        <!-- Loading -->
        <div x-show="loading && orders.length === 0" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary-500 border-t-transparent"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-400">Carregando pedidos...</p>
        </div>

        <!-- Lista de Pedidos -->
        <div x-show="!loading || orders.length > 0" x-cloak class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <template x-for="order in orders" :key="order.id">
                <div
                    @click="openQrCode(order)"
                    :class="order.payment_status === 'pending' ? 'border-danger-300 bg-danger-50 dark:border-danger-700 dark:bg-danger-950' : 'border-success-300 bg-success-50 dark:border-success-700 dark:bg-success-950'"
                    class="relative border-2 rounded-xl p-4 cursor-pointer hover:shadow-lg transition-all duration-200 hover:scale-105">

                    <!-- Indicador de status -->
                    <div class="absolute top-4 right-4">
                        <div
                            :class="order.payment_status === 'pending' ? 'bg-danger-500' : 'bg-success-500'"
                            class="w-3 h-3 rounded-full">
                            <div x-show="order.payment_status === 'pending'" class="absolute inset-0 rounded-full bg-danger-500 pulse-ring"></div>
                        </div>
                    </div>

                    <!-- Número do Pedido -->
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-2xl font-black text-gray-900 dark:text-white" x-text="'#' + order.order_number"></span>
                        <span
                            x-show="order.table_number"
                            class="text-base font-semibold text-gray-600 dark:text-gray-400"
                            x-text="'Mesa ' + order.table_number"></span>
                        <span
                            x-show="order.service_type === 'counter'"
                            class="text-base font-semibold text-gray-600 dark:text-gray-400">Balcão</span>
                    </div>

                    <!-- Cliente e Total -->
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-base font-semibold text-gray-700 dark:text-gray-300" x-text="order.customer_name"></p>
                        <p class="text-xl font-black text-primary-600 dark:text-primary-400" x-text="'R$ ' + order.total.toFixed(2).replace('.', ',')"></p>
                    </div>

                    <!-- Método de Pagamento e Tempo -->
                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <span x-show="order.payment_method === 'pix'" class="font-semibold">💰 PIX</span>
                            <span x-show="order.payment_method !== 'pix'" class="font-semibold" x-text="order.payment_method.toUpperCase()"></span>
                            <span
                                :class="order.payment_status === 'pending' ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400'"
                                class="font-semibold"
                                x-text="order.payment_status === 'pending' ? '• PENDENTE' : '• PAGO'"></span>
                        </span>
                        <span x-text="order.created_at_human"></span>
                    </div>

                    <!-- Badge PIX -->
                    <div
                        x-show="order.payment_method === 'pix' && order.pix && order.pix.qrcode"
                        class="mt-3 bg-primary-500 text-white rounded-lg py-2 text-center font-bold text-sm">
                        📱 Clique para QR Code
                    </div>
                </div>
            </template>

            <!-- Vazio -->
            <div x-show="orders.length === 0 && !loading" x-cloak class="col-span-full text-center py-12">
                <div class="text-6xl mb-4">🎉</div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Nenhum pedido pendente!</h3>
                <p class="text-gray-600 dark:text-gray-400">Todos os pedidos foram pagos</p>
            </div>
        </div>

        <!-- Modal QR Code (Tela Cheia) -->
        <div
            x-show="showModal"
            x-cloak
            @click.self="showModal = false"
            class="fixed inset-0 z-50 bg-black bg-opacity-90 flex items-center justify-center p-4">

            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 text-center relative">
                <!-- Fechar -->
                <button
                    @click="showModal = false"
                    class="absolute top-4 right-4 w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Pedido Info -->
                <div class="mb-4">
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white" x-text="'Pedido #' + selectedOrder?.order_number"></h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400" x-text="selectedOrder?.customer_name"></p>
                    <p class="text-3xl font-black text-primary-600 dark:text-primary-400 mt-2" x-text="'R$ ' + selectedOrder?.total.toFixed(2).replace('.', ',')"></p>
                </div>

                <!-- QR Code GIGANTE -->
                <div x-show="selectedOrder?.pix?.qrcode" class="mb-4">
                    <img
                        :src="selectedOrder?.pix?.qrcode"
                        alt="QR Code PIX"
                        class="mx-auto w-full max-w-xs">
                </div>

                <!-- Código Copia e Cola -->
                <div x-show="selectedOrder?.pix?.code" class="mb-4">
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">Código Copia e Cola</p>
                    <div class="bg-gray-100 dark:bg-gray-900 p-3 rounded-lg">
                        <p class="text-xs font-mono break-all text-gray-900 dark:text-gray-100" x-text="selectedOrder?.pix?.code"></p>
                    </div>
                </div>

                <!-- Botão Voltar -->
                <x-filament::button
                    @click="showModal = false"
                    color="gray"
                    size="lg"
                    class="w-full"
                >
                    Voltar
                </x-filament::button>
            </div>
        </div>

    </div>

    <script>
        function pendingOrdersApp() {
            return {
                orders: [],
                loading: false,
                showAll: false,
                lastUpdate: 'Agora',
                showModal: false,
                selectedOrder: null,
                refreshInterval: null,

                async init() {
                    await this.fetchOrders();

                    // Auto-refresh a cada 3 segundos
                    this.refreshInterval = setInterval(() => {
                        this.fetchOrders(true);
                    }, 3000);
                },

                async fetchOrders(silent = false) {
                    if (!silent) this.loading = true;

                    try {
                        const response = await fetch(`/api/v1/orders/pending?show_all=${this.showAll ? 1 : 0}`, {
                            headers: {
                                'Accept': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.orders = data.data;
                            this.lastUpdate = 'Agora';
                        }
                    } catch (error) {
                        console.error('Erro ao buscar pedidos:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                openQrCode(order) {
                    if (order.payment_method === 'pix' && order.pix && order.pix.qrcode) {
                        this.selectedOrder = order;
                        this.showModal = true;
                    }
                },
            }
        }
    </script>
</x-filament-panels::page>
