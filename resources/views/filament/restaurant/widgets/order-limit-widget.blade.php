@php
    $data = $this->getUsageData();
    $percentage = $data['percentage'];
    $current = $data['current'];
    $limit = $data['limit'];
    $planName = $data['plan_name'];
    $canCreate = $data['can_create'];

    // Determinar cor e mensagem baseado no uso
    if ($percentage >= 100) {
        $colorClass = 'bg-red-50 border-red-300';
        $iconColor = 'text-red-600';
        $progressColor = 'bg-red-600';
        $textColor = 'text-red-900';
        $title = '🚫 Limite de pedidos deste mês atingido';
        $message = 'Você atingiu o limite de ' . $limit . ' pedidos/mês do plano ' . $planName . '. Faça upgrade para processar mais pedidos.';
    } elseif ($percentage >= 90) {
        $colorClass = 'bg-orange-50 border-orange-300';
        $iconColor = 'text-orange-600';
        $progressColor = 'bg-orange-500';
        $textColor = 'text-orange-900';
        $title = '⚠️ Limite de pedidos quase atingido';
        $message = 'Você está usando ' . $percentage . '% do limite de pedidos deste mês. Considere fazer upgrade antes de atingir o limite.';
    } elseif ($percentage >= 70) {
        $colorClass = 'bg-yellow-50 border-yellow-300';
        $iconColor = 'text-yellow-600';
        $progressColor = 'bg-yellow-500';
        $textColor = 'text-yellow-900';
        $title = '💡 Aproximando do limite de pedidos';
        $message = 'Você está usando ' . $percentage . '% do limite de pedidos deste mês (' . $planName . ').';
    } else {
        $colorClass = 'bg-blue-50 border-blue-300';
        $iconColor = 'text-blue-600';
        $progressColor = 'bg-blue-500';
        $textColor = 'text-blue-900';
        $title = '✅ Pedidos dentro do limite';
        $message = 'Você processou ' . $current . ' de ' . $limit . ' pedidos disponíveis neste mês (plano ' . $planName . ').';
    }
@endphp

<x-filament-widgets::widget>
    <div class="border rounded-lg p-4 {{ $colorClass }}">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h3 class="font-semibold {{ $textColor }} mb-1">
                    {{ $title }}
                </h3>
                <p class="text-sm {{ $textColor }} mb-3">
                    {{ $message }}
                </p>

                <!-- Barra de progresso -->
                <div class="mb-3">
                    <div class="flex justify-between text-xs {{ $textColor }} mb-1">
                        <span>{{ $current }} / {{ $limit }} pedidos este mês</span>
                        <span class="font-semibold">{{ $percentage }}%</span>
                    </div>
                    <div class="w-full bg-white rounded-full h-2.5">
                        <div class="{{ $progressColor }} h-2.5 rounded-full transition-all duration-500"
                             style="width: {{ min($percentage, 100) }}%"></div>
                    </div>
                </div>

                @if ($percentage >= 90)
                    <a href="{{ route('filament.restaurant.pages.manage-subscription') }}"
                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold {{ $textColor }} hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Fazer Upgrade do Plano
                    </a>
                @endif
            </div>

            @if ($percentage >= 90)
                <div class="{{ $iconColor }}">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
