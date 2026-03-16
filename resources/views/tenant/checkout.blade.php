<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
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
                <!-- Animação Moderna - Jumping Dots -->
                <div class="mb-8 flex items-center justify-center">
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 bg-white rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-5 h-5 bg-white rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-5 h-5 bg-white rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>

                <h3 class="text-white text-2xl font-bold mb-2" x-text="loading ? 'Preparando seu pedido...' : 'Carregando suas informações...'"></h3>
                <p class="text-gray-300 text-sm" x-text="loading ? 'Estamos confirmando todos os detalhes' : 'Só mais um instante'"></p>
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
                <x-skeleton-card />
                <x-skeleton-card />
                <x-skeleton-card />
            </div>

            <!-- Carrinho vazio -->
            <div x-show="!pageLoading && cart.length === 0" x-cloak>
                <x-empty-state
                    title="Seu carrinho está vazio"
                    message="Adicione itens ao carrinho antes de finalizar o pedido"
                    icon="shopping-cart"
                    actionText="Ver Cardápio"
                    actionUrl="/"
                />
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
                    <div class="space-y-4">
                        <template x-for="item in cart" :key="item.cartId">
                            <div class="pb-4 border-b border-gray-100 last:border-0">
                                <div class="flex gap-3">
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

                                <!-- ⭐ Campo de observação por item -->
                                <div class="mt-3">
                                    <input
                                        type="text"
                                        x-model="item.notes"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition"
                                        placeholder="Observação para este item (ex: sem cebola, sem molho...)">
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
                        <div x-show="appliedCoupon && couponDiscount > 0" class="flex justify-between text-sm text-primary">
                            <span class="font-medium">🎟️ Cupom (<span x-text="appliedCoupon?.code"></span>)</span>
                            <span class="font-semibold">- R$ <span x-text="couponDiscount.toFixed(2).replace('.', ',')"></span></span>
                        </div>
                        <div x-show="useCashback && cashbackBalance > 0" class="flex justify-between text-sm text-green-600">
                            <span class="font-medium">💰 Desconto Cashback</span>
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
                <div x-show="cashbackIsActive" class="bg-white rounded-xl shadow-sm p-5 border border-gray-200">
                    <h2 class="text-base font-semibold mb-4 text-gray-900">Cashback</h2>

                    <!-- Vai Ganhar -->
                    <div x-show="willEarnCashback > 0" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Você vai ganhar</p>
                                    <p class="text-xs text-gray-600">
                                        <span x-text="cashbackPercentage"></span>% de cashback neste pedido
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600">
                                    +R$ <span x-text="willEarnCashback.toFixed(2).replace('.', ',')"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Saldo Disponível -->
                    <div x-show="cashbackBalance > 0" class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600">Saldo disponível</span>
                            <span class="text-sm font-semibold text-gray-900">R$ <span x-text="cashbackBalance.toFixed(2).replace('.', ',')"></span></span>
                        </div>

                        <label class="flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition"
                               :class="useCashback ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                            <input
                                type="checkbox"
                                x-model="useCashback"
                                id="use-cashback"
                                class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">Usar meu cashback</p>
                                <p class="text-xs text-gray-600 mt-0.5">
                                    Desconto de <strong>R$ <span x-text="Math.min(cashbackBalance, subtotal + currentDeliveryFee).toFixed(2).replace('.', ',')"></span></strong>
                                </p>
                            </div>
                        </label>
                    </div>

                    <!-- Sem Saldo -->
                    <div x-show="cashbackBalance === 0 && willEarnCashback === 0" class="text-center py-4">
                        <p class="text-sm text-gray-500">Você não possui saldo de cashback</p>
                    </div>
                </div>

                <!-- Cupom de Desconto -->
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-200">
                    <h2 class="text-base font-semibold mb-4 text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                        Cupom de Desconto
                    </h2>

                    <!-- Cupom Aplicado -->
                    <div x-show="appliedCoupon" x-cloak class="mb-4 p-3 bg-green-50 border-2 border-green-500 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-bold text-gray-900" x-text="appliedCoupon?.code"></p>
                                    <p class="text-xs text-gray-600" x-text="appliedCoupon?.description"></p>
                                </div>
                            </div>
                            <button
                                @click="removeCoupon()"
                                type="button"
                                class="text-red-600 hover:text-red-700 font-medium text-sm">
                                Remover
                            </button>
                        </div>
                        <div class="mt-2 text-right">
                            <p class="text-lg font-bold text-green-600">
                                - R$ <span x-text="couponDiscount.toFixed(2).replace('.', ',')"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Campo de Cupom -->
                    <div x-show="!appliedCoupon" class="space-y-3">
                        <div class="flex gap-2">
                            <input
                                type="text"
                                x-model="couponCode"
                                @keyup.enter="validateCoupon()"
                                placeholder="Digite o código do cupom"
                                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm font-mono uppercase focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                                :disabled="validatingCoupon">
                            <button
                                @click="validateCoupon()"
                                type="button"
                                :disabled="!couponCode || validatingCoupon"
                                :class="(!couponCode || validatingCoupon) ? 'bg-gray-300 cursor-not-allowed' : 'bg-primary hover:bg-red-700'"
                                class="px-6 py-3 text-white text-sm font-semibold rounded-lg transition">
                                <span x-show="!validatingCoupon">Aplicar</span>
                                <span x-show="validatingCoupon" class="inline-flex items-center gap-2">
                                    <x-loading-dots />
                                </span>
                            </button>
                        </div>

                        <!-- Mensagem de Erro -->
                        <div x-show="couponError" x-transition class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-700" x-text="couponError"></p>
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
                        :class="loading || !isFormValid ? 'bg-gray-300 cursor-not-allowed text-gray-600' : 'bg-primary hover:bg-red-700 text-white'"
                        class="w-full py-4 font-bold text-lg rounded-lg transition-all duration-200">
                        <span x-show="!loading">Confirmar Pedido - R$ <span x-text="total.toFixed(2).replace('.', ',')"></span></span>
                        <span x-show="loading" class="inline-flex items-center gap-2">
                            <x-loading-dots />
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
                                <option :value="neighborhood.name" x-text="neighborhood.name"></option>
                            </template>
                        </select>

                        <!-- Alerta quando não há bairros disponíveis -->
                        <div x-show="selectedCity && !loadingNeighborhoods && availableNeighborhoods.length === 0"
                             class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div class="text-sm text-yellow-800">
                                    <p class="font-semibold mb-1">Infelizmente não entregamos nesta região ainda 😔</p>
                                    <p>Entre em contato com {{ $tenant->name }} pelo WhatsApp
                                        <a :href="'https://wa.me/{{ preg_replace('/\D/', '', $tenant->phone) }}?text=Olá! Gostaria de fazer um pedido, mas meu bairro não está disponível.'"
                                           target="_blank"
                                           class="font-semibold underline hover:text-yellow-900">
                                            clicando aqui
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
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
            cashbackIsActive: false,
            cashbackPercentage: 0,
            willEarnCashback: 0,
            useCashback: false,
            couponCode: '',
            appliedCoupon: null,
            couponDiscount: 0,
            validatingCoupon: false,
            couponError: '',
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
                        this.cashbackIsActive = data.is_active || false;
                        this.cashbackPercentage = parseFloat(data.cashback_percentage) || 0;
                        console.log('💰 Cashback:', {
                            balance: this.cashbackBalance,
                            active: this.cashbackIsActive,
                            percentage: this.cashbackPercentage
                        });

                        // Calcular quanto vai ganhar
                        await this.calculateWillEarn();
                    }
                } catch (error) {
                    console.error('Erro ao carregar saldo de cashback:', error);
                    this.cashbackBalance = 0;
                    this.cashbackIsActive = false;
                }
            },

            async calculateWillEarn() {
                if (!this.cashbackIsActive || this.total === 0) {
                    this.willEarnCashback = 0;
                    return;
                }

                const token = localStorage.getItem('auth_token');
                if (!token) return;

                try {
                    const response = await fetch('/api/v1/cashback/calculate', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            total: this.total
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.willEarnCashback = parseFloat(data.will_earn) || 0;
                        console.log('🎁 Vai ganhar:', this.willEarnCashback);
                    }
                } catch (error) {
                    console.error('Erro ao calcular cashback:', error);
                    this.willEarnCashback = 0;
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
                // Calcula total: Subtotal + Entrega - Cupom - Cashback
                const totalBeforeDiscounts = this.subtotal + this.deliveryFee;
                const totalAfterCoupon = totalBeforeDiscounts - this.couponDiscount;
                const cashbackDiscount = this.useCashback ? Math.min(this.cashbackBalance, totalAfterCoupon) : 0;
                return Math.max(0, totalAfterCoupon - cashbackDiscount);
            },

            get cashbackAmount() {
                // Retorna quanto de cashback será usado (para exibição)
                const totalAfterCoupon = this.subtotal + this.deliveryFee - this.couponDiscount;
                return this.useCashback ? Math.min(this.cashbackBalance, totalAfterCoupon) : 0;
            },

            get isFormValid() {
                return this.selectedCity !== '' &&
                       this.selectedNeighborhood !== '' &&
                       this.deliveryStreet !== '' &&
                       this.deliveryNumber !== '' &&
                       this.paymentMethod !== '';
            },

            async validateCoupon() {
                if (!this.couponCode || this.couponCode.trim() === '') {
                    return;
                }

                this.validatingCoupon = true;
                this.couponError = '';

                try {
                    const orderTotal = this.subtotal + this.deliveryFee;

                    const response = await fetch('/api/v1/coupons/validate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            code: this.couponCode.trim().toUpperCase(),
                            order_total: orderTotal
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.valid) {
                        this.appliedCoupon = data.coupon;
                        this.couponDiscount = data.coupon.discount_amount;
                        this.couponCode = '';
                        this.couponError = '';
                        console.log('🎟️ Cupom aplicado:', this.appliedCoupon);
                    } else {
                        this.couponError = data.message || 'Cupom inválido';
                        this.appliedCoupon = null;
                        this.couponDiscount = 0;
                    }
                } catch (error) {
                    console.error('Erro ao validar cupom:', error);
                    this.couponError = 'Erro ao validar cupom. Tente novamente.';
                    this.appliedCoupon = null;
                    this.couponDiscount = 0;
                } finally {
                    this.validatingCoupon = false;
                }
            },

            removeCoupon() {
                this.appliedCoupon = null;
                this.couponDiscount = 0;
                this.couponCode = '';
                this.couponError = '';
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
                        notes: item.notes || '' // ⭐ Observação por item
                    }));

                    // Montar endereço completo
                    const fullAddress = `${this.deliveryStreet}, ${this.deliveryNumber}${this.deliveryComplement ? ' - ' + this.deliveryComplement : ''}${this.deliveryReference ? ' (' + this.deliveryReference + ')' : ''}`;

                    // ⭐ Se selecionou "pagar na entrega", usar o tipo específico (cash ou debit_card)
                    const finalPaymentMethod = this.paymentMethod === 'on_delivery'
                        ? this.deliveryPaymentType
                        : this.paymentMethod;

                    // Preparar payload
                    const payload = {
                        items: items,
                        delivery_address: fullAddress,
                        delivery_city: this.selectedCity,
                        delivery_neighborhood: this.selectedNeighborhood,
                        payment_method: finalPaymentMethod, // ⭐ CORRIGIDO: usa deliveryPaymentType se for on_delivery
                        use_cashback: this.useCashback, // ⭐ Boolean toggle (true = usar todo saldo)
                        coupon_code: this.appliedCoupon ? this.appliedCoupon.code : null, // ⭐ Cupom de desconto
                        notes: this.notes,
                        change_for: finalPaymentMethod === 'cash' ? this.changeFor : null // ⭐ CORRIGIDO: usa finalPaymentMethod
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
