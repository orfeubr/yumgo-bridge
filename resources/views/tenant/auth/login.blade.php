<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title>Login - {{ tenant()->name }}</title>

    <!-- 🔥 LOGIN COM SOCIAL ATUALIZADO: {{ now()->format('d/m/Y H:i:s') }} 🔥 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-orange-50 to-red-50 min-h-screen flex items-center justify-center p-4">
    <div x-data="authApp()" class="w-full max-w-md">
        <!-- Card de Login/Cadastro -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <!-- Logo/Nome do Restaurante -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-black bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent mb-2">
                    {{ tenant()->name }}
                </h1>
                <p class="text-gray-600">Faça login ou crie sua conta</p>
            </div>

            <!-- Login Social -->
            <div class="mb-6 space-y-3">
                <!-- Google -->
                <a href="/auth/google/redirect" class="w-full py-3 px-4 bg-white border-2 border-gray-300 rounded-xl flex items-center justify-center gap-3 hover:bg-gray-50 transition font-semibold text-gray-700">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span>Continuar com Google</span>
                </a>

                <!-- Facebook -->
                <a href="/auth/facebook/redirect" class="w-full py-3 px-4 bg-[#1877F2] rounded-xl flex items-center justify-center gap-3 hover:bg-[#166FE5] transition font-semibold text-white">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span>Continuar com Facebook</span>
                </a>

                <!-- Divisor -->
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-px bg-gray-300"></div>
                    <span class="text-sm text-gray-500 font-medium">ou</span>
                    <div class="flex-1 h-px bg-gray-300"></div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 mb-6 bg-gray-100 p-1 rounded-xl">
                <button
                    @click="mode = 'login'"
                    :class="mode === 'login' ? 'bg-white shadow-md' : 'text-gray-600'"
                    class="flex-1 py-3 rounded-lg font-bold transition"
                >
                    Entrar
                </button>
                <button
                    @click="mode = 'register'"
                    :class="mode === 'register' ? 'bg-white shadow-md' : 'text-gray-600'"
                    class="flex-1 py-3 rounded-lg font-bold transition"
                >
                    Cadastrar
                </button>
            </div>

            <!-- Mensagem de Erro -->
            <div x-show="error" class="mb-4 p-4 bg-red-50 border-2 border-red-200 text-red-700 rounded-xl text-sm">
                <div x-html="error"></div>
            </div>

            <!-- Formulário de Login -->
            <form x-show="mode === 'login'" @submit.prevent="login()" class="space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-2">Celular ou Email</label>
                    <input
                        type="text"
                        x-model="loginData.identifier"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:outline-none"
                        placeholder="(00) 00000-0000"
                        required
                    >
                    <p class="text-xs text-gray-500 mt-1">Digite seu celular ou email</p>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">Senha</label>
                    <input
                        type="password"
                        x-model="loginData.password"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:outline-none"
                        placeholder="Sua senha"
                        required
                    >
                </div>
                <div class="text-right">
                    <button type="button" @click="showForgotPassword = true" class="text-sm text-orange-600 hover:text-orange-700 font-semibold">
                        Esqueci a senha
                    </button>
                </div>
                <button
                    type="submit"
                    :disabled="loading"
                    :class="loading ? 'bg-gray-400' : 'bg-gradient-to-r from-orange-500 to-red-500 hover:shadow-xl'"
                    class="w-full py-4 text-white font-black rounded-xl transition"
                >
                    <span x-show="!loading">🔓 Entrar</span>
                    <span x-show="loading">⏳ Entrando...</span>
                </button>
            </form>

            <!-- Formulário de Cadastro -->
            <form x-show="mode === 'register'" @submit.prevent="register()" class="space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-2">Nome Completo *</label>
                    <input
                        type="text"
                        x-model="registerData.name"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:outline-none"
                        placeholder="Seu nome completo"
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">Celular/WhatsApp *</label>
                    <input
                        type="tel"
                        x-model="registerData.phone"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:outline-none"
                        placeholder="(00) 00000-0000"
                        required
                    >
                    <p class="text-xs text-gray-500 mt-1">Usaremos para enviar atualizações do pedido</p>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">E-mail (opcional)</label>
                    <input
                        type="email"
                        x-model="registerData.email"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:outline-none"
                        placeholder="seu@email.com"
                    >
                    <p class="text-xs text-gray-500 mt-1">Opcional, mas recomendado</p>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">Senha *</label>
                    <input
                        type="password"
                        x-model="registerData.password"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:outline-none"
                        placeholder="Mínimo 6 caracteres"
                        required
                        minlength="6"
                    >
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">Confirmar Senha *</label>
                    <input
                        type="password"
                        x-model="registerData.password_confirmation"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:outline-none"
                        placeholder="Digite a senha novamente"
                        required
                        minlength="6"
                    >
                </div>
                <button
                    type="submit"
                    :disabled="loading"
                    :class="loading ? 'bg-gray-400' : 'bg-gradient-to-r from-orange-500 to-red-500 hover:shadow-xl'"
                    class="w-full py-4 text-white font-black rounded-xl transition"
                >
                    <span x-show="!loading">🚀 Criar Conta</span>
                    <span x-show="loading">⏳ Criando...</span>
                </button>
            </form>

            <!-- Link Voltar -->
            <div class="mt-6 text-center">
                <a href="/" class="text-gray-600 hover:text-orange-600 text-sm font-semibold">
                    ← Voltar ao cardápio
                </a>
            </div>
        </div>

        <!-- Modal Esqueci a Senha -->
        <div x-show="showForgotPassword" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div @click.away="showForgotPassword = false" class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
                <h2 class="text-2xl font-black mb-4 bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                    Recuperar Senha
                </h2>
                <p class="text-gray-600 mb-6 text-sm">
                    Digite seu email para receber instruções de recuperação de senha.
                </p>

                <div x-show="forgotPasswordError" x-text="forgotPasswordError" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm"></div>
                <div x-show="forgotPasswordSuccess" x-text="forgotPasswordSuccess" class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm"></div>

                <form @submit.prevent="submitForgotPassword()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold mb-2">Email</label>
                        <input
                            type="email"
                            x-model="forgotPasswordEmail"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:outline-none"
                            placeholder="seu@email.com"
                            required
                        >
                    </div>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="showForgotPassword = false; forgotPasswordEmail = ''; forgotPasswordError = ''; forgotPasswordSuccess = ''"
                            class="flex-1 px-4 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="forgotPasswordLoading"
                            :class="forgotPasswordLoading ? 'bg-gray-400' : 'bg-gradient-to-r from-orange-500 to-red-500 hover:shadow-xl'"
                            class="flex-1 px-4 py-3 text-white rounded-xl font-semibold"
                        >
                            <span x-show="!forgotPasswordLoading">Enviar</span>
                            <span x-show="forgotPasswordLoading">Enviando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function authApp() {
        return {
            mode: 'login', // 'login' ou 'register'
            loading: false,
            error: '',
            showForgotPassword: false,
            forgotPasswordEmail: '',
            forgotPasswordLoading: false,
            forgotPasswordError: '',
            forgotPasswordSuccess: '',
            loginData: {
                identifier: '',
                password: ''
            },
            registerData: {
                name: '',
                phone: '',
                email: '',
                password: '',
                password_confirmation: ''
            },

            async login() {
                this.error = '';
                this.loading = true;

                try {
                    const response = await fetch('/api/v1/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.loginData)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        // Pegar erros de validação
                        if (data.errors) {
                            const errorMessages = Object.values(data.errors).flat();
                            // Formatar como lista HTML se houver múltiplos erros
                            if (errorMessages.length > 1) {
                                this.error = '<ul class="list-disc pl-5 space-y-1">' +
                                    errorMessages.map(msg => `<li>${msg}</li>`).join('') +
                                    '</ul>';
                                this.loading = false;
                                return;
                            } else {
                                throw new Error(errorMessages[0]);
                            }
                        }
                        throw new Error(data.message || 'Erro ao fazer login');
                    }

                    // Salvar token
                    localStorage.setItem('auth_token', data.token);
                    localStorage.setItem('customer', JSON.stringify(data.customer));

                    console.log('✅ Login bem-sucedido!', {
                        customer: data.customer.name,
                        token: data.token.substring(0, 20) + '...'
                    });

                    // Redirecionar
                    const redirectTo = new URLSearchParams(window.location.search).get('redirect') || '/';
                    console.log('Redirecionando para:', redirectTo);
                    window.location.href = redirectTo;

                } catch (error) {
                    console.error('❌ Erro no login:', error);
                    this.error = error.message;
                    this.loading = false;
                }
            },

            async register() {
                this.error = '';

                // Validar senhas
                if (this.registerData.password !== this.registerData.password_confirmation) {
                    this.error = 'As senhas não coincidem';
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch('/api/v1/register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.registerData)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        // Pegar erros de validação
                        if (data.errors) {
                            const errorMessages = Object.values(data.errors).flat();
                            // Formatar como lista HTML se houver múltiplos erros
                            if (errorMessages.length > 1) {
                                this.error = '<ul class="list-disc pl-5 space-y-1">' +
                                    errorMessages.map(msg => `<li>${msg}</li>`).join('') +
                                    '</ul>';
                                this.loading = false;
                                return;
                            } else {
                                throw new Error(errorMessages[0]);
                            }
                        }
                        throw new Error(data.message || 'Erro ao criar conta');
                    }

                    // Salvar token
                    localStorage.setItem('auth_token', data.token);
                    localStorage.setItem('customer', JSON.stringify(data.customer));

                    console.log('✅ Cadastro bem-sucedido!', {
                        customer: data.customer.name,
                        token: data.token.substring(0, 20) + '...'
                    });

                    // Redirecionar
                    const redirectTo = new URLSearchParams(window.location.search).get('redirect') || '/';
                    console.log('Redirecionando para:', redirectTo);
                    window.location.href = redirectTo;

                } catch (error) {
                    console.error('❌ Erro no cadastro:', error);
                    this.error = error.message;
                    this.loading = false;
                }
            },

            async submitForgotPassword() {
                this.forgotPasswordError = '';
                this.forgotPasswordSuccess = '';
                this.forgotPasswordLoading = true;

                try {
                    const response = await fetch('/api/v1/forgot-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ email: this.forgotPasswordEmail })
                    });

                    const data = await response.json();

                    this.forgotPasswordSuccess = data.message || 'Email de recuperação enviado com sucesso!';
                    this.forgotPasswordEmail = '';

                    // Fechar modal após 3 segundos
                    setTimeout(() => {
                        this.showForgotPassword = false;
                        this.forgotPasswordSuccess = '';
                    }, 3000);

                } catch (error) {
                    this.forgotPasswordError = error.message || 'Erro ao enviar email de recuperação.';
                } finally {
                    this.forgotPasswordLoading = false;
                }
            }
        }
    }
    </script>
</body>
</html>
