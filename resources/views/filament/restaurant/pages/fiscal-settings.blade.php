<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end gap-3">
            <x-filament::button type="submit">
                Salvar Configurações
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />

    {{-- Info Card --}}
    <div class="mt-8 p-6 bg-blue-50 dark:bg-blue-950 rounded-lg border border-blue-200 dark:border-blue-800">
        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">
            📋 Como Configurar o Tributa AI
        </h3>

        <div class="space-y-3 text-sm text-blue-800 dark:text-blue-200">
            <div>
                <strong>1. Crie sua conta no Tributa AI:</strong><br>
                Acesse <a href="https://tributa.ai" target="_blank" class="underline">tributa.ai</a> e crie sua conta (tem plano gratuito para testes)
            </div>

            <div>
                <strong>2. Obtenha seu Token API:</strong><br>
                No painel do Tributa AI, vá em Configurações > API e copie seu token
            </div>

            <div>
                <strong>3. Configure o CSC (Código de Segurança):</strong><br>
                O CSC é obrigatório para NFC-e. Obtenha no portal da SEFAZ do seu estado
            </div>

            <div>
                <strong>4. Certificado Digital:</strong><br>
                O Tributa AI gerencia seu certificado digital A1. Faça upload no painel deles.
            </div>

            <div>
                <strong>5. Emissão Automática:</strong><br>
                Assim que configurar, as NFC-e serão emitidas automaticamente quando o pagamento for confirmado! 🎉
            </div>
        </div>

        <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-950 rounded border border-yellow-200 dark:border-yellow-800">
            <strong class="text-yellow-900 dark:text-yellow-100">⚠️ Importante:</strong>
            <p class="text-sm text-yellow-800 dark:text-yellow-200 mt-1">
                Comece sempre no ambiente <strong>Sandbox</strong> para testar. Só mude para Produção após validar que tudo está funcionando corretamente.
            </p>
        </div>
    </div>
</x-filament-panels::page>
