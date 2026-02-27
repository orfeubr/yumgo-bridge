<x-filament-panels::page>
    <div class="space-y-4">
        <!-- Info Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="font-semibold text-blue-900 mb-1">Acesso ao Banco de Dados</h3>
                    <p class="text-sm text-blue-800">
                        Você está acessando o <strong>Adminer</strong> - gerenciador de banco de dados PostgreSQL.
                        <br>Tenha cuidado ao fazer alterações diretas no banco!
                    </p>
                    <div class="mt-3 space-y-1 text-xs text-blue-700">
                        <p><strong>Servidor:</strong> {{ config('database.connections.pgsql.host') }}</p>
                        <p><strong>Banco:</strong> {{ config('database.connections.pgsql.database') }}</p>
                        <p><strong>Usuário:</strong> {{ config('database.connections.pgsql.username') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botão para abrir Adminer -->
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Gerenciador de Banco de Dados</h3>
                <p class="text-gray-600 mb-6">
                    Clique no botão abaixo para abrir o Adminer em uma nova janela.
                    O login será feito automaticamente com as credenciais do sistema.
                </p>
                <a
                    href="{{ route('admin.database') }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Abrir Adminer
                </a>
            </div>
        </div>

        <!-- Atalhos Rápidos -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.database') }}?pgsql={{ config('database.connections.pgsql.host') }}&username={{ config('database.connections.pgsql.username') }}&db={{ config('database.connections.pgsql.database') }}&ns=public" 
               target="_blank"
               class="flex items-center gap-3 p-4 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                </svg>
                <div>
                    <p class="font-medium text-gray-900 text-sm">Schema PUBLIC</p>
                    <p class="text-xs text-gray-500">Dados da plataforma</p>
                </div>
            </a>

            <a href="{{ route('admin.database') }}?pgsql={{ config('database.connections.pgsql.host') }}&username={{ config('database.connections.pgsql.username') }}&db={{ config('database.connections.pgsql.database') }}&sql=" 
               target="_blank"
               class="flex items-center gap-3 p-4 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div>
                    <p class="font-medium text-gray-900 text-sm">SQL Query</p>
                    <p class="text-xs text-gray-500">Executar queries SQL</p>
                </div>
            </a>

            <a href="{{ route('admin.database') }}" 
               target="_blank"
               class="flex items-center gap-3 p-4 bg-primary-50 hover:bg-primary-100 rounded-lg border border-primary-200 transition">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                <div>
                    <p class="font-medium text-primary-900 text-sm">Nova Janela</p>
                    <p class="text-xs text-primary-700">Abrir em tela cheia</p>
                </div>
            </a>
        </div>
    </div>
</x-filament-panels::page>
