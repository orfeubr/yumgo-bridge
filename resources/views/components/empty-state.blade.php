{{--
    Empty State (quando não há dados)
    Uso: <x-empty-state title="Carrinho vazio" message="Adicione produtos para continuar" icon="shopping-cart" />
--}}

@props(['title', 'message', 'icon' => 'inbox', 'actionText' => '', 'actionUrl' => ''])

<div class="flex flex-col items-center justify-center py-12 px-4 text-center">
    {{-- Ícone SVG animado --}}
    <div class="mb-6 relative">
        <div class="absolute inset-0 bg-red-100 rounded-full blur-2xl opacity-50 animate-pulse"></div>
        <div class="relative w-24 h-24 bg-gradient-to-br from-red-50 to-red-100 rounded-full flex items-center justify-center">
            @if($icon === 'shopping-cart')
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            @elseif($icon === 'inbox')
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            @elseif($icon === 'search')
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            @endif
        </div>
    </div>

    {{-- Título --}}
    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $title }}</h3>

    {{-- Mensagem --}}
    <p class="text-gray-600 max-w-sm mb-6">{{ $message }}</p>

    {{-- Ação (opcional) --}}
    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}" class="inline-flex items-center px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors">
            {{ $actionText }}
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </a>
    @endif
</div>
