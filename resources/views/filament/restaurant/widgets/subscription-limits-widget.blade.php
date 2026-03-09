<x-filament-widgets::widget>
    @php
        $stats = $this->getStats();
        $subscriptionInfo = $this->getSubscriptionInfo();
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg font-semibold">📊 Uso do Plano</span>
                    @if(!empty($subscriptionInfo))
                        <x-filament::badge :color="match($subscriptionInfo['status']) {
                            'active' => 'success',
                            'trialing' => 'info',
                            'past_due' => 'danger',
                            default => 'gray',
                        }">
                            {{ $subscriptionInfo['plan_name'] }}
                        </x-filament::badge>
                    @endif
                </div>

                <a href="{{ route('filament.restaurant.pages.manage-subscription') }}"
                   class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Gerenciar Assinatura
                </a>
            </div>
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Produtos --}}
            @php
                $products = $stats['products'] ?? [];
                $productsCurrent = $products['current'] ?? 0;
                $productsLimit = $products['limit'] ?? 'Ilimitado';
                $productsPercentage = $products['percentage'] ?? 0;

                $productsColor = match(true) {
                    $productsLimit === 'Ilimitado' => 'primary',
                    $productsPercentage >= 100 => 'danger',
                    $productsPercentage >= 80 => 'warning',
                    default => 'success'
                };
            @endphp

            <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-lg bg-{{ $productsColor }}-100 dark:bg-{{ $productsColor }}-900/30">
                            <svg class="w-5 h-5 text-{{ $productsColor }}-600 dark:text-{{ $productsColor }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Produtos</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total cadastrado</p>
                        </div>
                    </div>

                    <x-filament::badge :color="$productsColor" size="lg">
                        {{ $productsCurrent }}{{ $productsLimit !== 'Ilimitado' ? " / {$productsLimit}" : '' }}
                    </x-filament::badge>
                </div>

                @if($productsLimit !== 'Ilimitado')
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600 dark:text-gray-400">Uso:</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $productsPercentage }}%</span>
                        </div>

                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-{{ $productsColor }}-500 transition-all duration-300"
                                 style="width: {{ min($productsPercentage, 100) }}%"></div>
                        </div>

                        @if($productsPercentage >= 80)
                            <div class="flex items-start gap-2 p-2 rounded bg-{{ $productsPercentage >= 100 ? 'red' : 'yellow' }}-50 dark:bg-{{ $productsPercentage >= 100 ? 'red' : 'yellow' }}-900/20">
                                <svg class="w-4 h-4 text-{{ $productsPercentage >= 100 ? 'red' : 'yellow' }}-600 dark:text-{{ $productsPercentage >= 100 ? 'red' : 'yellow' }}-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-xs text-{{ $productsPercentage >= 100 ? 'red' : 'yellow' }}-700 dark:text-{{ $productsPercentage >= 100 ? 'red' : 'yellow' }}-300">
                                    @if($productsPercentage >= 100)
                                        <strong>Limite atingido!</strong> Faça upgrade para adicionar mais produtos.
                                    @else
                                        <strong>Atenção!</strong> Você está próximo do limite de produtos.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Produtos ilimitados neste plano</span>
                    </div>
                @endif
            </div>

            {{-- Pedidos do Mês --}}
            @php
                $orders = $stats['orders_this_month'] ?? [];
                $ordersCurrent = $orders['current'] ?? 0;
                $ordersLimit = $orders['limit'] ?? 'Ilimitado';
                $ordersPercentage = $orders['percentage'] ?? 0;

                $ordersColor = match(true) {
                    $ordersLimit === 'Ilimitado' => 'primary',
                    $ordersPercentage >= 100 => 'danger',
                    $ordersPercentage >= 80 => 'warning',
                    default => 'success'
                };
            @endphp

            <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-lg bg-{{ $ordersColor }}-100 dark:bg-{{ $ordersColor }}-900/30">
                            <svg class="w-5 h-5 text-{{ $ordersColor }}-600 dark:text-{{ $ordersColor }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Pedidos</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Este mês ({{ now()->format('M/Y') }})</p>
                        </div>
                    </div>

                    <x-filament::badge :color="$ordersColor" size="lg">
                        {{ $ordersCurrent }}{{ $ordersLimit !== 'Ilimitado' ? " / {$ordersLimit}" : '' }}
                    </x-filament::badge>
                </div>

                @if($ordersLimit !== 'Ilimitado')
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600 dark:text-gray-400">Uso:</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $ordersPercentage }}%</span>
                        </div>

                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-{{ $ordersColor }}-500 transition-all duration-300"
                                 style="width: {{ min($ordersPercentage, 100) }}%"></div>
                        </div>

                        @if($ordersPercentage >= 80)
                            <div class="flex items-start gap-2 p-2 rounded bg-{{ $ordersPercentage >= 100 ? 'red' : 'yellow' }}-50 dark:bg-{{ $ordersPercentage >= 100 ? 'red' : 'yellow' }}-900/20">
                                <svg class="w-4 h-4 text-{{ $ordersPercentage >= 100 ? 'red' : 'yellow' }}-600 dark:text-{{ $ordersPercentage >= 100 ? 'red' : 'yellow' }}-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-xs text-{{ $ordersPercentage >= 100 ? 'red' : 'yellow' }}-700 dark:text-{{ $ordersPercentage >= 100 ? 'red' : 'yellow' }}-300">
                                    @if($ordersPercentage >= 100)
                                        <strong>Limite atingido!</strong> Faça upgrade para processar mais pedidos.
                                    @else
                                        <strong>Atenção!</strong> Você está próximo do limite de pedidos deste mês.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Pedidos ilimitados neste plano</span>
                    </div>
                @endif
            </div>
        </div>

        @if(!empty($subscriptionInfo) && $subscriptionInfo['status'] === 'trialing')
            <div class="mt-4 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-1">
                            🎉 Período de Trial Ativo
                        </h4>
                        <p class="text-xs text-blue-700 dark:text-blue-300">
                            Você está testando o plano gratuitamente.
                            @if(!empty($subscriptionInfo['ends_at']))
                                O trial termina em <strong>{{ $subscriptionInfo['ends_at']->format('d/m/Y') }}</strong>.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
