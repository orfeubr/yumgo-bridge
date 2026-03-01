<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <div x-data="profileApp()" x-init="init()" class="min-h-screen">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white py-6 shadow-lg">
            <div class="max-w-4xl mx-auto px-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-black">👤 Meu Perfil</h1>
                        <p class="text-orange-100">{{ $tenant->name }}</p>
                    </div>
                    <a href="/meus-pedidos" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg font-bold transition">
                        📦 Pedidos
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 py-8">
            <!-- Verificar Login -->
            <div x-show="!isAuthenticated && !loading" class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="text-6xl mb-4">🔒</div>
                <h2 class="text-2xl font-bold mb-2">Login Necessário</h2>
                <p class="text-gray-600 mb-6">Faça login para ver seu perfil</p>
                <a href="/login" class="inline-block px-8 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl font-bold hover:shadow-lg transition">
                    🔑 Fazer Login
                </a>
            </div>

            <!-- Loading -->
            <div x-show="loading && isAuthenticated" class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <x-loading-spinner size="xl" />
                <p class="text-gray-600 mt-4">Carregando perfil...</p>
            </div>

            <!-- Perfil -->
            <div x-show="!loading && isAuthenticated" class="space-y-6">
                <!-- Cashback -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl shadow-lg p-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 mb-1">💰 Saldo de Cashback</p>
                            <p class="text-5xl font-black" x-text="'R$ ' + parseFloat(customer.cashback_balance || 0).toFixed(2)"></p>
                            <p class="text-green-100 mt-2">Use em suas próximas compras!</p>
                        </div>
                        <div class="text-right">
                            <div class="bg-white/20 px-4 py-2 rounded-xl mb-2">
                                <p class="text-sm">Nível</p>
                                <p class="text-2xl font-black" x-text="getTierLabel(customer.loyalty_tier)"></p>
                            </div>
                            <button @click="showCashbackHistory = true" class="text-sm underline hover:text-white">
                                Ver histórico
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                        <p class="text-4xl mb-2">📦</p>
                        <p class="text-3xl font-black text-gray-900" x-text="customer.total_orders || 0"></p>
                        <p class="text-sm text-gray-600">Pedidos</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                        <p class="text-4xl mb-2">💸</p>
                        <p class="text-3xl font-black text-orange-600" x-text="'R$ ' + parseFloat(customer.total_spent || 0).toFixed(2)"></p>
                        <p class="text-sm text-gray-600">Total Gasto</p>
                    </div>
                </div>

                <!-- Dados Pessoais -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-black mb-6">📋 Dados Pessoais</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nome Completo</label>
                            <input type="text" x-model="customer.name" :disabled="!editing" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:outline-none disabled:bg-gray-50" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">E-mail</label>
                            <input type="email" x-model="customer.email" :disabled="!editing" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:outline-none disabled:bg-gray-50" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Telefone</label>
                            <input type="tel" x-model="customer.phone" :disabled="!editing" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:outline-none disabled:bg-gray-50" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">CPF</label>
                            <input type="text" x-model="customer.cpf" :disabled="!editing" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:outline-none disabled:bg-gray-50" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Data de Nascimento</label>
                            <input type="date" x-model="customer.birth_date" :disabled="!editing" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:outline-none disabled:bg-gray-50" />
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button x-show="!editing" @click="editing = true" class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl font-bold hover:shadow-lg transition">
                            ✏️ Editar Perfil
                        </button>
                        <button x-show="editing" @click="saveProfile()" class="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-xl font-bold hover:shadow-lg transition">
                            ✅ Salvar Alterações
                        </button>
                        <button x-show="editing" @click="cancelEdit()" class="px-6 py-3 bg-gray-200 text-gray-800 rounded-xl font-bold hover:bg-gray-300 transition">
                            ❌ Cancelar
                        </button>
                    </div>
                </div>

                <!-- Botão Sair -->
                <div class="text-center">
                    <button @click="logout()" class="px-8 py-3 bg-red-100 text-red-700 rounded-xl font-bold hover:bg-red-200 transition">
                        🚪 Sair da Conta
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Histórico Cashback -->
        <div x-show="showCashbackHistory" @click.self="showCashbackHistory = false" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 text-white p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-black">💰 Histórico de Cashback</h2>
                        <button @click="showCashbackHistory = false" class="text-3xl hover:rotate-90 transition">&times;</button>
                    </div>
                </div>
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    <template x-if="cashbackTransactions.length === 0">
                        <p class="text-center text-gray-500 py-8">Nenhuma transação ainda</p>
                    </template>
                    <div class="space-y-3">
                        <template x-for="transaction in cashbackTransactions" :key="transaction.id">
                            <div class="border-2 border-gray-100 rounded-xl p-4 hover:border-green-200 transition">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-bold" :class="transaction.type === 'earned' ? 'text-green-600' : 'text-red-600'" x-text="transaction.description"></p>
                                        <p class="text-sm text-gray-500" x-text="formatDate(transaction.created_at)"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-black" :class="transaction.type === 'earned' ? 'text-green-600' : 'text-red-600'" x-text="(transaction.type === 'earned' ? '+' : '') + 'R$ ' + parseFloat(transaction.amount).toFixed(2)"></p>
                                        <p class="text-xs text-gray-500" x-text="'Saldo: R$ ' + parseFloat(transaction.balance_after).toFixed(2)"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function profileApp() {
            return {
                customer: {},
                cashbackTransactions: [],
                loading: true,
                editing: false,
                isAuthenticated: false,
                showCashbackHistory: false,
                originalCustomer: {},

                async init() {
                    const token = localStorage.getItem('auth_token');
                    if (!token) {
                        this.loading = false;
                        return;
                    }

                    this.isAuthenticated = true;

                    try {
                        // Carregar perfil
                        const response = await fetch('/api/v1/me', {
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json',
                            }
                        });

                        if (!response.ok) {
                            if (response.status === 401) {
                                localStorage.removeItem('auth_token');
                                this.isAuthenticated = false;
                                return;
                            }
                            throw new Error('Erro ao carregar perfil');
                        }

                        const data = await response.json();
                        this.customer = data.customer || {};
                        this.originalCustomer = { ...this.customer };

                        // Carregar transações de cashback
                        this.loadCashbackTransactions();
                    } catch (error) {
                        console.error(error);
                        alert('Erro ao carregar perfil');
                    } finally {
                        this.loading = false;
                    }
                },

                async loadCashbackTransactions() {
                    const token = localStorage.getItem('auth_token');
                    try {
                        const response = await fetch('/api/v1/cashback/transactions', {
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json',
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.cashbackTransactions = data.data || [];
                        }
                    } catch (error) {
                        console.error('Erro ao carregar histórico:', error);
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

                        if (!response.ok) {
                            throw new Error('Erro ao salvar perfil');
                        }

                        const data = await response.json();
                        this.customer = data.customer || this.customer;
                        this.originalCustomer = { ...this.customer };
                        this.editing = false;
                        alert('✅ Perfil atualizado com sucesso!');
                    } catch (error) {
                        console.error(error);
                        alert('Erro ao salvar perfil');
                    }
                },

                cancelEdit() {
                    this.customer = { ...this.originalCustomer };
                    this.editing = false;
                },

                getTierLabel(tier) {
                    const labels = {
                        'bronze': '🥉 Bronze',
                        'silver': '🥈 Prata',
                        'gold': '🥇 Ouro',
                        'platinum': '💎 Platina',
                    };
                    return labels[tier] || '🥉 Bronze';
                },

                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    const date = new Date(dateString);
                    if (isNaN(date.getTime())) return 'N/A';
                    return date.toLocaleDateString('pt-BR') + ' às ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                },

                logout() {
                    if (confirm('Deseja realmente sair?')) {
                        localStorage.removeItem('auth_token');
                        window.location.href = '/login';
                    }
                }
            }
        }
    </script>
</body>
</html>
