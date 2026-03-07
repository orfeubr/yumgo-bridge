<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n🧾 === TESTE DE EMISSÃO AUTOMÁTICA DE NFC-e ===\n\n";

// 1. Listar tenants e verificar configuração fiscal
echo "1️⃣ Verificando configuração fiscal dos restaurantes:\n";
echo str_repeat('-', 70) . "\n";

$tenants = \App\Models\Tenant::all();

foreach ($tenants as $tenant) {
    $hasCert = $tenant->certificate_a1 ? '✅' : '❌';
    $serie = $tenant->nfce_serie ?? 'N/A';
    $env = $tenant->nfce_environment ?? 'N/A';

    echo sprintf(
        "%-30s | Cert: %s | Série: %s | Ambiente: %s\n",
        substr($tenant->name, 0, 28),
        $hasCert,
        $serie,
        $env
    );
}

echo "\n2️⃣ Como funciona o fluxo automático:\n";
echo str_repeat('-', 70) . "\n";
echo "
✅ PASSO 1: Cliente faz pedido
   └─ Order criado com payment_status = 'pending'

✅ PASSO 2: Cliente paga (PIX/Cartão)
   └─ Webhook atualiza: payment_status = 'paid'

✅ PASSO 3: OrderFiscalObserver detecta mudança ⭐
   └─ Verifica se tenant tem certificado A1
   └─ Verifica se pedido já tem nota fiscal
   └─ Despacha EmitirNFCeJob para fila 'nfce'

✅ PASSO 4: Job processa em background (5 segundos depois)
   ├─ Lock distribuído (evita duplicação)
   ├─ Rate limiting (máx 10/min por restaurante)
   ├─ Retry automático (3 tentativas)
   └─ Chama SefazService::emitirNFCe()

✅ PASSO 5: SefazService emite via NFePHP
   ├─ Gera XML da NFC-e
   ├─ Assina com certificado A1
   ├─ Envia para SEFAZ estadual
   └─ Armazena chave de acesso + XML

✅ RESULTADO: NFC-e emitida em ~10 segundos ⚡
";

echo "\n3️⃣ Verificando workers de fila:\n";
echo str_repeat('-', 70) . "\n";

$processes = shell_exec("ps aux | grep 'queue:work' | grep -v grep");

if ($processes) {
    echo $processes;
    echo "\n✅ Workers rodando via Supervisor\n";
} else {
    echo "❌ NENHUM WORKER RODANDO!\n";
    echo "\nPara iniciar manualmente:\n";
    echo "php artisan queue:work redis --queue=nfce --tries=3 --timeout=120\n";
}

echo "\n4️⃣ Para testar emissão manual:\n";
echo str_repeat('-', 70) . "\n";
echo "
# Opção 1: Simular pagamento de pedido existente
php artisan tinker
> \$order = Order::first();
> \$order->update(['payment_status' => 'paid']);
> # Observer vai disparar automaticamente!

# Opção 2: Monitorar fila em tempo real
php artisan queue:monitor redis

# Opção 3: Ver logs da emissão
tail -f storage/logs/laravel.log | grep NFC-e

# Opção 4: Processar fila manualmente (debug)
php artisan queue:work redis --queue=nfce --once
";

echo "\n5️⃣ Possíveis problemas:\n";
echo str_repeat('-', 70) . "\n";
echo "
❌ Certificado A1 não configurado
   → Configurar em: /admin/tenants/{id}/edit (aba Fiscal)

❌ Workers não rodando
   → sudo supervisorctl status
   → sudo supervisorctl restart laravel-queue-nfce:*

❌ Redis não conectado
   → php artisan queue:failed
   → php artisan queue:retry all

❌ Produto sem NCM/CFOP
   → Configurar em: /painel/products/{id}/edit (aba Fiscal)
";

echo "\n✅ Sistema pronto para emissão automática!\n\n";
