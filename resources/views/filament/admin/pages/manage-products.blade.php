<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Seletor de Restaurante -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Selecione um Restaurante</h3>
            <x-filament-panels::form wire:submit="save">
                {{ $this->form }}
            </x-filament-panels::form>
        </div>

        <!-- Tabela de Produtos -->
        @if($selectedTenantId)
            <div class="bg-white rounded-lg shadow">
                {{ $this->table }}
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <p class="text-yellow-800 font-semibold">Selecione um restaurante para gerenciar seus produtos</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
