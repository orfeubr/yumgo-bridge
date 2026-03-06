<x-filament-panels::page>
    <div class="space-y-6">

        {{-- AVISO --}}
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4 rounded-lg">
            <p class="font-semibold text-yellow-900 dark:text-yellow-200">
                💡 Esta página gera credenciais para conectar o app de impressão
            </p>
        </div>

        {{-- PASSOS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Passo 1: Download --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border-2 border-blue-500">
                <div class="text-center mb-4">
                    <div class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-xl mx-auto mb-2">
                        1
                    </div>
                    <h3 class="font-bold text-lg">Baixar App</h3>
                </div>

                <a href="{{ route('download.bridge') }}"
                   class="block w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-lg font-bold transition">
                    ⬇️ Baixar YumGo Bridge
                </a>
                <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-2">
                    Windows, macOS e Linux
                </p>
            </div>

            {{-- Passo 2: Credenciais --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border-2 border-green-500">
                <div class="text-center mb-4">
                    <div class="w-12 h-12 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-xl mx-auto mb-2">
                        2
                    </div>
                    <h3 class="font-bold text-lg">Copiar Dados</h3>
                </div>

                {{-- ID --}}
                <div class="mb-3">
                    <label class="block text-xs font-medium mb-1">ID do Restaurante</label>
                    <div class="flex gap-2">
                        <input type="text"
                               value="{{ tenant('id') }}"
                               readonly
                               class="flex-1 px-3 py-2 text-sm font-mono bg-gray-50 dark:bg-gray-900 border rounded">
                        <button type="button"
                                onclick="navigator.clipboard.writeText('{{ tenant('id') }}'); new FilamentNotification().title('Copiado!').success().send();"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded font-bold">
                            📋
                        </button>
                    </div>
                </div>

                {{-- Token --}}
                <div>
                    <label class="block text-xs font-medium mb-1">Token de Acesso</label>
                    <div class="flex gap-2">
                        <input type="text"
                               value="{{ $this->printerToken }}"
                               readonly
                               class="flex-1 px-3 py-2 text-sm font-mono bg-gray-50 dark:bg-gray-900 border rounded">
                        <button type="button"
                                onclick="navigator.clipboard.writeText('{{ $this->printerToken }}'); new FilamentNotification().title('Copiado!').success().send();"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded font-bold">
                            📋
                        </button>
                    </div>
                </div>
            </div>

            {{-- Passo 3: Configurar --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border-2 border-purple-500">
                <div class="text-center mb-4">
                    <div class="w-12 h-12 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold text-xl mx-auto mb-2">
                        3
                    </div>
                    <h3 class="font-bold text-lg">Configurar</h3>
                </div>

                <ol class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
                    <li>✅ Abra o YumGo Bridge</li>
                    <li>✅ Cole o ID e Token</li>
                    <li>✅ Clique em Conectar</li>
                    <li>✅ Configure suas impressoras</li>
                </ol>
            </div>

        </div>

        {{-- AJUDA RÁPIDA --}}
        <details class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <summary class="cursor-pointer font-semibold text-gray-900 dark:text-gray-100">
                ❓ Precisa de ajuda?
            </summary>
            <div class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <p><strong>Impressora não conecta?</strong> Verifique se está ligada e conectada ao computador.</p>
                <p><strong>Token inválido?</strong> Copie novamente usando o botão 📋 acima.</p>
                <p><strong>App não abre?</strong> Execute como administrador no Windows.</p>
            </div>
        </details>

    </div>
</x-filament-panels::page>
