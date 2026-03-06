<x-filament-panels::page>
    <div class="space-y-6">
        {{-- ALERTA IMPORTANTE --}}
        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-6 border-2 border-blue-300 dark:border-blue-700">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <x-heroicon-o-information-circle class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-200 mb-2">
                        ℹ️ Como Funciona a Impressão Automática
                    </h3>
                    <div class="text-sm text-blue-800 dark:text-blue-300 space-y-2">
                        <p>
                            <strong>⚠️ Esta página NÃO configura impressoras!</strong>
                        </p>
                        <p>
                            As impressoras são configuradas no <strong>app YumGo Bridge</strong> instalado no seu computador.
                            Aqui você apenas obtém as credenciais de acesso.
                        </p>
                        <div class="mt-3 p-3 bg-white dark:bg-blue-950/50 rounded border border-blue-200 dark:border-blue-800">
                            <p class="font-medium mb-2">📱 Fluxo correto:</p>
                            <ol class="list-decimal list-inside space-y-1 text-xs">
                                <li>Gere o <strong>Token de Acesso</strong> abaixo</li>
                                <li>Baixe e instale o <strong>YumGo Bridge</strong> no computador</li>
                                <li>Cole as credenciais (ID + Token) no app</li>
                                <li>Configure suas impressoras <strong>dentro do app</strong></li>
                                <li>Pronto! Pedidos pagos imprimem automaticamente 🎉</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informações do Restaurante --}}
        <x-filament::section>
            <x-slot name="heading">
                🔑 Credenciais de Acesso (Para o App)
            </x-slot>

            <x-slot name="description">
                Copie estas informações e cole no app YumGo Bridge
            </x-slot>

            <div class="space-y-4">
                {{-- ID do Restaurante --}}
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        ID do Restaurante
                    </label>
                    <div class="mt-1 flex gap-2">
                        <input
                            type="text"
                            value="{{ $this->getRestaurantId() }}"
                            readonly
                            class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 font-mono text-sm"
                            id="restaurant-id"
                        >
                        <x-filament::button
                            color="gray"
                            icon="heroicon-o-clipboard-document"
                            x-on:click="
                                navigator.clipboard.writeText('{{ $this->getRestaurantId() }}');
                                $tooltip('Copiado!', { timeout: 2000 });
                            "
                        >
                            Copiar
                        </x-filament::button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Cole este ID no app YumGo Bridge
                    </p>
                </div>

                {{-- Nome do Restaurante --}}
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nome do Restaurante
                    </label>
                    <input
                        type="text"
                        value="{{ $this->getRestaurantName() }}"
                        readonly
                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800"
                    >
                </div>
            </div>
        </x-filament::section>

        {{-- Token de Acesso --}}
        <x-filament::section>
            <x-slot name="heading">
                🔑 Token de Acesso
            </x-slot>

            <x-slot name="description">
                Gere um token para conectar o app YumGo Bridge
            </x-slot>

            <div class="space-y-4">
                @if(session('new_token'))
                    {{-- Mostra token recém-criado --}}
                    <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-4 border border-yellow-200 dark:border-yellow-800">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-600 dark:text-yellow-500" />
                            </div>
                            <div class="flex-1 space-y-2">
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                                    ⚠️ Copie este token AGORA! Ele só será exibido uma vez.
                                </p>

                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        value="{{ session('new_token') }}"
                                        readonly
                                        class="flex-1 rounded-lg border-yellow-300 dark:border-yellow-700 dark:bg-yellow-900/30 font-mono text-sm"
                                        id="new-token"
                                    >
                                    <x-filament::button
                                        color="warning"
                                        icon="heroicon-o-clipboard-document"
                                        x-on:click="
                                            navigator.clipboard.writeText('{{ session('new_token') }}');
                                            $tooltip('Token copiado!', { timeout: 2000 });
                                        "
                                    >
                                        Copiar Token
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($this->hasActiveToken())
                    {{-- Token ativo --}}
                    <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 dark:text-green-500" />
                                <div>
                                    <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                        Token ativo
                                    </p>
                                    <p class="text-xs text-green-600 dark:text-green-400">
                                        Criado {{ $this->getTokenCreatedAt() }}
                                    </p>
                                </div>
                            </div>

                            <x-filament::button
                                color="danger"
                                outlined
                                size="sm"
                                wire:click="revokeToken"
                                wire:confirm="Tem certeza? O app será desconectado."
                            >
                                Revogar Token
                            </x-filament::button>
                        </div>
                    </div>
                @else
                    {{-- Nenhum token --}}
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700">
                        <div class="text-center space-y-3">
                            <div>
                                <x-heroicon-o-key class="w-12 h-12 mx-auto text-gray-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nenhum token ativo
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Gere um token para conectar o app YumGo Bridge
                                </p>
                            </div>
                            <div>
                                <x-filament::button
                                    color="primary"
                                    icon="heroicon-o-plus"
                                    wire:click="generateToken"
                                >
                                    Gerar Token de Acesso
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::section>

        {{-- Download do App --}}
        <x-filament::section>
            <x-slot name="heading">
                📥 Download do YumGo Bridge
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Windows --}}
                <a href="https://github.com/orfeubr/yumgo/releases/latest/download/YumGo-Bridge-1.7.0-win-x64.exe"
                   target="_blank"
                   class="flex items-center gap-4 p-6 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 hover:bg-gray-50 dark:hover:bg-gray-800 transition group">
                    <div class="flex-shrink-0 text-4xl">
                        🪟
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400">
                            Windows
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Windows 10/11 (64-bit)
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            Versão 1.7.0 • ~80 MB
                        </p>
                    </div>
                    <div>
                        <x-heroicon-o-arrow-down-tray class="w-6 h-6 text-gray-400 group-hover:text-primary-600" />
                    </div>
                </a>

                {{-- macOS --}}
                <a href="https://github.com/orfeubr/yumgo/releases/latest/download/YumGo-Bridge-1.7.0-mac.dmg"
                   target="_blank"
                   class="flex items-center gap-4 p-6 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 hover:bg-gray-50 dark:hover:bg-gray-800 transition group">
                    <div class="flex-shrink-0 text-4xl">
                        🍎
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400">
                            macOS
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Intel / Apple Silicon
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            Versão 1.7.0 • ~90 MB
                        </p>
                    </div>
                    <div>
                        <x-heroicon-o-arrow-down-tray class="w-6 h-6 text-gray-400 group-hover:text-primary-600" />
                    </div>
                </a>
            </div>

            <div class="mt-4 text-center">
                <a href="https://github.com/orfeubr/yumgo/releases" target="_blank" class="text-sm text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">
                    Ver todas as versões →
                </a>
            </div>
        </x-filament::section>

        {{-- Como Usar --}}
        <x-filament::section>
            <x-slot name="heading">
                📱 Passo a Passo
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <ol class="space-y-3">
                    <li>
                        <strong>Baixe o app</strong> clicando no botão acima (Windows ou macOS)
                    </li>
                    <li>
                        <strong>Instale o app</strong> no computador que está conectado à impressora
                    </li>
                    <li>
                        <strong>Abra o app</strong> e cole as credenciais acima
                    </li>
                    <li>
                        <strong>Configure sua impressora</strong> (USB ou Rede)
                    </li>
                    <li>
                        <strong>Pronto!</strong> Os pedidos serão impressos automaticamente
                    </li>
                </ol>

                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <strong>💡 Dica:</strong> Mantenha o app aberto para receber pedidos em tempo real.
                        Você pode minimizá-lo para a bandeja do sistema.
                    </p>
                </div>
            </div>
        </x-filament::section>

        {{-- Suporte --}}
        <x-filament::section>
            <x-slot name="heading">
                🆘 Precisa de Ajuda?
            </x-slot>

            <div class="space-y-3">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Em caso de dúvidas ou problemas:
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="mailto:suporte@yumgo.com.br" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        <x-heroicon-o-envelope class="w-6 h-6 text-gray-400" />
                        <div>
                            <p class="text-sm font-medium">Email</p>
                            <p class="text-xs text-gray-500">suporte@yumgo.com.br</p>
                        </div>
                    </a>

                    <a href="https://wa.me/5511999999999" target="_blank" class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        <x-heroicon-o-phone class="w-6 h-6 text-gray-400" />
                        <div>
                            <p class="text-sm font-medium">WhatsApp</p>
                            <p class="text-xs text-gray-500">(11) 99999-9999</p>
                        </div>
                    </a>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
