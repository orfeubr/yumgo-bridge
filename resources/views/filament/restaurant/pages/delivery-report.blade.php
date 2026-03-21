<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Filtros --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">📊 Filtros</h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Períodos Rápidos --}}
                <div class="md:col-span-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Período Rápido:
                    </label>
                    <div class="flex gap-2">
                        <button wire:click="setQuickPeriod('today')" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-primary-100 dark:hover:bg-primary-900 rounded-lg text-sm font-semibold transition">
                            Hoje
                        </button>
                        <button wire:click="setQuickPeriod('yesterday')" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-primary-100 dark:hover:bg-primary-900 rounded-lg text-sm font-semibold transition">
                            Ontem
                        </button>
                        <button wire:click="setQuickPeriod('week')" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-primary-100 dark:hover:bg-primary-900 rounded-lg text-sm font-semibold transition">
                            Últimos 7 dias
                        </button>
                        <button wire:click="setQuickPeriod('month')" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-primary-100 dark:hover:bg-primary-900 rounded-lg text-sm font-semibold transition">
                            Últimos 30 dias
                        </button>
                    </div>
                </div>

                {{-- Data Início --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Data Início:
                    </label>
                    <input
                        type="date"
                        wire:model.live="startDate"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    />
                </div>

                {{-- Data Fim --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Data Fim:
                    </label>
                    <input
                        type="date"
                        wire:model.live="endDate"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    />
                </div>

                {{-- Entregador --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Entregador:
                    </label>
                    <select
                        wire:model.live="driverId"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    >
                        <option value="">Todos</option>
                        @foreach($this->getDriverOptions() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Pagamento --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Status:
                    </label>
                    <select
                        wire:model.live="paymentStatus"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    >
                        <option value="all">Todas</option>
                        <option value="unpaid">Não Pagas</option>
                        <option value="paid">Pagas</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Relatório por Entregador --}}
        @if($this->getDeliveriesByDriver()->count() > 0)
            @foreach($this->getDeliveriesByDriver() as $data)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-gray-200 dark:border-gray-700 p-6">
                    {{-- Cabeçalho do Entregador --}}
                    <div class="flex justify-between items-start mb-4 pb-4 border-b-2 border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            @if($data['driver']->photo)
                                <img
                                    src="{{ Storage::url($data['driver']->photo) }}"
                                    alt="{{ $data['driver']->name }}"
                                    class="w-16 h-16 rounded-full object-cover"
                                />
                            @else
                                <div class="w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-2xl">
                                    🏍️
                                </div>
                            @endif
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ $data['driver']->name }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $data['driver']->vehicle_type }} {{ $data['driver']->vehicle_plate }}
                                </p>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="flex items-center gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Entregas</p>
                                    <p class="text-2xl font-black text-primary-600 dark:text-primary-400">
                                        {{ $data['total_deliveries'] }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total a Pagar</p>
                                    <p class="text-3xl font-black text-green-600 dark:text-green-400">
                                        R$ {{ number_format($data['total_amount'], 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Status de Pagamento --}}
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 text-center">
                            <p class="text-xs text-gray-600 dark:text-gray-400">Total</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $data['total_deliveries'] }}</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/30 rounded-lg p-3 text-center">
                            <p class="text-xs text-green-700 dark:text-green-400">✅ Pagas</p>
                            <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $data['paid_deliveries'] }}</p>
                        </div>
                        <div class="bg-orange-50 dark:bg-orange-900/30 rounded-lg p-3 text-center">
                            <p class="text-xs text-orange-700 dark:text-orange-400">⏳ Pendentes</p>
                            <p class="text-xl font-bold text-orange-600 dark:text-orange-400">{{ $data['unpaid_deliveries'] }}</p>
                        </div>
                    </div>

                    {{-- Lista de Entregas --}}
                    <div class="space-y-2 mb-4">
                        @foreach($data['deliveries'] as $delivery)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    @if($delivery->paid_at)
                                        <span class="text-2xl">✅</span>
                                    @else
                                        <span class="text-2xl">⏳</span>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            Pedido #{{ $delivery->order->order_number }}
                                        </p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ $delivery->order->customer->name }} •
                                            {{ $delivery->delivered_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-lg text-green-600 dark:text-green-400">
                                        R$ {{ number_format($delivery->delivery_fee, 2, ',', '.') }}
                                    </p>
                                    @if($delivery->paid_at)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Pago em {{ $delivery->paid_at->format('d/m H:i') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Botão Marcar como Pago --}}
                    @if($data['unpaid_deliveries'] > 0)
                        <button
                            wire:click="markAsPaid({{ $data['driver']->id }})"
                            wire:confirm="Confirma o pagamento de {{ $data['unpaid_deliveries'] }} entrega(s) totalizando R$ {{ number_format($data['deliveries']->whereNull('paid_at')->sum('delivery_fee'), 2, ',', '.') }}?"
                            class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition"
                        >
                            💵 MARCAR COMO PAGO ({{ $data['unpaid_deliveries'] }} entrega(s) - R$ {{ number_format($data['deliveries']->whereNull('paid_at')->sum('delivery_fee'), 2, ',', '.') }})
                        </button>
                    @else
                        <div class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-center py-3 rounded-lg font-semibold">
                            ✅ Todas as entregas deste período foram pagas
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="bg-gray-100 dark:bg-gray-900 rounded-xl p-12 text-center">
                <div class="text-6xl mb-4">📭</div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                    Nenhuma entrega encontrada
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Ajuste os filtros para ver os resultados
                </p>
            </div>
        @endif

    </div>
</x-filament-panels::page>
