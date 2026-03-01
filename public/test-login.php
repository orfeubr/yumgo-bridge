<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simular request
$request = Illuminate\Http\Request::create('https://marmitaria-gi.yumgo.com.br/test-login.php');
$request->headers->set('Host', 'marmitaria-gi.yumgo.com.br');

$response = $kernel->handle($request);

echo "<pre>";
echo "🔍 TESTE DE LOGIN - Marmitaria da Gi\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    // Inicializar tenancy
    $tenant = \App\Models\Tenant::find('marmitaria-gi');

    if (!$tenant) {
        echo "❌ Tenant não encontrado!\n";
        exit;
    }

    tenancy()->initialize($tenant);

    echo "✅ Tenant inicializado: {$tenant->name}\n";
    echo "   ID: {$tenant->id}\n\n";

    // Buscar usuário
    $user = \App\Models\User::where('email', 'admin@marmitaria-gi.com')->first();

    if (!$user) {
        echo "❌ Usuário não encontrado!\n";
        tenancy()->end();
        exit;
    }

    echo "✅ Usuário encontrado\n";
    echo "   Nome: {$user->name}\n";
    echo "   Email: {$user->email}\n";
    echo "   Ativo: " . ($user->active ? 'SIM' : 'NÃO') . "\n";
    echo "   Email Verificado: " . ($user->email_verified_at ? 'SIM' : 'NÃO') . "\n\n";

    // Testar senha
    $senhaCorreta = \Illuminate\Support\Facades\Hash::check('123456', $user->password);
    echo "🔑 Senha '123456' correta? " . ($senhaCorreta ? 'SIM ✅' : 'NÃO ❌') . "\n\n";

    // Testar canAccessPanel
    $panel = \Filament\Facades\Filament::getPanel('restaurant');
    $podeAcessar = $user->canAccessPanel($panel);
    echo "🚪 Pode acessar painel? " . ($podeAcessar ? 'SIM ✅' : 'NÃO ❌') . "\n\n";

    // Tentar fazer login
    echo "🔐 Tentando fazer login...\n";
    \Illuminate\Support\Facades\Auth::guard('web')->login($user);

    if (\Illuminate\Support\Facades\Auth::guard('web')->check()) {
        echo "✅ LOGIN REALIZADO COM SUCESSO!\n";
        echo "   Usuário logado: " . \Illuminate\Support\Facades\Auth::guard('web')->user()->email . "\n\n";

        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "✅ TUDO FUNCIONANDO!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        echo "Credenciais:\n";
        echo "URL: https://marmitaria-gi.yumgo.com.br/painel\n";
        echo "Email: admin@marmitaria-gi.com\n";
        echo "Senha: 123456\n";
    } else {
        echo "❌ ERRO AO FAZER LOGIN!\n";
    }

    tenancy()->end();

} catch (\Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "   Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "</pre>";
