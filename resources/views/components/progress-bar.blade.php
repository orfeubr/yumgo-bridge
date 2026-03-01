{{--
    Progress Bar Animado
    Uso: <x-progress-bar :percent="75" label="Processando pedido..." />
--}}

@props(['percent' => 0, 'label' => ''])

<div class="w-full space-y-2">
    @if($label)
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-700 font-medium">{{ $label }}</span>
            <span class="text-red-600 font-bold">{{ $percent }}%</span>
        </div>
    @endif

    <div class="relative w-full h-3 bg-gray-200 rounded-full overflow-hidden">
        {{-- Barra de progresso --}}
        <div
            class="absolute inset-y-0 left-0 bg-gradient-to-r from-red-500 to-red-600 rounded-full transition-all duration-500 ease-out"
            style="width: {{ $percent }}%"
        >
            {{-- Efeito de brilho --}}
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-30 animate-slide"></div>
        </div>
    </div>
</div>

<style>
@keyframes slide {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.animate-slide {
    animation: slide 1.5s infinite;
}
</style>
