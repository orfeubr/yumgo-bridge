<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastre seu Restaurante - {{ $platformSettings->platform_name ?? 'YumGo' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Pagar.me JS SDK -->
    <script src="https://assets.pagar.me/pagarme-js/5.0/pagarme.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF4D2D',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center">
                    @if(isset($platformSettings) && $platformSettings->platform_logo && file_exists(public_path('logo.png')))
                        <img src="{{ asset('logo.png') }}?v={{ filemtime(public_path('logo.png')) }}"
                             alt="{{ $platformSettings->platform_name }}"
                             class="h-16 md:h-20 max-w-[280px] object-contain">
                    @else
                        <div class="bg-primary text-white px-4 py-2 rounded-lg font-bold text-xl">
                            {{ $platformSettings->platform_name ?? 'YumGo' }}
                        </div>
                    @endif
                </a>
                <a href="/painel" class="text-gray-600 hover:text-primary font-semibold transition">
                    <i class="fas fa-sign-in-alt mr-2"></i> Já tenho conta
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Hero -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-3">
                    Cadastre seu Restaurante
                </h1>
                <p class="text-lg text-gray-600">
                    Preencha os dados abaixo e comece a vender em minutos! 🚀
                </p>
            </div>

            <!-- Formulário -->
            <form action="{{ route('signup.store') }}" method="POST" class="bg-white rounded-2xl shadow-lg p-8" id="signupForm">
                @csrf

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border-2 border-red-300 rounded-lg p-6">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-4"></i>
                            <div class="flex-1">
                                <strong class="font-bold text-lg text-red-900 block mb-2">Ops! Corrija os erros abaixo:</strong>
                                <ul class="space-y-1 text-sm text-red-700">
                                    @foreach($errors->all() as $error)
                                        <li class="flex items-start">
                                            <i class="fas fa-circle text-xs mt-1.5 mr-2"></i>
                                            <span>{{ $error }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border-2 border-red-300 rounded-lg p-6">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-600 text-2xl mr-4"></i>
                            <div class="flex-1">
                                <strong class="font-bold text-lg text-red-900 block mb-2">Erro ao criar conta:</strong>
                                <p class="text-sm text-red-700">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-6 bg-green-50 border-2 border-green-300 rounded-lg p-6">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 text-2xl mr-4"></i>
                            <div class="flex-1">
                                <strong class="font-bold text-lg text-green-900">{{ session('success') }}</strong>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Dados do Restaurante -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1 flex items-center">
                        <i class="fas fa-store text-primary mr-3"></i>
                        Dados do Restaurante
                    </h2>
                    <p class="text-sm text-gray-600 mb-6">Informações básicas do seu estabelecimento</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nome do Restaurante <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="restaurant_name" value="{{ old('restaurant_name') }}"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                   placeholder="Ex: Pizzaria Bella Napoli">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                URL do Restaurante <span class="text-red-600">*</span>
                            </label>
                            <div class="flex">
                                <input type="text" name="restaurant_slug" value="{{ old('restaurant_slug') }}"
                                       required pattern="[a-z0-9\-]+"
                                       class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-l-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                       placeholder="pizzaria-bella">
                                <div class="px-6 py-3 bg-gray-100 border-2 border-l-0 border-gray-200 rounded-r-lg text-gray-600 font-medium">
                                    .yumgo.com.br
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Apenas letras minúsculas, números e hífens</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Email <span class="text-red-600">*</span>
                            </label>
                            <input type="email" name="restaurant_email" value="{{ old('restaurant_email') }}"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                   placeholder="contato@restaurante.com">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Telefone/WhatsApp <span class="text-red-600">*</span>
                            </label>
                            <input type="tel" name="restaurant_phone" value="{{ old('restaurant_phone') }}"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                   placeholder="(11) 99999-9999">
                        </div>
                    </div>
                </div>

                <hr class="my-8 border-gray-200">

                <!-- Dados do Responsável -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1 flex items-center">
                        <i class="fas fa-user text-primary mr-3"></i>
                        Dados do Responsável
                    </h2>
                    <p class="text-sm text-gray-600 mb-6">Suas informações de acesso ao painel</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nome Completo <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="owner_name" value="{{ old('owner_name') }}"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                   placeholder="João Silva">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Email de Acesso <span class="text-red-600">*</span>
                            </label>
                            <input type="email" name="owner_email" value="{{ old('owner_email') }}"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                   placeholder="joao@email.com">
                            <p class="mt-2 text-xs text-gray-500">Será usado para fazer login no painel administrativo</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Senha <span class="text-red-600">*</span>
                            </label>
                            <input type="password" name="owner_password"
                                   required minlength="6"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                   placeholder="Mínimo 6 caracteres">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Confirmar Senha <span class="text-red-600">*</span>
                            </label>
                            <input type="password" name="owner_password_confirmation"
                                   required minlength="6"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                   placeholder="Repita a senha">
                        </div>
                    </div>
                </div>

                <hr class="my-8 border-gray-200">

                <!-- Escolha do Plano -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1 flex items-center">
                        <i class="fas fa-crown text-primary mr-3"></i>
                        Escolha seu Plano
                    </h2>
                    <p class="text-sm text-gray-600 mb-6">Selecione o plano ideal para seu negócio</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($plans as $plan)
                            <label class="cursor-pointer group">
                                <input type="radio" name="plan_id" value="{{ $plan->id }}"
                                       {{ old('plan_id') == $plan->id ? 'checked' : ($loop->index === 1 ? 'checked' : '') }}
                                       required
                                       class="sr-only peer">
                                <div class="border-3 border-gray-200 rounded-2xl p-6 peer-checked:border-primary peer-checked:bg-red-50 hover:border-primary hover:shadow-lg transition-all duration-200">
                                    @if($plan->name === 'Pro')
                                        <div class="bg-primary text-white text-xs font-bold px-3 py-1 rounded-full inline-block mb-3">
                                            ⭐ RECOMENDADO
                                        </div>
                                    @endif

                                    <h3 class="text-xl font-bold mb-3 text-gray-900">{{ $plan->name }}</h3>

                                    <div class="mb-4">
                                        <span class="text-4xl font-bold text-primary">R$ {{ number_format($plan->price_monthly, 2, ',', '.') }}</span>
                                        <span class="text-gray-600">/mês</span>
                                    </div>

                                    <div class="bg-orange-100 text-orange-800 px-3 py-2 rounded-lg text-sm font-bold inline-block mb-4">
                                        {{ $plan->commission_percentage }}% comissão
                                    </div>

                                    @if($plan->features && is_array($plan->features))
                                        <ul class="space-y-3 text-sm">
                                            @foreach(array_slice($plan->features, 0, 4) as $feature)
                                                <li class="flex items-start text-gray-700">
                                                    <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                                                    <span>{{ $feature }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <hr class="my-8 border-gray-200">

                <!-- Dados de Pagamento -->
                <div class="mb-8" id="paymentSection">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1 flex items-center">
                        <i class="fas fa-credit-card text-primary mr-3"></i>
                        Dados de Pagamento
                    </h2>
                    <p class="text-sm text-gray-600 mb-6">
                        🎁 <strong>7 dias grátis!</strong> Você só será cobrado após o período de teste.
                    </p>

                    <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-0.5"></i>
                            <div>
                                <p class="text-sm text-blue-900 font-semibold mb-1">Período de Avaliação Gratuito</p>
                                <p class="text-xs text-blue-700">
                                    Use o sistema gratuitamente por 7 dias. Você pode cancelar a qualquer momento antes do término do período de avaliação e não será cobrado.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Número do Cartão <span class="text-red-600">*</span>
                            </label>
                            <input type="text" id="card_number"
                                   required maxlength="19"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                   placeholder="0000 0000 0000 0000">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Validade <span class="text-red-600">*</span>
                            </label>
                            <input type="text" id="card_expiry"
                                   required maxlength="5"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                   placeholder="MM/AA">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                CVV <span class="text-red-600">*</span>
                            </label>
                            <input type="text" id="card_cvv"
                                   required maxlength="4"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                   placeholder="000">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nome no Cartão <span class="text-red-600">*</span>
                            </label>
                            <input type="text" id="card_holder_name"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                                   placeholder="Nome como está no cartão">
                        </div>

                        <!-- Hidden field para o token -->
                        <input type="hidden" name="card_token" id="card_token">
                    </div>

                    <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-lock text-green-600 mr-2"></i>
                            <span>Seus dados de pagamento são criptografados e seguros. Usamos a Pagar.me para processar pagamentos.</span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-10">
                    <button type="submit" id="submitBtn"
                            class="w-full bg-primary hover:bg-red-700 text-white font-bold py-4 px-8 rounded-xl text-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-rocket mr-2"></i>
                        Criar Minha Conta Grátis
                    </button>
                    <p class="text-center text-sm text-gray-500 mt-4">
                        Ao criar sua conta, você concorda com nossos <a href="/termos" class="text-primary hover:underline">Termos de Uso</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Configurar Pagar.me
        const pagarme = window.pagarme || {};
        const ENCRYPTION_KEY = '{{ config("services.pagarme.encryption_key") }}';

        // Elementos do formulário
        const form = document.getElementById('signupForm');
        const btn = document.getElementById('submitBtn');
        const cardNumberInput = document.getElementById('card_number');
        const cardExpiryInput = document.getElementById('card_expiry');
        const cardCvvInput = document.getElementById('card_cvv');
        const cardHolderInput = document.getElementById('card_holder_name');
        const cardTokenInput = document.getElementById('card_token');

        // Formatar campos do cartão
        cardNumberInput?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });

        cardExpiryInput?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });

        cardCvvInput?.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });

        // Interceptar submit para tokenizar cartão
        let isSubmitting = false;

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (isSubmitting) return;
            isSubmitting = true;

            console.log('✅ Iniciando tokenização do cartão...');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processando pagamento...';

            try {
                // Extrair dados do cartão
                const cardNumber = cardNumberInput.value.replace(/\s/g, '');
                const expiry = cardExpiryInput.value.split('/');
                const cvv = cardCvvInput.value;
                const holderName = cardHolderInput.value;

                if (!cardNumber || !expiry[0] || !expiry[1] || !cvv || !holderName) {
                    throw new Error('Preencha todos os dados do cartão');
                }

                // Criar objeto de cartão
                const cardData = {
                    number: cardNumber,
                    holder_name: holderName,
                    exp_month: expiry[0],
                    exp_year: '20' + expiry[1],
                    cvv: cvv
                };

                console.log('📋 Tokenizando cartão...');

                // Tokenizar usando Pagar.me
                const response = await fetch('https://api.pagar.me/core/v5/tokens?appId=' + ENCRYPTION_KEY, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        type: 'card',
                        card: cardData
                    })
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Erro ao processar cartão');
                }

                const tokenData = await response.json();
                console.log('✅ Token gerado:', tokenData.id);

                // Adicionar token ao formulário
                cardTokenInput.value = tokenData.id;

                // Limpar campos sensíveis (segurança)
                cardNumberInput.value = '';
                cardCvvInput.value = '';
                cardExpiryInput.value = '';

                // Enviar formulário
                console.log('📤 Enviando formulário...');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Criando sua conta...';
                form.submit();

            } catch (error) {
                console.error('❌ Erro:', error);
                alert('Erro ao processar cartão: ' + error.message + '\n\nVerifique os dados e tente novamente.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-rocket mr-2"></i> Criar Minha Conta Grátis';
                isSubmitting = false;
            }
        });

        // Auto-gerar slug do nome do restaurante
        const nameInput = document.querySelector('[name="restaurant_name"]');
        const slugInput = document.querySelector('[name="restaurant_slug"]');
        let manualSlug = {{ old('restaurant_slug') ? 'true' : 'false' }};

        nameInput?.addEventListener('input', function() {
            if (!manualSlug) {
                slugInput.value = this.value
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });

        slugInput?.addEventListener('input', function() {
            manualSlug = true;
        });
    </script>
</body>
</html>
