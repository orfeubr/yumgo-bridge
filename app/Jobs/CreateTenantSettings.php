<?php

namespace App\Jobs;

use App\Models\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\Tenant;

class CreateTenantSettings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Tenant $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(): void
    {
        $tenantId = $this->tenant->getTenantKey();

        // Inicializar tenancy para criar no schema correto
        tenancy()->initialize($this->tenant);

        // Verificar se já existe
        $existingSettings = Settings::first();

        if (!$existingSettings) {
            // Criar settings padrão
            $settings = new Settings();
            $settings->restaurant_name = $this->tenant->name;
            $settings->email = $this->tenant->email ?? null;
            $settings->phone = null;
            $settings->address = $this->tenant->address ?? null;
            $settings->instagram = null;
            $settings->facebook = null;
            $settings->whatsapp = null;
            $settings->delivery_fee = 5.00;
            $settings->min_order_value = 20.00;
            $settings->accepts_pix = true;
            $settings->accepts_credit_card = true;
            $settings->accepts_debit_card = true;
            $settings->accepts_cash = true;
            $settings->is_open = true;

            // Horários padrão (11:00-14:00 e 18:00-23:00)
            $settings->opening_hours = [
                'monday' => ['open' => '11:00', 'close' => '23:00'],
                'tuesday' => ['open' => '11:00', 'close' => '23:00'],
                'wednesday' => ['open' => '11:00', 'close' => '23:00'],
                'thursday' => ['open' => '11:00', 'close' => '23:00'],
                'friday' => ['open' => '11:00', 'close' => '23:00'],
                'saturday' => ['open' => '11:00', 'close' => '23:00'],
                'sunday' => ['open' => '11:00', 'close' => '23:00'],
            ];

            $settings->save();

            \Log::info("✅ Settings criado para tenant: {$tenantId}");
        }

        tenancy()->end();
    }
}
