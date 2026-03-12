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
        <div class="max-w-2xl mx-auto">
            <x-filament::section>
                <div class="text-center py-8">
                    <!-- Ícone Bonito -->
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-100 dark:bg-orange-900/20">
                        <svg class="h-8 w-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>

                    <!-- Título -->
                    <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                        Assinatura Necessária
                    </h3>

                    <!-- Descrição -->
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        Para usar o sistema de delivery, você precisa de uma assinatura ativa.<br>
                        Entre em contato com nossa equipe para ativar sua conta.
                    </p>

                    <!-- Card de Contato -->
                    <div class="mt-8 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/10 dark:to-red-900/10 rounded-lg p-6 border border-orange-200 dark:border-orange-800">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">
                            📞 Fale com o Suporte
                        </h4>

                        <div class="space-y-3 text-sm">
                            <!-- WhatsApp -->
                            <a href="https://wa.me/5511999999999?text=Olá!%20Preciso%20ativar%20minha%20assinatura"
                               target="_blank"
                               class="flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-3 rounded-lg transition-colors">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                                WhatsApp
                            </a>

                            <!-- Email -->
                            <a href="mailto:suporte@yumgo.com.br?subject=Ativar%20Assinatura"
                               class="flex items-center justify-center gap-2 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                suporte@yumgo.com.br
                            </a>
                        </div>
                    </div>

                    <!-- Info Adicional -->
                    <div class="mt-6 text-xs text-gray-500 dark:text-gray-400">
                        <p>💡 Dica: Tenha em mãos o CNPJ do seu restaurante para agilizar o processo</p>
                    </div>
                </div>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>
