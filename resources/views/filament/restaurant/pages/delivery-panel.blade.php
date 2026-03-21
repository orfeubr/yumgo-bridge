<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Pedidos Aguardando Entregador --}}
        <div>
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">
                📦 Pedidos Aguardando Entregador ({{ $this->getPendingOrders()->count() }})
            </h2>

            @if($this->getPendingOrders()->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->getPendingOrders() as $order)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border-2 border-orange-300 dark:border-orange-700 p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        Pedido #{{ $order->order_number }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $order->customer->name }}
                                    </p>
                                </div>
                                <span class="bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 text-xs px-2 py-1 rounded-full font-bold">
                                    {{ $order->status }}
                                </span>
                            </div>

                            <div class="space-y-2 mb-4 text-sm">
                                <div class="flex items-start gap-2">
                                    <span class="text-gray-500 dark:text-gray-400">📍</span>
                                    <span class="text-gray-700 dark:text-gray-300 flex-1">
                                        {{ $order->delivery_address }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">💰 Total:</span>
                                    <span class="font-bold text-primary-600 dark:text-primary-400">
                                        R$ {{ number_format($order->total, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">🚚 Taxa:</span>
                                    <span class="font-bold text-green-600 dark:text-green-400">
                                        R$ {{ number_format($order->delivery_fee, 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Select de Entregador --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Atribuir Entregador:
                                </label>
                                <select
                                    wire:change="$dispatch('assignDriver', { orderId: {{ $order->id }}, driverId: $event.target.value })"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white text-sm"
                                >
                                    <option value="">Selecione...</option>
                                    @foreach($this->getActiveDrivers() as $driver)
                                        <option value="{{ $driver->id }}">
                                            {{ $driver->name }} ({{ $driver->deliveries_count }} ativas)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-12 text-center">
                    <div class="text-6xl mb-4">✅</div>
                    <p class="text-gray-600 dark:text-gray-400 font-semibold">
                        Nenhum pedido aguardando entregador
                    </p>
                </div>
            @endif
        </div>

        {{-- Entregas em Andamento --}}
        <div>
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">
                🚚 Entregas em Andamento ({{ $this->getInTransitDeliveries()->count() }})
            </h2>

            @if($this->getInTransitDeliveries()->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->getInTransitDeliveries() as $delivery)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border-2 border-blue-300 dark:border-blue-700 p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        Pedido #{{ $delivery->order->order_number }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $delivery->order->customer->name }}
                                    </p>
                                </div>
                                <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs px-2 py-1 rounded-full font-bold">
                                    {{ $delivery->status_name }}
                                </span>
                            </div>

                            <div class="space-y-2 mb-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 dark:text-gray-400">🏍️</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ $delivery->driver->name ?? 'Sem entregador' }}
                                    </span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-gray-500 dark:text-gray-400">📍</span>
                                    <span class="text-gray-700 dark:text-gray-300 flex-1 text-xs">
                                        {{ $delivery->delivery_address }}
                                    </span>
                                </div>
                            </div>

                            {{-- Botões de Status --}}
                            <div class="grid grid-cols-2 gap-2">
                                @if($delivery->status === 'driver_assigned')
                                    <button
                                        wire:click="$dispatch('updateDeliveryStatus', { deliveryId: {{ $delivery->id }}, status: 'picked_up' })"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold py-2 px-3 rounded transition"
                                    >
                                        ✅ Coletado
                                    </button>
                                @endif

                                @if($delivery->status === 'picked_up')
                                    <button
                                        wire:click="$dispatch('updateDeliveryStatus', { deliveryId: {{ $delivery->id }}, status: 'in_transit' })"
                                        class="bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold py-2 px-3 rounded transition"
                                    >
                                        🚚 Em Trânsito
                                    </button>
                                @endif

                                @if(in_array($delivery->status, ['picked_up', 'in_transit']))
                                    <button
                                        wire:click="$dispatch('updateDeliveryStatus', { deliveryId: {{ $delivery->id }}, status: 'delivered' })"
                                        class="bg-green-500 hover:bg-green-600 text-white text-xs font-bold py-2 px-3 rounded transition"
                                    >
                                        ✅ Entregue
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-12 text-center">
                    <div class="text-6xl mb-4">🚚</div>
                    <p class="text-gray-600 dark:text-gray-400 font-semibold">
                        Nenhuma entrega em andamento
                    </p>
                </div>
            @endif
        </div>

        {{-- Entregadores Disponíveis --}}
        <div>
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">
                🏍️ Entregadores Ativos ({{ $this->getActiveDrivers()->count() }})
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($this->getActiveDrivers() as $driver)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 text-center">
                        @if($driver->photo)
                            <img
                                src="{{ Storage::url($driver->photo) }}"
                                alt="{{ $driver->name }}"
                                class="w-16 h-16 rounded-full mx-auto mb-2 object-cover"
                            />
                        @else
                            <div class="w-16 h-16 rounded-full mx-auto mb-2 bg-gray-300 dark:bg-gray-700 flex items-center justify-center text-2xl">
                                🏍️
                            </div>
                        @endif
                        <p class="font-bold text-sm text-gray-900 dark:text-white">{{ $driver->name }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            {{ $driver->deliveries_count }} entrega(s)
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-filament-panels::page>
