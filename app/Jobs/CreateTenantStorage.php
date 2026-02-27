<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Stancl\Tenancy\Contracts\Tenant;

class CreateTenantStorage implements ShouldQueue
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
        $storagePath = storage_path("tenant{$tenantId}");

        // Criar diretórios necessários
        $directories = [
            "{$storagePath}/framework/cache/data",
            "{$storagePath}/framework/sessions",
            "{$storagePath}/framework/views",
            "{$storagePath}/logs",
            "{$storagePath}/app/public",
        ];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0775, true);
                \Log::info("✅ Diretório criado: {$directory}");
            }
        }

        // Criar .gitignore
        $gitignore = "{$storagePath}/.gitignore";
        if (!File::exists($gitignore)) {
            File::put($gitignore, "*\n!.gitignore\n");
        }

        \Log::info("✅ Storage criado para tenant: {$tenantId}");
    }
}
