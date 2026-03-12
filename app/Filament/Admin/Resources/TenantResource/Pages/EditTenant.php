<?php

namespace App\Filament\Admin\Resources\TenantResource\Pages;

use App\Filament\Admin\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 🔍 DEBUG: Ver o que está vindo do formulário
        Log::info('=== SALVANDO TENANT ===', [
            'cuisine_types_recebido' => $data['cuisine_types'] ?? 'NULL',
            'tipo' => gettype($data['cuisine_types'] ?? null),
            'logo_recebido' => $data['logo'] ?? 'NULL',
        ]);

        return $data;
    }

    /**
     * Hook executado DEPOIS de salvar o tenant
     *
     * Força a sincronização da logo para o settings do tenant,
     * pois o Observer nem sempre é disparado corretamente quando
     * a logo é deletada pelo Filament FileUpload.
     */
    protected function afterSave(): void
    {
        $tenant = $this->getRecord();

        // ✅ Forçar sincronização da logo (incluindo quando é NULL/deletada)
        try {
            tenancy()->initialize($tenant);

            $settings = \App\Models\Settings::first();

            if ($settings) {
                $settings->update(['logo' => $tenant->logo]);

                Log::info("✅ Logo sincronizada via hook afterSave", [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'logo_path' => $tenant->logo ?? 'NULL (deletada)',
                ]);
            }

            tenancy()->end();
        } catch (\Exception $e) {
            Log::error("❌ Erro ao sincronizar logo no afterSave: " . $e->getMessage());
            tenancy()->end();
        }
    }
}
