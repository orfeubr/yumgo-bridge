<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestNfceEmission extends Command
{
    protected $signature = 'nfce:test {tenant?}';
    protected $description = 'Testa emissão de NFC-e em ambiente de homologação';

    public function handle()
    {
        $this->info('🧪 TESTE DE EMISSÃO NFC-e - Ambiente de Homologação');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        // 1. Selecionar tenant
        $tenantSlug = $this->argument('tenant');

        if (!$tenantSlug) {
            $tenants = Tenant::active()->pluck('name', 'slug')->toArray();
            if (empty($tenants)) {
                $this->error('❌ Nenhum tenant ativo encontrado!');
                return 1;
            }
            $tenantSlug = $this->choice('Selecione o restaurante:', $tenants);
        }

        $tenant = Tenant::where('slug', $tenantSlug)->first();
        if (!$tenant) {
            $this->error("❌ Tenant '{$tenantSlug}' não encontrado!");
            return 1;
        }

        tenancy()->initialize($tenant);
        $this->info("✅ Tenant: {$tenant->name}");
        $this->newLine();

        // 2. Verificar configurações
        $this->info('📋 VERIFICANDO CONFIGURAÇÕES FISCAIS:');
        $this->line('─────────────────────────────────────');

        $checks = [
            'CNPJ' => !empty($tenant->cpf_cnpj),
            'Certificado A1' => !empty($tenant->certificate_a1),
            'CSC ID' => !empty($tenant->csc_id),
            'CSC Token' => !empty($tenant->csc_token),
            'Série NFC-e' => !empty($tenant->nfce_serie),
            'Número NFC-e' => !empty($tenant->nfce_numero),
            'Ambiente' => $tenant->nfce_environment === 'homologacao',
        ];

        foreach ($checks as $item => $status) {
            $icon = $status ? '✅' : '❌';
            $this->line("{$icon} {$item}");
        }
        $this->newLine();

        // Contar problemas
        $problems = count(array_filter($checks, fn($v) => !$v));

        if ($problems > 0) {
            $this->warn("⚠️  {$problems} configuração(ões) faltando!");
            $this->newLine();

            if (!$this->confirm('Deseja continuar mesmo assim? (pode falhar)', true)) {
                return 0;
            }
        }

        // 3. Criar pedido de teste
        $this->info('📦 CRIANDO PEDIDO DE TESTE:');
        $this->line('─────────────────────────────────────');

        try {
            // Criar customer fake
            $customer = \App\Models\Customer::firstOrCreate(
                ['email' => 'teste@nfce.com'],
                [
                    'name' => 'Cliente Teste NFC-e',
                    'phone' => '11999999999',
                    'cpf' => '00000000000',
                    'password' => bcrypt('teste123'),
                ]
            );
            $this->line("✅ Customer: {$customer->name}");

            // Criar pedido fake
            $order = \App\Models\Order::create([
                'customer_id' => $customer->id,
                'order_number' => 'TEST-' . now()->format('YmdHis'),
                'status' => 'pending',
                'payment_method' => 'pix',
                'payment_status' => 'paid', // ← IMPORTANTE: Só emite se PAID
                'subtotal' => 50.00,
                'delivery_fee' => 5.00,
                'discount' => 0,
                'total' => 55.00,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_cpf' => $customer->cpf,
                'delivery_address' => 'Rua Teste, 123',
                'delivery_neighborhood' => 'Centro',
                'delivery_city' => 'São Paulo',
                'delivery_state' => 'SP',
                'delivery_zipcode' => '01001000',
            ]);

            // Adicionar items
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_name' => 'Marmitex Teste',
                'quantity' => 1,
                'unit_price' => 50.00,
                'subtotal' => 50.00,
                'ncm' => '19059090', // Código NCM genérico para alimentos
                'cfop' => '5102', // Venda dentro do estado
            ]);

            $this->line("✅ Pedido: #{$order->order_number}");
            $this->line("✅ Total: R$ " . number_format($order->total, 2, ',', '.'));
            $this->newLine();

        } catch (\Exception $e) {
            $this->error("❌ Erro ao criar pedido: {$e->getMessage()}");
            return 1;
        }

        // 4. Tentar emitir NFC-e
        $this->info('📄 EMITINDO NFC-e (isso pode levar alguns segundos...):');
        $this->line('─────────────────────────────────────');

        if (!class_exists(\App\Services\SefazService::class)) {
            $this->error('❌ SefazService não encontrado!');
            $this->warn('Execute: composer require nfephp-org/sped-nfe');
            return 1;
        }

        try {
            $sefazService = app(\App\Services\SefazService::class);
            $result = $sefazService->emitirNFCe($order);

            if ($result['success']) {
                $this->info('✅ NFC-e EMITIDA COM SUCESSO!');
                $this->newLine();
                $this->line("Chave de Acesso: {$result['nfce_key']}");
                $this->line("Número: {$result['numero']}");
                $this->line("Série: {$result['serie']}");
                $this->newLine();

                if (isset($result['xml_path'])) {
                    $this->line("📁 XML salvo em: {$result['xml_path']}");
                }
            } else {
                $this->error('❌ FALHA NA EMISSÃO!');
                $this->newLine();
                $this->error("Erro: {$result['message']}");

                if (isset($result['details'])) {
                    $this->line("\nDetalhes:");
                    $this->line(print_r($result['details'], true));
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ EXCEÇÃO DURANTE EMISSÃO!');
            $this->newLine();
            $this->error("Erro: {$e->getMessage()}");
            $this->line("\nStack trace:");
            $this->line($e->getTraceAsString());
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════');
        $this->info('🏁 TESTE CONCLUÍDO');

        return 0;
    }
}
