<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Realizado - {{ $platformSettings->platform_name ?? 'YumGo' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-2xl w-full">
            <!-- Success Icon -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-4">
                    <i class="fas fa-check text-5xl text-green-600"></i>
                </div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">
                    🎉 Parabéns!
                </h1>
                <p class="text-xl text-gray-600">
                    Seu restaurante foi cadastrado com sucesso!
                </p>
            </div>

            <!-- Info Card -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
                <h2 class="text-2xl font-bold mb-6">{{ $tenant->name }}</h2>

                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-globe text-red-600"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 mb-1">URL do seu restaurante</div>
                            <a href="https://{{ $tenant->slug }}.yumgo.com.br" target="_blank"
                               class="text-red-600 hover:underline font-medium">
                                https://{{ $tenant->slug }}.yumgo.com.br
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-cog text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 mb-1">Painel Administrativo</div>
                            <a href="https://{{ $tenant->slug }}.yumgo.com.br/painel" target="_blank"
                               class="text-blue-600 hover:underline font-medium">
                                https://{{ $tenant->slug }}.yumgo.com.br/painel
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-clock text-orange-600"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 mb-1">Status da Conta</div>
                            <div class="text-orange-600 font-semibold">
                                🕐 Aguardando aprovação
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="bg-blue-50 border border-blue-300 rounded-lg p-6 mb-6">
                <h3 class="font-bold text-blue-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    Próximos Passos
                </h3>
                <p class="text-blue-800 mb-4">
                    Seu cadastro foi realizado com sucesso! <strong>Nossa equipe irá revisar</strong> e aprovar seu restaurante em breve.
                </p>
                <ol class="space-y-3 text-yellow-900">
                    <li class="flex items-start">
                        <span class="font-bold mr-2">1.</span>
                        <span><strong>Acesse "Minha Assinatura"</strong> no painel administrativo</span>
                    </li>
                    <li class="flex items-start">
                        <span class="font-bold mr-2">2.</span>
                        <span><strong>Configure dados bancários</strong> para receber pagamentos (Pagar.me)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="font-bold mr-2">3.</span>
                        <span><strong>Ative sua assinatura</strong> para liberar o sistema completo</span>
                    </li>
                    <li class="flex items-start">
                        <span class="font-bold mr-2">4.</span>
                        <span>Adicione produtos, configure cashback e comece a vender!</span>
                    </li>
                </ol>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="https://{{ $tenant->slug }}.yumgo.com.br/painel"
                   class="px-8 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold inline-flex items-center">
                    <i class="fas fa-cog mr-2"></i> Acessar Painel Administrativo
                </a>
                <a href="https://{{ $tenant->slug }}.yumgo.com.br"
                   class="px-8 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold inline-flex items-center">
                    <i class="fas fa-eye mr-2"></i> Ver Loja
                </a>
            </div>

            <!-- Help -->
            <div class="text-center mt-8 text-gray-600">
                <p>
                    <i class="fas fa-question-circle mr-1"></i>
                    Precisa de ajuda?
                    <a href="mailto:suporte@yumgo.com.br" class="text-red-600 hover:underline font-semibold">
                        Entre em contato
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
