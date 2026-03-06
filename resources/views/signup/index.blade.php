<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastre seu Restaurante - YumGo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center space-x-2">
                    <div class="bg-red-600 text-white px-3 py-2 rounded-lg font-bold text-xl">
                        YumGo
                    </div>
                </a>
                <a href="/admin" class="text-gray-600 hover:text-red-600">
                    Já tenho conta
                </a>
            </div>
        </div>
    </header>

    <!-- Wizard de Cadastro -->
    <div class="container mx-auto px-4 py-12" x-data="signupWizard()">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center flex-1">
                        <div class="flex items-center text-white relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center"
                                 :class="step >= 1 ? 'bg-red-600' : 'bg-gray-300'">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase"
                                 :class="step >= 1 ? 'text-red-600' : 'text-gray-500'">
                                Restaurante
                            </div>
                        </div>
                        <div class="flex-auto border-t-2 transition duration-500 ease-in-out"
                             :class="step >= 2 ? 'border-red-600' : 'border-gray-300'"></div>
                    </div>

                    <div class="flex items-center flex-1">
                        <div class="flex items-center text-white relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center"
                                 :class="step >= 2 ? 'bg-red-600' : 'bg-gray-300'">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase"
                                 :class="step >= 2 ? 'text-red-600' : 'text-gray-500'">
                                Responsável
                            </div>
                        </div>
                        <div class="flex-auto border-t-2 transition duration-500 ease-in-out"
                             :class="step >= 3 ? 'border-red-600' : 'border-gray-300'"></div>
                    </div>

                    <div class="flex items-center">
                        <div class="flex items-center text-white relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center"
                                 :class="step >= 3 ? 'bg-red-600' : 'bg-gray-300'">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase"
                                 :class="step >= 3 ? 'text-red-600' : 'text-gray-500'">
                                Plano
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulário -->
            <form action="{{ route('signup.store') }}" method="POST" class="bg-white rounded-lg shadow-lg p-8">
                @csrf

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                        <strong class="font-bold">Ops!</strong>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Step 1: Dados do Restaurante -->
                <div x-show="step === 1" x-transition>
                    <h2 class="text-2xl font-bold mb-6">Dados do Restaurante</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nome do Restaurante *
                            </label>
                            <input type="text" name="restaurant_name" value="{{ old('restaurant_name') }}"
                                   x-model="restaurant_name"
                                   @input="generateSlug()"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   placeholder="Ex: Pizzaria Bella">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                URL do Restaurante *
                            </label>
                            <div class="flex items-center">
                                <input type="text" name="restaurant_slug" value="{{ old('restaurant_slug') }}"
                                       x-model="restaurant_slug"
                                       required
                                       pattern="[a-z0-9-]+"
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                       placeholder="pizzaria-bella">
                                <div class="px-4 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg text-gray-600">
                                    .yumgo.com.br
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Sua loja ficará em: <span class="font-semibold" x-text="'https://' + (restaurant_slug || 'seu-restaurante') + '.yumgo.com.br'"></span>
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Email do Restaurante *
                                </label>
                                <input type="email" name="restaurant_email" value="{{ old('restaurant_email') }}"
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                       placeholder="contato@restaurante.com">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Telefone/WhatsApp *
                                </label>
                                <input type="tel" name="restaurant_phone" value="{{ old('restaurant_phone') }}"
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                       placeholder="(11) 99999-9999">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="button" @click="step = 2"
                                class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">
                            Próximo <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Dados do Responsável -->
                <div x-show="step === 2" x-transition>
                    <h2 class="text-2xl font-bold mb-6">Dados do Responsável</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nome Completo *
                            </label>
                            <input type="text" name="owner_name" value="{{ old('owner_name') }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   placeholder="João Silva">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email *
                            </label>
                            <input type="email" name="owner_email" value="{{ old('owner_email') }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                   placeholder="joao@email.com">
                            <p class="mt-1 text-sm text-gray-500">Será usado para acessar o painel administrativo</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Senha *
                                </label>
                                <input type="password" name="owner_password"
                                       required minlength="6"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                       placeholder="Mínimo 6 caracteres">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirmar Senha *
                                </label>
                                <input type="password" name="owner_password_confirmation"
                                       required minlength="6"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                       placeholder="Repita a senha">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-between">
                        <button type="button" @click="step = 1"
                                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">
                            <i class="fas fa-arrow-left mr-2"></i> Voltar
                        </button>
                        <button type="button" @click="step = 3"
                                class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">
                            Próximo <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Escolha do Plano -->
                <div x-show="step === 3" x-transition>
                    <h2 class="text-2xl font-bold mb-6">Escolha seu Plano</h2>
                    <p class="text-gray-600 mb-6">Todos os planos incluem 15 dias de teste grátis!</p>

                    <div class="grid md:grid-cols-3 gap-6 mb-6">
                        @foreach($plans as $plan)
                            <label class="cursor-pointer">
                                <input type="radio" name="plan_id" value="{{ $plan->id }}"
                                       {{ old('plan_id') == $plan->id ? 'checked' : ($loop->index === 1 ? 'checked' : '') }}
                                       class="sr-only peer"
                                       required>
                                <div class="border-2 border-gray-200 rounded-lg p-6 peer-checked:border-red-600 peer-checked:bg-red-50 hover:border-red-300 transition">
                                    @if($plan->name === 'Pro')
                                        <div class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded inline-block mb-2">
                                            POPULAR
                                        </div>
                                    @endif

                                    <h3 class="text-xl font-bold mb-2">{{ $plan->name }}</h3>

                                    <div class="text-3xl font-bold text-red-600 mb-1">
                                        R$ {{ number_format($plan->price_monthly, 2, ',', '.') }}
                                    </div>
                                    <div class="text-sm text-gray-600 mb-4">por mês</div>

                                    <div class="bg-red-100 text-red-700 px-3 py-1 rounded text-sm font-semibold inline-block mb-4">
                                        {{ $plan->commission_percentage }}% comissão
                                    </div>

                                    @if($plan->features && is_array($plan->features))
                                        <ul class="space-y-2 text-sm">
                                            @foreach(array_slice($plan->features, 0, 3) as $feature)
                                                <li class="flex items-start">
                                                    <i class="fas fa-check text-green-500 mt-0.5 mr-2"></i>
                                                    <span>{{ $feature }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                            <div class="text-sm text-blue-800">
                                <strong>Teste grátis por 15 dias!</strong><br>
                                Você pode cancelar a qualquer momento durante o período de teste sem ser cobrado.
                                Dados bancários podem ser configurados depois no painel administrativo.
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-between">
                        <button type="button" @click="step = 2"
                                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">
                            <i class="fas fa-arrow-left mr-2"></i> Voltar
                        </button>
                        <button type="submit"
                                class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-bold text-lg">
                            <i class="fas fa-check mr-2"></i> Criar Minha Conta Grátis
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function signupWizard() {
            return {
                step: 1,
                restaurant_name: '{{ old("restaurant_name") }}',
                restaurant_slug: '{{ old("restaurant_slug") }}',

                generateSlug() {
                    if (!this.restaurant_slug || this.restaurant_slug === '') {
                        this.restaurant_slug = this.restaurant_name
                            .toLowerCase()
                            .normalize('NFD')
                            .replace(/[\u0300-\u036f]/g, '')
                            .replace(/[^a-z0-9]+/g, '-')
                            .replace(/^-+|-+$/g, '');
                    }
                }
            }
        }
    </script>
</body>
</html>
