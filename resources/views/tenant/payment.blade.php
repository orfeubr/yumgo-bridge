<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#EA1D2C',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white sticky top-0 z-50 shadow-md">
            <div class="max-w-2xl mx-auto px-4 py-4">
                <a href="/" class="text-gray-700 hover:text-primary text-sm mb-3 inline-flex items-center gap-2 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar ao cardápio
                </a>
                <div class="flex items-center gap-3 mt-2">
                    <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Pagamento</h1>
                        <p class="text-sm text-gray-600">{{ $tenant->name }}</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-2xl mx-auto px-4 py-6">
            <!-- Loading -->
            <div id="loading" class="bg-white rounded-lg border border-gray-200 p-12 text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
                <p class="text-sm text-gray-500 mt-4">Carregando pagamento...</p>
            </div>

            <!-- Erro -->
            <div id="error" class="bg-white rounded-lg border border-gray-200 p-8 text-center hidden">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold mb-2 text-gray-900">Erro ao carregar pagamento</h2>
                <p id="error-message" class="text-gray-600 text-sm mb-6"></p>
                <a href="/" class="inline-block px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-red-700 transition">
                    Voltar ao Cardápio
                </a>
            </div>

            <!-- Conteúdo de Pagamento -->
            <div id="payment-content" class="space-y-4 hidden">
                <!-- Resumo do Pedido -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Pedido</p>
                            <p id="order-number" class="text-xl font-bold text-gray-900"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 font-medium">Total</p>
                            <p id="total-amount" class="text-xl font-bold text-primary"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="animate-pulse text-2xl">⏳</div>
                        <div class="flex-1">
                            <p class="font-semibold text-yellow-800 text-sm">Aguardando Pagamento</p>
                            <p class="text-xs text-yellow-700">Complete o pagamento abaixo</p>
                        </div>
                    </div>
                </div>

                <!-- PIX -->
                <div id="pix-section" class="hidden">
                    <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                        <h2 class="text-lg font-bold mb-4 text-gray-900">Escaneie o QR Code</h2>
                        <div class="bg-white p-4 rounded-xl inline-block border-2 border-gray-200">
                            <img id="pix-qrcode" alt="QR Code PIX" class="w-64 h-64 mx-auto">
                        </div>
                        <p class="text-gray-600 text-sm mt-4">Use o app do seu banco para escanear</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <h3 class="font-semibold mb-3 text-gray-900 text-sm">Ou copie o código PIX:</h3>
                        <div class="flex gap-2">
                            <input id="pix-code" type="text" readonly
                                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-xs font-mono">
                            <button onclick="copyPixCode()"
                                class="px-5 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-red-700 transition whitespace-nowrap text-sm">
                                Copiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cartão -->
                <div id="card-section" class="bg-white rounded-xl shadow-sm p-6 hidden">
                    <h2 class="text-lg font-bold mb-4 text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Dados do Cartão
                    </h2>

                    <!-- 🔐 Formulário Pagar.me (Tokenização Segura) -->
                    <form id="payment-form" data-pagarmecheckout-form class="space-y-4">
                        <!-- Token será preenchido automaticamente -->
                        <input type="hidden" name="pagarmetoken" id="pagarmetoken">

                        <!-- Número do Cartão -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número do Cartão *</label>
                            <input type="text" data-pagarmecheckout-element="number" placeholder="0000 0000 0000 0000" maxlength="19"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none font-mono" required>
                        </div>

                        <!-- Nome no Cartão -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome no Cartão *</label>
                            <input type="text" data-pagarmecheckout-element="holder_name" placeholder="NOME COMO ESTÁ NO CARTÃO"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none uppercase" required>
                        </div>

                        <!-- Validade e CVV -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Validade *</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" data-pagarmecheckout-element="exp_month" placeholder="MM" maxlength="2"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none font-mono" required>
                                    <input type="text" data-pagarmecheckout-element="exp_year" placeholder="AAAA" maxlength="4"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none font-mono" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CVV *</label>
                                <input type="text" data-pagarmecheckout-element="cvv" placeholder="000" maxlength="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none font-mono" required>
                            </div>
                        </div>

                        <!-- 🔐 Endereço de Cobrança (obrigatório para tokenização) -->
                        <div class="border-t pt-4 mt-2">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Endereço de Cobrança</h3>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">CEP *</label>
                                    <input type="text" data-pagarmecheckout-element="address_zipcode" placeholder="00000-000" maxlength="9"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" required>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Endereço *</label>
                                    <input type="text" data-pagarmecheckout-element="address_line1" placeholder="Rua, número"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" required>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Cidade *</label>
                                        <input type="text" data-pagarmecheckout-element="address_city" placeholder="Cidade"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Estado *</label>
                                        <input type="text" data-pagarmecheckout-element="address_state" placeholder="UF" maxlength="2"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none uppercase" required>
                                    </div>
                                </div>

                                <input type="hidden" data-pagarmecheckout-element="address_country" value="BR">
                            </div>
                        </div>

                        <!-- Mensagem de Erro -->
                        <div id="card-error" class="p-3 bg-red-50 border border-red-200 rounded-lg hidden">
                            <p class="text-sm text-red-700"></p>
                        </div>

                        <!-- Botão de Pagamento -->
                        <button type="submit" id="pay-button"
                            class="w-full py-4 bg-primary text-white font-bold text-lg rounded-lg hover:bg-red-700 transition">
                            Pagar <span id="pay-amount"></span>
                        </button>

                        <p class="text-xs text-center text-gray-500 mt-3">
                            🔒 Pagamento seguro processado pelo Pagar.me
                        </p>
                    </form>
                </div>

                <!-- Status Check -->
                <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>
                        <p class="text-gray-700 font-medium text-sm">Verificando pagamento...</p>
                    </div>
                    <p class="text-xs text-gray-500">
                        Você será redirecionado assim que o pagamento for confirmado
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔐 Pagar.me Tokenizecard SDK -->
    <script src="https://checkout.pagar.me/v1/tokenizecard.js"
            data-pagarmecheckout-app-id="{{ config('services.pagarme.encryption_key') }}"></script>

    <script>
        const ORDER_NUMBER = '{{ $orderNumber }}';
        const AUTH_TOKEN = localStorage.getItem('auth_token');
        let paymentData = null;
        let checkInterval = null;
        let pagarmeInitialized = false;

        // Elementos do DOM
        const elements = {
            loading: document.getElementById('loading'),
            error: document.getElementById('error'),
            errorMessage: document.getElementById('error-message'),
            paymentContent: document.getElementById('payment-content'),
            orderNumber: document.getElementById('order-number'),
            totalAmount: document.getElementById('total-amount'),
            pixSection: document.getElementById('pix-section'),
            cardSection: document.getElementById('card-section'),
            paymentForm: document.getElementById('payment-form'),
            payButton: document.getElementById('pay-button'),
            cardError: document.getElementById('card-error'),
        };

        // Carregar informações do pagamento
        async function loadPaymentInfo() {
            try {
                const response = await fetch(`/api/v1/orders/number/${ORDER_NUMBER}/payment`, {
                    headers: {
                        'Authorization': `Bearer ${AUTH_TOKEN}`,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Erro ao carregar pagamento');
                }

                paymentData = await response.json();
                console.log('💳 Dados do pagamento:', paymentData);

                // Atualizar UI
                elements.orderNumber.textContent = '#' + paymentData.order_number;
                elements.totalAmount.textContent = 'R$ ' + parseFloat(paymentData.amount).toFixed(2).replace('.', ',');
                document.getElementById('pay-amount').textContent = elements.totalAmount.textContent;

                // Mostrar seção correta
                if (paymentData.method === 'pix') {
                    showPixSection();
                } else if (paymentData.method === 'credit_card' || paymentData.method === 'debit_card') {
                    showCardSection();
                }

                // Esconder loading
                elements.loading.classList.add('hidden');
                elements.paymentContent.classList.remove('hidden');

                // Iniciar verificação de status
                startPaymentCheck();

            } catch (error) {
                console.error('❌ Erro ao carregar:', error);
                showError(error.message);
            }
        }

        // Mostrar seção PIX
        function showPixSection() {
            if (paymentData.pix?.qrcode_image) {
                document.getElementById('pix-qrcode').src = paymentData.pix.qrcode_image;
                document.getElementById('pix-code').value = paymentData.pix.qrcode_text || '';
                elements.pixSection.classList.remove('hidden');
            }
        }

        // Mostrar seção Cartão
        function showCardSection() {
            elements.cardSection.classList.remove('hidden');
            initializePagarmeCheckout();
        }

        // 🔐 Inicializar Pagar.me Checkout (Tokenização Segura)
        function initializePagarmeCheckout() {
            console.log('🔐 Inicializando Pagar.me Checkout...');

            if (typeof PagarmeCheckout === 'undefined') {
                console.error('❌ PagarmeCheckout não carregou!');
                showCardError('Erro ao carregar sistema de pagamento. Recarregue a página.');
                return;
            }

            // Callback de sucesso - recebe o token
            function successCallback(data) {
                console.log('✅ Token recebido:', data);

                // Extrair o token do objeto data
                // O SDK retorna um objeto com formato: { "pagarmetoken-0": "token_xxx" }
                let token = null;

                // Procurar por qualquer chave que contenha "token"
                for (let key in data) {
                    if (key.includes('pagarmetoken') || key.includes('token')) {
                        token = data[key];
                        break;
                    }
                }

                if (!token) {
                    console.error('❌ Token não encontrado no objeto:', data);
                    showCardError('Erro ao processar cartão. Tente novamente.');
                    return false;
                }

                console.log('✅ Token extraído:', token);

                // Processar pagamento
                processCardPayment(token);

                // Retornar false para prevenir submit do form
                return false;
            }

            // Callback de erro
            function failCallback(error) {
                console.error('❌ Erro no Pagar.me:', error);
                showCardError('Dados do cartão inválidos. Verifique e tente novamente.');
                elements.payButton.disabled = false;
                elements.payButton.textContent = 'Pagar ' + elements.totalAmount.textContent;
            }

            // Inicializar
            PagarmeCheckout.init(successCallback, failCallback);
            pagarmeInitialized = true;
            console.log('✅ Pagar.me Checkout inicializado!');
        }

        // Processar pagamento com cartão
        async function processCardPayment(cardToken) {
            console.log('💳 Processando pagamento com token:', cardToken.substring(0, 20) + '...');

            try {
                elements.payButton.disabled = true;
                elements.payButton.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mx-auto"></div>';

                const response = await fetch(`/api/v1/orders/${ORDER_NUMBER}/pay-with-card`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${AUTH_TOKEN}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        card_id: cardToken,
                        method: paymentData.method,
                        installments: 1
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Erro ao processar pagamento');
                }

                console.log('✅ Pagamento processado:', data);

                // Se aprovado, redirecionar
                if (data.status === 'paid') {
                    window.location.href = `/pedido/${ORDER_NUMBER}/confirmado`;
                }

            } catch (error) {
                console.error('❌ Erro ao processar:', error);
                showCardError(error.message);
                elements.payButton.disabled = false;
                elements.payButton.textContent = 'Pagar ' + elements.totalAmount.textContent;
            }
        }

        // Copiar código PIX
        function copyPixCode() {
            const code = document.getElementById('pix-code');
            code.select();
            document.execCommand('copy');
            alert('Código PIX copiado!');
        }

        // Verificar status do pagamento
        function startPaymentCheck() {
            checkInterval = setInterval(async () => {
                try {
                    const response = await fetch(`/api/v1/orders/number/${ORDER_NUMBER}`, {
                        headers: {
                            'Authorization': `Bearer ${AUTH_TOKEN}`,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();

                        if (data.payment_status === 'paid' || data.status === 'confirmed') {
                            console.log('✅ Pagamento confirmado!');
                            clearInterval(checkInterval);
                            window.location.href = `/pedido/${ORDER_NUMBER}/confirmado`;
                        }
                    }
                } catch (error) {
                    console.error('Erro ao verificar status:', error);
                }
            }, 5000);
        }

        // Mostrar erro
        function showError(message) {
            elements.loading.classList.add('hidden');
            elements.paymentContent.classList.add('hidden');
            elements.error.classList.remove('hidden');
            elements.errorMessage.textContent = message;
        }

        // Mostrar erro do cartão
        function showCardError(message) {
            elements.cardError.querySelector('p').textContent = message;
            elements.cardError.classList.remove('hidden');
            setTimeout(() => {
                elements.cardError.classList.add('hidden');
            }, 5000);
        }

        // Inicializar ao carregar página
        if (!AUTH_TOKEN) {
            window.location.href = '/?login=required';
        } else {
            loadPaymentInfo();
        }
    </script>
</body>
</html>
