<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;

class SefazService
{
    private Tools $tools;
    private Tenant $tenant;
    private string $environment;

    public function __construct()
    {
        $tenant = tenant();

        if (!$tenant) {
            throw new \Exception('Tenant não inicializado');
        }

        $this->tenant = $tenant;
        $this->environment = $tenant->nfce_environment ?? 'homologacao';

        // Inicializar Tools NFePHP
        $this->initializeTools();
    }

    /**
     * Inicializar ferramentas NFePHP
     */
    private function initializeTools(): void
    {
        try {
            $config = $this->buildConfig();
            $certificate = $this->loadCertificate();

            $this->tools = new Tools(json_encode($config), $certificate);
            $this->tools->model('55'); // NF-e modelo 55 (NFC-e)

        } catch (\Exception $e) {
            Log::error('Erro ao inicializar NFePHP Tools', [
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Erro ao inicializar ferramentas fiscais: ' . $e->getMessage());
        }
    }

    /**
     * Montar configuração do NFePHP
     */
    private function buildConfig(): array
    {
        $tenant = $this->tenant;

        return [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb' => $this->environment === 'production' ? 1 : 2,
            'razaosocial' => $tenant->razao_social,
            'siglaUF' => $tenant->fiscal_state,
            'cnpj' => $this->cleanCNPJ($tenant->cnpj),
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
            'tokenIBPT' => 'AAAAAAA',
            'CSC' => $tenant->csc_token,
            'CSCid' => $tenant->csc_id,
            'aProxyConf' => [
                'proxyIp' => '',
                'proxyPort' => '',
                'proxyUser' => '',
                'proxyPass' => ''
            ]
        ];
    }

    /**
     * Carregar certificado digital A1
     */
    private function loadCertificate(): Certificate
    {
        $tenant = $this->tenant;

        if (!$tenant->certificate_a1) {
            throw new \Exception('Certificado digital A1 não configurado');
        }

        // Certificado está em base64 no banco
        $pfxContent = base64_decode($tenant->certificate_a1);
        $password = $tenant->certificate_password;

        return Certificate::readPfx($pfxContent, $password);
    }

    /**
     * Emitir NFC-e para um pedido
     */
    public function emitNFCe(Order $order): array
    {
        Log::info('🧾 Iniciando emissão NFC-e SEFAZ', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        try {
            // Validar configuração fiscal
            $this->validateFiscalConfig();

            // Montar XML da NFC-e
            $nfe = $this->buildNFCeXML($order);

            // Assinar XML
            $signedXML = $this->tools->signNFe($nfe);

            // Enviar para SEFAZ
            $response = $this->tools->sefazEnviaLote([$signedXML], 1);

            // Processar resposta
            $std = new Standardize($response);
            $arr = $std->toArray();

            if ($arr['cStat'] == '103') { // Lote recebido com sucesso
                // Aguardar processamento (em produção, fazer polling)
                sleep(2);

                $recibo = $arr['infRec']['nRec'];
                $protocol = $this->tools->sefazConsultaRecibo($recibo);

                $stdProtocol = new Standardize($protocol);
                $arrProtocol = $stdProtocol->toArray();

                if (isset($arrProtocol['protNFe']['infProt'])) {
                    $infProt = $arrProtocol['protNFe']['infProt'];

                    if ($infProt['cStat'] == '100') { // Autorizada
                        // Gerar DANFE PDF
                        $chave = $infProt['chNFe'];
                        $protocolo = $infProt['nProt'];

                        Log::info('✅ NFC-e autorizada pela SEFAZ', [
                            'chave' => $chave,
                            'protocolo' => $protocolo,
                        ]);

                        return [
                            'status' => 'authorized',
                            'chave_acesso' => $chave,
                            'protocolo' => $protocolo,
                            'xml' => $signedXML,
                            'data_autorizacao' => $infProt['dhRecbto'],
                        ];
                    } else {
                        throw new \Exception('Nota rejeitada: ' . $infProt['xMotivo']);
                    }
                }
            }

            throw new \Exception('Erro ao processar lote: ' . $arr['xMotivo']);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao emitir NFC-e', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Montar XML da NFC-e
     */
    private function buildNFCeXML(Order $order): string
    {
        $tenant = $this->tenant;
        $customer = $order->customer;

        $nfe = new Make();

        // Informações da NF-e
        $std = new \stdClass();
        $std->versao = '4.00';
        $std->Id = null; // Será gerado automaticamente
        $std->pk_nItem = '';

        $infNFe = $nfe->taginfNFe($std);

        // Identificação da NF-e
        $std = new \stdClass();
        $std->cUF = $this->getUFCode($tenant->fiscal_state);
        $std->cNF = rand(10000000, 99999999);
        $std->natOp = 'VENDA';
        $std->mod = 65; // NFC-e
        $std->serie = $tenant->nfce_serie;
        $std->nNF = $tenant->nfce_numero;
        $std->dhEmi = date('Y-m-d\TH:i:sP');
        $std->tpNF = 1; // Saída
        $std->idDest = 1; // Operação interna
        $std->cMunFG = $this->getMunicipalityCode($tenant->fiscal_city, $tenant->fiscal_state);
        $std->tpImp = 4; // DANFE NFC-e
        $std->tpEmis = 1; // Normal
        $std->cDV = 0; // Será calculado
        $std->tpAmb = $this->environment === 'production' ? 1 : 2;
        $std->finNFe = 1; // Normal
        $std->indFinal = 1; // Consumidor final
        $std->indPres = 1; // Presencial
        $std->procEmi = 0; // Aplicação do contribuinte
        $std->verProc = '1.0';

        $ide = $nfe->tagide($std);

        // Emitente
        $std = new \stdClass();
        $std->xNome = $tenant->razao_social;
        $std->xFant = $tenant->name;
        $std->IE = $this->cleanIE($tenant->inscricao_estadual);
        $std->CRT = $this->getCRTCode($tenant->regime_tributario);
        $std->CNPJ = $this->cleanCNPJ($tenant->cnpj);

        $emit = $nfe->tagemit($std);

        // Endereço do Emitente
        $std = new \stdClass();
        $std->xLgr = $tenant->fiscal_address;
        $std->nro = $tenant->fiscal_number;
        $std->xCpl = $tenant->fiscal_complement;
        $std->xBairro = $tenant->fiscal_neighborhood;
        $std->cMun = $this->getMunicipalityCode($tenant->fiscal_city, $tenant->fiscal_state);
        $std->xMun = $tenant->fiscal_city;
        $std->UF = $tenant->fiscal_state;
        $std->CEP = $this->cleanCEP($tenant->fiscal_zipcode);
        $std->cPais = '1058';
        $std->xPais = 'BRASIL';

        $enderEmit = $nfe->tagenderEmit($std);

        // Destinatário (Cliente)
        if ($customer->cpf) {
            $std = new \stdClass();
            $std->xNome = $customer->name;
            $std->indIEDest = 9; // Não contribuinte
            $std->CPF = $this->cleanCPF($customer->cpf);
            $std->email = $customer->email;

            $dest = $nfe->tagdest($std);
        }

        // Itens da NF-e
        foreach ($order->items as $index => $item) {
            $product = $item->product;
            $itemNum = $index + 1;

            // Produto
            $std = new \stdClass();
            $std->item = $itemNum;
            $std->cProd = $product->id;
            $std->cEAN = 'SEM GTIN';
            $std->xProd = $item->product_name;
            $std->NCM = $product->ncm ?? '19059090';
            $std->CFOP = $product->cfop ?? '5405';
            $std->uCom = 'UN';
            $std->qCom = $item->quantity;
            $std->vUnCom = number_format($item->unit_price, 2, '.', '');
            $std->vProd = number_format($item->subtotal, 2, '.', '');
            $std->cEANTrib = 'SEM GTIN';
            $std->uTrib = 'UN';
            $std->qTrib = $item->quantity;
            $std->vUnTrib = number_format($item->unit_price, 2, '.', '');
            $std->indTot = 1;

            $prod = $nfe->tagprod($std);

            // Impostos (Simples Nacional)
            $std = new \stdClass();
            $std->item = $itemNum;
            $std->vTotTrib = 0;

            $imposto = $nfe->tagimposto($std);

            // ICMS
            $std = new \stdClass();
            $std->item = $itemNum;
            $std->orig = 0; // Nacional
            $std->CSOSN = '102'; // Simples Nacional sem permissão de crédito

            $icms = $nfe->tagICMSSN102($std);
        }

        // Totais
        $std = new \stdClass();
        $std->vBC = 0;
        $std->vICMS = 0;
        $std->vICMSDeson = 0;
        $std->vFCP = 0;
        $std->vBCST = 0;
        $std->vST = 0;
        $std->vFCPST = 0;
        $std->vFCPSTRet = 0;
        $std->vProd = number_format($order->subtotal, 2, '.', '');
        $std->vFrete = 0;
        $std->vSeg = 0;
        $std->vDesc = number_format($order->discount, 2, '.', '');
        $std->vII = 0;
        $std->vIPI = 0;
        $std->vIPIDevol = 0;
        $std->vPIS = 0;
        $std->vCOFINS = 0;
        $std->vOutro = 0;
        $std->vNF = number_format($order->total, 2, '.', '');
        $std->vTotTrib = 0;

        $ICMSTot = $nfe->tagICMSTot($std);

        // Transporte
        $std = new \stdClass();
        $std->modFrete = 9; // Sem frete

        $transp = $nfe->tagtransp($std);

        // Pagamento
        $std = new \stdClass();
        $std->vTroco = 0;

        $pag = $nfe->tagpag($std);

        $std = new \stdClass();
        $std->tPag = $this->getPaymentType($order->payment_method);
        $std->vPag = number_format($order->total, 2, '.', '');

        $detPag = $nfe->tagdetPag($std);

        // Informações Adicionais
        $std = new \stdClass();
        $std->infCpl = "Pedido: {$order->order_number}";

        $infAdic = $nfe->taginfAdic($std);

        // Gerar XML
        $xml = $nfe->getXML();

        return $xml;
    }

    /**
     * Validar configuração fiscal
     */
    private function validateFiscalConfig(): void
    {
        $tenant = $this->tenant;
        $errors = [];

        if (!$tenant->cnpj) $errors[] = 'CNPJ não configurado';
        if (!$tenant->razao_social) $errors[] = 'Razão Social não configurada';
        if (!$tenant->inscricao_estadual) $errors[] = 'Inscrição Estadual não configurada';
        if (!$tenant->certificate_a1) $errors[] = 'Certificado A1 não configurado';
        if (!$tenant->certificate_password) $errors[] = 'Senha do certificado não configurada';
        if (!$tenant->csc_id || !$tenant->csc_token) $errors[] = 'CSC não configurado';
        if (!$tenant->fiscal_address) $errors[] = 'Endereço fiscal não configurado';

        if (!empty($errors)) {
            throw new \Exception('Configuração fiscal incompleta: ' . implode(', ', $errors));
        }
    }

    // Helper methods
    private function cleanCNPJ(?string $cnpj): ?string
    {
        return $cnpj ? preg_replace('/[^0-9]/', '', $cnpj) : null;
    }

    private function cleanCPF(?string $cpf): ?string
    {
        return $cpf ? preg_replace('/[^0-9]/', '', $cpf) : null;
    }

    private function cleanIE(?string $ie): ?string
    {
        return $ie ? preg_replace('/[^0-9]/', '', $ie) : null;
    }

    private function cleanCEP(?string $cep): ?string
    {
        return $cep ? preg_replace('/[^0-9]/', '', $cep) : null;
    }

    private function getUFCode(string $uf): int
    {
        $codes = [
            'AC' => 12, 'AL' => 27, 'AP' => 16, 'AM' => 13, 'BA' => 29,
            'CE' => 23, 'DF' => 53, 'ES' => 32, 'GO' => 52, 'MA' => 21,
            'MT' => 51, 'MS' => 50, 'MG' => 31, 'PA' => 15, 'PB' => 25,
            'PR' => 41, 'PE' => 26, 'PI' => 22, 'RJ' => 33, 'RN' => 24,
            'RS' => 43, 'RO' => 11, 'RR' => 14, 'SC' => 42, 'SP' => 35,
            'SE' => 28, 'TO' => 17
        ];

        return $codes[$uf] ?? 35;
    }

    private function getMunicipalityCode(string $city, string $uf): string
    {
        // Simplificado - em produção, usar tabela IBGE
        return '3550308'; // São Paulo (exemplo)
    }

    private function getCRTCode(string $regime): int
    {
        return match($regime) {
            'simples_nacional', 'mei' => 1,
            'lucro_presumido', 'lucro_real' => 3,
            default => 1,
        };
    }

    private function getPaymentType(string $method): string
    {
        return match($method) {
            'cash' => '01',
            'credit_card' => '03',
            'debit_card' => '04',
            'pix' => '17',
            default => '99',
        };
    }
}
