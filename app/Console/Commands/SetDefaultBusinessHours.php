<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class SetDefaultBusinessHours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:set-default-hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configura horários padrão (11h-23h) para todos os restaurantes que não têm horário configurado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Configurando horários padrão para restaurantes...');

        // Horário padrão: Segunda a Domingo, 11:00 - 23:00
        $defaultHours = [
            'monday' => ['open' => '11:00', 'close' => '23:00', 'closed' => false],
            'tuesday' => ['open' => '11:00', 'close' => '23:00', 'closed' => false],
            'wednesday' => ['open' => '11:00', 'close' => '23:00', 'closed' => false],
            'thursday' => ['open' => '11:00', 'close' => '23:00', 'closed' => false],
            'friday' => ['open' => '11:00', 'close' => '23:00', 'closed' => false],
            'saturday' => ['open' => '11:00', 'close' => '23:00', 'closed' => false],
            'sunday' => ['open' => '11:00', 'close' => '23:00', 'closed' => false],
        ];

        // Buscar restaurantes sem horário configurado
        $tenants = Tenant::whereNull('business_hours')->get();

        if ($tenants->isEmpty()) {
            $this->info('Nenhum restaurante precisa de configuração de horário.');
            return 0;
        }

        $bar = $this->output->createProgressBar($tenants->count());
        $bar->start();

        $updated = 0;
        foreach ($tenants as $tenant) {
            $tenant->update([
                'business_hours' => $defaultHours,
                'accepting_orders' => true,
            ]);
            $updated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("✅ {$updated} restaurante(s) configurado(s) com sucesso!");
        $this->info('Horário padrão: Segunda a Domingo, 11:00 - 23:00');

        return 0;
    }
}
