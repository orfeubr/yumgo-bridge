<?php

namespace App\Filament\Restaurant\Resources\SettingsResource\Pages;

use App\Filament\Restaurant\Resources\SettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSettings extends EditRecord
{
    protected static string $resource = SettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        \Log::info('🔍 EditSettings: mutateFormDataBeforeSave chamado', [
            'all_keys' => array_keys($data),
            'has_tenant_logo' => isset($data['tenant_logo']),
            'tenant_logo_value' => $data['tenant_logo'] ?? 'NOT_SET',
        ]);

        if (isset($data['tenant_logo'])) {
            $tenant = tenancy()->tenant;
            if ($tenant) {
                $oldLogo = $tenant->getRawOriginal('logo');
                $newLogo = $data['tenant_logo'];

                \Log::info('💾 Salvando logo no Tenant', [
                    'tenant_slug' => $tenant->slug,
                    'old_logo' => $oldLogo,
                    'new_logo' => $newLogo,
                ]);

                // Deletar arquivo antigo se necessário
                if ($oldLogo && $newLogo !== $oldLogo && $newLogo !== null) {
                    $oldPath = storage_path('app/public/' . $oldLogo);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                        \Log::info('🗑️ Arquivo antigo deletado', ['file' => $oldLogo]);
                    }
                }

                $tenant->logo = $newLogo;
                $tenant->save();

                \Log::info('✅ Logo salva com sucesso', ['logo' => $newLogo ?: 'NULL']);
            }

            unset($data['tenant_logo']);
        }

        return $data;
    }
}
