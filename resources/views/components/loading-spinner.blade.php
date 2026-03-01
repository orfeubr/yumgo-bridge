{{--
    Loading Spinner YumGo
    Uso: <x-loading-spinner size="md" />
    Tamanhos: sm, md, lg, xl
--}}

@props(['size' => 'md'])

@php
$sizes = [
    'sm' => 'w-4 h-4',
    'md' => 'w-8 h-8',
    'lg' => 'w-12 h-12',
    'xl' => 'w-16 h-16',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="inline-flex items-center justify-center">
    <div class="relative {{ $sizeClass }}">
        {{-- Círculo externo (pulsante) --}}
        <div class="absolute inset-0 border-4 border-red-100 rounded-full animate-pulse"></div>

        {{-- Círculo rotativo --}}
        <div class="absolute inset-0 border-4 border-red-600 rounded-full border-t-transparent animate-spin"></div>

        {{-- Ponto central --}}
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-2 h-2 bg-red-600 rounded-full animate-ping"></div>
        </div>
    </div>
</div>
