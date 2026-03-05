<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>Bem-vindo - {{ $settings->restaurant_name ?? tenant('name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body{font-family:'Poppins',sans-serif}
        [x-cloak]{display:none!important}
    </style>
</head>
<body class="bg-gray-50">

<div x-data="welcomeApp()" x-init="init()" x-cloak>

    <!-- STEP 1: Boas-vindas e Localização -->
    <div x-show="currentStep === 1" class="min-h-screen flex flex-col">

        <!-- Header -->
        <div class="bg-white border-b border-gray-200 p-4">
            @if($settings && $settings->logo)
            <img src="{{ route('stancl.tenancy.asset', ['path' => $settings->logo]) }}" alt="{{ $settings->restaurant_name }}" class="h-12 mx-auto object-contain">
            @else
            <h1 class="text-2xl font-bold text-red-600 text-center">{{ $settings->restaurant_name ?? tenant('name') }}</h1>
            @endif
        </div>

        <div class="flex-1 flex flex-col items-center justify-center p-6 max-w-md mx-auto w-full">

            <!-- Ícone -->
            <div class="w-24 h-24 bg-red-500 rounded-full flex items-center justify-center mb-6">
                <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>

            <!-- Título -->
            <h1 class="text-3xl font-bold text-gray-900 mb-2 text-center">Onde você quer receber seu pedido?</h1>
            <p class="text-gray-500 text-center mb-8">Precisamos saber sua localização para mostrar os produtos disponíveis</p>

            <!-- Seleção de Cidade -->
            <div class="w-full mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Cidade</label>
                <select x-model="selectedCity" @change="loadNeighborhoods()"
                        class="w-full px-4 py-4 border border-gray-300 rounded-lg text-base focus:border-red-500 focus:ring focus:ring-red-100 transition">
                    <option value="">Selecione sua cidade</option>
                    @foreach($availableCities as $city)
                    <option value="{{ $city }}">{{ $city }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Seleção de Bairro -->
            <div class="w-full mb-6" x-show="selectedCity">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Bairro</label>
                <select x-model="selectedNeighborhood" :disabled="loadingNeighborhoods || neighborhoods.length === 0"
                        class="w-full px-4 py-4 border border-gray-300 rounded-lg text-base focus:border-red-500 focus:ring focus:ring-red-100 transition disabled:bg-gray-100">
                    <option value="">
                        <span x-show="loadingNeighborhoods">Carregando...</span>
                        <span x-show="!loadingNeighborhoods && neighborhoods.length === 0">Nenhum bairro disponível</span>
                        <span x-show="!loadingNeighborhoods && neighborhoods.length > 0">Selecione seu bairro</span>
                    </option>
                    <template x-for="neighborhood in neighborhoods" :key="neighborhood.name">
                        <option :value="neighborhood.name" x-text="neighborhood.name + ' (Taxa: R$ ' + parseFloat(neighborhood.delivery_fee).toFixed(2).replace('.', ',') + ')'"></option>
                    </template>
                </select>
            </div>

            <!-- Botão Continuar -->
            <button @click="goToLogin()" :disabled="!selectedCity || !selectedNeighborhood"
                    :class="selectedCity && selectedNeighborhood ? 'bg-red-500 hover:bg-red-600' : 'bg-gray-300 cursor-not-allowed'"
                    class="w-full py-4 text-white font-semibold text-lg rounded-lg transition-colors mb-4">
                Continuar
            </button>

            <!-- Link para ver cardápio sem login -->
            <button @click="skipLogin()" class="text-gray-500 text-sm font-medium hover:text-gray-700 transition">
                Ver cardápio sem fazer login
            </button>

        </div>

    </div>

    <!-- STEP 2: Login/Cadastro -->
    <div x-show="currentStep === 2" class="min-h-screen flex flex-col">

        <!-- Header com voltar -->
        <div class="bg-white border-b border-gray-200 p-4 flex items-center">
            <button @click="currentStep = 1" class="mr-3">
                <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <h2 class="text-lg font-semibold text-gray-900">Entrar ou Cadastrar</h2>
        </div>

        <div class="flex-1 flex flex-col items-center justify-center p-6 max-w-md mx-auto w-full">

            <!-- Logo -->
            @if($settings && $settings->logo)
            <img src="{{ route('stancl.tenancy.asset', ['path' => $settings->logo]) }}" alt="{{ $settings->restaurant_name }}" class="h-16 mb-6 object-contain">
            @else
            <h1 class="text-2xl font-bold text-red-600 mb-6">{{ $settings->restaurant_name ?? tenant('name') }}</h1>
            @endif

            <!-- Login Social -->
            <div class="w-full space-y-3 mb-6">

                <!-- Google -->
                <button @click="loginWithGoogle()" class="w-full py-3.5 px-4 bg-white border-2 border-gray-300 rounded-lg flex items-center justify-center gap-3 hover:bg-gray-50 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Continuar com Google</span>
                </button>

                <!-- Facebook -->
                <button @click="loginWithFacebook()" class="w-full py-3.5 px-4 bg-[#1877F2] rounded-lg flex items-center justify-center gap-3 hover:bg-[#166FE5] transition">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span class="font-semibold text-white">Continuar com Facebook</span>
                </button>

            </div>

            <!-- Divisor -->
            <div class="w-full flex items-center gap-3 mb-6">
                <div class="flex-1 h-px bg-gray-300"></div>
                <span class="text-sm text-gray-500 font-medium">ou</span>
                <div class="flex-1 h-px bg-gray-300"></div>
            </div>

            <!-- Login Tradicional -->
            <div class="w-full space-y-3">

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email ou Telefone</label>
                    <input type="text" x-model="loginForm.identifier" placeholder="Digite seu email ou celular"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-red-500 focus:ring focus:ring-red-100 transition">
                </div>

                <!-- Senha -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Senha</label>
                    <input type="password" x-model="loginForm.password" placeholder="Digite sua senha"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-red-500 focus:ring focus:ring-red-100 transition">
                </div>

                <!-- Botão Entrar -->
                <button @click="login()" :disabled="!loginForm.identifier || !loginForm.password"
                        :class="loginForm.identifier && loginForm.password ? 'bg-red-500 hover:bg-red-600' : 'bg-gray-300 cursor-not-allowed'"
                        class="w-full py-3.5 text-white font-semibold rounded-lg transition-colors">
                    Entrar
                </button>

                <!-- Link Esqueci Senha -->
                <div class="text-center">
                    <a href="#" class="text-sm text-red-600 font-medium hover:text-red-700">Esqueci minha senha</a>
                </div>

            </div>

            <!-- Divisor -->
            <div class="w-full my-6 h-px bg-gray-200"></div>

            <!-- Criar Conta -->
            <button @click="currentStep = 3" class="w-full py-3.5 bg-white border-2 border-red-500 text-red-500 font-semibold rounded-lg hover:bg-red-50 transition">
                Criar nova conta
            </button>

        </div>

    </div>

    <!-- STEP 3: Cadastro -->
    <div x-show="currentStep === 3" class="min-h-screen flex flex-col bg-white">

        <!-- Header com voltar -->
        <div class="bg-white border-b border-gray-200 p-4 flex items-center">
            <button @click="currentStep = 2" class="mr-3">
                <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <h2 class="text-lg font-semibold text-gray-900">Criar Conta</h2>
        </div>

        <div class="flex-1 overflow-y-auto p-6 max-w-md mx-auto w-full">

            <!-- Formulário de Cadastro -->
            <div class="space-y-4">

                <!-- Nome -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nome Completo</label>
                    <input type="text" x-model="registerForm.name" placeholder="Digite seu nome completo"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-red-500 focus:ring focus:ring-red-100 transition">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                    <input type="email" x-model="registerForm.email" placeholder="seu@email.com"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-red-500 focus:ring focus:ring-red-100 transition">
                </div>

                <!-- Telefone -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Telefone (WhatsApp)</label>
                    <input type="tel" x-model="registerForm.phone" placeholder="(11) 99999-9999"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-red-500 focus:ring focus:ring-red-100 transition">
                    <p class="text-xs text-gray-500 mt-1">Você receberá um código de verificação via WhatsApp</p>
                </div>

                <!-- Senha -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Senha</label>
                    <input type="password" x-model="registerForm.password" placeholder="Mínimo 6 caracteres"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-red-500 focus:ring focus:ring-red-100 transition">
                </div>

                <!-- Confirmar Senha -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmar Senha</label>
                    <input type="password" x-model="registerForm.password_confirmation" placeholder="Digite a senha novamente"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-red-500 focus:ring focus:ring-red-100 transition">
                </div>

                <!-- Cidade (pré-selecionada) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Cidade</label>
                    <input type="text" :value="selectedCity" disabled
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                </div>

                <!-- Bairro (pré-selecionado) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Bairro</label>
                    <input type="text" :value="selectedNeighborhood" disabled
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                </div>

                <!-- Rua -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Rua</label>
                    <input type="text" x-model="registerForm.street" placeholder="Nome da rua"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-red-500 focus:ring focus:ring-red-100 transition">
                </div>

                <!-- Número e CEP -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Número</label>
                        <input type="text" x-model="registerForm.number" placeholder="123"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-red-500 focus:ring focus:ring-red-100 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">CEP</label>
                        <input type="text" x-model="registerForm.zipcode" placeholder="13200-000"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-red-500 focus:ring focus:ring-red-100 transition">
                    </div>
                </div>

                <!-- Complemento -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Complemento (opcional)</label>
                    <input type="text" x-model="registerForm.complement" placeholder="Apto, Bloco, etc."
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-red-500 focus:ring focus:ring-red-100 transition">
                </div>

                <!-- Termos -->
                <div class="flex items-start gap-2">
                    <input type="checkbox" x-model="registerForm.acceptTerms" class="w-5 h-5 text-red-500 border-gray-300 rounded focus:ring-red-500 mt-0.5">
                    <label class="text-sm text-gray-700">
                        Eu aceito os <a href="#" class="text-red-600 font-medium">Termos de Uso</a> e a <a href="#" class="text-red-600 font-medium">Política de Privacidade</a>
                    </label>
                </div>

                <!-- Botão Criar Conta -->
                <button @click="register()"
                        :disabled="!registerForm.name || !registerForm.email || !registerForm.phone || !registerForm.password || !registerForm.acceptTerms"
                        :class="registerForm.name && registerForm.email && registerForm.phone && registerForm.password && registerForm.acceptTerms ? 'bg-red-500 hover:bg-red-600' : 'bg-gray-300 cursor-not-allowed'"
                        class="w-full py-4 text-white font-semibold text-lg rounded-lg transition-colors">
                    Criar Conta
                </button>

            </div>

        </div>

    </div>

    <!-- Toast de Mensagem -->
    <div x-show="showToast" x-transition.opacity class="fixed top-4 right-4 z-[99999] bg-red-600 text-white px-6 py-3 rounded-lg shadow-2xl flex items-center gap-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span x-text="toastMessage" class="font-semibold"></span>
    </div>

</div>

<script>
function welcomeApp() {
    return {
        currentStep: 1,
        selectedCity: '',
        selectedNeighborhood: '',
        neighborhoods: [],
        loadingNeighborhoods: false,
        showToast: false,
        toastMessage: '',
        loginForm: {
            identifier: '',
            password: ''
        },
        registerForm: {
            name: '',
            email: '',
            phone: '',
            password: '',
            password_confirmation: '',
            street: '',
            number: '',
            zipcode: '',
            complement: '',
            acceptTerms: false
        },

        init() {
            // Verificar se já está logado
            const token = localStorage.getItem('auth_token');
            if (token) {
                window.location.href = '/';
            }

            // Verificar se já tem localização salva
            const savedCity = localStorage.getItem('selected_city');
            const savedNeighborhood = localStorage.getItem('selected_neighborhood');
            if (savedCity && savedNeighborhood) {
                this.selectedCity = savedCity;
                this.selectedNeighborhood = savedNeighborhood;
            }
        },

        async loadNeighborhoods() {
            if (!this.selectedCity) {
                this.neighborhoods = [];
                return;
            }

            this.loadingNeighborhoods = true;
            this.selectedNeighborhood = '';

            try {
                const response = await fetch(`/api/v1/location/enabled-neighborhoods/${encodeURIComponent(this.selectedCity)}`);
                if (response.ok) {
                    const data = await response.json();
                    this.neighborhoods = data.data || [];
                }
            } catch (error) {
                this.showToastNotification('Erro ao carregar bairros');
            } finally {
                this.loadingNeighborhoods = false;
            }
        },

        goToLogin() {
            if (!this.selectedCity || !this.selectedNeighborhood) {
                this.showToastNotification('Selecione cidade e bairro');
                return;
            }

            // Salvar localização
            localStorage.setItem('selected_city', this.selectedCity);
            localStorage.setItem('selected_neighborhood', this.selectedNeighborhood);

            this.currentStep = 2;
        },

        skipLogin() {
            if (!this.selectedCity || !this.selectedNeighborhood) {
                this.showToastNotification('Selecione cidade e bairro');
                return;
            }

            // Salvar localização
            localStorage.setItem('selected_city', this.selectedCity);
            localStorage.setItem('selected_neighborhood', this.selectedNeighborhood);

            // Ir para home
            window.location.href = '/';
        },

        async login() {
            try {
                const response = await fetch('/api/v1/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        identifier: this.loginForm.identifier,
                        password: this.loginForm.password
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    localStorage.setItem('auth_token', data.token);
                    localStorage.setItem('customer', JSON.stringify(data.customer));
                    window.location.href = '/';
                } else {
                    this.showToastNotification(data.message || 'Erro ao fazer login');
                }
            } catch (error) {
                this.showToastNotification('Erro ao fazer login');
            }
        },

        async register() {
            try {
                const response = await fetch('/api/v1/auth/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ...this.registerForm,
                        city: this.selectedCity,
                        neighborhood: this.selectedNeighborhood
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    localStorage.setItem('auth_token', data.token);
                    localStorage.setItem('customer', JSON.stringify(data.customer));

                    // Redirecionar para verificação de WhatsApp
                    this.showToastNotification('Conta criada! Enviando código de verificação...');
                    setTimeout(() => {
                        window.location.href = '/verificar-whatsapp';
                    }, 2000);
                } else {
                    this.showToastNotification(data.message || 'Erro ao criar conta');
                }
            } catch (error) {
                this.showToastNotification('Erro ao criar conta');
            }
        },

        loginWithGoogle() {
            window.location.href = '/auth/google/redirect';
        },

        loginWithFacebook() {
            window.location.href = '/auth/facebook/redirect';
        },

        showToastNotification(message) {
            this.toastMessage = message;
            this.showToast = true;
            setTimeout(() => {
                this.showToast = false;
            }, 3000);
        }
    }
}
</script>

</body>
</html>
