@extends('tenant.layouts.app')

@section('title', 'Meu Perfil')

@section('content')
<div x-data="profileApp()" x-init="init()" class="bg-gray-50 min-h-screen pb-24">
    <!-- Header Clean -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-2xl mx-auto px-4 py-6">
            <!-- Botão Voltar -->
            <a href="/" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-4 font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar ao cardápio
            </a>

            <div class="flex items-center gap-4">
                <!-- Avatar Clean -->
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h1 class="text-xl font-semibold text-gray-900" x-text="customer.name || 'Carregando...'"></h1>
                    <p class="text-sm text-gray-500" x-text="customer.email"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-6 space-y-4">
        <!-- Cashback Card Clean -->
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium">Saldo Cashback</span>
                </div>
                <span class="text-xs px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full font-medium" x-text="getTierLabel(customer.loyalty_tier)"></span>
            </div>
            <div class="flex items-baseline justify-between">
                <p class="text-3xl font-bold text-gray-900" x-text="'R$ ' + parseFloat(customer.cashback_balance || 0).toFixed(2).replace('.', ',')"></p>
                <button @click="showCashbackHistory = true" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
                    Ver histórico →
                </button>
            </div>
        </div>

        <!-- Stats Clean -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
                <div class="flex items-center justify-center gap-2 mb-2 text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span class="text-xs font-medium">Pedidos</span>
                </div>
                <p class="text-2xl font-bold text-gray-900" x-text="customer.total_orders || 0"></p>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
                <div class="flex items-center justify-center gap-2 mb-2 text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-xs font-medium">Total Gasto</span>
                </div>
                <p class="text-2xl font-bold text-gray-900" x-text="'R$ ' + parseFloat(customer.total_spent || 0).toFixed(0)"></p>
            </div>
        </div>

        <!-- Menu de Opções Clean -->
        <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-100">
            <!-- Dados Pessoais -->
            <button @click="showEditProfile = true" class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Dados Pessoais</span>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <!-- Meus Endereços -->
            <button @click="showAddressesModal = true" class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <div class="text-left">
                        <span class="text-sm font-medium text-gray-700">Meus Endereços</span>
                        <p class="text-xs text-gray-500" x-text="addresses.length + ' endereço(s) cadastrado(s)'"></p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <!-- Meus Pedidos -->
            <a href="/meus-pedidos" class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Meus Pedidos</span>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <!-- Sair -->
            <button @click="logout()" class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="text-sm font-medium text-red-600">Sair da Conta</span>
                </div>
            </button>
        </div>
    </div>

    <!-- Modal Editar Perfil -->
    <div x-show="showEditProfile" @click.self="showEditProfile = false" x-cloak
         class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-end md:items-center md:justify-center z-[9999]" x-transition>
        <div class="bg-white w-full md:max-w-lg md:rounded-2xl rounded-t-3xl max-h-[85vh] overflow-hidden shadow-2xl" @click.stop x-transition>
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Dados Pessoais</h2>
                <button @click="showEditProfile = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 overflow-y-auto max-h-[calc(85vh-80px)]">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Nome Completo</label>
                        <input type="text" x-model="customer.name"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">E-mail</label>
                        <input type="email" x-model="customer.email" disabled
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500" />
                        <p class="text-xs text-gray-500 mt-1">O e-mail não pode ser alterado</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Telefone</label>
                        <input type="tel" x-model="customer.phone"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Data de Nascimento</label>
                        <input type="date" x-model="customer.birth_date"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400" />
                        <p class="text-xs text-gray-500 mt-1">🎂 Cashback em dobro no seu aniversário!</p>
                    </div>

                    <button @click="saveProfile()"
                            class="w-full py-3.5 bg-gray-900 text-white rounded-lg font-medium text-sm hover:bg-gray-800 transition">
                        Salvar Alterações
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Endereços -->
    <div x-show="showAddressesModal" @click.self="showAddressesModal = false" x-cloak
         class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-end md:items-center md:justify-center z-[9999]" x-transition>
        <div class="bg-white w-full md:max-w-lg md:rounded-2xl rounded-t-3xl max-h-[85vh] overflow-hidden shadow-2xl" @click.stop x-transition>
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Meus Endereços</h2>
                <button @click="showAddressesModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 overflow-y-auto max-h-[calc(85vh-160px)]">
                <template x-if="addresses.length === 0">
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm text-gray-500 font-medium">Nenhum endereço cadastrado</p>
                    </div>
                </template>

                <div class="space-y-3">
                    <template x-for="address in addresses" :key="address.id">
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition">
                            <div class="flex justify-between gap-3">
                                <div class="flex-1">
                                    <p class="font-medium text-sm text-gray-900 mb-1" x-text="address.label || 'Endereço'"></p>
                                    <p class="text-xs text-gray-600" x-text="address.street + ', ' + address.number"></p>
                                    <p class="text-xs text-gray-500" x-text="address.neighborhood + ' - ' + address.city"></p>
                                </div>
                                <button @click="deleteAddress(address.id)" class="text-gray-400 hover:text-red-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-200">
                <button @click="showAddAddress = true; showAddressesModal = false"
                        class="w-full py-3 bg-gray-900 text-white rounded-lg font-medium text-sm hover:bg-gray-800 transition">
                    + Adicionar Endereço
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Novo Endereço -->
    <div x-show="showAddAddress" @click.self="showAddAddress = false" x-cloak
         class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-end md:items-center md:justify-center z-[9999]" x-transition>
        <div class="bg-white w-full md:max-w-lg md:rounded-2xl rounded-t-3xl max-h-[85vh] overflow-hidden shadow-2xl" @click.stop x-transition>
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Novo Endereço</h2>
                <button @click="showAddAddress = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 overflow-y-auto max-h-[calc(85vh-160px)]">
                <div class="space-y-4">
                    <input type="text" x-model="newAddress.label" placeholder="Nome (Ex: Casa, Trabalho)"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400" />

                    <select x-model="newAddress.city" @change="loadNeighborhoods()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400">
                        <option value="">Selecione a cidade</option>
                        <template x-for="city in availableCities" :key="city">
                            <option :value="city" x-text="city"></option>
                        </template>
                    </select>

                    <!-- Bairro - Dropdown com Busca (Igual Checkout) -->
                    <div x-data="{
                        open: false,
                        search: '',
                        get filteredNeighborhoods() {
                            if (!this.search) return availableNeighborhoods;
                            return availableNeighborhoods.filter(n =>
                                n.name.toLowerCase().includes(this.search.toLowerCase())
                            );
                        }
                    }" @click.away="open = false">
                        <div class="relative">
                            <button
                                type="button"
                                @click="open = !open"
                                :disabled="!newAddress.city || loadingNeighborhoods"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400 disabled:bg-gray-50 text-left flex items-center justify-between"
                                :class="!newAddress.neighborhood ? 'text-gray-400' : 'text-gray-900'">
                                <span x-text="newAddress.neighborhood || (loadingNeighborhoods ? 'Carregando bairros...' : 'Selecione ou pesquise o bairro')"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Loading spinner -->
                            <div x-show="loadingNeighborhoods" class="absolute right-10 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            <!-- Dropdown com Busca -->
                            <div
                                x-show="open"
                                x-transition
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-80 overflow-hidden">

                                <!-- Campo de Busca -->
                                <div class="p-2 border-b border-gray-200 sticky top-0 bg-white">
                                    <div class="relative">
                                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <input
                                            x-model="search"
                                            type="text"
                                            placeholder="Pesquisar bairro..."
                                            class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-md text-sm focus:border-gray-400 focus:ring-1 focus:ring-gray-200 focus:outline-none"
                                            @click.stop>
                                        <!-- Limpar busca -->
                                        <button
                                            x-show="search"
                                            @click="search = ''"
                                            type="button"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Lista de Bairros -->
                                <div class="overflow-y-auto max-h-64">
                                    <template x-if="filteredNeighborhoods.length === 0">
                                        <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                            Nenhum bairro encontrado
                                        </div>
                                    </template>
                                    <template x-for="neighborhood in filteredNeighborhoods" :key="neighborhood.name">
                                        <button
                                            type="button"
                                            @click="newAddress.neighborhood = neighborhood.name; open = false; search = ''"
                                            class="w-full px-4 py-2.5 text-left hover:bg-gray-50 transition text-sm"
                                            :class="newAddress.neighborhood === neighborhood.name && 'bg-gray-100 text-gray-900'">
                                            <span class="font-medium" x-text="neighborhood.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="text" x-model="newAddress.street" placeholder="Rua"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400" />

                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" x-model="newAddress.number" placeholder="Número"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400" />
                        <input type="text" x-model="newAddress.complement" placeholder="Complemento"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-200 focus:border-gray-400" />
                    </div>

                    <button @click="saveAddress()" :disabled="!newAddress.city || !newAddress.neighborhood || !newAddress.street || !newAddress.number"
                            :class="newAddress.city && newAddress.neighborhood && newAddress.street && newAddress.number ? 'bg-gray-900 hover:bg-gray-800' : 'bg-gray-300 cursor-not-allowed'"
                            class="w-full py-3.5 text-white rounded-lg font-medium text-sm transition">
                        Salvar Endereço
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Histórico Cashback -->
    <div x-show="showCashbackHistory" @click.self="showCashbackHistory = false" x-cloak
         class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-end md:items-center md:justify-center z-[9999]" x-transition>
        <div class="bg-white w-full md:max-w-lg md:rounded-2xl rounded-t-3xl max-h-[80vh] overflow-hidden shadow-2xl" @click.stop x-transition>
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Histórico de Cashback</h2>
                <button @click="showCashbackHistory = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-4 overflow-y-auto max-h-[calc(80vh-80px)]">
                <template x-if="cashbackTransactions.length === 0">
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-gray-500 text-sm font-medium">Nenhuma transação ainda</p>
                    </div>
                </template>
                <div class="space-y-3">
                    <template x-for="transaction in cashbackTransactions" :key="transaction.id">
                        <div class="border rounded-lg p-4" :class="transaction.type === 'earned' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'">
                            <div class="flex justify-between items-start mb-2">
                                <p class="text-sm font-medium flex-1" :class="transaction.type === 'earned' ? 'text-green-700' : 'text-red-700'" x-text="transaction.description"></p>
                                <p class="text-lg font-bold ml-2" :class="transaction.type === 'earned' ? 'text-green-600' : 'text-red-600'"
                                   x-text="(transaction.type === 'earned' ? '+' : '-') + 'R$ ' + parseFloat(transaction.amount).toFixed(2).replace('.', ',')"></p>
                            </div>
                            <p class="text-xs" :class="transaction.type === 'earned' ? 'text-green-600' : 'text-red-600'" x-text="formatDate(transaction.created_at)"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div x-show="showToast" x-transition.opacity class="fixed top-20 right-4 z-[99999] bg-gray-900 text-white px-5 py-3 rounded-lg shadow-2xl">
        <span x-text="toastMessage" class="text-sm font-medium"></span>
    </div>
</div>

<script>
function profileApp() {
    return {
        customer: {},
        cashbackTransactions: [],
        addresses: [],
        showEditProfile: false,
        showAddressesModal: false,
        showCashbackHistory: false,
        showAddAddress: false,
        showToast: false,
        toastMessage: '',
        newAddress: {},
        availableCities: [],
        availableNeighborhoods: [],
        loadingNeighborhoods: false,
        loadingCities: false,

        async init() {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                window.location.href = '/';
                return;
            }

            try {
                const [profileRes, transactionsRes, addressesRes, citiesRes] = await Promise.all([
                    fetch('/api/v1/me', {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    }),
                    fetch('/api/v1/cashback/transactions', {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    }),
                    fetch('/api/v1/addresses', {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    }),
                    fetch('/api/v1/location/enabled-cities', {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    })
                ]);

                if (profileRes.ok) {
                    const data = await profileRes.json();
                    this.customer = data.customer || {};
                }

                if (transactionsRes.ok) {
                    const data = await transactionsRes.json();
                    this.cashbackTransactions = data.data || [];
                }

                if (addressesRes.ok) {
                    const data = await addressesRes.json();
                    this.addresses = data.data || [];
                }

                if (citiesRes.ok) {
                    const data = await citiesRes.json();
                    this.availableCities = data.data || [];
                    console.log('✅ Cidades do tenant carregadas:', this.availableCities);
                }
            } catch (error) {
                console.error(error);
            }
        },

        async saveProfile() {
            const token = localStorage.getItem('auth_token');
            try {
                const response = await fetch('/api/v1/me', {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.customer)
                });

                if (response.ok) {
                    const data = await response.json();
                    this.customer = data.customer || this.customer;
                    this.showEditProfile = false;
                    this.showToastNotification('✅ Perfil atualizado!');
                    localStorage.setItem('customer', JSON.stringify(this.customer));
                }
            } catch (error) {
                this.showToastNotification('❌ Erro ao salvar');
            }
        },

        async loadNeighborhoods() {
            // Limpar bairro selecionado quando cidade muda
            this.newAddress.neighborhood = '';

            if (!this.newAddress.city) {
                this.availableNeighborhoods = [];
                return;
            }

            this.loadingNeighborhoods = true;
            try {
                const response = await fetch(`/api/v1/location/enabled-neighborhoods/${encodeURIComponent(this.newAddress.city)}`);
                if (response.ok) {
                    const data = await response.json();
                    this.availableNeighborhoods = data.data || [];
                    console.log(`✅ Carregados ${this.availableNeighborhoods.length} bairros para ${this.newAddress.city}`);
                } else {
                    this.availableNeighborhoods = [];
                    console.warn('❌ Erro ao carregar bairros');
                }
            } catch (error) {
                console.error('❌ Erro ao carregar bairros:', error);
                this.availableNeighborhoods = [];
            } finally {
                this.loadingNeighborhoods = false;
            }
        },

        async saveAddress() {
            const token = localStorage.getItem('auth_token');
            try {
                const response = await fetch('/api/v1/addresses', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.newAddress)
                });

                if (response.ok) {
                    const data = await response.json();
                    this.addresses.push(data.data);
                    this.newAddress = {};
                    this.availableNeighborhoods = [];
                    this.showAddAddress = false;
                    this.showAddressesModal = true;
                    this.showToastNotification('✅ Endereço salvo!');
                }
            } catch (error) {
                this.showToastNotification('❌ Erro ao salvar');
            }
        },

        async deleteAddress(id) {
            if (!confirm('Excluir este endereço?')) return;

            const token = localStorage.getItem('auth_token');
            try {
                const response = await fetch(`/api/v1/addresses/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                if (response.ok) {
                    this.addresses = this.addresses.filter(a => a.id !== id);
                    this.showToastNotification('✅ Endereço excluído');
                }
            } catch (error) {
                this.showToastNotification('❌ Erro ao excluir');
            }
        },

        getTierLabel(tier) {
            const labels = { 'bronze': 'Bronze', 'silver': 'Prata', 'gold': 'Ouro', 'platinum': 'Platina' };
            return labels[tier] || 'Bronze';
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return 'N/A';
            return date.toLocaleDateString('pt-BR', {day: '2-digit', month: '2-digit', year: 'numeric'}) + ' às ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        },

        showToastNotification(message) {
            this.toastMessage = message;
            this.showToast = true;
            setTimeout(() => {
                this.showToast = false;
            }, 3000);
        },

        logout() {
            if (confirm('Deseja sair da sua conta?')) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('customer');
                window.location.href = '/';
            }
        }
    }
}
</script>
@endsection
