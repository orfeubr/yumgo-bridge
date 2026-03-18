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

        // ⭐ Reimprimir pedidos pendentes (evento disparado mas Bridge não respondeu)
        // Roda a cada 5 minutos para detectar pedidos que ficaram "esquecidos"
        // ⭐ Retry de impressões pendentes - Roda a cada 1 minuto (detecção rápida)
        $schedule->command('orders:retry-pending-prints --minutes=1')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
