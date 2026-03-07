<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class FiscalSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Configuração Fiscal';
    protected static ?string $title = 'Configuração Fiscal - Tributa AI';
    protected static ?int $navigationSort = 99;
    protected static ?string $navigationGroup = 'Configurações';

    protected static string $view = 'filament.restaurant.pages.fiscal-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $tenant = tenant();

        $this->form->fill([
            'nfce_enabled' => $tenant->certificate_a1 ? true : false,
            'nfce_environment' => $tenant->nfce_environment ?? 'homologacao',
            'cnpj' => $tenant->cnpj,
            'razao_social' => $tenant->razao_social,
            'inscricao_estadual' => $tenant->inscricao_estadual,
            'inscricao_municipal' => $tenant->inscricao_municipal,
            'regime_tributario' => $tenant->regime_tributario ?? 'simples_nacional',
            'certificate_a1' => $tenant->certificate_a1,
            'certificate_password' => $tenant->certificate_password,
            'nfce_serie' => $tenant->nfce_serie ?? 1,
            'nfce_numero' => $tenant->nfce_numero ?? 1,
            'csc_id' => $tenant->csc_id,
            'csc_token' => $tenant->csc_token,
            'tributaai_token' => $tenant->tributaai_token,
            'fiscal_address' => $tenant->fiscal_address,
            'fiscal_number' => $tenant->fiscal_number,
            'fiscal_complement' => $tenant->fiscal_complement,
            'fiscal_neighborhood' => $tenant->fiscal_neighborhood,
            'fiscal_city' => $tenant->fiscal_city,
            'fiscal_state' => $tenant->fiscal_state,
            'fiscal_zipcode' => $tenant->fiscal_zipcode,
        ]);

        // Notificar se já tem certificado
        if ($tenant->certificate_a1) {
            Notification::make()
                ->info()
                ->title('Certificado A1 já configurado')
                ->body('Para alterar, faça upload de um novo arquivo .pfx')
                ->send();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Emissão de NFC-e')
                    ->description('Configure a emissão DIRETA de NFC-e via SEFAZ (sem intermediários)')
                    ->schema([
                        Forms\Components\Placeholder::make('info_nfce')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    ✅ <strong>Emissão direta na SEFAZ</strong> - Sem custo mensal adicional<br>
                                    ✅ <strong>Totalmente automático</strong> - Nota emitida após confirmação do pagamento<br>
                                    ✅ <strong>Profissional</strong> - Igual aos grandes sistemas (iFood, AnotaAI)
                                </div>'
                            )),

                        Forms\Components\Radio::make('nfce_environment')
                            ->label('Ambiente NFC-e')
                            ->options([
                                'homologacao' => 'Homologação (Testes)',
                                'production' => 'Produção',
                            ])
                            ->inline()
                            ->default('homologacao')
                            ->required()
                            ->helperText('Comece sempre em Homologação para testar')
                            ->live(),
                    ])->columns(1),

                Forms\Components\Section::make('Dados da Empresa')
                    ->description('Informações fiscais da empresa (conforme cadastro na SEFAZ)')
                    ->schema([
                        Forms\Components\TextInput::make('cnpj')
                            ->label('CNPJ')
                            ->mask('99.999.999/9999-99')
                            ->required()
                            ->maxLength(18),

                        Forms\Components\TextInput::make('razao_social')
                            ->label('Razão Social')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('inscricao_estadual')
                            ->label('Inscrição Estadual (IE)')
                            ->required()
                            ->maxLength(20)
                            ->helperText('Conforme cadastro na SEFAZ'),

                        Forms\Components\TextInput::make('inscricao_municipal')
                            ->label('Inscrição Municipal (IM)')
                            ->maxLength(20)
                            ->helperText('Opcional'),

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
                            ->helperText('Conforme sua situação na Receita Federal')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Certificado Digital A1')
                    ->description('Certificado digital para assinatura da NFC-e')
                    ->schema([
                        Forms\Components\FileUpload::make('certificate_file')
                            ->label('Arquivo do Certificado (.pfx)')
                            ->acceptedFileTypes(['application/x-pkcs12', '.pfx'])
                            ->maxSize(5120)
                            ->helperText('Faça upload do arquivo .pfx do seu certificado A1')
                            ->disk('local')
                            ->directory('certificates')
                            ->visibility('private')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    try {
                                        // Ler conteúdo do arquivo
                                        $path = storage_path('app/' . $state);
                                        if (file_exists($path)) {
                                            $content = file_get_contents($path);
                                            $base64 = base64_encode($content);
                                            $set('certificate_a1', $base64);

                                            Notification::make()
                                                ->success()
                                                ->title('Certificado carregado!')
                                                ->body('Lembre-se de salvar as configurações.')
                                                ->send();

                                            // Deletar arquivo temporário
                                            @unlink($path);
                                        }
                                    } catch (\Exception $e) {
                                        Notification::make()
                                            ->danger()
                                            ->title('Erro ao processar certificado')
                                            ->body($e->getMessage())
                                            ->send();
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        // Campo hidden para armazenar o certificado em base64
                        Forms\Components\Hidden::make('certificate_a1'),

                        // Status do certificado
                        Forms\Components\Placeholder::make('certificate_status')
                            ->label('Status do Certificado')
                            ->content(function () {
                                $tenant = tenant();
                                if ($tenant->certificate_a1) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            ✅ Certificado instalado
                                        </span>'
                                    );
                                }
                                return new \Illuminate\Support\HtmlString(
                                    '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        ⚠️ Nenhum certificado instalado
                                    </span>'
                                );
                            })
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('certificate_password')
                            ->label('Senha do Certificado')
                            ->password()
                            ->revealable()
                            ->required()
                            ->helperText('Senha do arquivo .pfx')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('certificate_info')
                            ->label('ℹ️ Informações Importantes')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="text-sm space-y-2">
                                    <p><strong>Onde obter:</strong> Certisign, Serasa, Valid, etc.</p>
                                    <p><strong>Validade:</strong> Certificado A1 vale 1 ano</p>
                                    <p><strong>Custo:</strong> R$ 150-200/ano</p>
                                    <p><strong>Renovação:</strong> Deve ser renovado anualmente</p>
                                </div>'
                            ))
                            ->columnSpanFull(),
                    ])->columns(1),

                Forms\Components\Section::make('Configuração NFC-e')
                    ->description('CSC, Série e Número da NFC-e')
                    ->schema([
                        Forms\Components\TextInput::make('nfce_serie')
                            ->label('Série NFC-e')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->helperText('Série autorizada pela SEFAZ (geralmente 1)'),

                        Forms\Components\TextInput::make('nfce_numero')
                            ->label('Número Atual')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->helperText('Próximo número a ser emitido (incrementado automaticamente)'),

                        Forms\Components\TextInput::make('csc_id')
                            ->label('CSC ID')
                            ->required()
                            ->helperText('ID do Código de Segurança do Contribuinte (obtenha no portal da SEFAZ)'),

                        Forms\Components\TextInput::make('csc_token')
                            ->label('CSC Token')
                            ->password()
                            ->revealable()
                            ->required()
                            ->helperText('Código de Segurança do Contribuinte (obrigatório para NFC-e)'),

                        Forms\Components\Placeholder::make('csc_info')
                            ->label('ℹ️ Como obter o CSC')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="text-sm space-y-2">
                                    <p><strong>1.</strong> Acesse o portal da SEFAZ do seu estado</p>
                                    <p><strong>2.</strong> Login com seu certificado A1</p>
                                    <p><strong>3.</strong> Menu: NFC-e > Gerar CSC</p>
                                    <p><strong>4.</strong> Copie o ID e o Token gerados</p>
                                    <p class="text-yellow-600">⚠️ Guarde o CSC com segurança!</p>
                                </div>'
                            ))
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Classificação Automática de Produtos com IA')
                    ->description('Utilize IA para classificar produtos automaticamente (NCM, CFOP, CEST)')
                    ->schema([
                        Forms\Components\Placeholder::make('info_tributaai')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="space-y-4">
                                    <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700">
                                        <div class="flex items-start gap-3">
                                            <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div class="flex-1">
                                                <h3 class="text-sm font-semibold text-green-800 dark:text-green-200 mb-2">
                                                    ✅ Classificação com IA Disponível GRATUITAMENTE!
                                                </h3>
                                                <div class="text-sm text-green-700 dark:text-green-300 space-y-1">
                                                    <p><strong>A plataforma YumGo oferece classificação fiscal com IA para todos os restaurantes!</strong></p>
                                                    <p>🤖 IA sugere NCM/CFOP/CEST automaticamente</p>
                                                    <p>⚡ Cache de 30 dias - Economiza tempo e dinheiro</p>
                                                    <p>📦 Basta clicar no botão "🤖 Classificar com IA" ao cadastrar produtos</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700">
                                        <p class="text-sm text-blue-800 dark:text-blue-200">
                                            <strong>ℹ️ Token Próprio (Opcional):</strong><br>
                                            Se você já possui uma conta no Tributa AI e quer usar seu token próprio, pode configurá-lo abaixo.
                                            Caso contrário, deixe vazio para usar o token compartilhado da plataforma.
                                        </p>
                                    </div>
                                </div>'
                            ))
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('tributaai_token')
                            ->label('Token Próprio Tributa AI (Apenas Planos Enterprise)')
                            ->password()
                            ->revealable()
                            ->helperText('Deixe vazio para usar o token compartilhado da plataforma (recomendado)')
                            ->placeholder('Deixe vazio para usar token da plataforma')
                            ->columnSpanFull(),
                    ])->columns(1)->collapsible()->collapsed(),

                Forms\Components\Section::make('Endereço Fiscal')
                    ->description('Endereço da sede da empresa (conforme CNPJ)')
                    ->schema([
                        Forms\Components\TextInput::make('fiscal_zipcode')
                            ->label('CEP')
                            ->mask('99999-999')
                            ->required()
                            ->maxLength(9),

                        Forms\Components\TextInput::make('fiscal_address')
                            ->label('Logradouro')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('fiscal_number')
                            ->label('Número')
                            ->required()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('fiscal_complement')
                            ->label('Complemento')
                            ->maxLength(255)
                            ->helperText('Opcional'),

                        Forms\Components\TextInput::make('fiscal_neighborhood')
                            ->label('Bairro')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('fiscal_city')
                            ->label('Cidade')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('fiscal_state')
                            ->label('Estado')
                            ->options([
                                'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
                                'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal',
                                'ES' => 'Espírito Santo', 'GO' => 'Goiás', 'MA' => 'Maranhão',
                                'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul', 'MG' => 'Minas Gerais',
                                'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná', 'PE' => 'Pernambuco',
                                'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
                                'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima',
                                'SC' => 'Santa Catarina', 'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins',
                            ])
                            ->required()
                            ->searchable(),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $tenant = tenant();
            $tenant->update($data);

            Notification::make()
                ->success()
                ->title('Configurações salvas com sucesso!')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erro ao salvar configurações')
                ->body($e->getMessage())
                ->send();
        }
    }
}
