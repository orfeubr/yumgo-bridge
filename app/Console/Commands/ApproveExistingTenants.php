<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class ApproveExistingTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:approve-existing
                            {--force : Aprovar sem confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aprova automaticamente restaurantes que estavam pendentes (útil após migração)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pending = Tenant::where('approval_status', 'pending_approval')
            ->where('status', 'active')
            ->get();

        if ($pending->isEmpty()) {
            $this->info('✅ Nenhum restaurante pendente encontrado.');
            return 0;
        }

        $this->info("Encontrados {$pending->count()} restaurantes pendentes de aprovação:");
        $this->newLine();

        foreach ($pending as $tenant) {
            $this->line("  • {$tenant->name} ({$tenant->slug})");
        }

        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Deseja aprovar todos esses restaurantes?', true)) {
                $this->warn('❌ Operação cancelada.');
                return 1;
            }
        }

        $updated = Tenant::where('approval_status', 'pending_approval')
            ->where('status', 'active')
            ->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]);

        $this->info("✅ {$updated} restaurantes aprovados com sucesso!");

        return 0;
    }
}
