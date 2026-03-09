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
            $settings->trade_name = $this->tenant->name; // ✅ Nome Fantasia
            $settings->email = $this->tenant->email ?? null;
            $settings->phone = $this->tenant->phone ?? null;
            $settings->address = null;
            $settings->instagram = null;
            $settings->facebook = null;
            $settings->whatsapp = $this->tenant->phone ?? null;
            $settings->delivery_fee = 5.00;
            $settings->minimum_order_value = 20.00; // ✅ Campo correto
            $settings->accept_pix = true; // ✅ Sem S
            $settings->accept_credit_card = true; // ✅ Sem S
            $settings->accept_debit_card = true; // ✅ Sem S
            $settings->accept_cash = true; // ✅ Sem S
            $settings->is_open_now = true; // ✅ is_open_now

            // Horários padrão (11:00-23:00)
            $settings->business_hours = [ // ✅ business_hours
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
