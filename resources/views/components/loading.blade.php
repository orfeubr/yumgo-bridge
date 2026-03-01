{{--
    Loading Padrão (agora usa o novo loading-spinner)
    Uso: <x-loading /> ou <x-loading>Mensagem customizada</x-loading>
--}}
<div {{ $attributes->merge(['class' => 'bg-white rounded-lg border border-gray-200 p-12 text-center']) }}>
    <x-loading-spinner size="lg" />
    <p class="text-sm text-gray-500 mt-4">{{ $slot->isEmpty() ? 'Carregando...' : $slot }}</p>
</div>
