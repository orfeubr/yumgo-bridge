<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Info Card -->
        <div class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-gray-800 dark:to-gray-700 border-2 border-primary-200 dark:border-primary-900 rounded-xl p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-primary-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">🎨 Identidade Visual</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Configure a logo, favicon e cores da sua plataforma. As alterações aparecerão no painel admin após salvar.</p>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        {{ $this->form }}

        <!-- Dicas -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <h4 class="font-bold text-blue-900 dark:text-blue-200 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                💡 Dicas de Design
            </h4>
            <ul class="text-sm text-blue-800 dark:text-blue-300 space-y-1.5">
                <li>• <strong>Logo:</strong> Use PNG com fundo transparente para melhor resultado</li>
                <li>• <strong>Dimensões:</strong> Logo ideal: 200x50px (ou proporção 4:1)</li>
                <li>• <strong>Favicon:</strong> Ícone simples e reconhecível funciona melhor (32x32px ou 64x64px)</li>
                <li>• <strong>Cores:</strong> Use cores que combinem com sua marca</li>
                <li>• <strong>Formatos:</strong> PNG, JPG, SVG para logo | PNG, ICO para favicon</li>
            </ul>
        </div>

        <!-- Status -->
        <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <strong>Como aplicar:</strong> Após fazer upload dos arquivos, clique no botão "Salvar Alterações" no canto superior direito.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
