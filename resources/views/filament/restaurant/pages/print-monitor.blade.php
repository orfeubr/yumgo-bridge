<x-filament-panels::page>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <div class="space-y-6">

        {{-- ===== ESTATÍSTICAS (Cards no topo) ===== --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $stats = $this->stats;
                $bridgeStatus = $this->bridgeStatus;
            @endphp

            {{-- Total de pedidos (24h) --}}
            <x-filament::section class="!p-0">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pedidos (24h)</p>
                            <p class="text-3xl font-bold mt-1">{{ $stats['total'] }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </x-filament::section>

            {{-- Impressos --}}
            <x-filament::section class="!p-0">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Impressos</p>
                            <p class="text-3xl font-bold mt-1 text-green-600">{{ $stats['printed'] }}</p>
                        </div>
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </x-filament::section>

            {{-- Pendentes --}}
            <x-filament::section class="!p-0">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pendentes</p>
                            <p class="text-3xl font-bold mt-1 text-yellow-600">{{ $stats['pending'] }}</p>
                        </div>
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </x-filament::section>

            {{-- Falhas --}}
            <x-filament::section class="!p-0">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Falhas</p>
                            <p class="text-3xl font-bold mt-1 text-red-600">{{ $stats['failed'] }}</p>
                        </div>
                        <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- ===== STATUS DO BRIDGE ===== --}}
        <x-filament::section>
            <x-slot name="heading">
                🖨️ Status do YumGo Bridge
            </x-slot>

            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        {{-- Indicador pulsante se online --}}
                        <span class="relative flex h-4 w-4">
                            @if($bridgeStatus['status'] === 'online')
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-green-500"></span>
                            @else
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500"></span>
                            @endif
                        </span>

                        <div>
                            <span class="text-lg font-semibold">
                                {{ $bridgeStatus['status'] === 'online' ? '🟢 Online' : '🔴 Offline' }}
                            </span>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $bridgeStatus['message'] }}
                            </p>
                            @if($bridgeStatus['version'])
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                    Versão: {{ $bridgeStatus['version'] }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Impressoras Conectadas --}}
            @if(!empty($bridgeStatus['printers']))
                <div class="mt-4 pt-4 border-t dark:border-gray-700">
                    <h4 class="text-sm font-semibold mb-3">Impressoras Conectadas:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($bridgeStatus['printers'] as $printer)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <div class="p-2 bg-primary-100 dark:bg-primary-900 rounded">
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-sm">{{ $printer['name'] ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ ucfirst($printer['location'] ?? 'counter') }} -
                                        <span class="text-green-600">{{ ucfirst($printer['status'] ?? 'ready') }}</span>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-filament::section>

        {{-- ===== ALERTAS DE FALHAS ===== --}}
        @php
            $failed = $this->failedPrints;
        @endphp

        @if($failed->count() > 0)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <span>⚠️ Alertas de Impressão</span>
                        <x-filament::badge color="danger">
                            {{ $failed->count() }}
                        </x-filament::badge>
                    </div>
                </x-slot>

                <div class="space-y-3">
                    @foreach($failed as $order)
                        <div class="flex items-start gap-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <div class="p-2 bg-red-100 dark:bg-red-900 rounded">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold">Pedido #{{ $order['order_number'] }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">•</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $order['customer'] }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">•</span>
                                    <span class="text-sm font-medium">{{ $order['total'] }}</span>
                                </div>
                                <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                    ❌ {{ $order['error'] }}
                                </p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    {{ $order['created_at'] }} • {{ $order['attempts'] }} tentativas
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{-- ===== PEDIDOS PENDENTES ===== --}}
        @php
            $pending = $this->pendingPrints;
        @endphp

        @if($pending->count() > 0)
            <x-filament::section x-data="printManager()">
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <span>⏳ Aguardando Impressão</span>
                            <x-filament::badge color="warning">
                                {{ $pending->count() }}
                            </x-filament::badge>
                        </div>

                        {{-- Ações em massa --}}
                        <div class="flex gap-2" x-show="selectedOrders.length > 0" x-cloak>
                            <span class="text-sm text-gray-600 dark:text-gray-400 self-center" x-text="`${selectedOrders.length} selecionado(s)`"></span>
                            <x-filament::button
                                color="warning"
                                size="sm"
                                @click="forceReprint()"
                                x-bind:disabled="loading"
                            >
                                <span x-show="!loading">🔄 Reimprimir</span>
                                <span x-show="loading" x-cloak>⏳ Aguarde...</span>
                            </x-filament::button>
                            <x-filament::button
                                color="danger"
                                size="sm"
                                @click="cancelPrint()"
                                x-bind:disabled="loading"
                            >
                                <span x-show="!loading">🚫 Cancelar</span>
                                <span x-show="loading" x-cloak>⏳ Aguarde...</span>
                            </x-filament::button>
                        </div>
                    </div>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-2 px-3 w-12">
                                    <input
                                        type="checkbox"
                                        class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                        @change="toggleAll($event.target.checked)"
                                        :checked="selectedOrders.length === {{ $pending->count() }} && {{ $pending->count() }} > 0"
                                    >
                                </th>
                                <th class="text-left py-2 px-3">Pedido</th>
                                <th class="text-left py-2 px-3">Cliente</th>
                                <th class="text-left py-2 px-3">Total</th>
                                <th class="text-left py-2 px-3">Data/Hora</th>
                                <th class="text-left py-2 px-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending as $order)
                                <tr class="border-b border-gray-200 dark:border-gray-700 hover:!bg-gray-50 dark:hover:!bg-gray-700 transition-colors cursor-pointer">
                                    <td class="py-2 px-3">
                                        <input
                                            type="checkbox"
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                            value="{{ $order['id'] }}"
                                            @change="toggleOrder({{ $order['id'] }}, $event.target.checked)"
                                            :checked="selectedOrders.includes({{ $order['id'] }})"
                                        >
                                    </td>
                                    <td class="py-2 px-3 font-medium !text-gray-900 dark:!text-gray-100">#{{ $order['order_number'] }}</td>
                                    <td class="py-2 px-3 !text-gray-900 dark:!text-gray-100">{{ $order['customer'] }}</td>
                                    <td class="py-2 px-3 !text-gray-900 dark:!text-gray-100">{{ $order['total'] }}</td>
                                    <td class="py-2 px-3 !text-gray-700 dark:!text-gray-300">{{ $order['created_at'] }}</td>
                                    <td class="py-2 px-3">
                                        <div class="flex gap-2">
                                            <button
                                                type="button"
                                                class="text-xs px-2 py-1 bg-warning-100 text-warning-700 hover:bg-warning-200 rounded"
                                                @click="forceReprintSingle({{ $order['id'] }})"
                                                x-bind:disabled="loading"
                                            >
                                                🔄 Reimprimir
                                            </button>
                                            <button
                                                type="button"
                                                class="text-xs px-2 py-1 bg-danger-100 text-danger-700 hover:bg-danger-200 rounded"
                                                @click="cancelPrintSingle({{ $order['id'] }})"
                                                x-bind:disabled="loading"
                                            >
                                                🚫 Cancelar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif

        {{-- ===== HISTÓRICO DE IMPRESSÕES BEM-SUCEDIDAS ===== --}}
        <x-filament::section collapsible>
            <x-slot name="heading">
                ✅ Histórico de Impressões (últimas 20)
            </x-slot>

            @php
                $recent = $this->recentPrints;
            @endphp

            @if($recent->count() === 0)
                <div class="text-center py-8 text-gray-500">
                    <p>Nenhuma impressão registrada ainda.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-2 px-3">Pedido</th>
                                <th class="text-left py-2 px-3">Cliente</th>
                                <th class="text-left py-2 px-3">Total</th>
                                <th class="text-left py-2 px-3">Impresso em</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent as $order)
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                    <td class="py-2 px-3 font-medium text-gray-900 dark:text-gray-100">#{{ $order['order_number'] }}</td>
                                    <td class="py-2 px-3 text-gray-900 dark:text-gray-100">{{ $order['customer'] }}</td>
                                    <td class="py-2 px-3 text-gray-900 dark:text-gray-100">{{ $order['total'] }}</td>
                                    <td class="py-2 px-3 text-gray-700 dark:text-gray-300">
                                        {{ $order['printed_at'] ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- ===== INSTRUÇÕES ===== --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                📖 Como Funciona
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <h3>🚀 Monitoramento em Tempo Real</h3>
                <p>Esta página mostra o status da impressão automática em tempo real, com:</p>
                <ul>
                    <li>✅ <strong>Impressões bem-sucedidas:</strong> Histórico completo</li>
                    <li>⚠️ <strong>Falhas de impressão:</strong> Alertas visuais e sonoros</li>
                    <li>⏳ <strong>Pedidos pendentes:</strong> Aguardando impressão</li>
                    <li>🖨️ <strong>Status do Bridge:</strong> Online/Offline em tempo real</li>
                </ul>

                <h3>🔧 Solução de Problemas</h3>
                <ul>
                    <li><strong>Bridge offline:</strong> Verifique se o YumGo Bridge está aberto no computador</li>
                    <li><strong>Impressão falha:</strong> Use o botão "Reimprimir Pedido" no topo da página</li>
                    <li><strong>Múltiplas falhas:</strong> Use "Reimprimir Todas Falhas" para tentar novamente</li>
                    <li><strong>Testar impressora:</strong> Use "Testar Impressora" com um pedido existente</li>
                </ul>

                <h3>📊 Sobre as Estatísticas</h3>
                <p>As estatísticas mostram dados das <strong>últimas 24 horas</strong>:</p>
                <ul>
                    <li><strong>Pedidos:</strong> Total de pedidos criados</li>
                    <li><strong>Impressos:</strong> Pedidos impressos com sucesso</li>
                    <li><strong>Pendentes:</strong> Aguardando impressão</li>
                    <li><strong>Falhas:</strong> Tentativas de impressão que falharam</li>
                </ul>

                <h3>🔄 Auto-Retry</h3>
                <p>O Bridge tenta automaticamente 3 vezes antes de reportar falha:</p>
                <ul>
                    <li>1ª tentativa: Imediata</li>
                    <li>2ª tentativa: Após 1 minuto</li>
                    <li>3ª tentativa: Após 2 minutos</li>
                </ul>
                <p>Se todas falharem, você receberá um alerta aqui.</p>
            </div>
        </x-filament::section>

    </div>

    @push('scripts')
    <script>
        function printManager() {
            return {
                selectedOrders: [],
                loading: false,

                toggleAll(checked) {
                    if (checked) {
                        // Selecionar todos os pedidos visíveis
                        this.selectedOrders = Array.from(
                            document.querySelectorAll('input[type="checkbox"][value]')
                        ).map(el => parseInt(el.value));
                    } else {
                        this.selectedOrders = [];
                    }
                },

                toggleOrder(orderId, checked) {
                    if (checked) {
                        if (!this.selectedOrders.includes(orderId)) {
                            this.selectedOrders.push(orderId);
                        }
                    } else {
                        this.selectedOrders = this.selectedOrders.filter(id => id !== orderId);
                    }
                },

                async forceReprint() {
                    if (this.selectedOrders.length === 0) {
                        alert('Selecione pelo menos um pedido');
                        return;
                    }

                    if (!confirm(`Reimprimir ${this.selectedOrders.length} pedido(s)?`)) {
                        return;
                    }

                    this.loading = true;

                    try {
                        const response = await fetch('/api/v1/bridge/force-reprint', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                order_ids: this.selectedOrders
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            alert(data.message || 'Pedidos reenviados para impressão!');
                            this.selectedOrders = [];
                            window.location.reload();
                        } else {
                            alert('Erro: ' + (data.message || 'Falha ao reimprimir'));
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro ao comunicar com o servidor');
                    } finally {
                        this.loading = false;
                    }
                },

                async cancelPrint() {
                    if (this.selectedOrders.length === 0) {
                        alert('Selecione pelo menos um pedido');
                        return;
                    }

                    if (!confirm(`Cancelar impressão de ${this.selectedOrders.length} pedido(s)?`)) {
                        return;
                    }

                    this.loading = true;

                    try {
                        const response = await fetch('/api/v1/bridge/cancel-print', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                order_ids: this.selectedOrders
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            alert(data.message || 'Impressões canceladas!');
                            this.selectedOrders = [];
                            window.location.reload();
                        } else {
                            alert('Erro: ' + (data.message || 'Falha ao cancelar'));
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro ao comunicar com o servidor');
                    } finally {
                        this.loading = false;
                    }
                },

                async forceReprintSingle(orderId) {
                    if (!confirm('Reimprimir este pedido?')) {
                        return;
                    }

                    this.loading = true;

                    try {
                        const response = await fetch('/api/v1/bridge/force-reprint', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                order_ids: [orderId]
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            alert('Pedido reenviado para impressão!');
                            window.location.reload();
                        } else {
                            alert('Erro: ' + (data.message || 'Falha ao reimprimir'));
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro ao comunicar com o servidor');
                    } finally {
                        this.loading = false;
                    }
                },

                async cancelPrintSingle(orderId) {
                    if (!confirm('Cancelar impressão deste pedido?')) {
                        return;
                    }

                    this.loading = true;

                    try {
                        const response = await fetch('/api/v1/bridge/cancel-print', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                order_ids: [orderId]
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            alert('Impressão cancelada!');
                            window.location.reload();
                        } else {
                            alert('Erro: ' + (data.message || 'Falha ao cancelar'));
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Erro ao comunicar com o servidor');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-filament-panels::page>
