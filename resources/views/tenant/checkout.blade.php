<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Pedido - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Skeleton loading */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s ease-in-out infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Overlay de confirmação */
        .order-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pulse-ring {
            width: 100px;
            height: 100px;
            border: 4px solid #EA1D2C;
            border-radius: 50%;
            animation: pulse-ring 1.5s ease-out infinite;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.95);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.7;
            }
            100% {
                transform: scale(0.95);
                opacity: 1;
            }
        }
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
    <div x-data="checkoutApp()" x-init="init()" class="min-h-screen">
        <!-- Overlay de Processamento -->
        <div x-show="loading || pageLoading" x-cloak class="order-overlay">
            <div class="text-center">
                <div class="pulse-ring mx-auto mb-6 flex items-center justify-center">
                    <svg class="w-12 h-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-white text-2xl font-bold mb-2" x-text="loading ? 'Processando seu pedido...' : 'Carregando...'"></h3>
                <p class="text-gray-300 text-sm mb-4" x-text="loading ? 'Aguarde enquanto confirmamos os dados' : 'Aguarde enquanto carregamos as informações'"></p>
                <div class="flex items-center justify-center gap-2">
                    <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0s"></div>
                    <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
            </div>
        </div>

        <!-- Header Estilo iFood -->
        <header class="bg-white sticky top-0 z-50 shadow-md">
            <div class="max-w-4xl mx-auto px-4 py-4">
                <a href="/" class="text-gray-700 hover:text-primary text-sm mb-3 inline-flex items-center gap-2 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar ao cardápio
                </a>
                <div class="flex items-center gap-3 mt-2">
                    <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Finalizar Pedido</h1>
                        <p class="text-sm text-gray-600">{{ $tenant->name }}</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Loading Skeleton -->
            <div x-show="pageLoading" class="space-y-4">
                <!-- Skeleton do Carrinho -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="skeleton h-6 w-32 rounded mb-4"></div>
                    <div class="space-y-3">
                        <div class="flex gap-3 pb-3 border-b border-gray-100">
                            <div class="flex-1 space-y-2">
                                <div class="skeleton h-5 w-48 rounded"></div>
                                <div class="skeleton h-4 w-64 rounded"></div>
                                <div class="skeleton h-4 w-24 rounded"></div>
                            </div>
                            <div class="skeleton h-6 w-20 rounded"></div>
                        </div>
                        <div class="flex gap-3 pb-3">
                            <div class="flex-1 space-y-2">
                                <div class="skeleton h-5 w-40 rounded"></div>
                                <div class="skeleton h-4 w-56 rounded"></div>
                                <div class="skeleton h-4 w-24 rounded"></div>
                            </div>
                            <div class="skeleton h-6 w-20 rounded"></div>
                        </div>
                    </div>
                </div>

                <!-- Skeleton de Endereço -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="skeleton h-6 w-48 rounded mb-4"></div>
                    <div class="space-y-3">
                        <div class="skeleton h-12 w-full rounded-lg"></div>
                        <div class="skeleton h-12 w-full rounded-lg"></div>
                    </div>
                </div>

                <!-- Skeleton de Pagamento -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="skeleton h-6 w-56 rounded mb-4"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="skeleton h-24 w-full rounded-lg"></div>
                        <div class="skeleton h-24 w-full rounded-lg"></div>
                    </div>
                </div>

                <!-- Skeleton do Botão -->
                <div class="skeleton h-14 w-full rounded-lg"></div>
            </div>

            <!-- Carrinho vazio -->
            <div x-show="!pageLoading && cart.length === 0" x-cloak class="bg-white rounded-xl shadow-sm p-12 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold mb-2 text-gray-900">Seu carrinho está vazio</h2>
                <p class="text-gray-600 mb-6">Adicione itens ao carrinho antes de finalizar o pedido</p>
                <a href="/" class="inline-block px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-red-700 transition">
                    Ver Cardápio
                </a>
            </div>

            <!-- Checkout Form -->
            <div x-show="!pageLoading && cart.length > 0" x-cloak class="space-y-4">
                <!-- Resumo do Carrinho -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="text-lg font-bold mb-4 text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                        </svg>
                        Seu Pedido
                    </h2>
                    <div class="space-y-3">
                        <template x-for="item in cart" :key="item.cartId">
                            <div class="flex gap-3 pb-3 border-b border-gray-100 last:border-0">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 text-[15px]" x-text="item.name"></p>
                                    <p class="text-xs text-gray-500 mt-1" x-show="item.details" x-text="item.details"></p>
                                    <p class="text-sm text-primary font-semibold mt-1">
                                        <span x-text="item.quantity"></span>x R$ <span x-text="item.price.toFixed(2).replace('.', ',')"></span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-lg text-gray-900" x-text="'R$ ' + (item.price * item.quantity).toFixed(2).replace('.', ',')"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="text-gray-900 font-medium" x-text="'R$ ' + subtotal.toFixed(2).replace('.', ',')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Taxa de entrega</span>
                            <span class="text-gray-900 font-medium" x-text="currentDeliveryFee > 0 ? 'R$ ' + currentDeliveryFee.toFixed(2).replace('.', ',') : (selectedNeighborhood ? 'Grátis' : 'A calcular')"></span>
                        </div>
                        <div x-show="useCashback && cashbackAmount > 0" class="flex justify-between text-sm text-green-600">
                            <span class="font-medium">Desconto Cashback</span>
                            <span class="font-semibold">- R$ <span x-text="cashbackAmount.toFixed(2).replace('.', ',')"></span></span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-gray-900 pt-2 border-t border-gray-200">
                            <span>Total</span>
                            <span class="text-primary" x-text="'R$ ' + total.toFixed(2).replace('.', ',')"></span>
                        </div>
                    </div>
                </div>

                <!-- Endereço Completo -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="text-lg font-bold mb-4 text-gray-900 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                            </svg>
                            Endereço de Entrega
                        </span>
                    </h2>

                    <!-- Endereços Salvos -->
                    <div x-show="savedAddresses.length > 0" class="space-y-2">
                        <template x-for="address in savedAddresses" :key="address.id">
                            <div
                                :class="selectedAddressId === address.id ? 'border-primary bg-red-50' : 'border-gray-200 hover:border-gray-300'"
                                class="flex items-start gap-3 p-3 border-2 rounded-lg transition">
                                <input
                                    type="radio"
                                    :value="address.id"
                                    x-model="selectedAddressId"
                                    @change="selectSavedAddress(address)"
                                    class="mt-1 w-4 h-4 text-primary cursor-pointer">
                                <div class="flex-1 cursor-pointer" @click="selectedAddressId = address.id; selectSavedAddress(address)">
                                    <p class="font-semibold text-sm text-gray-900" x-text="address.label || 'Endereço'"></p>
                                    <p class="text-xs text-gray-600 mt-0.5" x-text="`${address.street}, ${address.number}${address.complement ? ' - ' + address.complement : ''}`"></p>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="`${address.neighborhood} - ${address.city}`"></p>
                                </div>
                                <button
                                    @click.stop="editAddress(address)"
                                    type="button"
                                    class="p-2 text-gray-400 hover:text-primary hover:bg-red-50 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <!-- Botão Adicionar Novo -->
                        <button
                            @click="openAddressModal()"
                            type="button"
                            class="w-full p-3 border-2 border-dashed border-gray-300 rounded-lg text-sm font-semibold text-gray-600 hover:border-primary hover:text-primary hover:bg-red-50 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Adicionar Novo Endereço
                        </button>
                    </div>

                    <!-- Se não tem endereços salvos, mostrar botão para abrir modal -->
                    <div x-show="savedAddresses.length === 0" class="text-center py-6">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-gray-500 text-sm mb-4">Você ainda não tem endereços salvos</p>
                        <button
                            @click="openAddressModal()"
                            type="button"
                            class="px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-red-700 transition inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Adicionar Endereço
                        </button>
                    </div>
                </div>

                <!-- Cashback -->
                <div x-show="cashbackBalance > 0" class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl shadow-sm p-5 border border-green-200">
                    <h2 class="text-lg font-bold mb-3 text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Usar Saldo de Cashback
                        <span class="ml-auto text-sm bg-green-600 text-white px-3 py-1 rounded-full font-semibold">
                            Disponível: R$ <span x-text="cashbackBalance.toFixed(2).replace('.', ',')"></span>
                        </span>
                    </h2>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                x-model="useCashback"
                                id="use-cashback"
                                class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="use-cashback" class="text-sm font-medium text-gray-700 cursor-pointer">
                                Quero usar meu saldo de cashback neste pedido
                            </label>
                        </div>
                        <div x-show="useCashback" x-transition class="mt-3">
                            <label class="block text-sm font-semibold mb-2 text-gray-900">Quanto deseja usar?</label>
                            <div class="flex gap-2">
                                <input
                                    type="number"
                                    x-model.number="cashbackAmount"
                                    :max="Math.min(cashbackBalance, subtotal)"
                                    min="0"
                                    step="0.01"
                                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                                    placeholder="0.00">
                                <button
                                    @click="cashbackAmount = Math.min(cashbackBalance, subtotal)"
                                    type="button"
                                    class="px-4 py-3 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition whitespace-nowrap">
                                    Usar Tudo
                                </button>
                            </div>
                            <p class="text-xs text-gray-600 mt-2">
                                💡 Você pode usar até <strong>R$ <span x-text="Math.min(cashbackBalance, subtotal).toFixed(2).replace('.', ',')"></span></strong> neste pedido
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Forma de Pagamento -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="text-lg font-bold mb-4 text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                        </svg>
                        Forma de Pagamento
                    </h2>

                    <!-- Grid de Métodos de Pagamento -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <!-- PIX -->
                        <button
                            type="button"
                            x-show="paymentMethods.find(m => m.key === 'pix')"
                            @click="selectPayment('pix')"
                            :class="paymentMethod === 'pix' ? 'border-primary bg-red-50 ring-2 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                            class="p-4 border-2 rounded-lg transition text-center">
                            <div class="flex items-center justify-center h-16">
                                <img src="/images/pix-logo.png" alt="PIX" class="h-12 w-auto">
                            </div>
                            <div class="font-semibold text-xs text-gray-900 mt-2">PIX</div>
                        </button>

                        <!-- Cartão de Crédito -->
                        <button
                            type="button"
                            x-show="paymentMethods.find(m => m.key === 'credit_card')"
                            @click="selectPayment('credit_card')"
                            :class="paymentMethod === 'credit_card' ? 'border-primary bg-red-50 ring-2 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                            class="p-4 border-2 rounded-lg transition text-center">
                            <div class="flex items-center justify-center h-16 gap-2">
                                <img src="/images/visa-logo.png" alt="Visa" class="h-8 w-auto">
                                <img src="/images/mastercard-logo.png" alt="Mastercard" class="h-8 w-auto">
                            </div>
                            <div class="font-semibold text-xs text-gray-900 mt-2">Crédito</div>
                        </button>

                        <!-- Cartão de Débito -->
                        <button
                            type="button"
                            x-show="paymentMethods.find(m => m.key === 'debit_card')"
                            @click="selectPayment('debit_card')"
                            :class="paymentMethod === 'debit_card' ? 'border-primary bg-red-50 ring-2 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                            class="p-4 border-2 rounded-lg transition text-center">
                            <div class="flex items-center justify-center h-16 gap-2">
                                <img src="/images/visa-logo.png" alt="Visa" class="h-8 w-auto">
                                <img src="/images/mastercard-logo.png" alt="Mastercard" class="h-8 w-auto">
                            </div>
                            <div class="font-semibold text-xs text-gray-900 mt-2">Débito</div>
                        </button>

                        <!-- Pagar na Entrega -->
                        <button
                            type="button"
                            x-show="paymentMethods.find(m => m.key === 'on_delivery')"
                            @click="selectPayment('on_delivery')"
                            :class="paymentMethod === 'on_delivery' ? 'border-primary bg-red-50 ring-2 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                            class="p-4 border-2 rounded-lg transition text-center">
                            <div class="flex items-center justify-center h-16">
                                <svg class="w-12 h-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                                </svg>
                            </div>
                            <div class="font-semibold text-xs text-gray-900 mt-2">Na Entrega</div>
                        </button>
                    </div>

                    <!-- Opções de Pagamento na Entrega -->
                    <div x-show="paymentMethod === 'on_delivery'" x-cloak x-transition class="mt-4 space-y-3">
                        <label class="block text-sm font-semibold mb-2 text-gray-900">Como você vai pagar?</label>

                        <template x-for="option in deliveryPaymentOptions" :key="option.key">
                            <label
                                :class="deliveryPaymentType === option.key ? 'border-primary bg-red-50 ring-2 ring-primary' : 'border-gray-200 hover:border-gray-300'"
                                class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition">
                                <input
                                    type="radio"
                                    :value="option.key"
                                    x-model="deliveryPaymentType"
                                    class="text-primary focus:ring-primary">
                                <!-- Logo ou ícone -->
                                <div class="w-10 h-10 flex items-center justify-center">
                                    <img x-show="option.logo" :src="option.logo" :alt="option.label" class="h-8 w-auto">
                                    <span x-show="!option.logo" class="text-2xl" x-text="option.icon"></span>
                                </div>
                                <span class="flex-1 font-medium text-sm" x-text="option.label"></span>
                                <span x-show="option.type === 'meal_voucher'" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">Vale</span>
                            </label>
                        </template>

                        <!-- Campo de Troco (se dinheiro) -->
                        <div x-show="deliveryPaymentType === 'cash'" x-transition class="mt-3 space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    x-model="needsChange"
                                    class="rounded text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-900">Precisa de troco?</span>
                            </label>

                            <div x-show="needsChange" x-transition>
                                <label class="block text-sm font-medium mb-1 text-gray-700">Troco para quanto?</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">R$</span>
                                    <input
                                        type="number"
                                        x-model="changeFor"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition text-sm"
                                        placeholder="50.00"
                                        step="0.01"
                                        :max="maxChange">
                                </div>
                                <p x-show="maxChange" class="text-xs text-gray-500 mt-1">
                                    💡 Troco disponível até R$ <span x-text="maxChange ? maxChange.toFixed(2).replace('.', ',') : '0,00'"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Instruções (se houver) -->
                        <div x-show="deliveryInstructions" x-transition class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs font-medium text-blue-900 mb-1">📋 Instruções:</p>
                            <p class="text-xs text-blue-700" x-text="deliveryInstructions"></p>
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="text-lg font-bold mb-4 text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                        </svg>
                        Observações (Opcional)
                    </h2>
                    <textarea
                        x-model="notes"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition text-sm"
                        placeholder="Ex: Sem cebola, caprichar no molho..."></textarea>
                </div>

                <!-- Botão Confirmar -->
                <div class="sticky bottom-0 bg-white rounded-xl shadow-lg p-4 border-t border-gray-200">
                    <button
                        @click="submitOrder()"
                        :disabled="loading || !isFormValid"
                        :class="loading || !isFormValid ? 'bg-gray-300 cursor-not-allowed' : 'bg-primary hover:bg-red-700'"
                        class="w-full py-4 text-white font-bold text-lg rounded-lg transition-all duration-200">
                        <span x-show="!loading">Confirmar Pedido - R$ <span x-text="total.toFixed(2).replace('.', ',')"></span></span>
                        <span x-show="loading">
                            <svg class="animate-spin inline w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processando...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de Endereço (Adicionar/Editar) -->
        <div x-show="showAddressModal" x-cloak class="fixed inset-0 z-50 flex items-end md:items-center md:justify-center">
            <!-- Backdrop -->
            <div @click="closeAddressModal()" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

            <!-- Modal Content -->
            <div class="relative bg-white w-full md:max-w-lg md:rounded-xl rounded-t-xl max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="sticky top-0 bg-white border-b border-gray-200 px-5 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900" x-text="editingAddressId ? 'Editar Endereço' : 'Novo Endereço'"></h3>
                    <button @click="closeAddressModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <div class="p-5 space-y-3">
                    <!-- Label (Casa, Trabalho, etc) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Identificação</label>
                        <input
                            type="text"
                            x-model="addressLabel"
                            placeholder="Ex: Casa, Trabalho, Casa da Vó..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                    </div>

                    <!-- Cidade -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cidade *</label>
                        <select
                            x-model="selectedCity"
                            @change="loadNeighborhoodsForModal()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                            <option value="">Selecione a cidade</option>
                            <template x-for="city in availableCities" :key="city">
                                <option :value="city" x-text="city"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Bairro -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bairro *</label>
                        <select
                            x-model="selectedNeighborhood"
                            :disabled="!selectedCity || loadingNeighborhoods"
                            @change="updateDeliveryFeeFromNeighborhood()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none disabled:bg-gray-100">
                            <option value="">
                                <span x-show="!selectedCity">Selecione a cidade primeiro</span>
                                <span x-show="selectedCity && loadingNeighborhoods">Carregando...</span>
                                <span x-show="selectedCity && !loadingNeighborhoods">Selecione o bairro</span>
                            </option>
                            <template x-for="neighborhood in availableNeighborhoods" :key="neighborhood.id">
                                <option :value="neighborhood.name" x-text="`${neighborhood.name} - R$ ${parseFloat(neighborhood.delivery_fee).toFixed(2).replace('.', ',')}`"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Rua -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rua *</label>
                        <input
                            type="text"
                            x-model="deliveryStreet"
                            placeholder="Nome da rua"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                    </div>

                    <!-- Número e Complemento -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número *</label>
                            <input
                                type="text"
                                x-model="deliveryNumber"
                                placeholder="123"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                            <input
                                type="text"
                                x-model="deliveryComplement"
                                placeholder="Apto, Bloco..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                        </div>
                    </div>

                    <!-- CEP -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                        <input
                            type="text"
                            x-model="deliveryZipcode"
                            placeholder="00000-000"
                            maxlength="9"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none">
                    </div>

                    <!-- Checkbox Salvar -->
                    <div class="flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <input
                            type="checkbox"
                            x-model="shouldSaveAddress"
                            id="save-address"
                            class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <label for="save-address" class="text-sm text-gray-700 cursor-pointer">
                            <span class="font-semibold">Salvar este endereço</span> para pedidos futuros
                        </label>
                    </div>

                    <!-- Taxa de entrega -->
                    <div x-show="selectedNeighborhood && currentDeliveryFee > 0" class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <span class="text-sm text-blue-900 font-medium">Taxa de entrega:</span>
                        <span class="text-sm font-bold text-blue-900">
                            R$ <span x-text="currentDeliveryFee.toFixed(2).replace('.', ',')"></span>
                        </span>
                    </div>

                    <p class="text-xs text-gray-500">* Campos obrigatórios</p>
                </div>

                <!-- Footer -->
                <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 flex gap-3">
                    <button
                        @click="closeAddressModal()"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button
                        @click="saveAddress()"
                        :disabled="!deliveryStreet || !deliveryNumber || !selectedCity || !selectedNeighborhood || savingAddress"
                        :class="(!deliveryStreet || !deliveryNumber || !selectedCity || !selectedNeighborhood || savingAddress) ? 'bg-gray-300 cursor-not-allowed' : 'bg-primary hover:bg-red-700'"
                        class="flex-1 px-4 py-3 rounded-lg font-semibold text-white transition">
                        <span x-show="!savingAddress" x-text="editingAddressId ? 'Atualizar' : 'Confirmar'"></span>
                        <span x-show="savingAddress">Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function checkoutApp() {
        return {
            cart: [],
            customerName: '',
            customerPhone: '',
            customerEmail: '',
            paymentMethod: 'pix',
            changeFor: '',
            notes: '',
            loading: false,
            pageLoading: true,
            savedAddresses: [],
            selectedAddressId: null,
            showAddressModal: false,
            selectedCity: '',
            selectedNeighborhood: '',
            deliveryStreet: '',
            deliveryNumber: '',
            deliveryComplement: '',
            deliveryReference: '',
            currentDeliveryFee: 0,
            currentDeliveryTime: 0,
            availableCities: [],
            availableNeighborhoods: [],
            loadingNeighborhoods: false,
            cashbackBalance: 0,
            useCashback: false,
            cashbackAmount: 0,
            addressLabel: '',
            deliveryZipcode: '',
            editingAddressId: null,
            shouldSaveAddress: true,
            savingAddress: false,
            paymentMethods: [],
            deliveryPaymentOptions: [],
            deliveryPaymentType: null,
            needsChange: false,

            async init() {
                // Verificar se está autenticado
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    window.location.href = '/?login=required';
                    return;
                }

                // Carregar dados do cliente
                const customer = JSON.parse(localStorage.getItem('customer') || '{}');
                if (customer.name) {
                    this.customerName = customer.name;
                    this.customerPhone = customer.phone || '';
                    this.customerEmail = customer.email || '';
                }

                // Carregar saldo de cashback
                await this.loadCashbackBalance();

                // Carregar carrinho do localStorage
                const savedCart = localStorage.getItem('yumgo_cart');
                if (savedCart) {
                    try {
                        this.cart = JSON.parse(savedCart);
                    } catch (e) {
                        console.error('Erro ao carregar carrinho:', e);
                        this.cart = [];
                    }
                }

                // Carregar endereços salvos, cidades disponíveis e métodos de pagamento
                await Promise.all([
                    this.loadSavedAddresses(),
                    this.loadAvailableCities(),
                    this.loadPaymentMethods()
                ]);

                // Se há endereços salvos, selecionar o primeiro automaticamente
                if (this.savedAddresses.length > 0) {
                    const defaultAddress = this.savedAddresses[0];
                    this.selectedAddressId = defaultAddress.id;
                    await this.selectSavedAddress(defaultAddress);
                }

                // Finalizar loading
                this.pageLoading = false;

                // Se carrinho vazio, redirecionar para home
                if (this.cart.length === 0) {
                    console.warn('⚠️ Carrinho vazio, redirecionando...');
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 1000);
                }
            },

            async loadSavedAddresses() {
                const token = localStorage.getItem('auth_token');
                try {
                    const response = await fetch('/api/v1/addresses', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    if (response.ok) {
                        const data = await response.json();
                        this.savedAddresses = data.data || [];
                    }
                } catch (error) {
                    console.error('Erro ao carregar endereços:', error);
                }
            },

            async loadAvailableCities() {
                try {
                    const response = await fetch('/api/v1/location/enabled-cities');
                    if (response.ok) {
                        const data = await response.json();
                        this.availableCities = data.data || [];
                    }
                } catch (error) {
                    console.error('Erro ao carregar cidades:', error);
                }
            },

            async loadPaymentMethods() {
                try {
                    const response = await fetch('/api/v1/settings/payment-methods');
                    if (response.ok) {
                        const data = await response.json();
                        this.paymentMethods = data.data || [];

                        // Se tem método "on_delivery", extrair as opções
                        const onDeliveryMethod = this.paymentMethods.find(m => m.key === 'on_delivery');
                        if (onDeliveryMethod && onDeliveryMethod.options) {
                            this.deliveryPaymentOptions = onDeliveryMethod.options;
                            // Selecionar primeira opção por padrão
                            if (this.deliveryPaymentOptions.length > 0) {
                                this.deliveryPaymentType = this.deliveryPaymentOptions[0].key;
                            }
                        }

                        console.log('💳 Métodos de pagamento carregados:', this.paymentMethods);
                    }
                } catch (error) {
                    console.error('Erro ao carregar métodos de pagamento:', error);
                }
            },

            async loadCashbackBalance() {
                const token = localStorage.getItem('auth_token');
                if (!token) return;

                try {
                    const response = await fetch('/api/v1/cashback/balance', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    if (response.ok) {
                        const data = await response.json();
                        this.cashbackBalance = parseFloat(data.balance) || 0;
                        console.log('💰 Saldo de cashback:', this.cashbackBalance);
                    }
                } catch (error) {
                    console.error('Erro ao carregar saldo de cashback:', error);
                    this.cashbackBalance = 0;
                }
            },

            async loadNeighborhoodsForCheckout() {
                if (!this.selectedCity) {
                    this.availableNeighborhoods = [];
                    return;
                }

                this.loadingNeighborhoods = true;
                try {
                    const response = await fetch(`/api/v1/location/enabled-neighborhoods/${encodeURIComponent(this.selectedCity)}`);
                    if (response.ok) {
                        const data = await response.json();
                        this.availableNeighborhoods = data.data || [];

                        // Se já tem bairro selecionado, atualizar taxa
                        if (this.selectedNeighborhood) {
                            this.updateDeliveryFeeFromNeighborhood();
                        }
                    }
                } catch (error) {
                    console.error('Erro ao carregar bairros:', error);
                } finally {
                    this.loadingNeighborhoods = false;
                }
            },

            async selectSavedAddress(address) {
                this.selectedCity = address.city;
                this.selectedNeighborhood = address.neighborhood;
                this.deliveryStreet = address.street;
                this.deliveryNumber = address.number;
                this.deliveryComplement = address.complement || '';
                this.deliveryReference = '';

                // Carregar bairros da cidade e atualizar taxa
                await this.loadNeighborhoodsForCheckout();

                // Calcular taxa de entrega automaticamente
                this.updateDeliveryFeeFromNeighborhood();
            },

            openAddressModal() {
                this.showAddressModal = true;
                this.editingAddressId = null;
                this.addressLabel = '';
                this.selectedCity = '';
                this.selectedNeighborhood = '';
                this.deliveryStreet = '';
                this.deliveryNumber = '';
                this.deliveryComplement = '';
                this.deliveryZipcode = '';
                this.shouldSaveAddress = true;
                this.availableNeighborhoods = [];
            },

            closeAddressModal() {
                this.showAddressModal = false;
                this.editingAddressId = null;
            },

            async editAddress(address) {
                this.showAddressModal = true;
                this.editingAddressId = address.id;
                this.addressLabel = address.label || '';
                this.selectedCity = address.city;
                this.deliveryStreet = address.street;
                this.deliveryNumber = address.number;
                this.deliveryComplement = address.complement || '';
                this.deliveryZipcode = address.zipcode || '';
                this.shouldSaveAddress = true;

                // Carregar bairros da cidade
                await this.loadNeighborhoodsForModal();
                this.selectedNeighborhood = address.neighborhood;
            },

            async saveAddress() {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    alert('Você precisa estar logado para salvar endereços');
                    return;
                }

                this.savingAddress = true;

                try {
                    const addressData = {
                        label: this.addressLabel,
                        city: this.selectedCity,
                        neighborhood: this.selectedNeighborhood,
                        street: this.deliveryStreet,
                        number: this.deliveryNumber,
                        complement: this.deliveryComplement,
                        zipcode: this.deliveryZipcode,
                        is_default: this.savedAddresses.length === 0 // Primeiro endereço é padrão
                    };

                    let response;
                    if (this.editingAddressId) {
                        // Atualizar endereço existente
                        response = await fetch(`/api/v1/addresses/${this.editingAddressId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(addressData)
                        });
                    } else if (this.shouldSaveAddress) {
                        // Salvar novo endereço
                        response = await fetch('/api/v1/addresses', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(addressData)
                        });
                    }

                    if (this.shouldSaveAddress || this.editingAddressId) {
                        if (response.ok) {
                            const data = await response.json();
                            // Recarregar lista de endereços
                            await this.loadSavedAddresses();

                            // Selecionar o endereço salvo/atualizado
                            const savedAddress = this.savedAddresses.find(a => a.id === (this.editingAddressId || data.data.id));
                            if (savedAddress) {
                                this.selectedAddressId = savedAddress.id;
                                await this.selectSavedAddress(savedAddress);
                            }
                        } else {
                            const error = await response.json();
                            alert('Erro ao salvar endereço: ' + (error.message || 'Erro desconhecido'));
                            this.savingAddress = false;
                            return;
                        }
                    } else {
                        // Apenas usar o endereço sem salvar
                        this.selectedAddressId = null;
                        await this.loadNeighborhoodsForModal();
                        this.updateDeliveryFeeFromNeighborhood();
                    }

                    this.closeAddressModal();
                } catch (error) {
                    console.error('Erro ao salvar endereço:', error);
                    alert('Erro ao salvar endereço. Tente novamente.');
                } finally {
                    this.savingAddress = false;
                }
            },

            async loadNeighborhoodsForModal() {
                if (!this.selectedCity) {
                    this.availableNeighborhoods = [];
                    return;
                }

                this.loadingNeighborhoods = true;

                try {
                    const response = await fetch(`/api/v1/location/enabled-neighborhoods/${encodeURIComponent(this.selectedCity)}`);
                    if (response.ok) {
                        const data = await response.json();
                        this.availableNeighborhoods = data.data || [];
                    }
                } catch (error) {
                    console.error('Erro ao carregar bairros:', error);
                    this.availableNeighborhoods = [];
                } finally {
                    this.loadingNeighborhoods = false;
                }
            },

            updateDeliveryFeeFromNeighborhood() {
                if (!this.selectedNeighborhood) {
                    this.currentDeliveryFee = 0;
                    this.currentDeliveryTime = 0;
                    return;
                }

                // Buscar a taxa do bairro selecionado
                const neighborhood = this.availableNeighborhoods.find(n => n.name === this.selectedNeighborhood);
                if (neighborhood) {
                    this.currentDeliveryFee = parseFloat(neighborhood.delivery_fee) || 0;
                    this.currentDeliveryTime = parseInt(neighborhood.delivery_time) || 30;
                }
            },

            selectPayment(method) {
                this.paymentMethod = method;

                // Se selecionar "on_delivery", garantir que tem uma opção selecionada
                if (method === 'on_delivery' && this.deliveryPaymentOptions.length > 0) {
                    if (!this.deliveryPaymentType) {
                        this.deliveryPaymentType = this.deliveryPaymentOptions[0].key;
                    }
                }
            },

            get subtotal() {
                return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },

            get deliveryFee() {
                return this.currentDeliveryFee;
            },

            get total() {
                const cashbackDiscount = this.useCashback ? Math.min(this.cashbackAmount, this.subtotal + this.deliveryFee) : 0;
                return Math.max(0, this.subtotal + this.deliveryFee - cashbackDiscount);
            },

            get isFormValid() {
                return this.selectedCity !== '' &&
                       this.selectedNeighborhood !== '' &&
                       this.deliveryStreet !== '' &&
                       this.deliveryNumber !== '' &&
                       this.paymentMethod !== '';
            },

            async submitOrder() {
                if (!this.isFormValid) {
                    alert('Por favor, preencha todos os campos obrigatórios!');
                    return;
                }

                if (this.cart.length === 0) {
                    alert('Seu carrinho está vazio!');
                    return;
                }

                this.loading = true;

                try {
                    // Pegar token de autenticação
                    const token = localStorage.getItem('auth_token');
                    if (!token) {
                        window.location.href = '/?login=required';
                        return;
                    }

                    // Validar endereço
                    if (!this.selectedCity || !this.selectedNeighborhood) {
                        alert('❌ Erro: Selecione um endereço de entrega válido.');
                        this.loading = false;
                        return;
                    }

                    // Preparar items no formato da API
                    const items = this.cart.map(item => ({
                        product_id: item.id,
                        quantity: item.quantity,
                        variation_id: item.variationId || null,
                        addons: item.addons || [],
                        notes: item.details || ''
                    }));

                    // Montar endereço completo
                    const fullAddress = `${this.deliveryStreet}, ${this.deliveryNumber}${this.deliveryComplement ? ' - ' + this.deliveryComplement : ''}${this.deliveryReference ? ' (' + this.deliveryReference + ')' : ''}`;

                    // Preparar payload
                    const payload = {
                        items: items,
                        delivery_address: fullAddress,
                        delivery_city: this.selectedCity,
                        delivery_neighborhood: this.selectedNeighborhood,
                        payment_method: this.paymentMethod,
                        use_cashback: this.useCashback ? this.cashbackAmount : 0,
                        notes: this.notes,
                        change_for: this.paymentMethod === 'cash' ? this.changeFor : null
                    };

                    console.log('📦 Payload do pedido:', payload);
                    console.log('📍 Cidade:', this.selectedCity);
                    console.log('📍 Bairro:', this.selectedNeighborhood);
                    console.log('💰 Taxa de entrega:', this.currentDeliveryFee);

                    // Fazer requisição
                    const response = await fetch('/api/v1/orders', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    });

                    console.log('📡 Status da resposta:', response.status);

                    // Verificar se a resposta é JSON válido
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const text = await response.text();
                        console.error('❌ Resposta não é JSON:', text);
                        console.error('❌ Content-Type:', contentType);
                        console.error('❌ Status:', response.status);
                        throw new Error('Erro no servidor. Verifique o console para mais detalhes.');
                    }

                    const data = await response.json();
                    console.log('📥 Resposta da API:', data);

                    if (!response.ok) {
                        if (response.status === 401) {
                            alert('❌ Sessão expirada! Faça login novamente.');
                            localStorage.removeItem('auth_token');
                            localStorage.removeItem('customer');
                            window.location.href = '/?login=required';
                            return;
                        }

                        // Mostrar erro de validação se houver
                        if (data.errors) {
                            console.error('❌ Erros de validação:', data.errors);
                            const errorMessages = Object.values(data.errors).flat().join('\n');
                            throw new Error(errorMessages || 'Erro de validação');
                        }

                        throw new Error(data.message || 'Erro ao criar pedido');
                    }

                    // Limpar carrinho
                    this.cart = [];
                    localStorage.removeItem('yumgo_cart');
                    localStorage.removeItem('yumgo_delivery');

                    // Redirecionar baseado no método de pagamento usando ORDER_NUMBER
                    const orderNumber = data.order.order_number; // Ex: 20260226-3CF56E
                    if (this.paymentMethod === 'cash') {
                        window.location.href = `/pedido/${orderNumber}/confirmado`;
                    } else {
                        window.location.href = `/pedido/${orderNumber}/pagamento`;
                    }

                } catch (error) {
                    console.error('❌ Erro no checkout:', error);

                    // Mostrar erro bonito
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-4 rounded-lg shadow-2xl max-w-md animate-fade-in';
                    errorDiv.innerHTML = `
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="font-bold text-lg">Erro ao finalizar pedido</p>
                                <p class="text-sm mt-1 opacity-90">${error.message || 'Erro desconhecido'}</p>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()" class="text-white/80 hover:text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    `;
                    document.body.appendChild(errorDiv);
                    setTimeout(() => errorDiv.remove(), 7000);

                    this.loading = false;
                }
            }
        }
    }
    </script>
</body>
</html>
