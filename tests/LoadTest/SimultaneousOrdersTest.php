<?php

namespace Tests\LoadTest;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Tenant;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class SimultaneousOrdersTest extends TestCase
{
    protected Tenant $tenant;
    protected OrderService $orderService;

    public function setUp(): void
    {
        parent::setUp();

        // Inicializa tenant de teste
        $this->tenant = Tenant::where('slug', 'marmitaria-gi')->first();
        tenancy()->initialize($this->tenant);

        $this->orderService = app(OrderService::class);
    }

    /**
     * Simula 50 pedidos chegando simultaneamente
     */
    public function test_50_simultaneous_orders()
    {
        echo "\n🔥 TESTE DE CARGA: 50 PEDIDOS SIMULTÂNEOS\n";
        echo str_repeat("=", 60) . "\n\n";

        $startTime = microtime(true);
        $errors = [];
        $successes = 0;
        $metrics = [
            'db_queries' => 0,
            'redis_ops' => 0,
            'memory_peak' => 0,
            'response_times' => [],
        ];

        // Pega produtos disponíveis
        $products = Product::where('is_available', true)->take(10)->get();

        if ($products->count() === 0) {
            $this->fail('❌ Nenhum produto disponível para teste');
        }

        echo "📦 Produtos disponíveis: {$products->count()}\n";
        echo "👥 Gerando 50 clientes únicos...\n\n";

        // Gera 50 clientes únicos
        $customers = [];
        for ($i = 1; $i <= 50; $i++) {
            $customer = Customer::create([
                'name' => "Cliente Teste Load {$i}",
                'email' => "loadtest{$i}_" . time() . "@test.com",
                'phone' => sprintf('119%08d', $i),
                'cashback_balance' => rand(0, 50),
                'loyalty_tier' => 'bronze',
            ]);
            $customers[] = $customer;
        }

        echo "✅ 50 clientes criados\n";
        echo "⏱️  Iniciando processamento simultâneo...\n\n";

        // Resetar contadores
        DB::flushQueryLog();
        DB::enableQueryLog();

        // Processar 50 pedidos "simultaneamente" (em loop rápido)
        $processes = [];
        foreach ($customers as $index => $customer) {
            $orderStartTime = microtime(true);

            try {
                // Monta dados do pedido
                $orderData = [
                    'customer_id' => $customer->id,
                    'items' => [
                        [
                            'product_id' => $products->random()->id,
                            'quantity' => rand(1, 3),
                            'unit_price' => rand(20, 80),
                            'subtotal' => rand(20, 240),
                        ]
                    ],
                    'subtotal' => rand(50, 200),
                    'delivery_fee' => 5.00,
                    'payment_method' => 'pix',
                    'delivery_address' => [
                        'street' => 'Rua Teste',
                        'number' => (string)rand(1, 999),
                        'neighborhood' => 'Centro',
                        'city' => 'São Paulo',
                        'state' => 'SP',
                        'zipcode' => '01000-000',
                    ],
                    'use_cashback' => rand(0, 1) === 1,
                ];

                // Simula criação do pedido
                $order = $this->orderService->create($orderData);

                $orderEndTime = microtime(true);
                $responseTime = ($orderEndTime - $orderStartTime) * 1000; // ms
                $metrics['response_times'][] = $responseTime;

                $successes++;

                echo sprintf(
                    "✅ [%02d] Pedido #%d criado em %.2fms | Cliente: %s\n",
                    $index + 1,
                    $order->id,
                    $responseTime,
                    $customer->name
                );

            } catch (\Exception $e) {
                $errors[] = [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ];

                echo sprintf(
                    "❌ [%02d] ERRO: %s\n",
                    $index + 1,
                    $e->getMessage()
                );
            }

            // Simula processamento "quase" simultâneo (delay mínimo)
            usleep(10000); // 10ms entre requisições
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000; // ms

        // Coleta métricas de banco
        $queries = DB::getQueryLog();
        $metrics['db_queries'] = count($queries);
        $metrics['memory_peak'] = memory_get_peak_usage(true) / 1024 / 1024; // MB

        // Calcula estatísticas de tempo de resposta
        $avgResponseTime = array_sum($metrics['response_times']) / count($metrics['response_times']);
        $minResponseTime = min($metrics['response_times']);
        $maxResponseTime = max($metrics['response_times']);
        sort($metrics['response_times']);
        $p95ResponseTime = $metrics['response_times'][floor(count($metrics['response_times']) * 0.95)];

        // Relatório Final
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 RELATÓRIO FINAL\n";
        echo str_repeat("=", 60) . "\n\n";

        echo "⏱️  TEMPO TOTAL: " . number_format($totalTime, 2) . "ms\n";
        echo "✅ SUCESSOS: {$successes}/50\n";
        echo "❌ ERROS: " . count($errors) . "/50\n\n";

        echo "📈 MÉTRICAS DE PERFORMANCE:\n";
        echo "   ├─ Tempo médio por pedido: " . number_format($avgResponseTime, 2) . "ms\n";
        echo "   ├─ Tempo mínimo: " . number_format($minResponseTime, 2) . "ms\n";
        echo "   ├─ Tempo máximo: " . number_format($maxResponseTime, 2) . "ms\n";
        echo "   ├─ P95 (95% dos pedidos): " . number_format($p95ResponseTime, 2) . "ms\n";
        echo "   ├─ Queries SQL: {$metrics['db_queries']}\n";
        echo "   └─ Memória pico: " . number_format($metrics['memory_peak'], 2) . "MB\n\n";

        if (!empty($errors)) {
            echo "❌ ERROS ENCONTRADOS:\n";
            foreach ($errors as $error) {
                echo "   • Customer #{$error['customer_id']}: {$error['error']}\n";
            }
            echo "\n";
        }

        // Limpa dados de teste
        echo "🧹 Limpando dados de teste...\n";
        foreach ($customers as $customer) {
            $customer->orders()->delete();
            $customer->delete();
        }
        echo "✅ Dados limpos\n\n";

        // Asserts
        $this->assertGreaterThanOrEqual(45, $successes, 'Pelo menos 90% dos pedidos devem ser processados com sucesso');
        $this->assertLessThan(500, $avgResponseTime, 'Tempo médio deve ser menor que 500ms');
        $this->assertLessThan(100, $metrics['memory_peak'], 'Memória pico deve ser menor que 100MB');
    }
}
