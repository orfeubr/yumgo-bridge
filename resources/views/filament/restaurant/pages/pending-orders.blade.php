<x-filament-panels::page>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
</head>
<body class="bg-gray-100 min-h-screen">
    <div x-data="pendingOrdersApp()" x-init="init()" class="max-w-4xl mx-auto p-4">

        <!-- Header -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Pedidos Pendentes</h1>
                        <p class="text-sm text-gray-600">
                            <span x-text="orders.length"></span> pedido(s)
                            <span class="mx-1">•</span>
                            <span x-text="lastUpdate"></span>
                        </p>
                    </div>
                </div>

                <!-- Toggle Mostrar Todos -->
                <button
                    @click="showAll = !showAll; fetchOrders()"
                    :class="showAll ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-700'"
                    class="px-4 py-2 rounded-lg font-semibold text-sm transition">
                    <span x-show="!showAll">Mostrar Todos</span>
                    <span x-show="showAll">Apenas Pendentes</span>
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div x-show="loading && orders.length === 0" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-red-500 border-t-transparent"></div>
            <p class="mt-4 text-gray-600">Carregando pedidos...</p>
        </div>

        <!-- Lista de Pedidos -->
        <div x-show="!loading || orders.length > 0" x-cloak class="space-y-3">
            <template x-for="order in orders" :key="order.id">
                <div
                    @click="openQrCode(order)"
                    :class="order.payment_status === 'pending' ? 'border-red-300 bg-red-50' : 'border-gray-300 bg-white'"
                    class="relative border-2 rounded-xl p-4 cursor-pointer hover:shadow-lg transition active:scale-98">

                    <!-- Indicador de status -->
                    <div class="absolute top-4 right-4">
                        <div
                            :class="order.payment_status === 'pending' ? 'bg-red-500' : 'bg-green-500'"
                            class="w-3 h-3 rounded-full">
                            <div x-show="order.payment_status === 'pending'" class="absolute inset-0 rounded-full bg-red-500 pulse-ring"></div>
                        </div>
                    </div>

                    <!-- Número do Pedido (GRANDE) -->
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-gray-900" x-text="'#' + order.order_number"></span>
                        <span
                            x-show="order.table_number"
                            class="text-lg font-semibold text-gray-600"
                            x-text="'Mesa ' + order.table_number"></span>
                        <span
                            x-show="order.service_type === 'counter'"
                            class="text-lg font-semibold text-gray-600">Balcão</span>
                    </div>

                    <!-- Cliente e Total -->
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-lg font-semibold text-gray-700" x-text="order.customer_name"></p>
                        <p class="text-2xl font-black text-red-600" x-text="'R$ ' + order.total.toFixed(2).replace('.', ',')"></p>
                    </div>

                    <!-- Método de Pagamento e Tempo -->
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span class="flex items-center gap-1">
                            <span x-show="order.payment_method === 'pix'" class="font-semibold">💰 PIX</span>
                            <span x-show="order.payment_method !== 'pix'" class="font-semibold" x-text="order.payment_method.toUpperCase()"></span>
                            <span
                                :class="order.payment_status === 'pending' ? 'text-red-600' : 'text-green-600'"
                                class="font-semibold"
                                x-text="order.payment_status === 'pending' ? '• PENDENTE' : '• PAGO'"></span>
                        </span>
                        <span x-text="order.created_at_human"></span>
                    </div>

                    <!-- Botão Mostrar QR Code -->
                    <div
                        x-show="order.payment_method === 'pix' && order.pix && order.pix.qrcode"
                        class="mt-3 bg-red-500 text-white rounded-lg py-3 text-center font-bold text-lg">
                        📱 Clique para mostrar QR Code
                    </div>
                </div>
            </template>

            <!-- Vazio -->
            <div x-show="orders.length === 0 && !loading" x-cloak class="text-center py-12">
                <div class="text-6xl mb-4">🎉</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Nenhum pedido pendente!</h3>
                <p class="text-gray-600">Todos os pedidos foram pagos</p>
            </div>
        </div>

        <!-- Modal QR Code (Tela Cheia) -->
        <div
            x-show="showModal"
            x-cloak
            @click.self="showModal = false"
            class="fixed inset-0 z-50 bg-black bg-opacity-90 flex items-center justify-center p-4">

            <div class="bg-white rounded-2xl max-w-lg w-full p-6 text-center">
                <!-- Fechar -->
                <button
                    @click="showModal = false"
                    class="absolute top-4 right-4 w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Pedido Info -->
                <div class="mb-4">
                    <h2 class="text-2xl font-black text-gray-900" x-text="'Pedido #' + selectedOrder?.order_number"></h2>
                    <p class="text-lg text-gray-600" x-text="selectedOrder?.customer_name"></p>
                    <p class="text-3xl font-black text-red-600 mt-2" x-text="'R$ ' + selectedOrder?.total.toFixed(2).replace('.', ',')"></p>
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
                    <p class="text-xs text-gray-600 mb-2">Código Copia e Cola</p>
                    <div class="bg-gray-100 p-3 rounded-lg">
                        <p class="text-xs font-mono break-all" x-text="selectedOrder?.pix?.code"></p>
                    </div>
                </div>

                <!-- Botão Voltar -->
                <button
                    @click="showModal = false"
                    class="w-full bg-gray-900 text-white rounded-lg py-4 font-bold text-lg hover:bg-gray-800 transition">
                    Voltar
                </button>
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
