<x-filament-panels::page>
    <div class="space-y-6">

        {{-- AVISO IMPORTANTE (Compacto) --}}
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4 rounded-lg">
            <div class="flex gap-3">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-yellow-600 flex-shrink-0" />
                <div>
                    <p class="font-semibold text-yellow-900 dark:text-yellow-200">
                        Esta página gera credenciais. Configurar impressoras é no app desktop.
                    </p>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                        Navegadores não acessam impressoras USB por segurança.
                    </p>
                </div>
            </div>
        </div>

        {{-- FLUXO SIMPLES --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Passo 1 --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border-2 border-blue-200 dark:border-blue-800">
                <div class="text-center mb-3">
                    <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto">
                        1
                    </div>
                </div>
                <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-center mb-2">
                    Baixar App
                </h4>
                <div class="space-y-2">
                    <a href="https://github.com/orfeubr/yumgo/releases/latest"
                       target="_blank"
                       class="block w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-lg text-sm font-medium transition">
                        🪟 Windows
                    </a>
                    <a href="https://github.com/orfeubr/yumgo/releases/latest"
                       target="_blank"
                       class="block w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-lg text-sm font-medium transition">
                        🍎 macOS
                    </a>
                    <a href="https://github.com/orfeubr/yumgo/releases/latest"
                       target="_blank"
                       class="block w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-lg text-sm font-medium transition">
                        🐧 Linux
                    </a>
                </div>
            </div>

            {{-- Passo 2 --}}
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border-2 border-green-200 dark:border-green-800">
                <div class="text-center mb-3">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto">
                        2
                    </div>
                </div>
                <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-center mb-3">
                    Copiar Credenciais
                </h4>

                {{-- ID do Restaurante --}}
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        ID do Restaurante
                    </label>
                    <div class="flex gap-2">
                        <input type="text"
                               value="{{ tenant('id') }}"
                               readonly
                               class="flex-1 px-2 py-1.5 text-xs font-mono bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded"
                        >
                        <button type="button"
                                onclick="navigator.clipboard.writeText('{{ tenant('id') }}');
                                         new FilamentNotification().title('Copiado!').success().send();"
                                class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded text-xs font-medium">
                            📋
                        </button>
                    </div>
                </div>

                {{-- Token --}}
                <div>
                    @if($hasToken)
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Token Ativo
                        </label>
                        <div class="flex gap-2">
                            <div class="flex-1 px-2 py-1.5 bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 rounded">
                                <span class="text-xs text-green-700 dark:text-green-300 font-medium">✓ Configurado</span>
                            </div>
                            <button type="button"
                                    onclick="if(confirm('Revogar token? O app será desconectado.')) window.location.href = '?revokeToken=1';"
                                    class="px-3 py-1.5 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 rounded text-xs font-medium">
                                ✕
                            </button>
                        </div>
                    @else
                        <button type="button"
                                onclick="window.location.href = '?generateToken=1';"
                                class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold">
                            🔑 Gerar Token
                        </button>
                    @endif
                </div>
            </div>

            {{-- Passo 3 --}}
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border-2 border-purple-200 dark:border-purple-800">
                <div class="text-center mb-3">
                    <div class="w-10 h-10 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto">
                        3
                    </div>
                </div>
                <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-center mb-3">
                    Configurar no App
                </h4>
                <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                    <div class="flex items-start gap-2">
                        <span class="text-purple-600">1.</span>
                        <span>Cole ID e Token no app</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-purple-600">2.</span>
                        <span>Clique "Conectar"</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-purple-600">3.</span>
                        <span>Configure impressoras</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-purple-600">4.</span>
                        <span>Pronto! 🎉</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- DÚVIDAS COMUNS (Colapsável) --}}
        <details class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <summary class="px-4 py-3 cursor-pointer font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                ❓ Dúvidas Frequentes
            </summary>
            <div class="px-4 pb-4 pt-2 space-y-3 text-sm">
                <div>
                    <p class="font-medium text-gray-900 dark:text-gray-100 mb-1">
                        Por que preciso baixar um app?
                    </p>
                    <p class="text-gray-600 dark:text-gray-400">
                        Navegadores não podem acessar impressoras USB por segurança. O app instalado no seu computador tem acesso total às impressoras.
                    </p>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-gray-100 mb-1">
                        Preciso deixar o navegador aberto?
                    </p>
                    <p class="text-gray-600 dark:text-gray-400">
                        Não! Pode fechar o navegador. Só precisa deixar o app desktop aberto (fica na bandeja do sistema).
                    </p>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-gray-100 mb-1">
                        Como imprimir pedidos antigos?
                    </p>
                    <p class="text-gray-600 dark:text-gray-400">
                        No painel de pedidos, clique no pedido e use o botão "Reimprimir".
                    </p>
                </div>
            </div>
        </details>

        {{-- AJUDA --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700">
            <div class="flex items-center gap-3">
                <x-heroicon-o-question-mark-circle class="w-6 h-6 text-blue-600" />
                <div class="flex-1">
                    <p class="font-medium text-gray-900 dark:text-gray-100">
                        Precisa de ajuda?
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Consulte o <a href="https://github.com/orfeubr/yumgo/blob/master/electron-bridge/GUIA-USUARIO.md" target="_blank" class="text-blue-600 hover:text-blue-700 underline">guia completo</a> ou entre em contato com o suporte.
                    </p>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
