<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Verificar pagamentos pendentes a cada 1 minuto
        $schedule->job(\App\Jobs\CheckPendingPayments::class)
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer();

        // Cancelar pedidos não pagos expirados (dias anteriores ou após horário de fechamento)
        // Roda a cada hora para processar todos os tenants
        $schedule->command('orders:cancel-expired')
            ->hourly()
            ->withoutOverlapping()
            ->onOneServer();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
