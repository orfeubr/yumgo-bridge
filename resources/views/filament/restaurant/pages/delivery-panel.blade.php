<x-filament-panels::page>
    <div class="mb-4 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-xl border-2 border-blue-200 dark:border-blue-700">
        <div class="flex items-center gap-3">
            <span class="text-4xl">🚗</span>
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Painel de Entregas</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Gerencie as entregas em andamento. Marque como entregue quando finalizar.</p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
