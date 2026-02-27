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
                                            ->imageEditor()
                                            ->imageEditorAspectRatios(['1:1'])
                                            ->helperText('Tamanho recomendado: 512x512px'),

                                        Forms\Components\FileUpload::make('banner')
                                            ->label('Banner')
                                            ->image()
                                            ->directory('banners')
                                            ->imageEditor()
                                            ->imageEditorAspectRatios(['16:9'])
                                            ->helperText('Tamanho recomendado: 1920x1080px'),
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
                                Forms\Components\Section::make('Configurações da Impressora Térmica')
                                    ->schema([
                                        Forms\Components\Select::make('printer_type')
                                            ->label('Tipo de Impressora')
                                            ->options([
                                                'none' => 'Nenhuma',
                                                'network' => 'Rede (IP)',
                                                'usb' => 'USB',
                                                'bluetooth' => 'Bluetooth',
                                            ])
                                            ->default('none')
                                            ->reactive(),

                                        Forms\Components\TextInput::make('printer_ip')
                                            ->label('IP da Impressora')
                                            ->placeholder('192.168.1.100')
                                            ->visible(fn (Forms\Get $get) => $get('printer_type') === 'network'),

                                        Forms\Components\TextInput::make('printer_port')
                                            ->label('Porta')
                                            ->numeric()
                                            ->default(9100)
                                            ->visible(fn (Forms\Get $get) => $get('printer_type') === 'network'),

                                        Forms\Components\TextInput::make('printer_model')
                                            ->label('Modelo da Impressora')
                                            ->placeholder('Ex: Epson TM-T20, Bematech MP-4200'),

                                        Forms\Components\Select::make('paper_width')
                                            ->label('Largura do Papel (mm)')
                                            ->options([
                                                '58' => '58mm',
                                                '80' => '80mm',
                                            ])
                                            ->default('58'),

                                        Forms\Components\Toggle::make('auto_print_orders')
                                            ->label('Imprimir Pedidos Automaticamente')
                                            ->default(false)
                                            ->helperText('Imprime automaticamente quando um novo pedido chega'),

                                        Forms\Components\TextInput::make('print_copies')
                                            ->label('Número de Cópias')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->maxValue(5),
                                    ])->columns(2),
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
