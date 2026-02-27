<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurante não encontrado</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        <!-- Card Principal -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 text-center">
            <!-- Ícone de Erro -->
            <div class="mb-6">
                <div class="mx-auto w-24 h-24 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>

            <!-- Título -->
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                🍽️ Restaurante não encontrado
            </h1>

            <!-- Descrição -->
            <p class="text-lg text-gray-600 mb-2">
                O domínio <strong class="text-red-600">{{ $domain }}</strong> não está cadastrado na plataforma YumGo.
            </p>

            <p class="text-gray-500 mb-8">
                Verifique se o endereço está correto ou entre em contato com o restaurante.
            </p>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-8"></div>

            <!-- Informações -->
            <div class="bg-blue-50 rounded-xl p-6 mb-8 text-left">
                <h2 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Você é dono de restaurante?
                </h2>
                <p class="text-blue-800 text-sm mb-4">
                    Se você quer cadastrar este domínio na plataforma YumGo:
                </p>
                <ol class="list-decimal list-inside text-blue-700 text-sm space-y-2">
                    <li>Acesse o painel administrativo da YumGo</li>
                    <li>Crie um novo restaurante</li>
                    <li>Configure o domínio <strong>{{ $domain }}</strong></li>
                    <li>Aguarde a propagação (até 5 minutos)</li>
                </ol>
            </div>

            <!-- Ações -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="https://yumgo.com.br" class="px-8 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white font-bold rounded-xl hover:shadow-lg transition-all">
                    Ir para YumGo
                </a>
                <a href="javascript:history.back()" class="px-8 py-3 bg-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-300 transition-all">
                    Voltar
                </a>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    Domínio procurado: <code class="bg-gray-100 px-2 py-1 rounded text-red-600 font-mono">{{ $domain }}</code>
                </p>
                <p class="text-xs text-gray-400 mt-2">
                    Erro: Tenant não identificado (404)
                </p>
            </div>
        </div>

        <!-- Card de Suporte -->
        <div class="mt-6 bg-white rounded-xl shadow-lg p-6 text-center">
            <p class="text-gray-600 text-sm mb-3">
                <strong>Precisa de ajuda?</strong>
            </p>
            <a href="mailto:suporte@yumgo.com.br" class="text-blue-600 hover:text-blue-700 font-semibold text-sm">
                suporte@yumgo.com.br
            </a>
        </div>
    </div>
</body>
</html>
