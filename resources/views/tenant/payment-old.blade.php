<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>Pagamento - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- 🔐 Pagar.me Tokenizecard JS (URL CORRETA da documentação oficial) -->
    <script src="https://checkout.pagar.me/v1/tokenizecard.js"
            data-pagarmecheckout-app-id="{{ config('services.pagarme.encryption_key') }}"
            onload="console.log('✅ Pagar.me Tokenizecard carregado com sucesso')"
            onerror="console.error('❌ Erro ao carregar Pagar.me Tokenizecard')"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
                        secondary: '#717171',
                        accent: '#3E3E3E'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div x-data="paymentApp()" x-init="init()" class="min-h-screen">
        <!-- Header Clean -->
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
            <div x-show="loading && !error" x-cloak class="bg-white rounded-lg border border-gray-200 p-12 text-center">
                <x-loading-spinner size="lg" />
                <p class="text-sm text-gray-500 mt-4">Carregando pagamento...</p>
            </div>

            <!-- Erro -->
            <div x-show="error && !loading" x-cloak class="bg-white rounded-lg border border-gray-200 p-8 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold mb-2 text-gray-900">Erro ao carregar pagamento</h2>
                <p class="text-gray-600 text-sm mb-6" x-text="error"></p>
                <a href="/" class="inline-block px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-red-700 transition">
                    Voltar ao Cardápio
                </a>
            </div>

            <!-- Conteúdo de Pagamento -->
            <div x-show="!loading && !error" x-cloak class="space-y-4">
                <!-- Status do Pedido -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Pedido</p>
                            <p class="text-xl font-bold text-gray-900" x-text="'#' + orderNumber"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 font-medium">Total</p>
                            <p class="text-xl font-bold text-primary" x-text="'R$ ' + totalAmount"></p>
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

                <!-- QR Code PIX -->
                <div x-show="paymentMethod === 'pix' && qrcodeImage" class="bg-white rounded-xl shadow-sm p-6 text-center">
                    <h2 class="text-lg font-bold mb-4 text-gray-900">Escaneie o QR Code</h2>
                    <div class="bg-white p-4 rounded-xl inline-block border-2 border-gray-200">
                        <img :src="qrcodeImage" alt="QR Code PIX" class="w-64 h-64 mx-auto">
                    </div>
                    <p class="text-gray-600 text-sm mt-4">Use o app do seu banco para escanear</p>
                </div>

                <!-- Copiar Código PIX -->
                <div x-show="paymentMethod === 'pix' && qrcodeText" class="bg-white rounded-xl shadow-sm p-5">
                    <h3 class="font-semibold mb-3 text-gray-900 text-sm">Ou copie o código PIX:</h3>
                    <div class="flex gap-2">
                        <input
                            type="text"
                            :value="qrcodeText"
                            readonly
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-xs font-mono"
                        >
                        <button
                            @click="copyPixCode()"
                            class="px-5 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-red-700 transition whitespace-nowrap text-sm"
                        >
                            <span x-show="!copied">Copiar</span>
                            <span x-show="copied">✓ Copiado!</span>
                        </button>
                    </div>
                </div>

                <!-- Instruções PIX -->
                <div x-show="paymentMethod === 'pix'" class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                    <h3 class="font-semibold text-blue-900 mb-3 text-sm">Como pagar com PIX:</h3>
                    <ol class="space-y-2 text-blue-800 text-sm">
                        <li class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            Abra o app do seu banco
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            Escolha a opção PIX
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            Escaneie o QR Code ou cole o código
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                            Confirme o pagamento
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-blue-200 rounded-full flex items-center justify-center text-xs font-bold">5</span>
                            Aguarde a confirmação automática
                        </li>
                    </ol>
                </div>

                <!-- Pagamento com Cartão -->
                <div x-show="paymentMethod === 'credit_card' || paymentMethod === 'debit_card'" class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-bold mb-4 text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Dados do Cartão
                    </h2>

                    <!-- Formulário de Cartão (Pagar.me Tokenizecard) -->
                    <form id="cardForm" data-pagarmecheckout-form class="space-y-4">
                        <!-- Campo hidden para o token -->
                        <input type="hidden" name="pagarmetoken" id="pagarmetoken">

                        <!-- Número do Cartão -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número do Cartão *</label>
                            <input
                                type="text"
                                data-pagarmecheckout-element="number"
                                placeholder="0000 0000 0000 0000"
                                maxlength="19"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none font-mono"
                                required>
                        </div>

                        <!-- Nome no Cartão -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome no Cartão *</label>
                            <input
                                type="text"
                                data-pagarmecheckout-element="holder_name"
                                placeholder="NOME COMO ESTÁ NO CARTÃO"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none uppercase"
                                required>
                        </div>

                        <!-- Validade e CVV -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mês/Ano *</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input
                                        type="text"
                                        data-pagarmecheckout-element="exp_month"
                                        placeholder="MM"
                                        maxlength="2"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none font-mono"
                                        required>
                                    <input
                                        type="text"
                                        data-pagarmecheckout-element="exp_year"
                                        placeholder="AAAA"
                                        maxlength="4"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none font-mono"
                                        required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CVV *</label>
                                <input
                                    type="text"
                                    data-pagarmecheckout-element="cvv"
                                    placeholder="000"
                                    maxlength="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none font-mono"
                                    required>
                            </div>
                        </div>

                        <!-- Mensagem de Erro -->
                        <div x-show="cardError" x-transition class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-700" x-text="cardError"></p>
                        </div>

                        <!-- Botão de Pagamento -->
                        <button
                            type="submit"
                            :disabled="processingPayment"
                            :class="processingPayment ? 'bg-gray-300 cursor-not-allowed' : 'bg-primary hover:bg-red-700'"
                            class="w-full py-4 text-white font-bold text-lg rounded-lg transition">
                            <span x-show="!processingPayment">Pagar R$ <span x-text="totalAmount"></span></span>
                            <span x-show="processingPayment" class="inline-flex items-center gap-2">
                                <x-loading-spinner size="sm" />
                                Processando...
                            </span>
                        </button>

                        <p class="text-xs text-center text-gray-500 mt-3">
                            🔒 Pagamento seguro processado pelo Pagar.me
                        </p>
                    </form>
                </div>

                <!-- Status Check -->
                <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <x-loading-spinner size="sm" />
                        <p class="text-gray-700 font-medium text-sm">Verificando pagamento...</p>
                    </div>
                    <p class="text-xs text-gray-500">
                        Você será redirecionado assim que o pagamento for confirmado
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
    function paymentApp() {
        return {
            orderNumber: '{{ $orderNumber }}',
            loading: true,
            error: '',
            paymentMethod: '',
            paymentUrl: '',
            qrcodeImage: '',
            qrcodeText: '',
            displayOrderNumber: '',
            totalAmount: '',
            copied: false,
            checkInterval: null,
            // Dados do Cartão
            cardNumber: '',
            cardHolder: '',
            cardExpiry: '',
            cardCVV: '',
            processingPayment: false,
            cardError: '',

            async init() {
                await this.loadPaymentInfo();
                this.startPaymentCheck();
            },

            async loadPaymentInfo() {
                try {
                    const token = localStorage.getItem('auth_token');

                    const response = await fetch(`/api/v1/orders/number/${this.orderNumber}/payment`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Erro ao carregar pagamento');
                    }

                    this.paymentMethod = data.method || 'pix';
                    this.paymentUrl = data.payment_url || '';
                    this.qrcodeImage = data.pix?.qrcode_image || '';
                    this.qrcodeText = data.pix?.qrcode_text || '';
                    this.displayOrderNumber = data.order_number || this.orderNumber;
                    this.totalAmount = parseFloat(data.amount || 0).toFixed(2).replace('.', ',');
                    this.loading = false;

                    // Se for cartão e tiver URL, redireciona
                    if ((this.paymentMethod === 'credit_card' || this.paymentMethod === 'debit_card') && this.paymentUrl) {
                        setTimeout(() => {
                            window.open(this.paymentUrl, '_blank');
                        }, 2000);
                    }

                } catch (error) {
                    console.error('Erro:', error);
                    this.error = error.message;
                    this.loading = false;
                }
            },

            async copyPixCode() {
                try {
                    await navigator.clipboard.writeText(this.qrcodeText);
                    this.copied = true;
                    setTimeout(() => {
                        this.copied = false;
                    }, 2000);
                } catch (err) {
                    console.error('Erro ao copiar:', err);
                }
            },

            formatCardNumber() {
                // Remove não-dígitos
                let value = this.cardNumber.replace(/\D/g, '');
                // Adiciona espaços a cada 4 dígitos
                value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
                this.cardNumber = value;
            },

            formatExpiry() {
                // Remove não-dígitos
                let value = this.cardExpiry.replace(/\D/g, '');
                // Adiciona barra após 2 dígitos
                if (value.length >= 2) {
                    value = value.slice(0, 2) + '/' + value.slice(2, 4);
                }
                this.cardExpiry = value;
            },

            async submitCardPayment() {
                this.processingPayment = true;
                this.cardError = '';

                try {
                    const token = localStorage.getItem('auth_token');

                    // Extrair dados do cartão
                    const cardNumberClean = this.cardNumber.replace(/\s/g, '');
                    const [expMonth, expYear] = this.cardExpiry.split('/');

                    // Validações básicas
                    if (cardNumberClean.length !== 16) {
                        throw new Error('Número do cartão inválido');
                    }
                    if (!expMonth || !expYear) {
                        throw new Error('Validade do cartão inválida');
                    }
                    if (this.cardCVV.length < 3) {
                        throw new Error('CVV inválido');
                    }

                    // 🔐 TOKENIZAÇÃO SEGURA - Dados NUNCA passam pelo servidor!
                    console.log('🔐 Tokenizando cartão no navegador...');

                    const cardToken = await this.tokenizeCard({
                        number: cardNumberClean,
                        holder_name: this.cardHolder.toUpperCase(),
                        exp_month: parseInt(expMonth),
                        exp_year: parseInt('20' + expYear),
                        cvv: this.cardCVV
                    });

                    if (!cardToken) {
                        throw new Error('Erro ao processar dados do cartão');
                    }

                    console.log('✅ Cartão tokenizado com sucesso:', cardToken);

                    // Enviar APENAS o token para backend (não dados sensíveis!)
                    const response = await fetch(`/api/v1/orders/${this.orderNumber}/pay-with-card`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            card_id: cardToken,  // ✅ Token seguro
                            method: this.paymentMethod,
                            installments: 1  // 💳 Sempre à vista (sem parcelamento)
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Erro ao processar pagamento');
                    }

                    // Se aprovado, redirecionar
                    if (data.status === 'paid') {
                        console.log('✅ Pagamento aprovado!');
                        window.location.href = `/pedido/${this.orderNumber}/confirmado`;
                    } else {
                        // Se pendente, continuar verificando status
                        this.cardError = 'Pagamento em análise. Aguarde...';
                        setTimeout(() => {
                            this.cardError = '';
                        }, 3000);
                    }

                } catch (error) {
                    console.error('❌ Erro ao processar pagamento:', error);
                    this.cardError = error.message || 'Erro ao processar pagamento';
                } finally {
                    this.processingPayment = false;
                }
            },

            /**
             * 🔐 Tokeniza cartão usando Pagar.me JS SDK
             * Dados NUNCA passam pelo servidor!
             */
            async tokenizeCard(cardData) {
                try {
                    console.log('🔐 Iniciando tokenização...', {
                        number: cardData.number.substring(0, 6) + '******',
                        holder: cardData.holder_name,
                        exp: cardData.exp_month + '/' + cardData.exp_year
                    });

                    // Tentar esperar SDK carregar (máximo 2 segundos)
                    try {
                        await this.waitForPagarmeSDK(2000);
                    } catch (e) {
                        console.warn('⚠️ SDK não carregou, usando fallback (backend)');
                        return await this.tokenizeCardBackend(cardData);
                    }

                    // Verificar se SDK carregou
                    if (typeof window.pagarme === 'undefined') {
                        console.warn('⚠️ SDK não disponível, usando fallback (backend)');
                        return await this.tokenizeCardBackend(cardData);
                    }

                    console.log('✅ SDK disponível, tokenizando no navegador');

                    // Chave de criptografia pública (seguro expor no frontend)
                    const encryptionKey = '{{ config("services.pagarme.encryption_key") }}';
                    console.log('🔑 Encryption Key:', encryptionKey ? encryptionKey.substring(0, 10) + '...' : 'VAZIA');

                    if (!encryptionKey || encryptionKey === '') {
                        console.error('❌ PAGARME_ENCRYPTION_KEY não configurada!');
                        throw new Error('Erro de configuração. Contate o suporte.');
                    }

                    // Inicializa cliente Pagar.me
                    console.log('🔌 Conectando ao Pagar.me...');
                    const pagarme = await window.pagarme.client.connect({
                        encryption_key: encryptionKey
                    });

                    console.log('✅ Cliente Pagar.me conectado!');

                    // Tokeniza o cartão (criptografia acontece no navegador)
                    console.log('🔐 Tokenizando cartão...');
                    const card = await pagarme.security.encrypt(cardData);

                    console.log('✅ Cartão tokenizado com sucesso!', card.id);

                    return card.id; // Retorna apenas o token (ex: card_abc123xyz)

                } catch (error) {
                    console.error('❌ Erro COMPLETO na tokenização:', error);
                    console.error('Tipo do erro:', typeof error);
                    console.error('Mensagem:', error.message);
                    console.error('Stack:', error.stack);

                    // Log detalhado do erro
                    if (error.response) {
                        console.error('Response do erro:', error.response);
                        console.error('Errors:', error.response.errors);
                    }

                    // Mensagem mais específica baseada no erro
                    let errorMessage = 'Erro ao processar cartão. ';

                    if (error.message && error.message.includes('configuration')) {
                        errorMessage += 'Erro de configuração.';
                    } else if (error.response?.errors) {
                        const firstError = error.response.errors[0];
                        errorMessage += firstError.message || 'Dados inválidos.';
                    } else if (error.message) {
                        errorMessage += error.message;
                    } else {
                        errorMessage += 'Verifique os dados e tente novamente.';
                    }

                    throw new Error(errorMessage);
                }
            },

            /**
             * Espera o SDK do Pagar.me carregar (máximo 10 segundos)
             */
            async waitForPagarmeSDK(maxWait = 10000) {
                const startTime = Date.now();

                while (typeof window.pagarme === 'undefined') {
                    if (Date.now() - startTime > maxWait) {
                        console.error('⏱️ Timeout esperando SDK carregar');
                        throw new Error('SDK demorou muito para carregar');
                    }

                    console.log('⏳ Esperando SDK carregar...');
                    await new Promise(resolve => setTimeout(resolve, 100));
                }

                console.log('✅ SDK carregou após', Date.now() - startTime, 'ms');
            },

            startPaymentCheck() {
                // Verificar status do pagamento a cada 5 segundos
                this.checkInterval = setInterval(async () => {
                    try {
                        const token = localStorage.getItem('auth_token');
                        const response = await fetch(`/api/v1/orders/number/${this.orderNumber}`, {
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            console.log('🔍 Status do pedido:', {
                                status: data.status,
                                payment_status: data.payment_status
                            });

                            // Se pagamento confirmado, redireciona
                            if (data.payment_status === 'paid' || data.status === 'confirmed') {
                                console.log('✅ Pagamento confirmado! Redirecionando...');
                                clearInterval(this.checkInterval);
                                window.location.href = `/pedido/${this.orderNumber}/confirmado`;
                            }
                        }
                    } catch (error) {
                        console.error('Erro ao verificar status:', error);
                    }
                }, 5000);
            }
        }
    }
    </script>
</body>
</html>
