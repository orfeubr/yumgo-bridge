<x-filament-panels::page>
    @php
        $statusInfo = $this->getAccountStatus();
    @endphp

    <style>
        .payment-page * {
            color: inherit !important;
        }
        .payment-page h3,
        .payment-page h4,
        .payment-page p,
        .payment-page span,
        .payment-page div {
            color: inherit !important;
        }
        .banner-info {
            background: linear-gradient(135deg, #ffffff 0%, #fef3f2 100%);
            border: 2px solid #fed7aa;
            color: #1f2937 !important;
        }
        .banner-info * {
            color: inherit !important;
        }
        .banner-info h3 {
            color: #ea580c !important;
        }
        .banner-info h4 {
            color: #9a3412 !important;
        }
        .banner-info p {
            color: #78716c !important;
        }
    </style>

    <!-- Status da Conta -->
    <div class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-2 border-{{ $statusInfo['color'] }}-200 dark:border-{{ $statusInfo['color'] }}-800">
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Conta de Recebimentos
                        </h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">via</span>
                        <img src="/images/asaas-logo.png" alt="Asaas" class="h-12 w-auto" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                        <span class="hidden bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-3 py-1 rounded-md font-bold text-sm">ASAAS</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        @if(!$statusInfo['configured'])
                            Configure seus dados para começar a receber pagamentos online
                        @elseif($statusInfo['status'] === 'pending')
                            Sua conta está em análise pelo Asaas (1-3 dias úteis)
                        @elseif($statusInfo['status'] === 'approved')
                            Sua conta está ativa e você pode receber pagamentos!
                        @else
                            Verifique os dados e tente novamente
                        @endif
                    </p>
                </div>
                <div>
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-{{ $statusInfo['color'] }}-100 text-{{ $statusInfo['color'] }}-800 dark:bg-{{ $statusInfo['color'] }}-900/30 dark:text-{{ $statusInfo['color'] }}-400">
                        {{ $statusInfo['label'] }}
                    </span>
                </div>
            </div>

            @if($statusInfo['configured'] && $statusInfo['status'] === 'approved')
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg">
                            <p class="text-3xl mb-2">💰</p>
                            <p class="text-sm font-semibold text-blue-900 dark:text-blue-300">PIX Habilitado</p>
                            <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">R$ 0,99/transação</p>
                        </div>
                        <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg">
                            <p class="text-3xl mb-2">💳</p>
                            <p class="text-sm font-semibold text-green-900 dark:text-green-300">Cartão Habilitado</p>
                            <p class="text-xs text-green-700 dark:text-green-400 mt-1">2,99% + R$ 0,49</p>
                        </div>
                        <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg">
                            <p class="text-3xl mb-2">⚡</p>
                            <p class="text-sm font-semibold text-purple-900 dark:text-purple-300">Recebimento D+1</p>
                            <p class="text-xs text-purple-700 dark:text-purple-400 mt-1">Plano Starter</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Banner Como Funciona (apenas se não configurado) -->
    @if(!$statusInfo['configured'])
        <div class="mb-6 banner-info" style="margin-bottom: 1.5rem; border-radius: 0.75rem; box-shadow: 0 2px 8px rgba(249, 115, 22, 0.1); padding: 2rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <span style="font-size: 2rem;">💡</span>
                <h3 style="font-size: 1.5rem; font-weight: 700; margin: 0;">Como Configurar sua Conta de Recebimentos</h3>
            </div>
            <p style="margin-bottom: 1.5rem; font-size: 1rem; line-height: 1.6; color: #57534e !important;">
                Você <strong>não precisa criar conta</strong> no Asaas! Basta preencher seus dados abaixo e criaremos uma sub-conta automaticamente para você receber seus pagamentos.
            </p>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 1.5rem;">
                <div style="background: white; border: 2px solid #fed7aa; border-radius: 0.5rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f97316, #fb923c); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: white; font-weight: 700; font-size: 1.25rem;">1</div>
                    <h4 style="font-weight: 700; margin-bottom: 0.5rem; font-size: 1.05rem;">Preencha seus dados</h4>
                    <p style="font-size: 0.9rem; line-height: 1.5; margin: 0;">CPF/CNPJ, endereço e dados bancários</p>
                </div>
                <div style="background: white; border: 2px solid #fed7aa; border-radius: 0.5rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f97316, #fb923c); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: white; font-weight: 700; font-size: 1.25rem;">2</div>
                    <h4 style="font-weight: 700; margin-bottom: 0.5rem; font-size: 1.05rem;">Aguarde aprovação</h4>
                    <p style="font-size: 0.9rem; line-height: 1.5; margin: 0;">Análise KYC em 1-3 dias úteis</p>
                </div>
                <div style="background: white; border: 2px solid #fed7aa; border-radius: 0.5rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f97316, #fb923c); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: white; font-weight: 700; font-size: 1.25rem;">3</div>
                    <h4 style="font-weight: 700; margin-bottom: 0.5rem; font-size: 1.05rem;">Comece a vender!</h4>
                    <p style="font-size: 0.9rem; line-height: 1.5; margin: 0;">Receba via PIX e Cartão automaticamente</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Formulário -->
    <div class="payment-page mb-6">
        <form wire:submit="save">
            {{ $this->form }}

            <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
                <x-filament::button type="submit" color="success" size="lg">
                    @if(!$statusInfo['configured'])
                        🎉 Criar Conta de Recebimentos
                    @else
                        💾 Atualizar Dados
                    @endif
                </x-filament::button>
            </div>
        </form>
    </div>

    <!-- Mensagem de Sucesso -->
    @if($statusInfo['configured'] && $statusInfo['status'] === 'approved')
        <div style="margin-top: 1.5rem; background: linear-gradient(135deg, #dcfce7, #bbf7d0); border: 2px solid #86efac; border-radius: 0.5rem; padding: 1.5rem;">
            <div style="display: flex; align-items: start; gap: 1rem;">
                <span style="font-size: 2.5rem; line-height: 1;">🎉</span>
                <div style="flex: 1;">
                    <h4 style="font-weight: 700; color: #14532d !important; font-size: 1.125rem; margin-bottom: 0.5rem;">
                        Parabéns! Você está pronto para vender!
                    </h4>
                    <p style="font-size: 0.875rem; color: #15803d !important; margin-bottom: 0.75rem;">
                        Sua conta foi aprovada e está 100% funcional. Todos os pedidos feitos pelo app/site já vão gerar cobranças automaticamente!
                    </p>
                    <div style="display: flex; gap: 1rem; font-size: 0.875rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="color: #16a34a !important;">✓</span>
                            <span style="color: #166534 !important;">PIX automático</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="color: #16a34a !important;">✓</span>
                            <span style="color: #166534 !important;">Cartão de crédito</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="color: #16a34a !important;">✓</span>
                            <span style="color: #166534 !important;">Split automático</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
