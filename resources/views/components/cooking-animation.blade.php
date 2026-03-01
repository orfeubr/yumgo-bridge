{{--
    Animação de Cozinha (Chef preparando pedido)
    Uso: <x-cooking-animation />
--}}

<div class="relative w-32 h-32">
    <!-- Prato/Panela -->
    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-24 h-8 bg-gradient-to-b from-red-600 to-red-700 rounded-full shadow-lg animate-wobble">
        <!-- Borda do prato -->
        <div class="absolute -top-1 left-0 right-0 h-2 bg-red-500 rounded-full"></div>
    </div>

    <!-- Comida no prato (saltando) -->
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 w-12 h-12 animate-bounce-food">
        <!-- Bife/Carne -->
        <div class="absolute bottom-0 w-10 h-6 bg-amber-700 rounded-lg shadow-md transform rotate-12"></div>
        <!-- Batata/Acompanhamento -->
        <div class="absolute bottom-1 right-0 w-5 h-5 bg-yellow-500 rounded-full shadow-sm"></div>
        <!-- Salada/Verde -->
        <div class="absolute bottom-1 left-0 w-4 h-4 bg-green-500 rounded-sm transform -rotate-12"></div>
    </div>

    <!-- Vapor 1 -->
    <div class="absolute bottom-12 left-1/2 -translate-x-1/2 w-3 h-8 animate-steam-1">
        <div class="w-full h-full bg-gradient-to-t from-gray-300/60 to-transparent rounded-full blur-sm"></div>
    </div>

    <!-- Vapor 2 -->
    <div class="absolute bottom-14 left-1/2 -translate-x-1/2 -ml-4 w-3 h-10 animate-steam-2">
        <div class="w-full h-full bg-gradient-to-t from-gray-300/50 to-transparent rounded-full blur-sm"></div>
    </div>

    <!-- Vapor 3 -->
    <div class="absolute bottom-14 left-1/2 -translate-x-1/2 ml-4 w-3 h-10 animate-steam-3">
        <div class="w-full h-full bg-gradient-to-t from-gray-300/50 to-transparent rounded-full blur-sm"></div>
    </div>

    <!-- Colher/Espátula (mexendo) -->
    <div class="absolute top-0 right-4 w-1.5 h-20 bg-gray-700 rounded-full animate-stir origin-bottom">
        <!-- Cabo da colher -->
        <div class="absolute -top-3 -left-1 w-4 h-4 bg-gray-600 rounded-full"></div>
    </div>
</div>

<style>
/* Animação de balanço do prato */
@keyframes wobble {
    0%, 100% { transform: translateX(-50%) rotate(-1deg); }
    50% { transform: translateX(-50%) rotate(1deg); }
}

.animate-wobble {
    animation: wobble 1s ease-in-out infinite;
}

/* Animação da comida saltando */
@keyframes bounce-food {
    0%, 100% {
        transform: translate(-50%, 0);
    }
    50% {
        transform: translate(-50%, -8px);
    }
}

.animate-bounce-food {
    animation: bounce-food 0.6s ease-in-out infinite;
}

/* Vapor subindo */
@keyframes steam-1 {
    0% {
        transform: translate(-50%, 0) scale(1);
        opacity: 0.6;
    }
    100% {
        transform: translate(-50%, -30px) scale(1.5);
        opacity: 0;
    }
}

@keyframes steam-2 {
    0% {
        transform: translate(-50%, 0) scale(1);
        opacity: 0.5;
    }
    100% {
        transform: translate(-50%, -35px) scale(1.8);
        opacity: 0;
    }
}

@keyframes steam-3 {
    0% {
        transform: translate(-50%, 0) scale(1);
        opacity: 0.5;
    }
    100% {
        transform: translate(-50%, -32px) scale(1.6);
        opacity: 0;
    }
}

.animate-steam-1 {
    animation: steam-1 2s ease-out infinite;
}

.animate-steam-2 {
    animation: steam-2 2.3s ease-out infinite;
    animation-delay: 0.3s;
}

.animate-steam-3 {
    animation: steam-3 2.1s ease-out infinite;
    animation-delay: 0.6s;
}

/* Colher mexendo */
@keyframes stir {
    0%, 100% { transform: rotate(-15deg); }
    50% { transform: rotate(15deg); }
}

.animate-stir {
    animation: stir 0.8s ease-in-out infinite;
}
</style>
