{{--
    Skeleton Loading Card (para produtos, etc)
    Uso: <x-skeleton-card />
--}}

<div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 animate-pulse">
    {{-- Imagem --}}
    <div class="w-full h-48 bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 bg-[length:200%_100%] animate-shimmer"></div>

    {{-- Conteúdo --}}
    <div class="p-4 space-y-3">
        {{-- Título --}}
        <div class="h-5 bg-gray-200 rounded w-3/4"></div>

        {{-- Descrição --}}
        <div class="space-y-2">
            <div class="h-3 bg-gray-200 rounded w-full"></div>
            <div class="h-3 bg-gray-200 rounded w-5/6"></div>
        </div>

        {{-- Preço e botão --}}
        <div class="flex items-center justify-between pt-2">
            <div class="h-6 bg-gray-200 rounded w-20"></div>
            <div class="h-10 bg-gray-200 rounded-full w-28"></div>
        </div>
    </div>
</div>

<style>
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.animate-shimmer {
    animation: shimmer 2s infinite;
}
</style>
