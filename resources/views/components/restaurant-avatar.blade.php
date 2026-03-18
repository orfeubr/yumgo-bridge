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

    // Gerar cor consistente baseada no nome do restaurante
    $colors = [
        ['from' => 'from-red-400', 'to' => 'to-red-600', 'text' => 'text-white'],
        ['from' => 'from-orange-400', 'to' => 'to-orange-600', 'text' => 'text-white'],
        ['from' => 'from-amber-400', 'to' => 'to-amber-600', 'text' => 'text-white'],
        ['from' => 'from-emerald-400', 'to' => 'to-emerald-600', 'text' => 'text-white'],
        ['from' => 'from-teal-400', 'to' => 'to-teal-600', 'text' => 'text-white'],
        ['from' => 'from-blue-400', 'to' => 'to-blue-600', 'text' => 'text-white'],
        ['from' => 'from-indigo-400', 'to' => 'to-indigo-600', 'text' => 'text-white'],
        ['from' => 'from-purple-400', 'to' => 'to-purple-600', 'text' => 'text-white'],
        ['from' => 'from-pink-400', 'to' => 'to-pink-600', 'text' => 'text-white'],
    ];

    // Hash consistente do nome para escolher cor
    $colorIndex = abs(crc32($restaurant->name)) % count($colors);
    $color = $colors[$colorIndex];

    // Primeira letra em maiúscula
    $initial = mb_strtoupper(mb_substr($restaurant->name, 0, 1));
@endphp

<div class="relative {{ $height }} overflow-hidden bg-gray-100">
    @if($restaurant->logo)
        {{-- Tem logo: mostrar imagem --}}
        <img src="{{ $restaurant->logo_url }}"
             alt="{{ $restaurant->name }}"
             class="w-full h-full object-cover"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

        {{-- Fallback se imagem falhar ao carregar --}}
        <div class="w-full h-full bg-gradient-to-br {{ $color['from'] }} {{ $color['to'] }} flex items-center justify-center" style="display:none;">
            <div class="text-center">
                <div class="{{ $color['text'] }} text-6xl font-bold opacity-90">
                    {{ $initial }}
                </div>
                <div class="{{ $color['text'] }} text-xs font-medium opacity-75 mt-2 px-4">
                    {{ $restaurant->name }}
                </div>
            </div>
        </div>
    @else
        {{-- Sem logo: placeholder elegante com inicial --}}
        <div class="w-full h-full bg-gradient-to-br {{ $color['from'] }} {{ $color['to'] }} flex items-center justify-center">
            <div class="text-center">
                {{-- Ícone de restaurante --}}
                <div class="{{ $color['text'] }} opacity-20 mb-2">
                    <svg class="w-16 h-16 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/>
                    </svg>
                </div>

                {{-- Inicial do restaurante --}}
                <div class="{{ $color['text'] }} text-6xl font-bold opacity-90">
                    {{ $initial }}
                </div>

                {{-- Nome do restaurante --}}
                <div class="{{ $color['text'] }} text-xs font-medium opacity-75 mt-2 px-4 line-clamp-2">
                    {{ $restaurant->name }}
                </div>
            </div>
        </div>
    @endif

    {{ $slot }}
</div>
