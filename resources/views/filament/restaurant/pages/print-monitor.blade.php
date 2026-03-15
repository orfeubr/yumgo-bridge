<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Status do Bridge --}}
        <x-filament::section>
            <x-slot name="heading">
                🖨️ Status do Bridge
            </x-slot>

            @php
                $status = $this->getBridgeStatus();
            @endphp

            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            @if($status['status'] === 'online')
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-success-500"></span>
                            @elseif($status['status'] === 'stale')
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-warning-500"></span>
                            @else
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-danger-500"></span>
                            @endif
                        </span>
                        <span class="text-lg font-semibold">
                            {{ ucfirst($status['status']) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $status['message'] }}
                    </p>
                </div>

                <div class="flex gap-2">
                    <x-filament::button wire:click="testPrint" color="primary">
                        📤 Impressão Teste
                    </x-filament::button>

                    <x-filament::button wire:click="clearCache" color="gray" outlined>
                        🔄 Limpar Cache
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- Impressoras Configuradas --}}
        <x-filament::section>
            <x-slot name="heading">
                🖨️ Impressoras Configuradas
            </x-slot>

            @php
                $printers = $this->getPrinters();
            @endphp

            @if(empty($printers))
                <div class="text-center py-8 text-gray-500">
                    <p>Nenhuma impressora configurada ainda.</p>
                    <p class="text-sm mt-2">Configure no app YumGo Bridge.</p>
                </div>
            @else
                <div class="divide-y dark:divide-gray-700">
                    @foreach($printers as $location => $printer)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <span class="font-medium">{{ ucfirst($location) }}</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $printer['type'] ?? 'system' }} - {{ $printer['printerName'] ?? 'N/A' }}
                                </p>
                            </div>
                            <x-filament::badge color="success">
                                Ativo
                            </x-filament::badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Histórico de Impressões --}}
        <x-filament::section>
            <x-slot name="heading">
                📋 Histórico de Impressões (últimas 50)
            </x-slot>

            @php
                $history = $this->getPrintHistory();
            @endphp

            @if(empty($history))
                <div class="text-center py-8 text-gray-500">
                    <p>Nenhuma impressão registrada ainda.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-2 px-3">Data/Hora</th>
                                <th class="text-left py-2 px-3">Pedido</th>
                                <th class="text-left py-2 px-3">Local</th>
                                <th class="text-left py-2 px-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_reverse($history) as $item)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="py-2 px-3">
                                        {{ \Carbon\Carbon::parse($item['timestamp'] ?? now())->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="py-2 px-3 font-medium">
                                        #{{ $item['order_number'] ?? 'N/A' }}
                                    </td>
                                    <td class="py-2 px-3">
                                        {{ ucfirst($item['location'] ?? 'counter') }}
                                    </td>
                                    <td class="py-2 px-3">
                                        @if(($item['status'] ?? 'success') === 'success')
                                            <x-filament::badge color="success">
                                                ✓ Sucesso
                                            </x-filament::badge>
                                        @else
                                            <x-filament::badge color="danger">
                                                ✗ Erro
                                            </x-filament::badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Instruções --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                📖 Como Funciona
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <h3>1. Instalar Bridge</h3>
                <p>Baixe e instale o YumGo Bridge no computador conectado à impressora.</p>

                <h3>2. Configurar Token</h3>
                <p>Vá em <strong>Impressão Automática</strong> e gere um token. Cole no Bridge.</p>

                <h3>3. Configurar Impressora</h3>
                <p>No Bridge, selecione a impressora e configure.</p>

                <h3>4. Monitorar</h3>
                <p>Esta página mostra status em tempo real!</p>

                <h3>Solução de Problemas</h3>
                <ul>
                    <li><strong>Bridge offline:</strong> Verifique se o app está aberto</li>
                    <li><strong>Não imprime:</strong> Clique em "Impressão Teste"</li>
                    <li><strong>Impressora não aparece:</strong> Reconfigure no Bridge</li>
                </ul>
            </div>
        </x-filament::section>

    </div>

    {{-- Auto-refresh a cada 5 segundos --}}
    <script>
        setInterval(() => {
            @this.call('$refresh');
        }, 5000);
    </script>
</x-filament-panels::page>
