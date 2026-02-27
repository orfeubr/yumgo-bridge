<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#EA1D2C">

    <title>@yield('title', 'YumGo') - {{ $tenant->name }}</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon-192.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#FEF2F2',
                            100: '#FEE2E2',
                            500: '#EA1D2C',
                            600: '#DC2626',
                            700: '#B91C1C',
                        },
                        secondary: {
                            50: '#F7F7F7',
                            100: '#E5E5E5',
                            200: '#CCCCCC',
                            300: '#999999',
                            500: '#666666',
                            700: '#333333',
                            900: '#1A1A1A',
                        }
                    },
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }

        body {
            padding-bottom: env(safe-area-inset-bottom);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        }

        ::-webkit-scrollbar {
            display: none;
        }

        html {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        body {
            overscroll-behavior-y: contain;
        }

        /* Transições suaves */
        .transition-smooth {
            transition: all 0.2s ease-in-out;
        }

        /* Card hover effect */
        .card-hover:active {
            transform: scale(0.98);
        }

        /* Bottom nav */
        .nav-item-active {
            color: #EA1D2C;
        }

        .nav-item-inactive {
            color: #999999;
        }

        /* Modal scroll fix para iOS */
        .overscroll-contain {
            overscroll-behavior: contain;
        }

        /* Prevenir zoom em inputs iOS */
        input, textarea, select {
            font-size: 16px !important;
        }

        /* Active state para botões */
        .active\:scale-95:active {
            transform: scale(0.95);
        }

        .active\:scale-98:active {
            transform: scale(0.98);
        }

        /* Scroll suave em modals */
        .overflow-y-auto {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Suporte para svh (small viewport height) */
        @supports (height: 100svh) {
            .h-screen {
                height: 100svh;
            }
        }

        /* Ocultar scrollbar mas manter funcionalidade */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Animação de fade in down para toast */
        @keyframes fade-in-down {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        .animate-fade-in-down {
            animation: fade-in-down 0.3s ease-out;
        }

        /* Line clamp para truncar texto */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Melhorar performance de animações */
        .card-hover,
        .transition-smooth {
            will-change: transform;
        }

        /* Desktop: esconder bottom nav */
        @media (min-width: 768px) {
            body {
                padding-bottom: 0 !important;
            }
        }
    </style>

    @yield('styles')
</head>
<body class="bg-white">
    <!-- Content -->
    <div class="min-h-screen pb-16">
        @yield('content')
    </div>

    <!-- Bottom Navigation - Estilo iFood (apenas mobile) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50"
         style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="flex items-center justify-around h-14 max-w-screen-lg mx-auto px-2">
            <!-- Início -->
            <a href="/" class="flex flex-col items-center justify-center flex-1 py-2 transition-smooth {{ request()->is('/') ? 'nav-item-active' : 'nav-item-inactive' }}">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-xs font-medium">Início</span>
            </a>

            <!-- Pedidos -->
            <a href="/meus-pedidos" class="flex flex-col items-center justify-center flex-1 py-2 transition-smooth {{ request()->is('meus-pedidos') || request()->is('pedido*') ? 'nav-item-active' : 'nav-item-inactive' }}">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span class="text-xs font-medium">Pedidos</span>
            </a>

            <!-- Perfil -->
            <a href="/perfil" class="flex flex-col items-center justify-center flex-1 py-2 transition-smooth {{ request()->is('perfil') || request()->is('cashback') ? 'nav-item-active' : 'nav-item-inactive' }}">
                <svg class="w-6 h-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-xs font-medium">Perfil</span>
            </a>
        </div>
    </nav>

    <!-- PWA Install Banner -->
    <div id="installBanner" class="hidden fixed top-0 left-0 right-0 bg-white border-b border-gray-200 z-50 shadow-lg">
        <div class="flex items-center justify-between p-4 max-w-screen-lg mx-auto">
            <div class="flex items-center flex-1">
                <svg class="w-10 h-10 text-primary-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <div class="flex-1">
                    <p class="font-bold text-sm text-gray-900">Instalar App</p>
                    <p class="text-xs text-gray-600">Acesso rápido e offline</p>
                </div>
            </div>
            <button onclick="installApp()" class="px-4 py-2 bg-primary-500 text-white text-sm font-bold rounded-lg transition-smooth hover:bg-primary-600 mr-2">
                Instalar
            </button>
            <button onclick="dismissInstall()" class="p-2 text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <script>
        // Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('SW registered'))
                .catch(err => console.log('SW error:', err));
        }

        // Install Prompt
        let deferredPrompt;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;

            const banner = document.getElementById('installBanner');
            if (banner && !localStorage.getItem('installDismissed')) {
                banner.classList.remove('hidden');
            }
        });

        function installApp() {
            const banner = document.getElementById('installBanner');
            banner.classList.add('hidden');

            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    deferredPrompt = null;
                });
            }
        }

        function dismissInstall() {
            document.getElementById('installBanner').classList.add('hidden');
            localStorage.setItem('installDismissed', 'true');
        }

        // Haptic feedback
        function vibrate(duration = 5) {
            if ('vibrate' in navigator) {
                navigator.vibrate(duration);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('button, a').forEach(el => {
                el.addEventListener('touchstart', () => vibrate(5), { passive: true });
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
