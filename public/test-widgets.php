<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste de Widgets</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>🔍 Teste de Widgets - Dashboard Admin</h1>

    <h2>1. Widgets Existem?</h2>
    <?php
    $widgets = [
        'App\Filament\Widgets\StatsOverviewWidget',
        'App\Filament\Widgets\LatestTenantsWidget',
        'App\Filament\Widgets\RevenueChart',
        'App\Filament\Widgets\SubscriptionDistributionChart'
    ];

    foreach ($widgets as $widget) {
        $exists = class_exists($widget);
        echo "<p class='" . ($exists ? "success" : "error") . "'>";
        echo ($exists ? "✅" : "❌") . " " . basename(str_replace('\\', '/', $widget));
        echo "</p>";
    }
    ?>

    <h2>2. Dados Existem?</h2>
    <?php
    $totalTenants = \App\Models\Tenant::count();
    $activeTenants = \App\Models\Tenant::where('status', 'active')->count();
    echo "<p class='info'>Total Tenants: <strong>$totalTenants</strong></p>";
    echo "<p class='info'>Tenants Ativos: <strong>$activeTenants</strong></p>";
    ?>

    <h2>3. Dashboard Page Existe?</h2>
    <?php
    $dashboardClass = 'App\Filament\Pages\Dashboard';
    $exists = class_exists($dashboardClass);
    echo "<p class='" . ($exists ? "success" : "error") . "'>";
    echo ($exists ? "✅" : "❌") . " Dashboard Page";
    echo "</p>";

    if ($exists) {
        $dashboard = new $dashboardClass();
        $widgets = $dashboard->getWidgets();
        echo "<p class='info'>Widgets no Dashboard: <strong>" . count($widgets) . "</strong></p>";
        foreach ($widgets as $widget) {
            echo "<p class='success'>  - " . basename(str_replace('\\', '/', $widget)) . "</p>";
        }
    }
    ?>

    <h2>4. Panel Admin Configurado?</h2>
    <?php
    try {
        $panel = \Filament\Facades\Filament::getPanel('admin');
        echo "<p class='success'>✅ Panel 'admin' encontrado</p>";

        $registeredWidgets = $panel->getWidgets();
        echo "<p class='info'>Widgets Registrados: <strong>" . count($registeredWidgets) . "</strong></p>";
    } catch (\Exception $e) {
        echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    }
    ?>

    <hr>
    <p><strong>Conclusão:</strong> Se todos estão ✅, o problema é de renderização/cache do browser!</p>
    <p><a href="/admin">Ir para Dashboard Admin</a></p>
</body>
</html>
