<x-filament-panels::page>
    @if($subscription)
        {{-- Status Card --}}
        <div class="mb-6">
            <x-filament::section>
                <x-slot name="heading">
                    Status da Assinatura
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Status --}}
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                            Status Atual
                        </div>
                        <x-filament::badge :color="$this->getStatusColor()" size="lg">
                            {{ $this->getStatusLabel() }}
                        </x-filament::badge>
                    </div>

                    {{-- Plano --}}
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                            Plano Contratado
                        </div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $subscription->plan->name }}
                        </div>
                        <div class="text-sm text-gray-500">
                            R$ {{ number_format($subscription->amount, 2, ',', '.') }}/mês
                        </div>
                    </div>

                    {{-- Próxima cobrança --}}
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                            @if($subscription->status === 'trialing')
                                Fim do Trial
                            @else
                                Próxima Cobrança
                            @endif
                        </div>
                        @if($subscription->status === 'trialing' && $subscription->trial_ends_at)
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $subscription->trial_ends_at->format('d/m/Y') }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $subscription->trial_ends_at->diffForHumans() }}
                            </div>
                        @elseif($subscription->next_billing_date)
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $subscription->next_billing_date->format('d/m/Y') }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $subscription->next_billing_date->diffForHumans() }}
                            </div>
                        @else
                            <div class="text-gray-500">–</div>
                        @endif
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Detalhes da Assinatura --}}
        <div class="mb-6">
            <x-filament::section>
                <x-slot name="heading">
                    Detalhes
                </x-slot>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                            Data de Início
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $subscription->starts_at->format('d/m/Y') }}
                        </dd>
                    </div>

                    @if($subscription->last_payment_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Último Pagamento
                            </dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                {{ $subscription->last_payment_date->format('d/m/Y') }}
                                <span class="text-gray-500">({{ $subscription->last_payment_date->diffForHumans() }})</span>
                            </dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                            Método de Pagamento
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            @if($subscription->payment_method === 'credit_card')
                                💳 Cartão de Crédito
                            @elseif($subscription->payment_method === 'boleto')
                                📄 Boleto
                            @else
                                Não informado
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                            Integração Pagar.me
                        </dt>
                        <dd class="text-sm">
                            @if($subscription->pagarme_subscription_id)
                                <x-filament::badge color="success">
                                    ✓ Integrado
                                </x-filament::badge>
                            @else
                                <x-filament::badge color="gray">
                                    Não integrado
                                </x-filament::badge>
                            @endif
                        </dd>
                    </div>
                </dl>
            </x-filament::section>
        </div>

        {{-- Recursos do Plano --}}
        <div class="mb-6">
            <x-filament::section>
                <x-slot name="heading">
                    Recursos Inclusos
                </x-slot>

                @if($subscription->plan->features)
                    <ul class="space-y-3">
                        @foreach($subscription->plan->features as $feature)
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-sm text-gray-500">
                        Nenhum recurso configurado.
                    </div>
                @endif
            </x-filament::section>
        </div>

        {{-- Alertas --}}
        @if($subscription->status === 'past_due')
            <div class="mb-6">
                <x-filament::section>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                Pagamento Atrasado
                            </h3>
                            <p class="mt-2 text-sm text-red-700 dark:text-red-300">
                                Seu pagamento não foi aprovado. Entre em contato com o suporte ou atualize seu método de pagamento.
                            </p>
                        </div>
                    </div>
                </x-filament::section>
            </div>
        @endif

        @if($subscription->status === 'trialing')
            <div class="mb-6">
                <x-filament::section>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                Período de Trial Ativo
                            </h3>
                            <p class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                Você está testando gratuitamente até {{ $subscription->trial_ends_at->format('d/m/Y') }}.
                                Após essa data, será cobrado automaticamente.
                            </p>
                        </div>
                    </div>
                </x-filament::section>
            </div>
        @endif

    @else
        {{-- Sem assinatura --}}
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                Sem Assinatura Ativa
            </h3>
            <p class="mt-2 text-sm text-gray-500">
                Você não possui uma assinatura ativa no momento. Entre em contato com o suporte.
            </p>
        </div>
    @endif
</x-filament-panels::page>
