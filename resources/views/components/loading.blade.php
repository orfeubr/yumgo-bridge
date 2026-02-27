<!-- Loading Padrão -->
<div {{ $attributes->merge(['class' => 'bg-white rounded-lg border border-gray-200 p-12 text-center']) }}>
    <div class="animate-spin w-12 h-12 border-3 border-gray-300 border-t-gray-900 rounded-full mx-auto mb-4"></div>
    <p class="text-sm text-gray-500">{{ $slot->isEmpty() ? 'Carregando...' : $slot }}</p>
</div>
