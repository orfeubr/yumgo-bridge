<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class FiscalOnboarding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Começar com NFC-e';
    protected static ?string $title = 'Guia de Configuração NFC-e';
    protected static ?int $navigationSort = 98;
    protected static ?string $navigationGroup = 'Configurações';

    protected static string $view = 'filament.restaurant.pages.fiscal-onboarding';

    public ?array $data = [];
    public int $currentStep = 1;

    public function mount(): void
    {
        $tenant = tenant();

        // Detectar step atual baseado no que já foi configurado
        $this->currentStep = $this->detectCurrentStep($tenant);

        $this->form->fill([
            'current_step' => $this->currentStep,
        ]);
    }

    protected function detectCurrentStep(Tenant $tenant): int
    {
        // Step 1: Conhecendo a NFC-e (sempre disponível)
        // Step 2: Dados da Empresa
        if (!$tenant->cnpj || !$tenant->razao_social || !$tenant->inscricao_estadual) {
            return 1;
        }

        // Step 3: Certificado Digital
        if (!$tenant->certificate_a1 || !$tenant->certificate_password) {
            return 2;
        }

        // Step 4: CSC e Configurações SEFAZ
        if (!$tenant->csc_id || !$tenant->csc_token) {
            return 3;
        }

        // Step 5: Teste de Emissão
        if ($tenant->nfce_environment === 'homologacao') {
            return 4;
        }

        // Step 6: Produção (tudo configurado)
        return 5;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    // ===== STEP 1: INTRODUÇÃO =====
                    Forms\Components\Wizard\Step::make('introducao')
                        ->label('1. Entendendo a NFC-e')
                        ->icon('heroicon-o-light-bulb')
                        ->schema([
                            Forms\Components\Placeholder::make('intro_content')
                                ->label('')
                                ->content(new HtmlString('
                                    <div class="space-y-6">
                                        <div class="text-center mb-8">
                                            <div class="text-6xl mb-4">📄</div>
                                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                                Bem-vindo ao Sistema de NFC-e!
                                            </h2>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                Configure a emissão automática de notas fiscais em 5 passos simples
                                            </p>
                                        </div>

                                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-700">
                                            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">
                                                🤔 O que é NFC-e?
                                            </h3>
                                            <p class="text-sm text-blue-800 dark:text-blue-200 mb-3">
                                                <strong>NFC-e</strong> (Nota Fiscal do Consumidor Eletrônica) é o documento fiscal obrigatório para vendas ao consumidor final.
                                            </p>
                                            <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-2 ml-6 list-disc">
                                                <li>Substituiu o cupom fiscal antigo</li>
                                                <li>Obrigatória para delivery e vendas presenciais</li>
                                                <li>Emitida direto na SEFAZ (não precisa de intermediário)</li>
                                                <li>100% digital e automática</li>
                                            </ul>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-700">
                                                <div class="text-2xl mb-2">✅</div>
                                                <h4 class="font-semibold text-green-900 dark:text-green-100 mb-2">Vantagens</h4>
                                                <ul class="text-sm text-green-800 dark:text-green-200 space-y-1">
                                                    <li>• Sem custo mensal</li>
                                                    <li>• Emissão automática</li>
                                                    <li>• 100% digital</li>
                                                    <li>• Regularização fiscal</li>
                                                </ul>
                                            </div>

                                            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-700">
                                                <div class="text-2xl mb-2">💰</div>
                                                <h4 class="font-semibold text-yellow-900 dark:text-yellow-100 mb-2">Custos</h4>
                                                <ul class="text-sm text-yellow-800 dark:text-yellow-200 space-y-1">
                                                    <li>• Certificado A1: R$ 250/ano</li>
                                                    <li>• Credenciamento: GRÁTIS</li>
                                                    <li>• CSC: GRÁTIS</li>
                                                    <li>• Sistema YumGo: INCLUSO</li>
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                                🎯 O que você vai precisar:
                                            </h3>
                                            <div class="space-y-3">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0 text-primary-600 dark:text-primary-400 font-bold">1</div>
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">Dados da Empresa</p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">CNPJ, Razão Social, Inscrição Estadual</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0 text-primary-600 dark:text-primary-400 font-bold">2</div>
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">Certificado Digital A1</p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">Comprar em: Serasa, Certisign, Valid (~R$ 250)</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0 text-primary-600 dark:text-primary-400 font-bold">3</div>
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">Credenciamento SEFAZ</p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">Solicitar no portal da SEFAZ do seu estado (GRÁTIS)</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0 text-primary-600 dark:text-primary-400 font-bold">4</div>
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">CSC (Código de Segurança)</p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">Gerar no portal da SEFAZ após credenciamento (GRÁTIS)</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-700">
                                            <div class="flex items-start gap-3">
                                                <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                <div>
                                                    <h4 class="font-semibold text-red-900 dark:text-red-100 mb-1">⚠️ Importante sobre Responsabilidade</h4>
                                                    <p class="text-sm text-red-800 dark:text-red-200">
                                                        <strong>A YumGo fornece apenas a TECNOLOGIA para emissão.</strong><br>
                                                        A responsabilidade fiscal e tributária é 100% do RESTAURANTE.<br>
                                                        Você precisa ter seus próprios certificado, credenciamento e CSC.<br>
                                                        Recomendamos contratar um contador para orientação.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center mt-6">
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Prazo total: 2-3 semanas</p>
                                            <p class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                                                Vamos começar? 🚀
                                            </p>
                                        </div>
                                    </div>
                                ')),
                        ]),

                    // ===== STEP 2: DADOS DA EMPRESA =====
                    Forms\Components\Wizard\Step::make('dados_empresa')
                        ->label('2. Dados da Empresa')
                        ->icon('heroicon-o-building-office')
                        ->schema([
                            Forms\Components\Placeholder::make('step2_intro')
                                ->label('')
                                ->content(new HtmlString('
                                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                                        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                                            📋 Passo 2: Dados Cadastrais
                                        </h3>
                                        <p class="text-sm text-blue-800 dark:text-blue-200">
                                            Informe os dados da empresa conforme cadastro na Receita Federal e SEFAZ.
                                            Estes dados aparecerão em todas as NFC-e emitidas.
                                        </p>
                                    </div>
                                ')),

                            Forms\Components\TextInput::make('cnpj')
                                ->label('CNPJ')
                                ->mask('99.999.999/9999-99')
                                ->required()
                                ->maxLength(18)
                                ->helperText('Conforme cadastro na Receita Federal'),

                            Forms\Components\TextInput::make('razao_social')
                                ->label('Razão Social')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Nome empresarial registrado'),

                            Forms\Components\TextInput::make('inscricao_estadual')
                                ->label('Inscrição Estadual (IE)')
                                ->required()
                                ->maxLength(20)
                                ->helperText('Número da IE ativa na SEFAZ'),

                            Forms\Components\Radio::make('regime_tributario')
                                ->label('CRT - Código Regime Tributário')
                                ->options([
                                    'simples_nacional' => '1 - Simples Nacional',
                                    'mei' => '1 - MEI (Simples Nacional)',
                                    'lucro_presumido' => '3 - Lucro Presumido',
                                    'lucro_real' => '3 - Lucro Real',
                                ])
                                ->required()
                                ->default('simples_nacional')
                                ->helperText('Conforme enquadramento na Receita Federal')
                                ->columnSpanFull(),

                            Forms\Components\Placeholder::make('step2_tip')
                                ->label('')
                                ->content(new HtmlString('
                                    <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-700">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                            💡 <strong>Dica:</strong> Não tem certeza dos dados? Consulte seu contador ou acesse o portal da Receita Federal.
                                        </p>
                                    </div>
                                '))
                                ->columnSpanFull(),
                        ])->columns(2),

                    // ===== STEP 3: CERTIFICADO DIGITAL =====
                    Forms\Components\Wizard\Step::make('certificado')
                        ->label('3. Certificado Digital')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Forms\Components\Placeholder::make('step3_intro')
                                ->label('')
                                ->content(new HtmlString('
                                    <div class="space-y-4 mb-6">
                                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                                            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                                                🔐 Passo 3: Certificado Digital A1
                                            </h3>
                                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                                O certificado digital é usado para assinar digitalmente as NFC-e, garantindo autenticidade e validade jurídica.
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">1️⃣ Onde Comprar</h4>
                                                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                                    <li>• Serasa Experian</li>
                                                    <li>• Certisign</li>
                                                    <li>• Valid</li>
                                                    <li>• Soluti</li>
                                                </ul>
                                            </div>

                                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">2️⃣ Tipo</h4>
                                                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                                    <li>• <strong>Certificado A1</strong></li>
                                                    <li>• Arquivo .pfx</li>
                                                    <li>• Validade: 1 ano</li>
                                                    <li>• Preço: R$ 150-300</li>
                                                </ul>
                                            </div>

                                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">3️⃣ Processo</h4>
                                                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                                    <li>• Compra online</li>
                                                    <li>• Validação presencial</li>
                                                    <li>• Emissão (1-3 dias)</li>
                                                    <li>• Download do .pfx</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                ')),

                            Forms\Components\FileUpload::make('certificate_file')
                                ->label('Arquivo do Certificado (.pfx)')
                                ->acceptedFileTypes(['application/x-pkcs12', '.pfx'])
                                ->maxSize(5120)
                                ->helperText('Faça upload do arquivo .pfx do seu certificado A1')
                                ->disk('local')
                                ->directory('certificates')
                                ->visibility('private')
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('certificate_password')
                                ->label('Senha do Certificado')
                                ->password()
                                ->revealable()
                                ->required()
                                ->helperText('Senha criada durante a emissão do certificado')
                                ->columnSpanFull(),

                            Forms\Components\Placeholder::make('step3_tip')
                                ->label('')
                                ->content(new HtmlString('
                                    <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-700">
                                        <p class="text-sm text-red-800 dark:text-red-200">
                                            🔒 <strong>Segurança:</strong> Seu certificado é armazenado criptografado e usado apenas para assinar suas NFC-e. Nunca compartilhe com terceiros.
                                        </p>
                                    </div>
                                '))
                                ->columnSpanFull(),
                        ])->columns(1),

                    // ===== STEP 4: CSC E SEFAZ =====
                    Forms\Components\Wizard\Step::make('sefaz')
                        ->label('4. SEFAZ e CSC')
                        ->icon('heroicon-o-key')
                        ->schema([
                            Forms\Components\Placeholder::make('step4_intro')
                                ->label('')
                                ->content(new HtmlString('
                                    <div class="space-y-4 mb-6">
                                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                                            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                                                🔑 Passo 4: Credenciamento e CSC
                                            </h3>
                                            <p class="text-sm text-blue-800 dark:text-blue-200 mb-3">
                                                O CSC (Código de Segurança do Contribuinte) é gerado pela SEFAZ após seu credenciamento para emissão de NFC-e.
                                            </p>
                                        </div>

                                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                                            <h4 class="font-semibold text-gray-900 dark:text-white mb-4">📝 Como obter o CSC:</h4>
                                            <div class="space-y-4">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0 text-primary-600 dark:text-primary-400 font-bold text-sm">1</div>
                                                    <div class="flex-1">
                                                        <p class="font-medium text-gray-900 dark:text-white">Acesse o portal da SEFAZ do seu estado</p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                            SP: <a href="https://www.fazenda.sp.gov.br/nfce/" target="_blank" class="text-primary-600 hover:underline">fazenda.sp.gov.br/nfce</a><br>
                                                            Outros estados: Procure "Portal NFC-e" + nome do estado
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0 text-primary-600 dark:text-primary-400 font-bold text-sm">2</div>
                                                    <div class="flex-1">
                                                        <p class="font-medium text-gray-900 dark:text-white">Faça login com seu certificado A1</p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Use o certificado que você acabou de adquirir</p>
                                                    </div>
                                                </div>

                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0 text-primary-600 dark:text-primary-400 font-bold text-sm">3</div>
                                                    <div class="flex-1">
                                                        <p class="font-medium text-gray-900 dark:text-white">Solicite credenciamento NFC-e</p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Menu: Credenciamento > NFC-e > Solicitar</p>
                                                    </div>
                                                </div>

                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0 text-primary-600 dark:text-primary-400 font-bold text-sm">4</div>
                                                    <div class="flex-1">
                                                        <p class="font-medium text-gray-900 dark:text-white">Aguarde aprovação (1-3 dias)</p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Você receberá email de confirmação</p>
                                                    </div>
                                                </div>

                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center flex-shrink-0 text-primary-600 dark:text-primary-400 font-bold text-sm">5</div>
                                                    <div class="flex-1">
                                                        <p class="font-medium text-gray-900 dark:text-white">Gere o CSC</p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Menu: NFC-e > Gerar CSC > Copie o ID e o Token</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ')),

                            Forms\Components\TextInput::make('csc_id')
                                ->label('CSC ID')
                                ->required()
                                ->numeric()
                                ->helperText('Número do CSC (geralmente 1)'),

                            Forms\Components\TextInput::make('csc_token')
                                ->label('CSC Token')
                                ->password()
                                ->revealable()
                                ->required()
                                ->helperText('Código alfanumérico gerado pela SEFAZ')
                                ->columnSpanFull(),

                            Forms\Components\Radio::make('nfce_environment')
                                ->label('Ambiente de Emissão')
                                ->options([
                                    'homologacao' => 'Homologação (Teste) - Recomendado para começar',
                                    'production' => 'Produção (Notas Reais)',
                                ])
                                ->default('homologacao')
                                ->required()
                                ->helperText('Sempre comece em Homologação para testar!')
                                ->columnSpanFull(),

                            Forms\Components\Placeholder::make('step4_tip')
                                ->label('')
                                ->content(new HtmlString('
                                    <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-700">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                            ⚠️ <strong>Importante:</strong> Guarde o CSC Token com segurança! Ele é necessário para assinar todas as NFC-e. Caso perca, será necessário gerar um novo.
                                        </p>
                                    </div>
                                '))
                                ->columnSpanFull(),
                        ])->columns(2),

                    // ===== STEP 5: TESTE =====
                    Forms\Components\Wizard\Step::make('teste')
                        ->label('5. Testar Emissão')
                        ->icon('heroicon-o-beaker')
                        ->schema([
                            Forms\Components\Placeholder::make('step5_intro')
                                ->label('')
                                ->content(new HtmlString('
                                    <div class="space-y-6">
                                        <div class="text-center mb-8">
                                            <div class="text-6xl mb-4">🎉</div>
                                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                                Quase lá!
                                            </h2>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                Agora vamos testar a emissão de NFC-e em ambiente de homologação
                                            </p>
                                        </div>

                                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-6 border border-green-200 dark:border-green-700">
                                            <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-4">
                                                ✅ Tudo Configurado!
                                            </h3>
                                            <div class="space-y-2 text-sm text-green-800 dark:text-green-200">
                                                <p>✓ Dados da empresa cadastrados</p>
                                                <p>✓ Certificado digital configurado</p>
                                                <p>✓ CSC obtido e registrado</p>
                                                <p>✓ Ambiente de homologação ativo</p>
                                            </div>
                                        </div>

                                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-700">
                                            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4">
                                                🧪 Como Testar
                                            </h3>
                                            <ol class="space-y-3 text-sm text-blue-800 dark:text-blue-200">
                                                <li class="flex items-start gap-2">
                                                    <span class="font-bold">1.</span>
                                                    <span>Crie um pedido de teste no sistema (ou use um pedido existente)</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="font-bold">2.</span>
                                                    <span>Marque o pedido como "pago"</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="font-bold">3.</span>
                                                    <span>O sistema irá emitir a NFC-e automaticamente</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="font-bold">4.</span>
                                                    <span>Verifique se a chave de acesso foi gerada com sucesso</span>
                                                </li>
                                                <li class="flex items-start gap-2">
                                                    <span class="font-bold">5.</span>
                                                    <span>Repita o teste 5-10 vezes para garantir estabilidade</span>
                                                </li>
                                            </ol>
                                        </div>

                                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-6 border border-yellow-200 dark:border-yellow-700">
                                            <h3 class="text-lg font-semibold text-yellow-900 dark:text-yellow-100 mb-4">
                                                ⚡ Próximos Passos Após Testar
                                            </h3>
                                            <ol class="space-y-2 text-sm text-yellow-800 dark:text-yellow-200">
                                                <li>1️⃣ Teste por 1-2 semanas em homologação</li>
                                                <li>2️⃣ Valide todos os cenários (delivery, balcão, diferentes produtos)</li>
                                                <li>3️⃣ Quando confiante, volte aqui e mude para "Produção"</li>
                                                <li>4️⃣ Pronto! Todas as vendas reais terão NFC-e automática 🎉</li>
                                            </ol>
                                        </div>

                                        <div class="text-center mt-8 p-6 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 rounded-lg">
                                            <p class="text-lg font-semibold text-primary-900 dark:text-primary-100 mb-2">
                                                🚀 Parabéns pela Configuração!
                                            </p>
                                            <p class="text-sm text-primary-800 dark:text-primary-200">
                                                Seu restaurante está pronto para emitir NFC-e automaticamente!
                                            </p>
                                        </div>
                                    </div>
                                ')),
                        ]),

                ])->columnSpanFull()->persistStepInQueryString(),
            ])
            ->statePath('data');
    }

    public function getTitle(): string
    {
        return 'Guia de Configuração NFC-e - Passo a Passo';
    }

    public function hasLogo(): bool
    {
        return false;
    }
}
