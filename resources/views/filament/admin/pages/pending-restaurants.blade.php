<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Info Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-blue-900 mb-1">
                        Revise os restaurantes antes de aprovar
                    </h3>
                    <p class="text-sm text-blue-700">
                        Verifique se os dados estão corretos (nome, telefone, email) antes de aprovar.
                        Restaurantes aprovados ficarão visíveis no marketplace.
                    </p>
                </div>
            </div>
        </div>

        <!-- Table -->
        {{ $this->table }}
    </div>
</x-filament-panels::page>
