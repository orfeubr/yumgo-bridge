<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal do Entregador - {{ $driver->name }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- HTML5 QR Code Scanner -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        body {
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            user-select: none;
        }

        .btn-action {
            @apply w-full py-4 px-6 rounded-lg font-bold text-lg shadow-lg transition-all duration-200 active:scale-95;
        }

        .delivery-card {
            @apply bg-white rounded-lg shadow-md p-4 mb-4 border-l-4;
        }

        .scanner-reader {
            width: 100% !important;
            border-radius: 12px;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <div x-data="driverPortal()" x-init="init()" class="pb-20">

        <!-- Header com identificação do entregador -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 shadow-lg sticky top-0 z-10">
            <div class="flex items-center space-x-4">
                @if($driver->photo)
                    <img src="{{ Storage::url($driver->photo) }}" alt="{{ $driver->name }}" class="w-16 h-16 rounded-full border-4 border-white shadow-lg">
                @else
                    <div class="w-16 h-16 rounded-full bg-blue-400 flex items-center justify-center border-4 border-white shadow-lg">
                        <span class="text-2xl font-bold">{{ substr($driver->name, 0, 1) }}</span>
                    </div>
                @endif

                <div class="flex-1">
                    <h1 class="text-xl font-bold">{{ $driver->name }}</h1>
                    <p class="text-blue-100 text-sm">
                        @switch($driver->vehicle_type)
                            @case('moto') 🏍️ Moto @break
                            @case('carro') 🚗 Carro @break
                            @case('bicicleta') 🚲 Bicicleta @break
                            @case('a_pe') 🚶 A pé @break
                        @endswitch
                        @if($driver->vehicle_plate) - {{ $driver->vehicle_plate }} @endif
                    </p>
                </div>

                <div class="text-right">
                    <div class="text-2xl font-bold" x-text="totalDeliveries"></div>
                    <div class="text-xs text-blue-100">Entregas</div>
                </div>
            </div>
        </div>

        <!-- Botões de Scanner e Busca Manual -->
        <div class="p-4 bg-white shadow-md sticky top-28 z-9">
            <div class="grid grid-cols-2 gap-3">
                <button @click="openScanner()" class="bg-green-500 hover:bg-green-600 text-white btn-action">
                    📷 Escanear
                </button>
                <button @click="openManualSearch()" class="bg-blue-500 hover:bg-blue-600 text-white btn-action">
                    🔍 Buscar
                </button>
            </div>
        </div>

        <!-- Lista de Entregas Pendentes -->
        <div class="p-4">
            <div x-show="pendingDeliveries.length > 0">
                <h2 class="text-lg font-bold mb-3 text-gray-700">
                    📦 Pendentes (<span x-text="pendingDeliveries.length"></span>)
                </h2>

                <template x-for="delivery in pendingDeliveries" :key="delivery.id">
                    <div class="delivery-card border-yellow-500">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-bold text-lg">#<span x-text="delivery.order.order_number"></span></h3>
                                <p class="text-sm text-gray-600" x-text="delivery.order.customer.name"></p>
                            </div>
                            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-bold">
                                COLETAR
                            </span>
                        </div>

                        <div class="text-sm text-gray-700 mb-3">
                            📍 <span x-text="delivery.address"></span>
                        </div>

                        <button
                            @click="updateStatus(delivery.id, 'picked_up')"
                            class="bg-green-500 hover:bg-green-600 text-white btn-action"
                            :disabled="loading">
                            ✅ COLETEI
                        </button>
                    </div>
                </template>
            </div>

            <!-- Lista de Entregas Em Trânsito -->
            <div x-show="inTransitDeliveries.length > 0" class="mt-6">
                <h2 class="text-lg font-bold mb-3 text-gray-700">
                    🚚 Em Trânsito (<span x-text="inTransitDeliveries.length"></span>)
                </h2>

                <template x-for="delivery in inTransitDeliveries" :key="delivery.id">
                    <div class="delivery-card border-blue-500">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-bold text-lg">#<span x-text="delivery.order.order_number"></span></h3>
                                <p class="text-sm text-gray-600" x-text="delivery.order.customer.name"></p>
                            </div>
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold">
                                EM ROTA
                            </span>
                        </div>

                        <div class="text-sm text-gray-700 mb-3">
                            📍 <span x-text="delivery.address"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <button
                                @click="updateStatus(delivery.id, 'in_transit')"
                                class="bg-orange-500 hover:bg-orange-600 text-white btn-action text-sm"
                                :disabled="loading">
                                🚀 SAINDO
                            </button>
                            <button
                                @click="updateStatus(delivery.id, 'delivered')"
                                class="bg-green-500 hover:bg-green-600 text-white btn-action text-sm"
                                :disabled="loading">
                                ✅ ENTREGUEI
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Mensagem quando não há entregas -->
            <div x-show="pendingDeliveries.length === 0 && inTransitDeliveries.length === 0" class="text-center py-12">
                <div class="text-6xl mb-4">✅</div>
                <h3 class="text-xl font-bold text-gray-700 mb-2">Tudo entregue!</h3>
                <p class="text-gray-500">Não há entregas pendentes no momento.</p>
            </div>
        </div>

        <!-- Modal Scanner -->
        <div x-show="scannerOpen"
             x-cloak
             @click.self="closeScanner()"
             class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">📷 Escanear Código</h3>
                    <button @click="closeScanner()" class="text-gray-500 hover:text-gray-700 text-2xl">×</button>
                </div>

                <div id="qr-reader" class="scanner-reader mb-4"></div>

                <button @click="closeScanner()" class="w-full bg-gray-500 hover:bg-gray-600 text-white btn-action">
                    Cancelar
                </button>
            </div>
        </div>

        <!-- Modal Busca Manual -->
        <div x-show="manualSearchOpen"
             x-cloak
             @click.self="closeManualSearch()"
             class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">🔍 Buscar Pedido</h3>
                    <button @click="closeManualSearch()" class="text-gray-500 hover:text-gray-700 text-2xl">×</button>
                </div>

                <!-- Prefixo automático -->
                <div class="mb-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número do Pedido</label>
                    <div class="flex items-center gap-2">
                        <span class="text-lg font-mono text-gray-500 bg-gray-100 px-3 py-3 rounded-lg border-2 border-gray-300" x-text="orderPrefix"></span>
                        <input
                            type="text"
                            x-model="manualSearchCode"
                            @keyup.enter="searchOrder()"
                            @input="manualSearchCode = manualSearchCode.replace(/[^0-9]/g, '')"
                            placeholder="000000"
                            maxlength="6"
                            class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg text-lg font-mono focus:border-blue-500 focus:outline-none"
                            autofocus>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Digite apenas os 6 dígitos finais</p>
                </div>

                <div x-show="searchError" x-cloak class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm">
                    <span x-text="searchError"></span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button @click="closeManualSearch()" class="bg-gray-500 hover:bg-gray-600 text-white btn-action">
                        Cancelar
                    </button>
                    <button @click="searchOrder()" :disabled="loading || !manualSearchCode" class="bg-blue-500 hover:bg-blue-600 text-white btn-action disabled:opacity-50">
                        Buscar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Detalhes do Pedido Encontrado -->
        <div x-show="showOrderDetails"
             x-cloak
             @click.self="closeOrderDetails()"
             class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">📦 Detalhes do Pedido</h3>
                    <button @click="closeOrderDetails()" class="text-gray-500 hover:text-gray-700 text-2xl">×</button>
                </div>

                <template x-if="foundOrder">
                    <div>
                        <!-- Número do Pedido -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="text-sm text-gray-600 mb-1">Pedido</div>
                            <div class="text-2xl font-bold text-blue-600" x-text="'#' + foundOrder.order_number"></div>
                        </div>

                        <!-- Cliente -->
                        <div class="mb-4">
                            <div class="text-sm font-semibold text-gray-700 mb-1">👤 Cliente</div>
                            <div class="text-base" x-text="foundOrder.customer?.name || 'Não informado'"></div>
                            <div class="text-sm text-gray-500" x-text="foundOrder.customer?.phone || ''"></div>
                        </div>

                        <!-- Endereço Completo -->
                        <div class="mb-4">
                            <div class="text-sm font-semibold text-gray-700 mb-2">📍 Endereço de Entrega</div>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                <div class="text-base leading-relaxed">
                                    <div x-text="foundOrder.delivery_address"></div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <span x-text="foundOrder.delivery_neighborhood"></span> -
                                        <span x-text="foundOrder.delivery_city"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="mb-4">
                            <div class="text-sm font-semibold text-gray-700 mb-1">💰 Valor Total</div>
                            <div class="text-xl font-bold text-green-600">
                                R$ <span x-text="parseFloat(foundOrder.total).toFixed(2)"></span>
                            </div>
                        </div>

                        <!-- Botão Marcar como Entregue -->
                        <button
                            @click="markFoundOrderAsDelivered()"
                            :disabled="loading"
                            class="w-full py-4 bg-green-500 hover:bg-green-600 text-white rounded-lg font-bold text-lg transition-all active:scale-95 disabled:opacity-50">
                            ✅ MARCAR COMO ENTREGUE
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Toast de Sucesso -->
        <div x-show="showSuccessToast"
             x-cloak
             x-transition
             class="fixed bottom-24 left-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50">
            <div class="flex items-center">
                <span class="text-2xl mr-3">✅</span>
                <span x-text="successMessage"></span>
            </div>
        </div>

        <!-- Rodapé com atualização -->
        <div class="fixed bottom-0 left-0 right-0 bg-gray-800 text-white p-4 text-center text-sm">
            <div>Última atualização: <span x-text="lastUpdate"></span></div>
            <div class="text-xs text-gray-400 mt-1">Atualiza automaticamente a cada 30s</div>
        </div>

    </div>

    <script>
        function driverPortal() {
            return {
                pendingDeliveries: @json($pendingDeliveries),
                inTransitDeliveries: @json($inTransitDeliveries),
                loading: false,
                scannerOpen: false,
                manualSearchOpen: false,
                manualSearchCode: '',
                orderPrefix: '', // Prefixo automático (ex: 20260321-)
                searchError: '',
                showSuccessToast: false,
                successMessage: '',
                lastUpdate: '',
                html5QrCode: null,
                foundOrder: null, // Pedido encontrado
                showOrderDetails: false, // Modal de detalhes

                get totalDeliveries() {
                    return this.pendingDeliveries.length + this.inTransitDeliveries.length;
                },

                init() {
                    this.updateLastUpdate();
                    this.generateOrderPrefix();

                    // Auto-refresh a cada 30 segundos
                    setInterval(() => {
                        this.refreshDeliveries();
                    }, 30000);
                },

                generateOrderPrefix() {
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    this.orderPrefix = `${year}${month}${day}-`;
                },

                updateLastUpdate() {
                    const now = new Date();
                    this.lastUpdate = now.toLocaleTimeString('pt-BR');
                },

                async refreshDeliveries() {
                    try {
                        const response = await fetch(window.location.href);
                        const html = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Extrair dados atualizados do script
                        const scriptContent = doc.querySelector('script').textContent;
                        const pendingMatch = scriptContent.match(/pendingDeliveries: (\[.*?\])/);
                        const inTransitMatch = scriptContent.match(/inTransitDeliveries: (\[.*?\])/);

                        if (pendingMatch) this.pendingDeliveries = JSON.parse(pendingMatch[1]);
                        if (inTransitMatch) this.inTransitDeliveries = JSON.parse(inTransitMatch[1]);

                        this.updateLastUpdate();
                    } catch (error) {
                        console.error('Erro ao atualizar entregas:', error);
                    }
                },

                async updateStatus(deliveryId, status) {
                    if (this.loading) return;

                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('driver.update-status') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                delivery_id: deliveryId,
                                status: status
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Atualizar listas
                            this.refreshDeliveries();

                            // Mostrar toast
                            this.successMessage = data.message;
                            this.showSuccessToast = true;
                            setTimeout(() => {
                                this.showSuccessToast = false;
                            }, 3000);
                        } else {
                            alert('Erro: ' + (data.message || 'Erro ao atualizar status'));
                        }
                    } catch (error) {
                        alert('Erro de conexão. Verifique sua internet.');
                        console.error('Erro:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                openScanner() {
                    this.scannerOpen = true;

                    // Aguardar o DOM renderizar
                    this.$nextTick(() => {
                        this.startScanner();
                    });
                },

                closeScanner() {
                    if (this.html5QrCode) {
                        this.html5QrCode.stop().catch(err => console.error('Erro ao parar scanner:', err));
                    }
                    this.scannerOpen = false;
                },

                startScanner() {
                    this.html5QrCode = new Html5Qrcode("qr-reader");

                    this.html5QrCode.start(
                        { facingMode: "environment" },
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 }
                        },
                        (decodedText) => {
                            this.closeScanner();
                            this.manualSearchCode = decodedText;
                            this.searchOrder();
                        },
                        (errorMessage) => {
                            // Ignore scan errors
                        }
                    ).catch(err => {
                        console.error('Erro ao iniciar scanner:', err);
                        alert('Erro ao acessar câmera. Verifique as permissões.');
                        this.closeScanner();
                    });
                },

                openManualSearch() {
                    this.manualSearchOpen = true;
                    this.manualSearchCode = '';
                    this.searchError = '';
                },

                closeManualSearch() {
                    this.manualSearchOpen = false;
                    this.manualSearchCode = '';
                    this.searchError = '';
                },

                async searchOrder() {
                    if (!this.manualSearchCode.trim()) {
                        this.searchError = 'Digite o número do pedido';
                        return;
                    }

                    // Validar que tem 6 dígitos
                    if (this.manualSearchCode.length !== 6) {
                        this.searchError = 'Digite os 6 dígitos do pedido';
                        return;
                    }

                    this.loading = true;
                    this.searchError = '';

                    // Concatenar prefixo + código
                    const fullOrderNumber = this.orderPrefix + this.manualSearchCode;

                    try {
                        const response = await fetch('{{ route('driver.find-order') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                code: fullOrderNumber
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Fechar modal de busca
                            this.closeManualSearch();

                            // Limpar campo de busca
                            this.manualSearchCode = '';

                            // Atualizar lista de entregas
                            await this.refreshDeliveries();

                            // Mostrar mensagem de sucesso
                            alert(`✅ Pedido #${data.order.order_number} adicionado à sua lista!`);
                        } else {
                            this.searchError = data.message || 'Pedido não encontrado';
                        }
                    } catch (error) {
                        this.searchError = 'Erro de conexão. Verifique sua internet.';
                        console.error('Erro:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async markAsOutForDelivery(deliveryId) {
                    try {
                        const response = await fetch('{{ route('driver.update-status') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                delivery_id: deliveryId,
                                status: 'picked_up'
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.successMessage = '✅ Saiu para entrega!';
                            this.showSuccessToast = true;
                            setTimeout(() => {
                                this.showSuccessToast = false;
                            }, 3000);

                            this.refreshDeliveries();
                        }
                    } catch (error) {
                        console.error('Erro ao atualizar status:', error);
                    }
                },

                closeOrderDetails() {
                    this.showOrderDetails = false;
                    this.foundOrder = null;
                },

                async markFoundOrderAsDelivered() {
                    if (!this.foundOrder || !this.foundOrder.delivery) return;

                    await this.updateStatus(this.foundOrder.delivery.id, 'delivered');
                    this.closeOrderDetails();
                }
            }
        }
    </script>
</body>
</html>
