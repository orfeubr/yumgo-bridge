<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\SettingsResource\Pages;
use App\Models\Settings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingsResource extends Resource
{
    protected static ?string $model = Settings::class;

    protected static ?string $slug = 'configuracoes';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configurações';

    protected static ?string $modelLabel = 'Configuração';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Configurações')
                    ->tabs([
                        // TAB 1: Identidade Visual
                        Forms\Components\Tabs\Tab::make('Identidade Visual')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Forms\Components\Section::make('Logo e Banner')
                                    ->schema([
                                        Forms\Components\FileUpload::make('logo')
                                            ->label('Logo')
                                            ->image()
                                            ->directory('logos')
                                            ->helperText('Tamanho recomendado: 512x512px (formato: JPG, PNG)')
                                            ->maxSize(2048)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg']),

                                        Forms\Components\FileUpload::make('banner')
                                            ->label('Banner')
                                            ->image()
                                            ->directory('banners')
                                            ->helperText('Tamanho recomendado: 1920x1080px (formato: JPG, PNG)')
                                            ->maxSize(2048)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg']),
                                    ])->columns(2),

                                Forms\Components\Section::make('Cores do Tema')
                                    ->description('Personalize as cores do seu aplicativo')
                                    ->schema([
                                        Forms\Components\ColorPicker::make('primary_color')
                                            ->label('Cor Primária')
                                            ->default('#EA1D2C')
                                            ->helperText('Cor principal (botões, destaques)'),

                                        Forms\Components\ColorPicker::make('secondary_color')
                                            ->label('Cor Secundária')
                                            ->default('#333333')
                                            ->helperText('Cor de texto e elementos secundários'),

                                        Forms\Components\ColorPicker::make('accent_color')
                                            ->label('Cor de Destaque')
                                            ->default('#FFA500')
                                            ->helperText('Cor para promoções e badges'),
                                    ])->columns(3),
                            ]),

                        // TAB 2: Contato
                        Forms\Components\Tabs\Tab::make('Contato')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Forms\Components\Section::make('Informações de Contato')
                                    ->schema([
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Telefone')
                                            ->tel()
                                            ->placeholder('(11) 98765-4321'),

                                        Forms\Components\TextInput::make('whatsapp')
                                            ->label('WhatsApp')
                                            ->tel()
                                            ->placeholder('5511987654321')
                                            ->helperText('Formato: 55 + DDD + número'),

                                        Forms\Components\TextInput::make('email')
                                            ->label('E-mail')
                                            ->email()
                                            ->placeholder('contato@restaurante.com'),

                                        Forms\Components\Textarea::make('address')
                                            ->label('Endereço Completo')
                                            ->rows(2)
                                            ->placeholder('Rua, número, bairro, cidade - UF'),

                                        Forms\Components\TextInput::make('instagram')
                                            ->label('Instagram')
                                            ->placeholder('@seurestaurante')
                                            ->prefix('@'),

                                        Forms\Components\TextInput::make('facebook')
                                            ->label('Facebook')
                                            ->placeholder('facebook.com/seurestaurante'),
                                    ])->columns(2),
                            ]),

                        // TAB 3: Horários
                        Forms\Components\Tabs\Tab::make('Horários')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Forms\Components\Section::make('Horário de Funcionamento')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_open_now')
                                            ->label('Restaurante está aberto')
                                            ->helperText('Desative temporariamente se estiver fechado (feriados, etc.)')
                                            ->default(true),

                                        Forms\Components\Textarea::make('holiday_message')
                                            ->label('Mensagem de Fechamento')
                                            ->placeholder('Ex: Fechado para manutenção. Voltamos em breve!')
                                            ->rows(2)
                                            ->helperText('Mensagem exibida quando o restaurante está fechado'),
                                    ]),

                                Forms\Components\Section::make('Defina os Horários de Cada Dia')
                                    ->description('Marque os dias que o restaurante funciona e configure os horários')
                                    ->schema([
                                        // Segunda-feira
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\Toggle::make('business_hours_seg_enabled')
                                                    ->label('Segunda-feira')
                                                    ->default(true)
                                                    ->inline(false)
                                                    ->reactive(),
                                                Forms\Components\TimePicker::make('business_hours_seg_open')
                                                    ->label('Abre às')
                                                    ->seconds(false)
                                                    ->default('18:00')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_seg_enabled')),
                                                Forms\Components\TimePicker::make('business_hours_seg_close')
                                                    ->label('Fecha às')
                                                    ->seconds(false)
                                                    ->default('23:00')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_seg_enabled')),
                                            ]),

                                        // Terça-feira
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\Toggle::make('business_hours_ter_enabled')
                                                    ->label('Terça-feira')
                                                    ->default(true)
                                                    ->inline(false)
                                                    ->reactive(),
                                                Forms\Components\TimePicker::make('business_hours_ter_open')
                                                    ->label('Abre às')
                                                    ->seconds(false)
                                                    ->default('18:00')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_ter_enabled')),
                                                Forms\Components\TimePicker::make('business_hours_ter_close')
                                                    ->label('Fecha às')
                                                    ->seconds(false)
                                                    ->default('23:00')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_ter_enabled')),
                                            ]),

                                        // Quarta-feira
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\Toggle::make('business_hours_qua_enabled')
                                                    ->label('Quarta-feira')
                                                    ->default(true)
                                                    ->inline(false)
                                                    ->reactive(),
                                                Forms\Components\TimePicker::make('business_hours_qua_open')
                                                    ->label('Abre às')
                                                    ->seconds(false)
                                                    ->default('18:00')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_qua_enabled')),
                                                Forms\Components\TimePicker::make('business_hours_qua_close')
                                                    ->label('Fecha às')
                                                    ->seconds(false)
                                                    ->default('23:00')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_qua_enabled')),
                                            ]),

                                        // Quinta-feira
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\Toggle::make('business_hours_qui_enabled')
                                                    ->label('Quinta-feira')
                                                    ->default(true)
                                                    ->inline(false)
                                                    ->reactive(),
                                                Forms\Components\TimePicker::make('business_hours_qui_open')
                                                    ->label('Abre às')
                                                    ->seconds(false)
                                                    ->default('18:00')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_qui_enabled')),
                                                Forms\Components\TimePicker::make('business_hours_qui_close')
                                                    ->label('Fecha às')
                                                    ->seconds(false)
                                                    ->default('23:00')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_qui_enabled')),
                                            ]),

                                        // Sexta-feira
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\Toggle::make('business_hours_sex_enabled')
                                                    ->label('Sexta-feira')
                                                    ->default(true)
                                                    ->inline(false)
                                                    ->reactive(),
                                                Forms\Components\TimePicker::make('business_hours_sex_open')
                                                    ->label('Abre às')
                                                    ->seconds(false)
                                                    ->default('18:00')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_sex_enabled')),
                                                Forms\Components\TimePicker::make('business_hours_sex_close')
                                                    ->label('Fecha às')
                                                    ->seconds(false)
                                                    ->default('23:30')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_sex_enabled')),
                                            ]),

                                        // Sábado
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\Toggle::make('business_hours_sab_enabled')
                                                    ->label('Sábado')
                                                    ->default(true)
                                                    ->inline(false)
                                                    ->reactive(),
                                                Forms\Components\TimePicker::make('business_hours_sab_open')
                                                    ->label('Abre às')
                                                    ->seconds(false)
                                                    ->default('18:00')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_sab_enabled')),
                                                Forms\Components\TimePicker::make('business_hours_sab_close')
                                                    ->label('Fecha às')
                                                    ->seconds(false)
                                                    ->default('23:30')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_sab_enabled')),
                                            ]),

                                        // Domingo
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\Toggle::make('business_hours_dom_enabled')
                                                    ->label('Domingo')
                                                    ->default(true)
                                                    ->inline(false)
                                                    ->reactive(),
                                                Forms\Components\TimePicker::make('business_hours_dom_open')
                                                    ->label('Abre às')
                                                    ->seconds(false)
                                                    ->default('18:00')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_dom_enabled')),
                                                Forms\Components\TimePicker::make('business_hours_dom_close')
                                                    ->label('Fecha às')
                                                    ->seconds(false)
                                                    ->default('23:00')
                                                    ->visible(fn (Forms\Get $get) => $get('business_hours_dom_enabled')),
                                            ]),
                                    ]),
                            ]),

                        // TAB 4: Delivery
                        Forms\Components\Tabs\Tab::make('Delivery')
                            ->icon('heroicon-o-truck')
                            ->schema([
                                Forms\Components\Section::make('Configurações Gerais')
                                    ->schema([
                                        Forms\Components\Toggle::make('allow_delivery')
                                            ->label('Permitir Delivery')
                                            ->default(true),

                                        Forms\Components\Toggle::make('allow_pickup')
                                            ->label('Permitir Retirada no Local')
                                            ->default(true),

                                        Forms\Components\TextInput::make('minimum_order_value')
                                            ->label('Pedido Mínimo (R$)')
                                            ->numeric()
                                            ->prefix('R$')
                                            ->default(0),

                                        Forms\Components\TextInput::make('estimated_delivery_time')
                                            ->label('Tempo Estimado de Entrega (min)')
                                            ->numeric()
                                            ->suffix('min')
                                            ->default(45),
                                    ])->columns(2),

                                Forms\Components\Section::make('Bairros de Entrega')
                                    ->description('Para cadastrar e gerenciar os bairros atendidos com taxas e tempos personalizados, acesse o menu "Bairros" na barra lateral.')
                                    ->schema([
                                        Forms\Components\Placeholder::make('neighborhoods_link')
                                            ->label('')
                                            ->content(new \Illuminate\Support\HtmlString(
                                                '<div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">' .
                                                '<svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">' .
                                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>' .
                                                '</svg>' .
                                                '<div>' .
                                                '<p class="font-semibold text-blue-900">Gerenciar Bairros de Entrega</p>' .
                                                '<p class="text-sm text-blue-700 mt-1">Acesse o menu <strong>Bairros</strong> para cadastrar zonas de entrega com busca automática, taxas personalizadas e controle completo.</p>' .
                                                '<a href="' . route('filament.restaurant.resources.bairros.index') . '" class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-blue-600 hover:text-blue-800">' .
                                                'Ir para Bairros →' .
                                                '</a>' .
                                                '</div>' .
                                                '</div>'
                                            ))
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // TAB 5: Pagamentos
                        Forms\Components\Tabs\Tab::make('Pagamentos')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Forms\Components\Section::make('Pagamentos Online (Integrado com Asaas)')
                                    ->description('Estes métodos processam pagamentos automaticamente via Asaas')
                                    ->schema([
                                        Forms\Components\Toggle::make('payment_pix_enabled')
                                            ->label('PIX')
                                            ->helperText('Pagamento instantâneo via QR Code')
                                            ->default(true)
                                            ->columnSpan(1),

                                        Forms\Components\Toggle::make('payment_credit_card_enabled')
                                            ->label('Cartão de Crédito')
                                            ->helperText('Visa, Mastercard, etc.')
                                            ->default(true)
                                            ->columnSpan(1),

                                        Forms\Components\Toggle::make('payment_debit_card_enabled')
                                            ->label('Cartão de Débito')
                                            ->helperText('Débito online')
                                            ->default(true)
                                            ->columnSpan(1),
                                    ])->columns(3)->collapsible(),

                                Forms\Components\Section::make('Pagamento na Entrega')
                                    ->description('Cliente paga diretamente ao entregador (apenas sinalização, sem processamento online)')
                                    ->schema([
                                        Forms\Components\Toggle::make('payment_on_delivery_enabled')
                                            ->label('Habilitar Pagamento na Entrega')
                                            ->helperText('Permite que cliente pague ao receber o pedido')
                                            ->default(true)
                                            ->reactive()
                                            ->columnSpan(3),

                                        Forms\Components\Fieldset::make('Métodos Aceitos')
                                            ->schema([
                                                Forms\Components\Grid::make(4)
                                                    ->schema([
                                                        Forms\Components\Checkbox::make('accept_cash_on_delivery')
                                                            ->label('💵 Dinheiro')
                                                            ->default(true)
                                                            ->reactive(),

                                                        Forms\Components\Checkbox::make('accept_card_on_delivery')
                                                            ->label('💳 Cartão (Máquina)')
                                                            ->helperText('Entregador leva maquininha')
                                                            ->default(false),

                                                        Forms\Components\Checkbox::make('accept_vr_on_delivery')
                                                            ->label('🎫 VR Benefícios')
                                                            ->helperText('Vale Refeição')
                                                            ->default(false),

                                                        Forms\Components\Checkbox::make('accept_va_on_delivery')
                                                            ->label('🍽️ Vale Alimentação')
                                                            ->helperText('VR, Alelo, Sodexo, Ticket')
                                                            ->default(false),

                                                        Forms\Components\Checkbox::make('accept_sodexo_on_delivery')
                                                            ->label('🟢 Sodexo')
                                                            ->default(false),

                                                        Forms\Components\Checkbox::make('accept_alelo_on_delivery')
                                                            ->label('🔵 Alelo')
                                                            ->default(false),

                                                        Forms\Components\Checkbox::make('accept_ticket_on_delivery')
                                                            ->label('🟡 Ticket')
                                                            ->default(false),
                                                    ]),
                                            ])
                                            ->visible(fn (Forms\Get $get) => $get('payment_on_delivery_enabled')),

                                        Forms\Components\TextInput::make('min_change_value')
                                            ->label('Troco para até')
                                            ->numeric()
                                            ->prefix('R$')
                                            ->placeholder('100.00')
                                            ->helperText('Valor máximo que o entregador pode levar de troco')
                                            ->visible(fn (Forms\Get $get) => $get('accept_cash_on_delivery'))
                                            ->columnSpan(2),

                                        Forms\Components\Textarea::make('delivery_payment_instructions')
                                            ->label('Instruções para o Entregador')
                                            ->placeholder('Ex: Sempre confirmar o valor com o cliente antes de finalizar. Para cartão, usar a maquininha X.')
                                            ->rows(3)
                                            ->visible(fn (Forms\Get $get) => $get('payment_on_delivery_enabled'))
                                            ->columnSpanFull(),
                                    ])->columns(3)->collapsible(),

                                Forms\Components\Section::make('Configuração Asaas')
                                    ->description('Configure sua conta Asaas para receber pagamentos online')
                                    ->schema([
                                        Forms\Components\Placeholder::make('asaas_config')
                                            ->label('')
                                            ->content('Para configurar sua conta Asaas, acesse: Painel → Conta de Pagamento'),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
                            ]),

                        // TAB 6: Impressora
                        Forms\Components\Tabs\Tab::make('Impressora')
                            ->icon('heroicon-o-printer')
                            ->schema([
                                // ⚠️ AVISO IMPORTANTE
                                Forms\Components\Section::make('⚠️ Como Funciona a Impressão Automática?')
                                    ->description('Entenda a arquitetura antes de configurar')
                                    ->schema([
                                        Forms\Components\Placeholder::make('architecture_explanation')
                                            ->label('')
                                            ->content(new \Illuminate\Support\HtmlString('
                                                <div class="space-y-4">
                                                    <!-- Aviso Principal -->
                                                    <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-4 border-2 border-yellow-400 dark:border-yellow-600">
                                                        <p class="text-base font-bold text-yellow-900 dark:text-yellow-100 mb-2">
                                                            ⚠️ Esta Página NÃO Configura Impressoras!
                                                        </p>
                                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                                            Navegadores <strong>não podem acessar</strong> impressoras USB/locais por motivos de segurança.
                                                        </p>
                                                    </div>

                                                    <!-- Fluxo Visual -->
                                                    <div class="rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-5 border border-blue-200 dark:border-blue-700">
                                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-3">
                                                            🔄 Funcionamento em 3 Passos:
                                                        </h4>

                                                        <div class="space-y-3">
                                                            <div class="flex gap-3 items-start">
                                                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center">1</div>
                                                                <div class="flex-1">
                                                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Nesta Página (Painel Web)</p>
                                                                    <p class="text-xs text-gray-600 dark:text-gray-400">→ Apenas GERA credenciais (ID + Token)</p>
                                                                </div>
                                                            </div>

                                                            <div class="flex gap-3 items-start">
                                                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-600 text-white text-sm font-bold flex items-center justify-center">2</div>
                                                                <div class="flex-1">
                                                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">No Computador (App YumGo Bridge)</p>
                                                                    <p class="text-xs text-gray-600 dark:text-gray-400">→ DETECTA e CONFIGURA impressoras USB/Rede</p>
                                                                </div>
                                                            </div>

                                                            <div class="flex gap-3 items-start">
                                                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-600 text-white text-sm font-bold flex items-center justify-center">3</div>
                                                                <div class="flex-1">
                                                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Cliente Faz Pedido Pago</p>
                                                                    <p class="text-xs text-gray-600 dark:text-gray-400">→ App IMPRIME automaticamente em ~3 segundos</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Analogia WhatsApp -->
                                                    <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-700">
                                                        <p class="text-sm font-semibold text-green-900 dark:text-green-100 mb-2">
                                                            💡 Pense como WhatsApp Web:
                                                        </p>
                                                        <div class="text-xs text-green-800 dark:text-green-200 space-y-1">
                                                            <p>• <strong>WhatsApp Web</strong> = Painel Web (interface apenas)</p>
                                                            <p>• <strong>App do Celular</strong> = YumGo Bridge (faz tudo de verdade)</p>
                                                            <p>• <strong>Mensagens</strong> = Pedidos (impressos pelo app)</p>
                                                        </div>
                                                    </div>

                                                    <!-- Link para Página Dedicada -->
                                                    <div class="rounded-lg bg-blue-100 dark:bg-blue-900/30 p-4 border border-blue-300 dark:border-blue-600">
                                                        <p class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-3">
                                                            📄 Precisa de Ajuda Completa?
                                                        </p>
                                                        <a href="/painel/configuracoes?tab=-impressora-tab"
                                                           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                            Ir para Página de Configuração de Impressora
                                                        </a>
                                                    </div>
                                                </div>
                                            ')),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),

                                Forms\Components\Section::make('Impressão Automática')
                                    ->description('App para imprimir pedidos automaticamente')
                                    ->schema([
                                        Forms\Components\Placeholder::make('bridge_download')
                                            ->label('1. Baixar App')
                                            ->content(new \Illuminate\Support\HtmlString('
                                                <a href="' . route('download.bridge') . '" class="inline-flex items-center px-4 py-3 text-sm font-bold rounded-lg bg-blue-600 hover:bg-blue-700 text-white">
                                                    ⬇️ Baixar YumGo Bridge
                                                </a>
                                            ')),

                                        Forms\Components\Placeholder::make('restaurant_id_display')
                                            ->label('2. Copiar ID')
                                            ->content(function () {
                                                $tenantId = tenancy()->tenant?->id ?? 'N/A';
                                                return new \Illuminate\Support\HtmlString('
                                                    <div class="flex gap-2">
                                                        <input type="text" value="' . e($tenantId) . '" readonly class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 font-mono text-sm">
                                                        <button type="button" onclick="navigator.clipboard.writeText(\'' . e($tenantId) . '\'); new FilamentNotification().title(\'Copiado!\').success().send();" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg font-bold">
                                                            📋 Copiar
                                                        </button>
                                                    </div>
                                                ');
                                            }),

                                        Forms\Components\Placeholder::make('token_instructions')
                                            ->label('3. Gerar Token')
                                            ->content(function () {
                                                $user = auth()->user();
                                                $hasToken = $user->tokens()->where('name', 'bridge-app')->exists();

                                                if ($hasToken) {
                                                    return new \Illuminate\Support\HtmlString('
                                                        <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                                            <span class="font-bold text-green-900 dark:text-green-100">✅ Token Ativo</span>
                                                            <button type="button" onclick="if(confirm(\'Revogar token?\')) window.location.href=window.location.pathname+\'?revokeToken=1\';" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded font-bold text-sm">
                                                                Revogar
                                                            </button>
                                                        </div>
                                                    ');
                                                }

                                                return new \Illuminate\Support\HtmlString('
                                                    <button type="button" onclick="window.location.href=window.location.pathname+\'?generateToken=1\';" class="px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-bold">
                                                        🔑 Gerar Token
                                                    </button>
                                                ');
                                            }),
                                    ]),
                            ]),

                        // TAB 7: Pedidos
                        Forms\Components\Tabs\Tab::make('Pedidos')
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                Forms\Components\Section::make('Gestão de Pedidos')
                                    ->schema([
                                        Forms\Components\Toggle::make('auto_accept_orders')
                                            ->label('Aceitar Pedidos Automaticamente')
                                            ->default(false)
                                            ->helperText('Se desativado, você precisará aceitar manualmente'),

                                        Forms\Components\TextInput::make('preparation_time')
                                            ->label('Tempo de Preparo (min)')
                                            ->numeric()
                                            ->suffix('min')
                                            ->default(30),

                                        Forms\Components\Toggle::make('require_customer_phone')
                                            ->label('Exigir Telefone do Cliente')
                                            ->default(true),

                                        Forms\Components\Toggle::make('require_customer_cpf')
                                            ->label('Exigir CPF do Cliente')
                                            ->default(false),

                                        Forms\Components\Textarea::make('order_instructions')
                                            ->label('Instruções para Pedidos')
                                            ->placeholder('Ex: Pedidos acima de R$ 50 ganham brinde')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),

                        // TAB 8: Notificações
                        Forms\Components\Tabs\Tab::make('Notificações')
                            ->icon('heroicon-o-bell')
                            ->schema([
                                Forms\Components\Section::make('Notificações de Novos Pedidos')
                                    ->schema([
                                        Forms\Components\Toggle::make('notify_email_new_order')
                                            ->label('Notificar por E-mail')
                                            ->default(true),

                                        Forms\Components\TextInput::make('notification_email')
                                            ->label('E-mail para Notificações')
                                            ->email()
                                            ->placeholder('pedidos@restaurante.com'),

                                        Forms\Components\Toggle::make('notify_sms_new_order')
                                            ->label('Notificar por SMS')
                                            ->default(false),

                                        Forms\Components\Toggle::make('notify_whatsapp_new_order')
                                            ->label('Notificar por WhatsApp')
                                            ->default(false),

                                        Forms\Components\TextInput::make('notification_phone')
                                            ->label('Telefone para Notificações')
                                            ->tel()
                                            ->placeholder('5511987654321'),
                                    ])->columns(2),
                            ]),

                        // TAB 9: Recursos
                        Forms\Components\Tabs\Tab::make('Recursos')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Forms\Components\Section::make('Recursos Disponíveis')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_reviews')
                                            ->label('Avaliações de Clientes')
                                            ->default(true)
                                            ->helperText('Permitir que clientes avaliem produtos e pedidos'),

                                        Forms\Components\Toggle::make('enable_loyalty_program')
                                            ->label('Programa de Fidelidade (Cashback)')
                                            ->default(true)
                                            ->helperText('Sistema de cashback e pontos'),

                                        Forms\Components\Toggle::make('enable_coupons')
                                            ->label('Cupons de Desconto')
                                            ->default(true)
                                            ->helperText('Permitir uso de cupons promocionais'),

                                        Forms\Components\Toggle::make('enable_scheduled_orders')
                                            ->label('Agendamento de Pedidos')
                                            ->default(false)
                                            ->helperText('Permitir que clientes agendem pedidos para depois'),
                                    ])->columns(2),
                            ]),

                        // TAB 10: Políticas
                        Forms\Components\Tabs\Tab::make('Políticas')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Section::make('Termos e Políticas')
                                    ->schema([
                                        Forms\Components\RichEditor::make('terms_of_service')
                                            ->label('Termos de Serviço')
                                            ->columnSpanFull(),

                                        Forms\Components\RichEditor::make('privacy_policy')
                                            ->label('Política de Privacidade')
                                            ->columnSpanFull(),

                                        Forms\Components\RichEditor::make('return_policy')
                                            ->label('Política de Trocas e Devoluções')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),
                Tables\Columns\ColorColumn::make('primary_color')
                    ->label('Cor Primária'),
                Tables\Columns\IconColumn::make('is_open_now')
                    ->label('Aberto')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Atualização')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSettings::route('/'),
        ];
    }
}
