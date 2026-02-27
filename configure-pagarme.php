#!/usr/bin/env php
<?php

/**
 * Script Interativo para Configuração do Pagar.me
 *
 * Uso: php configure-pagarme.php
 */

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   🏦 CONFIGURAÇÃO PAGAR.ME - YumGo Platform\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

function ask($question, $default = null) {
    if ($default) {
        echo "{$question} [{$default}]: ";
    } else {
        echo "{$question}: ";
    }

    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);

    return $line ?: $default;
}

function confirm($question) {
    $answer = ask("{$question} (s/n)", 's');
    return in_array(strtolower($answer), ['s', 'sim', 'y', 'yes']);
}

echo "📋 Este script vai ajudá-lo a configurar o Pagar.me.\n\n";

// Passo 1: Verificar se já tem credenciais
$envFile = __DIR__ . '/.env';
$envContent = file_get_contents($envFile);

preg_match('/PAGARME_API_KEY=(.*)/', $envContent, $matches);
$hasApiKey = !empty($matches[1]);

if ($hasApiKey) {
    echo "✅ Credenciais Pagar.me já existem no .env\n";

    if (!confirm("Deseja reconfigurá-las?")) {
        echo "\n";
        echo "Pulando etapa de credenciais...\n";
        goto CREATE_RECIPIENT;
    }
}

// Passo 2: Obter credenciais
echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   PASSO 1: Obter Credenciais\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
echo "1. Acesse: https://dashboard.pagar.me/login\n";
echo "2. Vá em: Configurações → Chaves de API\n";
echo "3. Copie as chaves abaixo:\n\n";

$apiKey = ask("API Key (sk_test_... ou sk_live_...)");
$encryptionKey = ask("Encryption Key (ek_test_... ou ek_live_...)");
$webhookToken = ask("Webhook Token (crie uma string aleatória)", bin2hex(random_bytes(16)));

if (empty($apiKey) || empty($encryptionKey)) {
    echo "\n❌ Erro: API Key e Encryption Key são obrigatórias!\n";
    exit(1);
}

// Detectar ambiente
$environment = strpos($apiKey, '_test_') !== false ? 'sandbox' : 'production';
echo "\n";
echo "🔍 Ambiente detectado: " . strtoupper($environment) . "\n";

// Atualizar .env
echo "\n";
echo "💾 Atualizando .env...\n";

$newEnvContent = preg_replace(
    '/PAGARME_API_KEY=.*/',
    "PAGARME_API_KEY={$apiKey}",
    $envContent
);
$newEnvContent = preg_replace(
    '/PAGARME_ENCRYPTION_KEY=.*/',
    "PAGARME_ENCRYPTION_KEY={$encryptionKey}",
    $newEnvContent
);
$newEnvContent = preg_replace(
    '/PAGARME_WEBHOOK_TOKEN=.*/',
    "PAGARME_WEBHOOK_TOKEN={$webhookToken}",
    $newEnvContent
);

file_put_contents($envFile, $newEnvContent);

echo "✅ Credenciais salvas no .env\n";

// Limpar cache
exec('php artisan config:clear');
echo "✅ Cache de configuração limpo\n";

CREATE_RECIPIENT:

// Passo 3: Criar recebedor da plataforma
echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   PASSO 2: Criar Recebedor da Plataforma\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

// Verificar se já tem recipient
preg_match('/PAGARME_PLATFORM_RECIPIENT_ID=(.*)/', file_get_contents($envFile), $matches);
$hasRecipient = !empty($matches[1]);

if ($hasRecipient) {
    echo "✅ Recebedor da plataforma já configurado: {$matches[1]}\n";

    if (!confirm("Deseja criar um novo recebedor?")) {
        echo "\n";
        echo "Pulando criação de recebedor...\n";
        goto FINISH;
    }
}

echo "Este é o recebedor que vai receber a COMISSÃO da plataforma (3%).\n\n";

$name = ask("Nome/Razão Social", "YumGo LTDA");
$email = ask("Email", "financeiro@yumgo.com.br");
$document = ask("CPF/CNPJ (apenas números)", "12345678000190");
$phone = ask("Telefone com DDD (apenas números)", "11999999999");

$documentType = strlen($document) === 11 ? 'individual' : 'company';
$documentLabel = $documentType === 'individual' ? 'Pessoa Física' : 'Pessoa Jurídica';

echo "\n";
echo "Tipo detectado: {$documentLabel}\n\n";

echo "═══ DADOS BANCÁRIOS ═══\n";
echo "A conta deve estar na mesma titularidade do CPF/CNPJ informado.\n\n";

echo "Códigos dos principais bancos:\n";
echo "  001 = Banco do Brasil\n";
echo "  237 = Bradesco\n";
echo "  341 = Itaú\n";
echo "  033 = Santander\n";
echo "  104 = Caixa Econômica\n";
echo "  260 = Nubank\n";
echo "  077 = Inter\n\n";

$bankCode = ask("Código do Banco", "341");
$branchNumber = ask("Agência (sem dígito)", "0001");
$branchDigit = ask("Dígito da Agência", "0");
$accountNumber = ask("Número da Conta (sem dígito)", "12345678");
$accountDigit = ask("Dígito da Conta", "9");
$accountType = ask("Tipo de Conta (checking/savings)", "checking");

echo "\n";
echo "🔄 Criando recebedor no Pagar.me...\n";

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = new App\Services\PagarMeService();

    $recipientData = [
        'name' => $name,
        'email' => $email,
        'document' => $document,
        'type' => $documentType,
        'phone' => '+55' . $phone,
        'bank_account' => [
            'holder_name' => $name,
            'holder_type' => $documentType,
            'holder_document' => $document,
            'bank' => $bankCode,
            'branch_number' => $branchNumber,
            'branch_check_digit' => $branchDigit,
            'account_number' => $accountNumber,
            'account_check_digit' => $accountDigit,
            'type' => $accountType,
        ],
    ];

    echo "\n";
    echo "📤 Enviando requisição para Pagar.me...\n";

    $result = $service->createRecipient($recipientData);

    if (!$result || !isset($result['id'])) {
        throw new Exception('Erro ao criar recebedor. Resposta inválida da API.');
    }

    $recipientId = $result['id'];

    echo "✅ Recebedor criado com sucesso!\n";
    echo "   ID: {$recipientId}\n";

    // Atualizar .env
    $envContent = file_get_contents($envFile);
    $newEnvContent = preg_replace(
        '/PAGARME_PLATFORM_RECIPIENT_ID=.*/',
        "PAGARME_PLATFORM_RECIPIENT_ID={$recipientId}",
        $envContent
    );
    file_put_contents($envFile, $newEnvContent);

    echo "✅ ID do recebedor salvo no .env\n";

    // Limpar cache novamente
    exec('php artisan config:clear');
    echo "✅ Cache de configuração limpo\n";

} catch (Exception $e) {
    echo "\n";
    echo "❌ ERRO ao criar recebedor:\n";
    echo "   {$e->getMessage()}\n\n";
    echo "Possíveis causas:\n";
    echo "  - API Key inválida ou expirada\n";
    echo "  - Dados bancários incorretos\n";
    echo "  - CPF/CNPJ inválido\n";
    echo "  - Banco não suportado pelo Pagar.me\n";
    echo "\n";
    echo "Verifique os dados e tente novamente.\n";
    exit(1);
}

FINISH:

// Finalização
echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   ✅ CONFIGURAÇÃO CONCLUÍDA!\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

echo "📋 Próximos Passos:\n\n";

echo "1. Configure o Webhook no Dashboard Pagar.me:\n";
echo "   URL: https://yumgo.com.br/api/webhooks/pagarme\n";
echo "   Eventos: order.paid, charge.paid, order.payment_failed\n\n";

echo "2. Configure cada restaurante:\n";
echo "   Acesse: https://slug-restaurante.yumgo.com.br/painel/payment-account\n";
echo "   Preencha os dados e clique em 'Criar Conta de Recebimentos'\n\n";

echo "3. Teste a integração:\n";
echo "   - Crie um pedido de teste\n";
echo "   - Use PIX como forma de pagamento\n";
echo "   - Verifique se o QR Code é gerado\n";
echo "   - Em sandbox, o PIX é aprovado automaticamente\n\n";

echo "📚 Documentação completa em: SETUP-PAGARME.md\n";
echo "\n";

echo "🎉 Tudo pronto para começar a receber pagamentos!\n";
echo "\n";
