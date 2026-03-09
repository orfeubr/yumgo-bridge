<?php

namespace App\Filament\Restaurant\Pages;

use App\Services\PagarMeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Http;

class PaymentAccount extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Dados para Recebimento';

    protected static ?string $title = 'Configurar Recebimentos';

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 41;

    protected static string $view = 'filament.restaurant.pages.payment-account';

    public ?array $data = [];

    public function mount(): void
    {
        $tenant = tenant();

        $this->form->fill([
            // Dados pessoais
            'name' => $tenant->name,
            'email' => $tenant->email,
            'cpf_cnpj' => $tenant->cpf_cnpj,
            'birth_date' => $tenant->birth_date,
            'company_type' => $tenant->company_type,
            'phone' => $tenant->phone,
            'mobile_phone' => $tenant->mobile_phone,

            // Endereço
            'postal_code' => $tenant->address_zipcode,
            'address' => $tenant->address_street,
            'address_number' => $tenant->address_number,
            'complement' => $tenant->address_complement,
            'province' => $tenant->address_neighborhood,
            'city' => $tenant->address_city,
            'state' => $tenant->address_state,

            // Dados bancários
            'bank_code' => $tenant->bank_code,
            'bank_branch_digit' => $tenant->bank_branch_digit,
            'bank_agency' => $tenant->bank_agency,
            'bank_account' => $tenant->bank_account,
            'bank_account_digit' => $tenant->bank_account_digit,
            'bank_account_type' => $tenant->bank_account_type,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('👤 Dados Pessoais / Empresa')
                    ->description('Dados do titular da conta')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome Completo / Razão Social')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('cpf_cnpj')
                                    ->label('CPF ou CNPJ')
                                    ->required()
                                    ->mask(fn ($state) => strlen(preg_replace('/\D/', '', $state ?? '')) <= 11
                                        ? '999.999.999-99'
                                        : '99.999.999/9999-99')
                                    ->placeholder('000.000.000-00 ou 00.000.000/0000-00'),
                            ]),

                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Data de Nascimento')
                            ->required()
                            ->maxDate(now()->subYears(18))
                            ->displayFormat('d/m/Y')
                            ->helperText('Apenas para pessoa física (CPF). Obrigatório para o Pagar.me.'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('company_type')
                                    ->label('Tipo de Empresa')
                                    ->options([
                                        'MEI' => 'MEI',
                                        'LIMITED' => 'Ltda',
                                        'INDIVIDUAL' => 'Pessoa Física',
                                        'ASSOCIATION' => 'Associação',
                                    ])
                                    ->default('MEI')
                                    ->required(),

                                Forms\Components\TextInput::make('phone')
                                    ->label('Telefone')
                                    ->tel()
                                    ->mask('(99) 9999-9999')
                                    ->placeholder('(11) 3333-4444'),

                                Forms\Components\TextInput::make('mobile_phone')
                                    ->label('Celular')
                                    ->tel()
                                    ->required()
                                    ->mask('(99) 99999-9999')
                                    ->placeholder('(11) 98888-7777'),
                            ]),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('📍 Endereço')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('postal_code')
                                    ->label('CEP')
                                    ->required()
                                    ->mask('99999-999')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (strlen(preg_replace('/\D/', '', $state ?? '')) === 8) {
                                            $this->searchAddressByCep($state, $set);
                                        }
                                    })
                                    ->placeholder('00000-000')
                                    ->helperText('Digite o CEP para preencher automaticamente'),

                                Forms\Components\TextInput::make('address')
                                    ->label('Rua/Avenida')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                            ]),

                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('address_number')
                                    ->label('Número')
                                    ->required()
                                    ->maxLength(10),

                                Forms\Components\TextInput::make('complement')
                                    ->label('Complemento')
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('province')
                                    ->label('Bairro')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('city')
                                    ->label('Cidade')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Select::make('state')
                            ->label('Estado')
                            ->required()
                            ->options([
                                'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
                                'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
                                'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
                                'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
                                'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
                                'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
                                'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins',
                            ])
                            ->searchable(),
                    ]),

                Forms\Components\Section::make('🏦 Dados Bancários')
                    ->description('Conta para receber os pagamentos')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('bank_code')
                                    ->label('Banco')
                                    ->required()
                                    ->searchable()
                                    ->options([
                                        '001' => '001 - Banco do Brasil',
                                        '033' => '033 - Santander',
                                        '104' => '104 - Caixa Econômica',
                                        '237' => '237 - Bradesco',
                                        '341' => '341 - Itaú',
                                        '077' => '077 - Inter',
                                        '260' => '260 - Nubank',
                                        '323' => '323 - Mercado Pago',
                                        '336' => '336 - C6 Bank',
                                        '290' => '290 - PagSeguro',
                                        '380' => '380 - PicPay',
                                    ])
                                    ->placeholder('Selecione o banco'),

                                Forms\Components\Select::make('bank_account_type')
                                    ->label('Tipo de Conta')
                                    ->required()
                                    ->options([
                                        'checking' => 'Conta Corrente',
                                        'savings' => 'Conta Poupança',
                                    ])
                                    ->default('checking'),
                            ]),

                        Forms\Components\Grid::make(5)
                            ->schema([
                                Forms\Components\TextInput::make('bank_agency')
                                    ->label('Agência')
                                    ->required()
                                    ->numeric()
                                    ->maxLength(10)
                                    ->placeholder('0001')
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('bank_branch_digit')
                                    ->label('Dígito Ag.')
                                    ->maxLength(1)
                                    ->placeholder('0')
                                    ->default('0')
                                    ->helperText('Deixe 0 se não tiver')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('bank_account')
                                    ->label('Conta')
                                    ->required()
                                    ->numeric()
                                    ->maxLength(20)
                                    ->placeholder('12345678')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('bank_account_digit')
                                    ->label('Dígito')
                                    ->required()
                                    ->maxLength(2)
                                    ->placeholder('9')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Placeholder::make('bank_warning')
                            ->label('⚠️ Importante')
                            ->content('Os dados bancários devem estar na mesma titularidade do CPF/CNPJ informado acima.'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function searchAddressByCep(string $cep, callable $set): void
    {
        $cep = preg_replace('/\D/', '', $cep);

        try {
            $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

            if ($response->successful() && !isset($response->json()['erro'])) {
                $address = $response->json();

                $set('address', $address['logradouro'] ?? '');
                $set('province', $address['bairro'] ?? '');
                $set('city', $address['localidade'] ?? '');
                $set('state', $address['uf'] ?? '');

                Notification::make()
                    ->success()
                    ->title('CEP encontrado!')
                    ->body('Endereço preenchido automaticamente')
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->warning()
                ->title('CEP não encontrado')
                ->body('Preencha o endereço manualmente')
                ->send();
        }
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $tenant = tenant();

            // Validar CPF/CNPJ
            $cpfCnpj = preg_replace('/\D/', '', $data['cpf_cnpj'] ?? '');
            if (strlen($cpfCnpj) !== 11 && strlen($cpfCnpj) !== 14) {
                throw new \Exception('CPF ou CNPJ inválido. Por favor, preencha corretamente.');
            }

            // Validar campos obrigatórios
            $requiredFields = [
                'name' => 'Nome',
                'email' => 'Email',
                'company_type' => 'Tipo de Empresa',
                'mobile_phone' => 'Celular',
                'postal_code' => 'CEP',
                'address' => 'Endereço',
                'address_number' => 'Número',
                'province' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'Estado',
                'bank_code' => 'Banco',
                'bank_agency' => 'Agência',
                'bank_account' => 'Conta',
                'bank_account_digit' => 'Dígito',
                'bank_account_type' => 'Tipo de Conta',
            ];

            foreach ($requiredFields as $field => $label) {
                if (empty($data[$field])) {
                    throw new \Exception("Campo obrigatório não preenchido: {$label}");
                }
            }

            // Se já tem recebedor configurado, apenas atualizar dados
            if ($tenant->pagarme_recipient_id || $tenant->asaas_account_id) {
                $tenant->update([
                    'cpf_cnpj' => preg_replace('/\D/', '', $data['cpf_cnpj']),
                    'birth_date' => $data['birth_date'],
                    'company_type' => $data['company_type'],
                    'phone' => $data['phone'],
                    'mobile_phone' => $data['mobile_phone'],
                    'address_zipcode' => $data['postal_code'],
                    'address_street' => $data['address'],
                    'address_number' => $data['address_number'],
                    'address_complement' => $data['complement'],
                    'address_neighborhood' => $data['province'],
                    'address_city' => $data['city'],
                    'address_state' => $data['state'],
                    'bank_code' => $data['bank_code'],
                    'bank_name' => $data['bank_code'], // Mantém compatibilidade
                    'bank_agency' => $data['bank_agency'],
                    'bank_branch_digit' => $data['bank_branch_digit'] ?? '0',
                    'bank_account' => $data['bank_account'],
                    'bank_account_digit' => $data['bank_account_digit'],
                    'bank_account_type' => $data['bank_account_type'],
                ]);

                Notification::make()
                    ->success()
                    ->title('✅ Dados atualizados!')
                    ->body('Suas informações foram atualizadas com sucesso')
                    ->send();

                return;
            }

            // Criar recebedor no Pagar.me
            $pagarmeService = new PagarMeService();

            // Log para debug
            \Log::info('Criando recebedor Pagar.me', [
                'cpf_cnpj' => $cpfCnpj,
                'bank_code' => $data['bank_code'],
            ]);

            $recipientData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'document' => $cpfCnpj,
                'type' => strlen($cpfCnpj) === 11 ? 'individual' : 'company',
                'phone' => preg_replace('/\D/', '', $data['mobile_phone']),
                'bank_account' => [
                    'holder_name' => $data['name'],
                    'holder_type' => strlen($cpfCnpj) === 11 ? 'individual' : 'company',
                    'holder_document' => $cpfCnpj,
                    'bank' => $data['bank_code'],
                    'branch_number' => $data['bank_agency'],
                    'branch_check_digit' => $data['bank_branch_digit'] ?? '0',
                    'account_number' => $data['bank_account'],
                    'account_check_digit' => $data['bank_account_digit'],
                    'type' => $data['bank_account_type'], // checking ou savings
                ],
            ];

            $result = $pagarmeService->createRecipient($recipientData);

            if (!$result || !isset($result['id'])) {
                throw new \Exception('Erro ao criar recebedor no Pagar.me. Verifique os dados bancários.');
            }

            // Mapear para os nomes corretos das colunas
            $tenant->update([
                'cpf_cnpj' => $cpfCnpj,
                'birth_date' => $data['birth_date'],
                'company_type' => $data['company_type'],
                'phone' => $data['phone'],
                'mobile_phone' => $data['mobile_phone'],
                'address_zipcode' => $data['postal_code'],
                'address_street' => $data['address'],
                'address_number' => $data['address_number'],
                'address_complement' => $data['complement'],
                'address_neighborhood' => $data['province'],
                'address_city' => $data['city'],
                'address_state' => $data['state'],
                'bank_name' => $data['bank_code'],
                'bank_agency' => $data['bank_agency'],
                'bank_account' => $data['bank_account'],
                'bank_account_digit' => $data['bank_account_digit'],
                'bank_account_type' => $data['bank_account_type'],
                'bank_code' => $data['bank_code'],
                'bank_branch_digit' => $data['bank_branch_digit'] ?? '0',
                'pagarme_recipient_id' => $result['id'],
                'payment_gateway' => 'pagarme',
            ]);

            Notification::make()
                ->success()
                ->title('🎉 Recebedor criado com sucesso!')
                ->body('Seus dados foram cadastrados no Pagar.me. Você já pode receber pagamentos!')
                ->send();

        } catch (Halt $exception) {
            throw $exception;
        } catch (\Exception $e) {
            \Log::error('Erro ao salvar conta de pagamento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->danger()
                ->title('❌ Erro ao salvar')
                ->body($e->getMessage())
                ->persistent()
                ->send();

            throw new Halt();
        }
    }

    public function getAccountStatus(): array
    {
        $tenant = tenant();
        $activeGateway = $tenant->payment_gateway ?? 'pagarme';

        // Se o gateway ativo é Pagar.me
        if ($activeGateway === 'pagarme') {
            if (!empty($tenant->pagarme_recipient_id)) {
                return [
                    'configured' => true,
                    'status' => 'approved',
                    'label' => '✅ Configurada (Pagar.me)',
                    'color' => 'success',
                ];
            }

            // Pagar.me ativo mas sem recipient - avisar!
            if (!empty($tenant->asaas_account_id)) {
                return [
                    'configured' => false,
                    'status' => 'needs_migration',
                    'label' => '⚠️ Configure o Pagar.me',
                    'color' => 'warning',
                ];
            }

            // Nenhum gateway configurado
            return [
                'configured' => false,
                'status' => 'not_configured',
                'label' => '⚪ Não Configurada',
                'color' => 'gray',
            ];
        }

        // Se o gateway ativo é Asaas (legado)
        if ($activeGateway === 'asaas') {
            if (!empty($tenant->asaas_account_id)) {
                return [
                    'configured' => true,
                    'status' => 'legacy',
                    'label' => '✅ Configurada (Asaas)',
                    'color' => 'success',
                ];
            }

            // Asaas ativo mas sem account - avisar!
            return [
                'configured' => false,
                'status' => 'not_configured',
                'label' => '⚠️ Configure o Asaas',
                'color' => 'warning',
            ];
        }

        // Fallback para gateways desconhecidos
        return [
            'configured' => false,
            'status' => 'not_configured',
            'label' => '⚪ Não Configurada',
            'color' => 'gray',
        ];
    }
}
