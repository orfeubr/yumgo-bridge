<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SyncTenantLogos extends Command
{
    protected $signature = 'tenants:sync-logos';
    protected $description = 'Sincroniza logos dos tenants para o storage central (marketplace)';

    public function handle()
    {
        $this->info('🔄 Sincronizando logos dos restaurantes...');
        $this->newLine();

        $tenants = Tenant::all();
        $synced = 0;
        $skipped = 0;

        foreach ($tenants as $tenant) {
            $this->info("📍 Processando: {$tenant->name} (ID: {$tenant->id})");

            // Caminho do storage do tenant
            $tenantStoragePath = storage_path("tenant{$tenant->id}/app/public/logos");

            if (!File::exists($tenantStoragePath)) {
                $this->warn("  ⚠️  Diretório de logos não existe");
                $skipped++;
                continue;
            }

            // Buscar o logo mais recente
            $logoFiles = File::files($tenantStoragePath);

            if (empty($logoFiles)) {
                $this->warn("  ⚠️  Nenhum logo encontrado");
                $skipped++;
                continue;
            }

            // Pegar o mais recente (último modificado)
            usort($logoFiles, function ($a, $b) {
                return File::lastModified($b) - File::lastModified($a);
            });

            $mostRecentLogo = $logoFiles[0];
            $filename = basename($mostRecentLogo);
            $extension = pathinfo($mostRecentLogo, PATHINFO_EXTENSION);

            // Novo nome: tenant-slug + extensão
            $newFilename = $tenant->id . '.' . $extension;
            $centralPath = 'tenants/logos/' . $newFilename;

            // Copiar para storage central
            $disk = Storage::disk('public');
            $disk->put($centralPath, File::get($mostRecentLogo));

            // Atualizar tabela central (usando query builder para evitar problemas com UUID)
            Tenant::where('id', $tenant->id)->update(['logo' => $centralPath]);

            $this->info("  ✅ Logo copiado: {$centralPath}");
            $synced++;
        }

        $this->newLine();
        $this->info("✅ Sincronização concluída!");
        $this->info("   📊 Sincronizados: {$synced}");
        $this->info("   ⏭️  Ignorados: {$skipped}");

        return Command::SUCCESS;
    }
}
