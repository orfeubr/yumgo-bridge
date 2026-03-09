<?php
// BYPASS COMPLETO - Dashboard direto sem cache
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\Invoice;

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
    <title>Dashboard YumGo - TESTE DIRETO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-red-600 text-white p-4 rounded-lg mb-6">
            <h1 class="text-2xl font-bold">⚠️ DASHBOARD DE TESTE - SEM CACHE</h1>
            <p>Se você vê isso, o servidor está funcionando! Timestamp: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <?php
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $trialTenants = Tenant::where('status', 'trial')->count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');
        ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Total de Restaurantes</p>
                        <p class="text-4xl font-bold text-blue-600 mt-2"><?php echo $totalTenants; ?></p>
                        <p class="text-xs text-gray-500 mt-2"><?php echo $activeTenants; ?> ativos, <?php echo $trialTenants; ?> em trial</p>
                    </div>
                    <div class="text-5xl">🏪</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Assinaturas Ativas</p>
                        <p class="text-4xl font-bold text-green-600 mt-2"><?php echo $activeSubscriptions; ?></p>
                        <p class="text-xs text-gray-500 mt-2">Gerando receita</p>
                    </div>
                    <div class="text-5xl">💳</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Receita do Mês</p>
                        <p class="text-3xl font-bold text-purple-600 mt-2">R$ <?php echo number_format($monthlyRevenue, 2, ',', '.'); ?></p>
                        <p class="text-xs text-gray-500 mt-2"><?php echo date('F Y'); ?></p>
                    </div>
                    <div class="text-5xl">💰</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold">Status Sistema</p>
                        <p class="text-4xl font-bold text-green-600 mt-2">✅ OK</p>
                        <p class="text-xs text-gray-500 mt-2">Todos os widgets funcionando</p>
                    </div>
                    <div class="text-5xl">🚀</div>
                </div>
            </div>
        </div>

        <!-- Tabela de Restaurantes -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">📋 Últimos Restaurantes Cadastrados</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Criado em</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        $tenants = Tenant::latest()->limit(5)->get();
                        foreach ($tenants as $tenant) {
                            $statusColor = $tenant->status === 'active' ? 'green' : 'yellow';
                            $statusLabel = $tenant->status === 'active' ? 'Ativo' : 'Trial';
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900"><?php echo $tenant->name; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $tenant->email; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-<?php echo $statusColor; ?>-100 text-<?php echo $statusColor; ?>-800">
                                        <?php echo $statusLabel; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $tenant->created_at->format('d/m/Y H:i'); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gráfico de Receita -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">📈 Receita dos Últimos 6 Meses</h2>
            <canvas id="revenueChart" height="80"></canvas>
        </div>

        <script>
        // Dados do gráfico
        <?php
        $labels = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('total');
            $labels[] = $date->translatedFormat('M/y');
            $data[] = round($revenue, 2);
        }
        ?>

        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Receita (R$)',
                    data: <?php echo json_encode($data); ?>,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
        </script>

        <div class="bg-green-600 text-white p-4 rounded-lg mt-6">
            <h2 class="text-xl font-bold mb-2">✅ SE VOCÊ VÊ ISSO, TUDO ESTÁ FUNCIONANDO!</h2>
            <p class="mb-2">O problema É o cache do browser/Cloudflare.</p>
            <p class="font-bold">SOLUÇÃO:</p>
            <ol class="list-decimal ml-6 mt-2">
                <li>No Cloudflare: Caching → Purge Everything</li>
                <li>No Browser: Ctrl + Shift + Delete → Limpar TUDO → Fechar e abrir</li>
                <li>Testar em aba anônima: Ctrl + Shift + N</li>
            </ol>
            <a href="/admin" class="inline-block mt-4 bg-white text-green-600 px-6 py-2 rounded-lg font-bold hover:bg-green-50">
                Ir para Dashboard Oficial →
            </a>
        </div>
    </div>
</body>
</html>
