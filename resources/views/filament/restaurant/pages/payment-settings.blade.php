<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Informações Importantes -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Como funciona?</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Preencha seus dados para criar uma conta Asaas</li>
                            <li>Após aprovação, você receberá <strong>99% do valor</strong> de cada pedido</li>
                            <li>A plataforma fica com apenas <strong>1%</strong> de comissão <span class="text-xs italic">(sendo definido)</span></li>
                            <li>Os repasses são automáticos (D+30 para PIX, D+1 para cartão)</li>
                            <li>Taxas Asaas: PIX R$ 0,99 | Cartão 2,99% + R$ 0,49</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        <form wire:submit="save">
            {{ $this->form }}

            <div class="flex justify-end gap-3 mt-6">
                <x-filament::button type="submit" size="lg">
                    @if(tenant()->asaas_account_id)
                        Atualizar Dados
                    @else
                        Criar Conta de Recebimentos
                    @endif
                </x-filament::button>
            </div>
        </form>

        <!-- Ajuda -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mt-6">
            <h3 class="text-sm font-medium text-gray-900 mb-2">📋 Documentos Necessários</h3>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>• CPF ou CNPJ válido</li>
                <li>• Comprovante de endereço atualizado</li>
                <li>• Dados bancários corretos (mesma titularidade do CPF/CNPJ)</li>
            </ul>

            <h3 class="text-sm font-medium text-gray-900 mb-2 mt-4">💰 Quanto você recebe?</h3>
            <div class="text-sm text-gray-700">
                <p><strong>Exemplo:</strong> Pedido de R$ 100,00</p>
                <ul class="ml-4 mt-2 space-y-1">
                    <li>→ Você recebe: <strong class="text-green-600">R$ 97,00</strong></li>
                    <li>→ Comissão plataforma: R$ 3,00</li>
                    <li>→ Taxa Asaas PIX: R$ 0,99 (descontada do seu valor)</li>
                    <li>→ <strong>Líquido na sua conta: R$ 96,01</strong></li>
                </ul>
            </div>

            <h3 class="text-sm font-medium text-gray-900 mb-2 mt-4">⏰ Quando recebo?</h3>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>• <strong>PIX:</strong> D+30 (30 dias após confirmação)</li>
                <li>• <strong>Cartão de Crédito:</strong> D+1 (1 dia após confirmação)</li>
                <li>• <strong>Dinheiro:</strong> Você já recebe na entrega</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
