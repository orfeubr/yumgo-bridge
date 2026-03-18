@props(['restaurant', 'size' => 'md'])

@php
    // Configurações de tamanho
    $sizes = [
        'sm' => 'h-32',
        'md' => 'h-40',
        'lg' => 'h-48',
        'xl' => 'h-56',
    ];
    $height = $sizes[$size] ?? $sizes['md'];

    // Ícones minimalistas de restaurante (variação baseada no nome)
    $icons = [
        // Garfo e faca
        '<path d="M8.1 13.34l2.83-2.83L3.91 3.5c-1.56 1.56-1.56 4.09 0 5.66l4.19 4.18zm6.78-1.81c1.53.71 3.68.21 5.27-1.38 1.91-1.91 2.28-4.65.81-6.12-1.46-1.46-4.2-1.1-6.12.81-1.59 1.59-2.09 3.74-1.38 5.27L3.7 19.87l1.41 1.41L12 14.41l6.88 6.88 1.41-1.41L13.41 13l1.47-1.47z"/>',

        // Prato
        '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><circle cx="12" cy="12" r="3"/>',

        // Chef hat
        '<path d="M18.06 23h1.66c.84 0 1.53-.64 1.63-1.47L23 5.05h-5V1h-1.97v4.05h-4.97l.3 2.34c1.71.47 3.31 1.32 4.27 2.26 1.44 1.42 2.43 2.89 2.43 5.29V23zM1 22v1h15.03v-1c0-1.66-1.34-3-3-3h-9c-1.66 0-3 1.34-3 3zm0-5h15.03v1H1v-1z"/>',

        // Pizza
        '<path d="M12 2C8.43 2 5.23 3.54 3.01 6L12 22l8.99-16C18.78 3.55 15.57 2 12 2zM7 7c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm5 8c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>',

        // Hamburguer
        '<path d="M1 21h22v-2H1v2zm0-4h22v-2H1v2zm0-4h22V9H1v4zm0-6h22V5H1v2z"/>',
    ];

    // Escolher ícone baseado no hash do nome (consistente)
    $iconIndex = abs(crc32($restaurant->name)) % count($icons);
    $icon = $icons[$iconIndex];
@endphp

<div class="relative {{ $height }} overflow-hidden bg-gray-100">
    @if($restaurant->logo)
        {{-- Tem logo: mostrar imagem --}}
        <img src="{{ $restaurant->logo_url }}"
             alt="{{ $restaurant->name }}"
             class="w-full h-full object-cover"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

        {{-- Fallback se imagem falhar ao carregar --}}
        <div class="w-full h-full bg-gray-50 flex items-center justify-center" style="display:none;">
            <svg class="w-20 h-20 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        </div>
    @else
        {{-- Sem logo: placeholder minimalista cinza claro --}}
        <div class="w-full h-full bg-gray-50 flex items-center justify-center">
            {{-- Ícone minimalista de restaurante --}}
            <svg class="w-20 h-20 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        </div>
    @endif

    {{ $slot }}
</div>
