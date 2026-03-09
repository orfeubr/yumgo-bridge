<?php

namespace App\Console\Commands;

use App\Jobs\UpdateTenantStatsJob;
use Illuminate\Console\Command;

class UpdateTenantStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:update-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza estatísticas agregadas de todos os tenants (pedidos, avaliações, etc)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Atualizando estatísticas dos tenants...');

        // Dispatch job
        UpdateTenantStatsJob::dispatch();

        $this->info('✅ Job de atualização disparado! Verifique os logs para acompanhar o progresso.');

        return Command::SUCCESS;
    }
}
