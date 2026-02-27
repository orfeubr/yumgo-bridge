<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold">📦 Gestão Simplificada de Estoque</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Clique nos campos para editar rapidamente. O estoque é abatido automaticamente ao finalizar pedidos.
                </p>
            </div>

            {{ $this->table }}
        </div>

        <!-- Legenda -->
        <div class="bg-blue-50 dark:bg-blue-900/10 rounded-lg p-4">
            <h3 class="font-semibold text-sm mb-2">💡 Dicas Rápidas:</h3>
            <ul class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
                <li>✅ <strong>Controla?</strong> - Clique para ativar/desativar controle de estoque</li>
                <li>📊 <strong>Estoque Atual</strong> - Clique para editar a quantidade disponível</li>
                <li>⚠️ <strong>Estoque Mínimo</strong> - Define quando mostrar alerta de estoque baixo</li>
                <li>🛒 <strong>Abatimento Automático</strong> - Estoque reduz automaticamente ao finalizar pedido no PDV</li>
                <li>📦 <strong>Ações em Massa</strong> - Selecione vários produtos para ajustar de uma vez</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
