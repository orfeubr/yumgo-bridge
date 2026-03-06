<?php

/**
 * TESTE SIMULADO DE EMISSÃO DE NFC-e
 *
 * Este script simula a emissão de uma Nota Fiscal do Consumidor Eletrônica (NFC-e)
 * sem conectar à SEFAZ - apenas para validar a lógica e estrutura do XML.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║        TESTE SIMULADO DE EMISSÃO DE NFC-e                  ║\n";
echo "║        Restaurante: Boteco do Meu Rei                      ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// 1. Inicializar tenant
echo "📋 PASSO 1: Inicializando tenant...\n";
$tenant = Tenant::where('slug', 'botecodomeurei')->first();

if (!$tenant) {
    echo "❌ ERRO: Tenant não encontrado!\n";
    exit(1);
}

tenancy()->initialize($tenant);
echo "✅ Tenant inicializado: {$tenant->name}\n\n";

// 2. Criar dados de teste (pedido mock)
echo "📋 PASSO 2: Criando pedido de teste...\n";

// Buscar cliente existente ou criar mock
$customer = Customer::first();
if (!$customer) {
    echo "⚠️  Nenhum cliente encontrado, criando dados mock...\n";
    $customerData = [
        'name' => 'João Silva',
        'email' => 'joao.silva@email.com',
        'phone' => '11987654321',
        'cpf' => '123.456.789-00',
    ];
} else {
    $customerData = [
        'name' => $customer->name,
        'email' => $customer->email,
        'phone' => $customer->phone,
        'cpf' => $customer->cpf ?? '123.456.789-00',
    ];
}

// Buscar produtos existentes ou criar mock
$products = Product::active()->limit(3)->get();

if ($products->isEmpty()) {
    echo "⚠️  Nenhum produto encontrado, criando dados mock...\n";
    $itemsData = [
        [
            'name' => 'Feijoada Completa',
            'quantity' => 1,
            'price' => 45.00,
            'ncm' => '19059090',
            'cfop' => '5102',
            'cest' => null,
        ],
        [
            'name' => 'Caipirinha',
            'quantity' => 2,
            'price' => 15.00,
            'ncm' => '22030000',
            'cfop' => '5102',
            'cest' => null,
        ],
        [
            'name' => 'Porção de Torresmo',
            'quantity' => 1,
            'price' => 25.00,
            'ncm' => '19059090',
            'cfop' => '5102',
            'cest' => null,
        ],
    ];
} else {
    $itemsData = $products->map(function($product) {
        return [
            'name' => $product->name,
            'quantity' => 1,
            'price' => (float) $product->price,
            'ncm' => $product->ncm ?? '19059090',
            'cfop' => $product->cfop ?? '5102',
            'cest' => $product->cest,
        ];
    })->toArray();
}

// Calcular totais
$subtotal = array_sum(array_map(function($item) {
    return $item['quantity'] * $item['price'];
}, $itemsData));

$deliveryFee = 5.00;
$total = $subtotal + $deliveryFee;

// Criar objeto de pedido mock
$orderData = [
    'id' => 'TEST-' . time(),
    'customer_name' => $customerData['name'],
    'customer_email' => $customerData['email'],
    'customer_phone' => $customerData['phone'],
    'customer_cpf' => $customerData['cpf'],
    'subtotal' => $subtotal,
    'delivery_fee' => $deliveryFee,
    'total' => $total,
    'payment_method' => 'pix',
    'payment_status' => 'paid',
    'items' => $itemsData,
];

echo "✅ Pedido de teste criado!\n";
echo "   Cliente: {$orderData['customer_name']}\n";
echo "   Itens: " . count($itemsData) . "\n";
echo "   Total: R$ " . number_format($total, 2, ',', '.') . "\n\n";

// 3. Gerar XML da NFC-e (simulado)
echo "📋 PASSO 3: Gerando XML da NFC-e...\n\n";

$xml = generateNFCeXML($tenant, $orderData);

echo "✅ XML gerado com sucesso!\n\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    ESTRUTURA DO XML                        ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Validar estrutura
validateXMLStructure($xml);

// Mostrar resumo
echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    RESUMO DA NFC-e                         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "📄 DADOS DA NOTA:\n";
echo "   Série: 1\n";
echo "   Número: 1 (mock)\n";
echo "   Chave de Acesso: " . generateMockChaveAcesso() . "\n";
echo "   Ambiente: Homologação (teste)\n\n";

echo "🏢 EMITENTE:\n";
echo "   Razão Social: {$tenant->company_name}\n";
echo "   CNPJ: " . ($tenant->cnpj ?: '[NÃO CONFIGURADO]') . "\n";
echo "   Endereço: {$tenant->address}, {$tenant->number}\n";
echo "   Cidade: {$tenant->city} - {$tenant->state}\n\n";

echo "👤 DESTINATÁRIO:\n";
echo "   Nome: {$orderData['customer_name']}\n";
echo "   CPF: {$orderData['customer_cpf']}\n";
echo "   Email: {$orderData['customer_email']}\n\n";

echo "🛒 PRODUTOS:\n";
foreach ($itemsData as $index => $item) {
    $itemTotal = $item['quantity'] * $item['price'];
    echo "   " . ($index + 1) . ". {$item['name']}\n";
    echo "      Qtd: {$item['quantity']} x R$ " . number_format($item['price'], 2, ',', '.') .
         " = R$ " . number_format($itemTotal, 2, ',', '.') . "\n";
    echo "      NCM: {$item['ncm']} | CFOP: {$item['cfop']}\n\n";
}

echo "💰 VALORES:\n";
echo "   Subtotal: R$ " . number_format($subtotal, 2, ',', '.') . "\n";
echo "   Taxa de Entrega: R$ " . number_format($deliveryFee, 2, ',', '.') . "\n";
echo "   TOTAL: R$ " . number_format($total, 2, ',', '.') . "\n\n";

echo "💳 PAGAMENTO:\n";
echo "   Forma: " . strtoupper($orderData['payment_method']) . "\n";
echo "   Status: PAGO ✅\n\n";

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    RESULTADO DO TESTE                      ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "✅ TESTE SIMULADO CONCLUÍDO COM SUCESSO!\n\n";
echo "📝 Próximos passos para emissão REAL:\n";
echo "   1. Configurar Certificado A1 (certificado digital)\n";
echo "   2. Obter CSC ID e Token na SEFAZ do estado\n";
echo "   3. Cadastrar CNPJ e dados fiscais completos\n";
echo "   4. Testar em ambiente de homologação\n";
echo "   5. Solicitar autorização de uso na SEFAZ\n";
echo "   6. Ativar em produção\n\n";

echo "📚 Documentação: /docs/features/03-fiscal-notes.md\n\n";

// ===== FUNÇÕES AUXILIARES =====

function generateNFCeXML($tenant, $orderData) {
    // Simula geração de XML básico
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><nfeProc></nfeProc>');

    // Adiciona nó NFe
    $nfe = $xml->addChild('NFe');
    $nfe->addAttribute('xmlns', 'http://www.portalfiscal.inf.br/nfe');

    // Informações da NFC-e
    $infNFe = $nfe->addChild('infNFe');
    $infNFe->addAttribute('versao', '4.00');

    // IDE - Identificação
    $ide = $infNFe->addChild('ide');
    $ide->addChild('cUF', '35'); // São Paulo
    $ide->addChild('natOp', 'VENDA');
    $ide->addChild('mod', '65'); // Modelo 65 = NFC-e
    $ide->addChild('serie', '1');
    $ide->addChild('nNF', '1');
    $ide->addChild('dhEmi', date('c'));
    $ide->addChild('tpNF', '1'); // 1=Saída
    $ide->addChild('tpAmb', '2'); // 2=Homologação
    $ide->addChild('tpEmis', '1'); // 1=Normal
    $ide->addChild('tpImp', '4'); // 4=NFC-e
    $ide->addChild('indFinal', '1'); // 1=Consumidor final
    $ide->addChild('indPres', '1'); // 1=Presencial

    // Emitente
    $emit = $infNFe->addChild('emit');
    $emit->addChild('CNPJ', $tenant->cnpj ?: '00000000000000');
    $emit->addChild('xNome', $tenant->company_name);
    $emit->addChild('xFant', $tenant->name);

    $enderEmit = $emit->addChild('enderEmit');
    $enderEmit->addChild('xLgr', $tenant->address ?? 'Rua Teste');
    $enderEmit->addChild('nro', $tenant->number ?? '123');
    $enderEmit->addChild('xBairro', $tenant->neighborhood ?? 'Centro');
    $enderEmit->addChild('cMun', '3550308'); // São Paulo
    $enderEmit->addChild('xMun', $tenant->city ?? 'São Paulo');
    $enderEmit->addChild('UF', $tenant->state ?? 'SP');
    $enderEmit->addChild('CEP', str_replace('-', '', $tenant->zipcode ?? '01000000'));

    // Destinatário (consumidor)
    $dest = $infNFe->addChild('dest');
    if (!empty($orderData['customer_cpf'])) {
        $dest->addChild('CPF', preg_replace('/\D/', '', $orderData['customer_cpf']));
    }
    $dest->addChild('xNome', $orderData['customer_name']);
    $dest->addChild('indIEDest', '9'); // 9=Não contribuinte

    // Produtos
    foreach ($orderData['items'] as $index => $item) {
        $det = $infNFe->addChild('det');
        $det->addAttribute('nItem', $index + 1);

        $prod = $det->addChild('prod');
        $prod->addChild('cProd', str_pad($index + 1, 5, '0', STR_PAD_LEFT));
        $prod->addChild('xProd', $item['name']);
        $prod->addChild('NCM', $item['ncm']);
        $prod->addChild('CFOP', $item['cfop']);
        $prod->addChild('uCom', 'UN');
        $prod->addChild('qCom', number_format($item['quantity'], 4, '.', ''));
        $prod->addChild('vUnCom', number_format($item['price'], 10, '.', ''));
        $prod->addChild('vProd', number_format($item['quantity'] * $item['price'], 2, '.', ''));

        // Impostos (simplificado)
        $imposto = $det->addChild('imposto');
        $icms = $imposto->addChild('ICMS');
        $icmssn102 = $icms->addChild('ICMSSN102');
        $icmssn102->addChild('orig', '0');
        $icmssn102->addChild('CSOSN', '102'); // Simples Nacional
    }

    // Totais
    $total = $infNFe->addChild('total');
    $icmstot = $total->addChild('ICMSTot');
    $icmstot->addChild('vBC', '0.00');
    $icmstot->addChild('vICMS', '0.00');
    $icmstot->addChild('vProd', number_format($orderData['subtotal'], 2, '.', ''));
    $icmstot->addChild('vFrete', number_format($orderData['delivery_fee'], 2, '.', ''));
    $icmstot->addChild('vNF', number_format($orderData['total'], 2, '.', ''));

    // Forma de pagamento
    $pag = $infNFe->addChild('pag');
    $detPag = $pag->addChild('detPag');

    $tPag = match($orderData['payment_method']) {
        'pix' => '17',
        'credit_card' => '03',
        'debit_card' => '04',
        'money' => '01',
        default => '99',
    };

    $detPag->addChild('tPag', $tPag);
    $detPag->addChild('vPag', number_format($orderData['total'], 2, '.', ''));

    return $xml->asXML();
}

function validateXMLStructure($xml) {
    $checks = [
        'Declaração XML' => strpos($xml, '<?xml') !== false,
        'Nó NFe' => strpos($xml, '<NFe') !== false,
        'Identificação (IDE)' => strpos($xml, '<ide>') !== false,
        'Emitente (EMIT)' => strpos($xml, '<emit>') !== false,
        'Destinatário (DEST)' => strpos($xml, '<dest>') !== false,
        'Produtos (DET)' => strpos($xml, '<det ') !== false,
        'Totais (TOTAL)' => strpos($xml, '<total>') !== false,
        'Pagamento (PAG)' => strpos($xml, '<pag>') !== false,
        'ICMS' => strpos($xml, '<ICMS>') !== false,
        'NCM' => strpos($xml, '<NCM>') !== false,
        'CFOP' => strpos($xml, '<CFOP>') !== false,
    ];

    foreach ($checks as $item => $isValid) {
        $status = $isValid ? '✅' : '❌';
        echo "   {$status} {$item}\n";
    }
}

function generateMockChaveAcesso() {
    // Chave de acesso tem 44 dígitos
    // Formato: UF + AAMM + CNPJ + MOD + SERIE + NUMERO + TIPO + COD + DV
    $chave = '';
    for ($i = 0; $i < 44; $i++) {
        $chave .= rand(0, 9);
    }
    return $chave;
}
