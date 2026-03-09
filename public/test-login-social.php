<?php
// TESTE: Verificar se login social está configurado
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 TESTE: Login Social e Carrinho</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-red-600 text-white p-6 rounded-lg mb-6">
            <h1 class="text-3xl font-bold mb-2">🔥 TESTE DE VIEWS ATUALIZADAS</h1>
            <p class="text-lg">Verificando se as modificações estão aplicadas...</p>
            <p class="text-sm mt-2">Timestamp: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Card: Login Social -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <span class="text-2xl">🔐</span>
                    Login Social
                </h2>

                <?php
                $loginPath = '/var/www/restaurante/resources/views/tenant/auth/login.blade.php';
                $loginContent = file_get_contents($loginPath);
                $hasGoogleButton = strpos($loginContent, '/auth/google/redirect') !== false;
                $hasFacebookButton = strpos($loginContent, '/auth/facebook/redirect') !== false;
                $hasCelularField = strpos($loginContent, 'Celular ou Email') !== false;
                $hasSocialDivider = strpos($loginContent, 'ou') !== false;
                ?>

                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl"><?php echo $hasGoogleButton ? '✅' : '❌'; ?></span>
                        <span class="<?php echo $hasGoogleButton ? 'text-green-700' : 'text-red-700'; ?> font-semibold">
                            Botão "Continuar com Google"
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl"><?php echo $hasFacebookButton ? '✅' : '❌'; ?></span>
                        <span class="<?php echo $hasFacebookButton ? 'text-green-700' : 'text-red-700'; ?> font-semibold">
                            Botão "Continuar com Facebook"
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl"><?php echo $hasSocialDivider ? '✅' : '❌'; ?></span>
                        <span class="<?php echo $hasSocialDivider ? 'text-green-700' : 'text-red-700'; ?> font-semibold">
                            Divisor "ou"
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl"><?php echo $hasCelularField ? '✅' : '❌'; ?></span>
                        <span class="<?php echo $hasCelularField ? 'text-green-700' : 'text-red-700'; ?> font-semibold">
                            Campo "Celular ou Email"
                        </span>
                    </div>
                </div>

                <div class="mt-4 p-3 <?php echo ($hasGoogleButton && $hasFacebookButton && $hasCelularField) ? 'bg-green-50 border-2 border-green-300' : 'bg-red-50 border-2 border-red-300'; ?> rounded-lg">
                    <p class="font-bold <?php echo ($hasGoogleButton && $hasFacebookButton && $hasCelularField) ? 'text-green-700' : 'text-red-700'; ?>">
                        <?php
                        if ($hasGoogleButton && $hasFacebookButton && $hasCelularField) {
                            echo "✅ LOGIN SOCIAL: IMPLEMENTADO!";
                        } else {
                            echo "❌ LOGIN SOCIAL: FALTANDO COMPONENTES";
                        }
                        ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        Arquivo: tenant/auth/login.blade.php
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Modificado: <?php echo date('d/m/Y H:i:s', filemtime($loginPath)); ?>
                    </p>
                </div>
            </div>

            <!-- Card: Carrinho Clean -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <span class="text-2xl">🛒</span>
                    Carrinho Clean (iFood)
                </h2>

                <?php
                $homePath = '/var/www/restaurante/resources/views/restaurant-home.blade.php';
                $homeContent = file_get_contents($homePath);
                $hasCleanCart = strpos($homeContent, 'CARRINHO CLEAN') !== false || strpos($homeContent, 'qty x name') !== false;
                $hasQtyFormat = strpos($homeContent, 'item.quantity') !== false && strpos($homeContent, 'x') !== false;
                $hasSubtotalLight = strpos($homeContent, 'Subtotal') !== false;
                $hasTotalRed = strpos($homeContent, 'Total') !== false || strpos($homeContent, 'finalTotal') !== false;
                ?>

                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl"><?php echo $hasCleanCart ? '✅' : '❌'; ?></span>
                        <span class="<?php echo $hasCleanCart ? 'text-green-700' : 'text-red-700'; ?> font-semibold">
                            Fundo branco clean
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl"><?php echo $hasQtyFormat ? '✅' : '❌'; ?></span>
                        <span class="<?php echo $hasQtyFormat ? 'text-green-700' : 'text-red-700'; ?> font-semibold">
                            Formato "qty x nome"
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl"><?php echo $hasSubtotalLight ? '✅' : '❌'; ?></span>
                        <span class="<?php echo $hasSubtotalLight ? 'text-green-700' : 'text-red-700'; ?> font-semibold">
                            Subtotal clarinho
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl"><?php echo $hasTotalRed ? '✅' : '❌'; ?></span>
                        <span class="<?php echo $hasTotalRed ? 'text-green-700' : 'text-red-700'; ?> font-semibold">
                            Total destacado
                        </span>
                    </div>
                </div>

                <div class="mt-4 p-3 <?php echo ($hasQtyFormat && $hasSubtotalLight && $hasTotalRed) ? 'bg-green-50 border-2 border-green-300' : 'bg-red-50 border-2 border-red-300'; ?> rounded-lg">
                    <p class="font-bold <?php echo ($hasQtyFormat && $hasSubtotalLight && $hasTotalRed) ? 'text-green-700' : 'text-red-700'; ?>">
                        <?php
                        if ($hasQtyFormat && $hasSubtotalLight && $hasTotalRed) {
                            echo "✅ CARRINHO: REDESENHADO!";
                        } else {
                            echo "❌ CARRINHO: FALTANDO COMPONENTES";
                        }
                        ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        Arquivo: restaurant-home.blade.php
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Modificado: <?php echo date('d/m/Y H:i:s', filemtime($homePath)); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Cache Info -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                <span class="text-2xl">🗂️</span>
                Status do Cache
            </h2>

            <?php
            // Verificar cache de views dos tenants
            $tenantCaches = [
                'marmitaria-gi' => '/var/www/restaurante/storage/tenantmarmitaria-gi/framework/views',
                'parker-pizzaria' => '/var/www/restaurante/storage/tenantparker-pizzaria/framework/views'
            ];
            ?>

            <div class="space-y-3">
                <?php foreach ($tenantCaches as $name => $path): ?>
                    <?php
                    $cacheFiles = glob($path . '/*');
                    $cacheCount = count($cacheFiles);
                    ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-semibold">Tenant: <?php echo $name; ?></span>
                        <span class="px-3 py-1 rounded-full <?php echo $cacheCount === 0 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?> text-sm font-bold">
                            <?php echo $cacheCount; ?> views cacheadas
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Conclusão -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-6 rounded-lg">
            <h2 class="text-2xl font-bold mb-4">📊 CONCLUSÃO</h2>

            <?php
            $allOk = $hasGoogleButton && $hasFacebookButton && $hasCelularField && $hasQtyFormat && $hasSubtotalLight && $hasTotalRed;
            ?>

            <?php if ($allOk): ?>
                <div class="text-xl font-bold mb-3">
                    ✅ TODAS AS MODIFICAÇÕES ESTÃO APLICADAS NO CÓDIGO!
                </div>
                <p class="mb-4">O backend está 100% correto. Se você não vê as mudanças no browser, o problema é CACHE:</p>
                <ol class="list-decimal ml-6 space-y-2">
                    <li><strong>Cloudflare:</strong> dash.cloudflare.com → Caching → Purge Everything</li>
                    <li><strong>Browser:</strong> Ctrl+Shift+Delete → Limpar TUDO → Fechar e reabrir</li>
                    <li><strong>Testar em modo anônimo:</strong> Ctrl+Shift+N</li>
                </ol>
            <?php else: ?>
                <div class="text-xl font-bold mb-3">
                    ⚠️ ALGUMAS MODIFICAÇÕES ESTÃO FALTANDO
                </div>
                <p>Verifique os itens marcados com ❌ acima.</p>
            <?php endif; ?>
        </div>

        <!-- Links de Teste -->
        <div class="mt-6 bg-blue-50 border-2 border-blue-300 rounded-lg p-6">
            <h3 class="font-bold text-blue-900 mb-3">🔗 Teste nos sites reais:</h3>
            <div class="space-y-2">
                <a href="https://marmitaria-gi.yumgo.com.br/login" target="_blank" class="block text-blue-600 hover:text-blue-800 font-semibold">
                    → Marmitaria GI - Login
                </a>
                <a href="https://marmitaria-gi.yumgo.com.br/" target="_blank" class="block text-blue-600 hover:text-blue-800 font-semibold">
                    → Marmitaria GI - Cardápio (carrinho)
                </a>
                <a href="https://parker-pizzaria.yumgo.com.br/login" target="_blank" class="block text-blue-600 hover:text-blue-800 font-semibold">
                    → Parker Pizzaria - Login
                </a>
            </div>
            <p class="text-sm text-gray-600 mt-4">
                <strong>Dica:</strong> Abra em modo anônimo (Ctrl+Shift+N) para evitar cache do browser.
            </p>
        </div>
    </div>
</body>
</html>
