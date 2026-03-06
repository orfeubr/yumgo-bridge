<x-filament-panels::page>
    <div class="space-y-6">
        {{-- EXPLICAÇÃO VISUAL DA MECÂNICA --}}
        <div class="rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-6 border-2 border-blue-300 dark:border-blue-700 shadow-lg">
            <div class="space-y-6">
                {{-- Título --}}
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-12 h-12 bg-blue-600 dark:bg-blue-500 rounded-full flex items-center justify-center">
                        <x-heroicon-o-question-mark-circle class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-blue-900 dark:text-blue-200">
                            Como Funciona a Impressão Automática?
                        </h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Entenda o sistema em 2 minutos
                        </p>
                    </div>
                </div>

                {{-- Alerta Principal --}}
                <div class="bg-yellow-100 dark:bg-yellow-900/30 border-l-4 border-yellow-500 p-4 rounded">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                        <div>
                            <p class="font-semibold text-yellow-900 dark:text-yellow-200 text-base mb-1">
                                ⚠️ Esta página NÃO configura impressoras!
                            </p>
                            <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                Navegadores (Chrome, Firefox) <strong>não podem acessar</strong> impressoras USB do seu computador por segurança.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Fluxo Visual --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg p-5 border border-gray-200 dark:border-gray-700">
                    <p class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <span class="text-2xl">🔄</span>
                        <span>Como Funciona (Passo a Passo):</span>
                    </p>

                    <div class="space-y-4">
                        {{-- Passo 1 --}}
                        <div class="flex gap-4 items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                                1
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    🌐 Nesta página (Painel Web)
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Você <strong>gera credenciais</strong> (ID + Token) e <strong>baixa o app</strong>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                    ❌ Não vê impressoras (navegador não pode!)
                                </p>
                            </div>
                        </div>

                        {{-- Passo 2 --}}
                        <div class="flex gap-4 items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">
                                2
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    💻 No Computador (App YumGo Bridge)
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Você <strong>instala o app</strong>, cola as credenciais e <strong>configura impressoras USB/Rede</strong>
                                </p>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                    ✅ App vê impressoras locais! (instalado no PC)
                                </p>
                            </div>
                        </div>

                        {{-- Passo 3 --}}
                        <div class="flex gap-4 items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold">
                                3
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    🎉 Cliente Faz Pedido Pago
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Sistema envia notificação via internet para o app no seu computador
                                </p>
                                <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">
                                    ✅ App imprime automaticamente!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Analogia --}}
                <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4">
                    <p class="text-sm text-indigo-900 dark:text-indigo-200 font-medium mb-2">
                        💡 <strong>Pense no WhatsApp Web:</strong>
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-indigo-800 dark:text-indigo-300">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🌐</span>
                            <span><strong>WhatsApp Web</strong> = Painel (só interface)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg">📱</span>
                            <span><strong>App Celular</strong> = YumGo Bridge (faz tudo)</span>
                        </div>
                    </div>
                </div>

                {{-- Resumo --}}
                <div class="border-t border-blue-200 dark:border-blue-800 pt-4">
                    <p class="text-sm text-center text-blue-900 dark:text-blue-200 font-medium">
                        📌 <strong>Resumo:</strong> Esta página = gera credenciais | App instalado = vê impressoras e imprime
                    </p>
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
                <a href="https://github.com/orfeubr/yumgo/releases/latest"
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
                            Última versão • ~80 MB
                        </p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1 font-medium">
                            ✨ Sempre atualizado!
                        </p>
                    </div>
                    <div>
                        <x-heroicon-o-arrow-down-tray class="w-6 h-6 text-gray-400 group-hover:text-primary-600" />
                    </div>
                </a>

                {{-- macOS --}}
                <a href="https://github.com/orfeubr/yumgo/releases/latest"
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
                            Última versão • ~90 MB
                        </p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1 font-medium">
                            ✨ Sempre atualizado!
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
