<x-filament-panels::page>

    <div class="space-y-6">

        <!-- Informação principal -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-900 mb-2">💡 Como funciona?</h2>
                    <div class="text-sm text-gray-700 space-y-2">
                        <p><strong>Para Mesas:</strong> Cada mesa tem seu próprio QR Code. O cliente escaneia, escolhe o garçom e faz o pedido.</p>
                        <p><strong>Para Balcão:</strong> Um QR Code único para pedidos diretos no balcão (sem garçom).</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code do Balcão -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">🧍 QR Code do Balcão</h3>
                        <p class="text-sm text-gray-600 mt-1">Pedidos sem garçom, para retirada no balcão</p>
                    </div>
                    <a href="{{ route('restaurant.counter.qr-code') }}" target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Ver e Imprimir
                    </a>
                </div>
            </div>
            <div class="p-6 bg-gray-50">
                <div class="flex items-start space-x-3 text-sm">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-gray-700">
                        <strong>Ideal para:</strong> Lanchonetes, padarias, cafeterias e estabelecimentos com atendimento rápido no balcão
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Codes das Mesas -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">🪑 QR Codes das Mesas</h3>
                        <p class="text-sm text-gray-600 mt-1">Cada mesa tem seu próprio QR Code</p>
                    </div>
                    <a href="{{ route('filament.restaurant.resources.mesas.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition">
                        Gerenciar Mesas
                    </a>
                </div>
            </div>

            @if($this->getTables()->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($this->getTables() as $table)
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <span class="text-lg font-bold text-blue-600">{{ $table->number }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Mesa {{ $table->number }}</h4>
                                        <p class="text-sm text-gray-500">
                                            {{ $table->seats }} lugares •
                                            <span class="{{ $table->status === 'available' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $table->status_badge }}
                                            </span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('restaurant.table.qr-code', $table) }}" target="_blank"
                                       class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-sm transition">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                        Imprimir
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhuma mesa cadastrada</h3>
                    <p class="text-gray-600 mb-4">Cadastre suas mesas para gerar QR Codes individuais</p>
                    <a href="{{ route('filament.restaurant.resources.mesas.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Cadastrar Primeira Mesa
                    </a>
                </div>
            @endif
        </div>

        <!-- Dicas -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">💡 Dica</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Imprima os QR Codes e cole em locais visíveis. Recomendamos plastificar para maior durabilidade!</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-filament-panels::page>
