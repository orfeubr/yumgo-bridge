<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                <div class="animate-spin w-12 h-12 border-3 border-gray-300 border-t-gray-900 rounded-full mx-auto mb-4"></div>
                <p class="text-sm text-gray-500">Carregando...</p>
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
                        <img :src="'data:image/png;base64,' + qrcodeImage" alt="QR Code PIX" class="w-64 h-64 mx-auto">
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
                <div x-show="paymentMethod === 'credit_card' || paymentMethod === 'debit_card'" class="bg-white rounded-xl shadow-sm p-8 text-center">
                    <h2 class="text-lg font-bold mb-4 text-gray-900">Pagamento com Cartão</h2>
                    <div class="mb-6">
                        <p class="text-gray-600 text-sm mb-4">Você será redirecionado para a página segura de pagamento</p>
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                    </div>
                    <a x-show="paymentUrl" :href="paymentUrl" target="_blank"
                       class="inline-block px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-red-700 transition">
                        Ir para Pagamento Seguro
                    </a>
                    <p class="text-xs text-gray-500 mt-4">
                        Pagamento processado pelo Asaas - ambiente 100% seguro
                    </p>
                </div>

                <!-- Status Check -->
                <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <div class="animate-spin w-5 h-5 border-2 border-primary border-t-transparent rounded-full"></div>
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
