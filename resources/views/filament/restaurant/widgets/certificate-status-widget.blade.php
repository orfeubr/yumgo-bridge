@php
    $data = $this->getViewData();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-6">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Status da Configuração Fiscal
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Acompanhe o status da emissão de NFC-e
                    </p>
                </div>

                @if($data['environment'] === 'homologacao')
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200 rounded-full text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Modo Teste</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200 rounded-full text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Produção</span>
                    </div>
                @endif
            </div>

            {{-- Status Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Dados da Empresa --}}
                <div class="p-4 rounded-lg border @if($data['hasCompanyData']) bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800 @else bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 @endif">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center @if($data['hasCompanyData']) bg-green-100 dark:bg-green-900/30 @else bg-gray-200 dark:bg-gray-700 @endif">
                            @if($data['hasCompanyData'])
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium @if($data['hasCompanyData']) text-green-900 dark:text-green-100 @else text-gray-900 dark:text-white @endif">
                                Dados da Empresa
                            </p>
                            <p class="text-xs @if($data['hasCompanyData']) text-green-600 dark:text-green-400 @else text-gray-500 dark:text-gray-400 @endif">
                                @if($data['hasCompanyData'])
                                    Configurado ✓
                                @else
                                    Pendente
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Certificado Digital --}}
                <div class="p-4 rounded-lg border
                    @if($data['certificateStatus'] === 'valid') bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800
                    @elseif($data['certificateStatus'] === 'expiring_soon') bg-yellow-50 dark:bg-yellow-900/10 border-yellow-200 dark:border-yellow-800
                    @elseif($data['certificateStatus'] === 'expired') bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800
                    @else bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700
                    @endif">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center
                            @if($data['certificateStatus'] === 'valid') bg-green-100 dark:bg-green-900/30
                            @elseif($data['certificateStatus'] === 'expiring_soon') bg-yellow-100 dark:bg-yellow-900/30
                            @elseif($data['certificateStatus'] === 'expired') bg-red-100 dark:bg-red-900/30
                            @else bg-gray-200 dark:bg-gray-700
                            @endif">
                            @if($data['certificateStatus'] === 'valid')
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0121 12c0 5.523-4.477 10-10 10S1 17.523 1 12 5.477 2 11 2c1.524 0 2.975.33 4.282.916m6.623 10.108l-3.843 3.843"/>
                                </svg>
                            @elseif($data['certificateStatus'] === 'expiring_soon' || $data['certificateStatus'] === 'expired')
                                <svg class="w-6 h-6 @if($data['certificateStatus'] === 'expired') text-red-600 dark:text-red-400 @else text-yellow-600 dark:text-yellow-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium
                                @if($data['certificateStatus'] === 'valid') text-green-900 dark:text-green-100
                                @elseif($data['certificateStatus'] === 'expiring_soon') text-yellow-900 dark:text-yellow-100
                                @elseif($data['certificateStatus'] === 'expired') text-red-900 dark:text-red-100
                                @else text-gray-900 dark:text-white
                                @endif">
                                Certificado Digital
                            </p>
                            <p class="text-xs
                                @if($data['certificateStatus'] === 'valid') text-green-600 dark:text-green-400
                                @elseif($data['certificateStatus'] === 'expiring_soon') text-yellow-600 dark:text-yellow-400
                                @elseif($data['certificateStatus'] === 'expired') text-red-600 dark:text-red-400
                                @else text-gray-500 dark:text-gray-400
                                @endif">
                                @if($data['certificateStatus'] === 'valid')
                                    Válido ({{ $data['daysUntilExpiry'] }} dias)
                                @elseif($data['certificateStatus'] === 'expiring_soon')
                                    Expira em {{ $data['daysUntilExpiry'] }} dias!
                                @elseif($data['certificateStatus'] === 'expired')
                                    VENCIDO!
                                @else
                                    Não configurado
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- CSC --}}
                <div class="p-4 rounded-lg border @if($data['hasCsc']) bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800 @else bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 @endif">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center @if($data['hasCsc']) bg-green-100 dark:bg-green-900/30 @else bg-gray-200 dark:bg-gray-700 @endif">
                            @if($data['hasCsc'])
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium @if($data['hasCsc']) text-green-900 dark:text-green-100 @else text-gray-900 dark:text-white @endif">
                                CSC (SEFAZ)
                            </p>
                            <p class="text-xs @if($data['hasCsc']) text-green-600 dark:text-green-400 @else text-gray-500 dark:text-gray-400 @endif">
                                @if($data['hasCsc'])
                                    Configurado ✓
                                @else
                                    Pendente
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Geral --}}
            @if($data['isFullyConfigured'])
                <div class="p-4 bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-green-900 dark:text-green-100">
                                ✅ Sistema configurado e pronto para emitir NFC-e!
                            </p>
                            <p class="text-xs text-green-700 dark:text-green-300 mt-1">
                                Todas as vendas marcadas como "pago" gerarão NFC-e automaticamente.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-yellow-900 dark:text-yellow-100">
                                ⚠️ Configuração incompleta
                            </p>
                            <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                                Complete a configuração para começar a emitir NFC-e automaticamente.
                            </p>
                        </div>
                        <a href="{{ route('filament.restaurant.pages.fiscal-onboarding') }}" class="text-sm font-medium text-yellow-600 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 whitespace-nowrap">
                            Configurar →
                        </a>
                    </div>
                </div>
            @endif

            {{-- Alertas --}}
            @if($data['certificateStatus'] === 'expiring_soon')
                <div class="p-4 bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-orange-900 dark:text-orange-100">
                                🔔 Certificado expira em {{ $data['daysUntilExpiry'] }} dias!
                            </p>
                            <p class="text-xs text-orange-700 dark:text-orange-300 mt-1">
                                Renove seu certificado digital antes do vencimento para não interromper a emissão de notas.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if($data['certificateStatus'] === 'expired')
                <div class="p-4 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-red-900 dark:text-red-100">
                                ❌ Certificado VENCIDO!
                            </p>
                            <p class="text-xs text-red-700 dark:text-red-300 mt-1">
                                Seu certificado digital está vencido. Renove URGENTEMENTE para continuar emitindo NFC-e.
                            </p>
                        </div>
                        <a href="{{ route('filament.restaurant.pages.fiscal-settings') }}" class="text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 whitespace-nowrap">
                            Atualizar →
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
