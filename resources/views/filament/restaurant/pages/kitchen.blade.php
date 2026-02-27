<x-filament-panels::page>
    <div class="mb-4 p-4 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-xl border-2 border-orange-200 dark:border-orange-700">
        <div class="flex items-center gap-3">
            <span class="text-4xl">👨‍🍳</span>
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Painel da Cozinha</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Gerencie os pedidos em preparo. A tela atualiza automaticamente a cada 10 segundos.</p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
