<x-filament-panels::page>

    @if($this->isOpen && $this->cashRegister)
        {{-- CAIXA ABERTO --}}
        <div class="space-y-6">

            {{-- HEADER COM STATUS --}}
            <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-6 border-2 border-green-300 dark:border-green-700">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-4xl">🟢</span>
                            <div>
                                <h2 class="text-2xl font-black text-green-700 dark:text-green-300">CAIXA ABERTO</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Operador: <span class="font-semibold">{{ $this->cashRegister->user_name }}</span>
                                </p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Aberto em: {{ $this->cashRegister->opened_at->format('d/m/Y \à\s H:i') }}
                            ({{ $this->cashRegister->opened_at->diffForHumans() }})
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Fundo Inicial</p>
                        <p class="text-3xl font-black text-green-600 dark:text-green-400">
                            R$ {{ number_format($this->cashRegister->opening_balance, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- DASHBOARD DE VENDAS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- PEDIDOS --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-3xl">🛒</span>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Pedidos</span>
                    </div>
                    <p class="text-3xl font-black text-gray-900 dark:text-white">
                        {{ $this->cashRegister->orders_count }}
                    </p>
                    @if($this->cashRegister->cancelled_count > 0)
                        <p class="text-xs text-red-500 mt-1">
                            {{ $this->cashRegister->cancelled_count }} cancelados
                        </p>
                    @endif
                </div>

                {{-- TOTAL VENDAS --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-3xl">💰</span>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</span>
                    </div>
                    <p class="text-3xl font-black text-primary-600 dark:text-primary-400">
                        R$ {{ number_format($this->cashRegister->total_sales, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Todas as formas
                    </p>
                </div>

                {{-- SANGRIAS --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-3xl">💸</span>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Sangrias</span>
                    </div>
                    <p class="text-3xl font-black text-red-600 dark:text-red-400">
                        R$ {{ number_format($this->cashRegister->total_withdrawals, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Retiradas
                    </p>
                </div>

                {{-- REFORÇOS --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-3xl">💵</span>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Reforços</span>
                    </div>
                    <p class="text-3xl font-black text-blue-600 dark:text-blue-400">
                        R$ {{ number_format($this->cashRegister->total_deposits, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Adicionados
                    </p>
                </div>

            </div>

            {{-- VENDAS POR MÉTODO DE PAGAMENTO --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="text-2xl">📊</span> Vendas por Método de Pagamento
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">

                    {{-- DINHEIRO --}}
                    <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg border-2 border-green-200 dark:border-green-700">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-2xl">💵</span>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Dinheiro</span>
                        </div>
                        <p class="text-2xl font-black text-green-700 dark:text-green-300">
                            R$ {{ number_format($this->cashRegister->total_cash, 2, ',', '.') }}
                        </p>
                    </div>

                    {{-- PIX --}}
                    <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg border-2 border-blue-200 dark:border-blue-700">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-2xl">💰</span>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">PIX</span>
                        </div>
                        <p class="text-2xl font-black text-blue-700 dark:text-blue-300">
                            R$ {{ number_format($this->cashRegister->total_pix, 2, ',', '.') }}
                        </p>
                    </div>

                    {{-- CARTÃO CRÉDITO --}}
                    <div class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg border-2 border-purple-200 dark:border-purple-700">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-2xl">💳</span>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Crédito</span>
                        </div>
                        <p class="text-2xl font-black text-purple-700 dark:text-purple-300">
                            R$ {{ number_format($this->cashRegister->total_credit_card, 2, ',', '.') }}
                        </p>
                    </div>

                    {{-- CARTÃO DÉBITO --}}
                    <div class="p-4 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-lg border-2 border-orange-200 dark:border-orange-700">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-2xl">🏧</span>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Débito</span>
                        </div>
                        <p class="text-2xl font-black text-orange-700 dark:text-orange-300">
                            R$ {{ number_format($this->cashRegister->total_debit_card, 2, ',', '.') }}
                        </p>
                    </div>

                    {{-- OUTROS --}}
                    @if($this->cashRegister->total_other > 0)
                        <div class="p-4 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900/20 dark:to-gray-800/20 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-2xl">📝</span>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Outros</span>
                            </div>
                            <p class="text-2xl font-black text-gray-700 dark:text-gray-300">
                                R$ {{ number_format($this->cashRegister->total_other, 2, ',', '.') }}
                            </p>
                        </div>
                    @endif

                </div>
            </div>

            {{-- RESUMO PARA FECHAMENTO --}}
            <div class="bg-gradient-to-br from-primary-50 to-primary-100 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-lg border-2 border-primary-200 dark:border-primary-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="text-2xl">🧮</span> Resumo de Fechamento
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- DINHEIRO ESPERADO --}}
                    <div class="bg-white dark:bg-gray-900 rounded-lg p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">💰 Dinheiro Esperado</p>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Fundo Inicial:</span>
                                <span class="font-semibold">R$ {{ number_format($this->cashRegister->opening_balance, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-green-600">
                                <span>+ Vendas Dinheiro:</span>
                                <span class="font-semibold">R$ {{ number_format($this->cashRegister->total_cash, 2, ',', '.') }}</span>
                            </div>
                            @if($this->cashRegister->total_deposits > 0)
                                <div class="flex justify-between text-blue-600">
                                    <span>+ Reforços:</span>
                                    <span class="font-semibold">R$ {{ number_format($this->cashRegister->total_deposits, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            @if($this->cashRegister->total_withdrawals > 0)
                                <div class="flex justify-between text-red-600">
                                    <span>- Sangrias:</span>
                                    <span class="font-semibold">R$ {{ number_format($this->cashRegister->total_withdrawals, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="border-t-2 border-gray-300 dark:border-gray-600 pt-2 flex justify-between">
                                <span class="font-bold text-gray-900 dark:text-white">ESPERADO:</span>
                                <span class="text-2xl font-black text-primary-600 dark:text-primary-400">
                                    R$ {{ number_format($this->cashRegister->final_balance, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- OUTROS MÉTODOS (INFORMATIVO) --}}
                    <div class="bg-white dark:bg-gray-900 rounded-lg p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">📱 Outros Métodos (Informativo)</p>
                        <div class="space-y-1 text-sm">
                            @php
                                $otherTotal = $this->cashRegister->total_pix +
                                              $this->cashRegister->total_credit_card +
                                              $this->cashRegister->total_debit_card +
                                              $this->cashRegister->total_other;
                            @endphp
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">PIX + Cartões:</span>
                                <span class="font-semibold">R$ {{ number_format($otherTotal, 2, ',', '.') }}</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                ℹ️ Não incluso no fechamento<br>(já creditado automaticamente)
                            </p>
                        </div>
                    </div>

                    {{-- TOTAL GERAL --}}
                    <div class="bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900/40 dark:to-green-800/40 rounded-lg p-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">🎯 Vendas Totais do Turno</p>
                        <p class="text-4xl font-black text-green-700 dark:text-green-300">
                            R$ {{ number_format($this->cashRegister->total_sales, 2, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                            {{ $this->cashRegister->orders_count }} pedidos realizados
                        </p>
                    </div>

                </div>
            </div>

            {{-- ÚLTIMAS MOVIMENTAÇÕES --}}
            @if($this->cashRegister->movements->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="text-2xl">📋</span> Últimas Movimentações
                    </h3>

                    <div class="space-y-2">
                        @foreach($this->cashRegister->movements()->latest()->limit(5)->get() as $movement)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">{{ $movement->is_withdrawal ? '💸' : '💵' }}</span>
                                    <div>
                                        <p class="font-semibold text-sm text-gray-900 dark:text-white">
                                            {{ $movement->type_name }} - {{ $movement->reason }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $movement->user_name }} • {{ $movement->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <p class="text-lg font-bold {{ $movement->is_withdrawal ? 'text-red-600' : 'text-blue-600' }}">
                                    {{ $movement->is_withdrawal ? '-' : '+' }}R$ {{ number_format($movement->amount, 2, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

    @else
        {{-- CAIXA FECHADO --}}
        <div class="flex items-center justify-center min-h-[60vh]">
            <div class="text-center max-w-md">
                <div class="mb-6">
                    <span class="text-8xl">🔒</span>
                </div>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white mb-3">
                    Caixa Fechado
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Nenhum caixa aberto no momento. Abra um novo caixa para começar a registrar vendas.
                </p>

                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700 text-left">
                    <p class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">💡 Dica:</p>
                    <p class="text-sm text-blue-700 dark:text-blue-400">
                        Ao abrir o caixa, informe o <strong>fundo de troco</strong> (valor inicial em dinheiro).
                        Isso ajudará na conferência no fechamento.
                    </p>
                </div>
            </div>
        </div>
    @endif

</x-filament-panels::page>
