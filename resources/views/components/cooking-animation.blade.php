{{--
    Animação de Preparando Pedido (Versão Melhorada - Clara e Reconhecível)
    Uso: <x-cooking-animation />
--}}

<div class="relative w-32 h-32">
    <!-- Círculo animado com prato -->
    <div class="absolute inset-0 flex items-center justify-center">
        <!-- Pulsação de fundo -->
        <div class="absolute inset-0 bg-red-500/20 rounded-full animate-ping"></div>

        <!-- Círculo sólido com prato -->
        <div class="relative w-24 h-24 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center shadow-2xl animate-pulse-slow">
            <!-- Emoji do prato girando -->
            <span class="text-5xl animate-spin-slow">🍽️</span>
        </div>
    </div>
</div>

<style>
/* Pulsação lenta e suave */
@keyframes pulse-slow {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 10px 40px rgba(239, 68, 68, 0.3);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 15px 50px rgba(239, 68, 68, 0.5);
    }
}

.animate-pulse-slow {
    animation: pulse-slow 2s ease-in-out infinite;
}

/* Rotação lenta do emoji */
@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin-slow {
    display: inline-block;
    animation: spin-slow 3s linear infinite;
}
</style>
