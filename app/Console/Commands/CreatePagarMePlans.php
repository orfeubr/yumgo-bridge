<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Services\PagarMeService;
use Illuminate\Console\Command;

class CreatePagarMePlans extends Command
{
    protected $signature = 'pagarme:create-plans';
    protected $description = 'Cria planos no Pagar.me e atualiza pagarme_plan_id no banco';

    public function handle()
    {
        $this->info('🚀 Criando planos no Pagar.me...');
        $this->newLine();

        $pagarMeService = new PagarMeService();
        $plans = Plan::whereNull('pagarme_plan_id')->get();

        if ($plans->isEmpty()) {
            $this->info('✅ Todos os planos já têm pagarme_plan_id configurado!');
            return Command::SUCCESS;
        }

        foreach ($plans as $plan) {
            $this->info("📦 Criando plano: {$plan->name} (R$ {$plan->price_monthly})");

            try {
                $result = $pagarMeService->createPlan($plan);

                if ($result && isset($result['id'])) {
                    $plan->update(['pagarme_plan_id' => $result['id']]);
                    $this->info("   ✅ Plano criado: {$result['id']}");
                } else {
                    $this->error("   ❌ Falha ao criar plano {$plan->name}");
                }

                $this->newLine();

            } catch (\Exception $e) {
                $this->error("   ❌ Erro: {$e->getMessage()}");
                $this->newLine();
            }
        }

        $this->info('🎉 Processo concluído!');
        return Command::SUCCESS;
    }
}
