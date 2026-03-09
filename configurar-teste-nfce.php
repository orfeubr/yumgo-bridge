<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n🧪 === CONFIGURANDO TESTE DE NFC-e ===\n\n";

$tenant = \App\Models\Tenant::where('slug', 'marmitariadagi')->first();

if (!$tenant) {
    echo "❌ Tenant não encontrado\n";
    exit(1);
}

echo "📝 Configurando dados fiscais de TESTE...\n\n";

// Certificado gerado (auto-assinado para teste)
$cert = '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';

$tenant->update([
    // Dados da empresa (fictícios para teste)
    'cnpj' => '11.222.333/0001-44',
    'razao_social' => 'Marmitaria da Gi LTDA - TESTE',
    'inscricao_estadual' => '123456789',
    'inscricao_municipal' => '987654321',
    'regime_tributario' => 'simples_nacional',

    // Certificado A1 (auto-assinado para teste)
    'certificate_a1' => $cert,
    'certificate_password' => 'teste123',

    // Configuração NFC-e
    'nfce_serie' => 1,
    'nfce_numero' => 1,
    'nfce_environment' => 'homologacao',

    // CSC (Código de Segurança do Contribuinte) - padrão de homologação
    'csc_id' => '1',
    'csc_token' => '123456',

    // Endereço fiscal (fictício)
    'fiscal_address' => 'Rua das Marmitas',
    'fiscal_number' => '123',
    'fiscal_complement' => 'Loja 1',
    'fiscal_neighborhood' => 'Centro',
    'fiscal_city' => 'Belo Horizonte',
    'fiscal_state' => 'MG',
    'fiscal_zipcode' => '30000-000',
]);

echo "✅ Configuração concluída com sucesso!\n\n";
echo "📋 Dados configurados (TESTE - Homologação):\n";
echo str_repeat('━', 70) . "\n";
echo "  CNPJ: 11.222.333/0001-44\n";
echo "  Razão Social: Marmitaria da Gi LTDA - TESTE\n";
echo "  IE: 123456789\n";
echo "  IM: 987654321\n";
echo "  Regime Tributário: Simples Nacional\n";
echo "\n";
echo "  Certificado A1: ✅ Instalado (auto-assinado)\n";
echo "  Senha: teste123\n";
echo "\n";
echo "  CSC ID: 1\n";
echo "  CSC Token: 123456\n";
echo "  Ambiente: Homologação (testes)\n";
echo "  Série NFC-e: 1\n";
echo "  Próximo número: 1\n";
echo "\n";
echo "  Endereço Fiscal:\n";
echo "    Rua das Marmitas, 123 - Loja 1\n";
echo "    Centro - Belo Horizonte/MG\n";
echo "    CEP: 30000-000\n";
echo "\n";
echo str_repeat('━', 70) . "\n";
echo "\n⚠️  ATENÇÃO: Estes são dados FICTÍCIOS para TESTE!\n";
echo "   A NFC-e será emitida em ambiente de HOMOLOGAÇÃO.\n";
echo "   Não tem validade fiscal.\n\n";
