<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Informações do Cardápio -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold">{{ $record->name }}</h2>
                    @if($record->description)
                        <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $record->description }}</p>
                    @endif
                </div>
                <div class="flex gap-2">
                    @if($record->is_active)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                            ✅ Ativo
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                            ⏸️ Inativo
                        </span>
                    @endif
                </div>
            </div>

            @if($record->starts_at || $record->ends_at)
                <div class="flex gap-4 text-sm text-gray-600 dark:text-gray-400">
                    @if($record->starts_at)
                        <div>
                            <span class="font-semibold">Início:</span> {{ $record->starts_at->format('d/m/Y') }}
                        </div>
                    @endif
                    @if($record->ends_at)
                        <div>
                            <span class="font-semibold">Término:</span> {{ $record->ends_at->format('d/m/Y') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Cardápio por Dia -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->getItemsByDay() as $dayKey => $dayData)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <!-- Cabeçalho do Dia -->
                    <div class="bg-gradient-to-r
                        @if($dayKey === 'monday') from-blue-500 to-blue-600
                        @elseif($dayKey === 'tuesday') from-green-500 to-green-600
                        @elseif($dayKey === 'wednesday') from-yellow-500 to-yellow-600
                        @elseif($dayKey === 'thursday') from-orange-500 to-orange-600
                        @elseif($dayKey === 'friday') from-red-500 to-red-600
                        @elseif($dayKey === 'saturday') from-purple-500 to-purple-600
                        @else from-gray-500 to-gray-600
                        @endif
                        text-white p-4"
                    >
                        <h3 class="text-lg font-bold">{{ $dayData['label'] }}</h3>
                        <p class="text-sm opacity-90">{{ $dayData['items']->count() }} itens</p>
                    </div>

                    <!-- Lista de Produtos -->
                    <div class="p-4 space-y-3">
                        @foreach($dayData['items'] as $item)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg
                                @if(!$item->is_available) opacity-50 @endif"
                            >
                                <div class="flex-1">
                                    <p class="font-semibold">
                                        {{ $item->product->name }}
                                        @if(!$item->is_available)
                                            <span class="text-xs text-red-500">(Indisponível)</span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        @if($item->special_price)
                                            <span class="line-through text-gray-400">R$ {{ number_format($item->product->price, 2, ',', '.') }}</span>
                                            <span class="text-green-600 font-bold ml-2">R$ {{ number_format($item->special_price, 2, ',', '.') }}</span>
                                            <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded ml-1">PROMOÇÃO</span>
                                        @else
                                            R$ {{ number_format($item->product->price, 2, ',', '.') }}
                                        @endif
                                    </p>
                                </div>

                                @if($item->product->image)
                                    <img
                                        src="{{ Storage::url($item->product->image) }}"
                                        alt="{{ $item->product->name }}"
                                        class="w-16 h-16 object-cover rounded-lg ml-3"
                                    >
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        @if(empty($this->getItemsByDay()))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                <div class="text-gray-400 text-6xl mb-4">📅</div>
                <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">
                    Nenhum produto adicionado ainda
                </h3>
                <p class="text-gray-500">
                    Clique em "Editar" para adicionar produtos ao cardápio semanal.
                </p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
