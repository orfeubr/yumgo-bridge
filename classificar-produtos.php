<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n🔧 Classificando produtos com NCM/CFOP corretos...\n\n";

$tenant = \App\Models\Tenant::where('slug', 'marmitariadagi')->first();

if (!$tenant) {
    echo "❌ Tenant Marmitaria da Gi não encontrado\n";
    exit(1);
}

echo "Tenant: {$tenant->name}\n\n";
tenancy()->initialize($tenant);

// Mapeamento de classificação fiscal
$classificacoes = [
    // Alimentos preparados (carnes, marmitas, pratos)
    'alimentos' => [
        'ncm' => '19059090',
        'cfop' => '5405',
        'cest' => null,
        'keywords' => ['marmita', 'filé', 'feijoada', 'parmegiana', 'linguiça', 'frango', 'carne', 'peixe', 'porção', 'farofa', 'arroz', 'pudim'],
    ],
    // Bebidas não alcoólicas
    'bebidas' => [
        'ncm' => '22029900',
        'cfop' => '5405',
        'cest' => '0300700',
        'keywords' => ['coca', 'guaraná', 'refrigerante', 'suco'],
    ],
    // Águas
    'aguas' => [
        'ncm' => '22021000',
        'cfop' => '5405',
        'cest' => '0300100',
        'keywords' => ['água', 'agua'],
    ],
];

$produtos = \App\Models\Product::whereNull('ncm')->get();
$atualizados = 0;

foreach ($produtos as $produto) {
    $nameLower = mb_strtolower($produto->name);
    $classificado = false;

    // Tentar encontrar a categoria correta
    foreach ($classificacoes as $tipo => $dados) {
        foreach ($dados['keywords'] as $keyword) {
            if (str_contains($nameLower, $keyword)) {
                $produto->update([
                    'ncm' => $dados['ncm'],
                    'cfop' => $dados['cfop'],
                    'cest' => $dados['cest'],
                ]);

                echo sprintf(
                    "✅ %-35s → NCM: %s (%s)\n",
                    substr($produto->name, 0, 33),
                    $dados['ncm'],
                    $tipo
                );

                $atualizados++;
                $classificado = true;
                break 2;
            }
        }
    }

    if (!$classificado) {
        // Fallback: alimentos preparados
        $produto->update([
            'ncm' => '19059090',
            'cfop' => '5405',
            'cest' => null,
        ]);

        echo sprintf(
            "⚠️  %-35s → NCM: %s (fallback: alimentos)\n",
            substr($produto->name, 0, 33),
            '19059090'
        );

        $atualizados++;
    }
}

echo "\n📊 Resumo:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Total de produtos classificados: $atualizados\n";
echo "✅ Todos os produtos agora têm NCM/CFOP!\n";
echo "\n🎯 Pronto para emitir NFC-e!\n\n";
