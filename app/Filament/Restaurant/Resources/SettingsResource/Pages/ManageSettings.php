<?php

namespace App\Filament\Restaurant\Resources\SettingsResource\Pages;

use App\Filament\Restaurant\Resources\SettingsResource;
use App\Models\Settings;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class ManageSettings extends EditRecord
{
    protected static string $resource = SettingsResource::class;

    protected static ?string $title = 'Configurações do Restaurante';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // ⭐ Carregar logo do marketplace do Tenant (tabela central)
        $tenant = tenancy()->tenant;
        if ($tenant && $tenant->logo) {
            $data['tenant_logo'] = $tenant->logo;
        }

        // Se não houver registro, retorna valores padrão
        if (empty($data)) {
            return array_merge([
                'primary_color' => '#EA1D2C',
                'secondary_color' => '#333333',
                'accent_color' => '#FFA500',
                'is_open_now' => true,
                'allow_delivery' => true,
                'allow_pickup' => true,
                'accept_pix' => true,
                'accept_credit_card' => true,
                'accept_cash' => true,
                'printer_type' => 'none',
                'paper_width' => 58,
                'print_copies' => 1,
                'preparation_time' => 30,
                'estimated_delivery_time' => 45,
                'require_customer_phone' => true,
                'notify_email_new_order' => true,
                'enable_reviews' => true,
                'enable_loyalty_program' => true,
                'enable_coupons' => true,
            ], $this->getDefaultBusinessHoursFields());
        }

        // Converter business_hours do formato do banco para os campos do form
        if (isset($data['business_hours']) && is_array($data['business_hours'])) {
            $data = array_merge($data, $this->convertBusinessHoursToFields($data['business_hours']));
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // ⭐ Salvar logo do marketplace no Tenant (tabela central)
        \Log::info('🔍 ManageSettings::mutateFormDataBeforeSave', [
            'has_tenant_logo' => isset($data['tenant_logo']),
            'tenant_logo_value' => $data['tenant_logo'] ?? 'N/A',
            'all_keys' => array_keys($data),
        ]);

        if (isset($data['tenant_logo'])) {
            $tenant = tenancy()->tenant;
            \Log::info('💾 Tentando salvar logo no Tenant', [
                'tenant_id' => $tenant?->id,
                'logo_path' => $data['tenant_logo'],
            ]);

            if ($tenant) {
                $tenant->logo = $data['tenant_logo'];
                $tenant->save();
                \Log::info('✅ Logo salvo com sucesso no Tenant');
            } else {
                \Log::warning('⚠️ Tenant não encontrado!');
            }
            // Remover do array (não pertence ao Settings)
            unset($data['tenant_logo']);
        } else {
            \Log::info('⚠️ Campo tenant_logo não veio no $data');
        }

        // Converter campos individuais de volta para business_hours
        $businessHours = $this->convertFieldsToBusinessHours($data);
        $data['business_hours'] = $businessHours;

        // Remover campos temporários
        $days = ['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'];
        foreach ($days as $day) {
            unset($data["business_hours_{$day}_enabled"]);
            unset($data["business_hours_{$day}_open"]);
            unset($data["business_hours_{$day}_close"]);
        }

        return $data;
    }

    protected function getDefaultBusinessHoursFields(): array
    {
        return [
            'business_hours_seg_enabled' => true,
            'business_hours_seg_open' => '18:00',
            'business_hours_seg_close' => '23:00',
            'business_hours_ter_enabled' => true,
            'business_hours_ter_open' => '18:00',
            'business_hours_ter_close' => '23:00',
            'business_hours_qua_enabled' => true,
            'business_hours_qua_open' => '18:00',
            'business_hours_qua_close' => '23:00',
            'business_hours_qui_enabled' => true,
            'business_hours_qui_open' => '18:00',
            'business_hours_qui_close' => '23:00',
            'business_hours_sex_enabled' => true,
            'business_hours_sex_open' => '18:00',
            'business_hours_sex_close' => '23:30',
            'business_hours_sab_enabled' => true,
            'business_hours_sab_open' => '18:00',
            'business_hours_sab_close' => '23:30',
            'business_hours_dom_enabled' => true,
            'business_hours_dom_open' => '18:00',
            'business_hours_dom_close' => '23:00',
        ];
    }

    protected function convertBusinessHoursToFields(array $businessHours): array
    {
        $dayMap = [
            'Segunda-feira' => 'seg',
            'Terça-feira' => 'ter',
            'Quarta-feira' => 'qua',
            'Quinta-feira' => 'qui',
            'Sexta-feira' => 'sex',
            'Sábado' => 'sab',
            'Domingo' => 'dom',
        ];

        $fields = [];

        foreach ($dayMap as $dayName => $dayCode) {
            $hours = $businessHours[$dayName] ?? null;

            if (!$hours || $hours === 'Fechado') {
                $fields["business_hours_{$dayCode}_enabled"] = false;
                $fields["business_hours_{$dayCode}_open"] = '18:00';
                $fields["business_hours_{$dayCode}_close"] = '23:00';
            } else {
                // Formato: "18:00 - 23:00"
                if (str_contains($hours, ' - ')) {
                    [$open, $close] = explode(' - ', $hours);
                    $fields["business_hours_{$dayCode}_enabled"] = true;
                    $fields["business_hours_{$dayCode}_open"] = trim($open);
                    $fields["business_hours_{$dayCode}_close"] = trim($close);
                }
            }
        }

        return $fields;
    }

    protected function convertFieldsToBusinessHours(array $data): array
    {
        $dayMap = [
            'seg' => 'Segunda-feira',
            'ter' => 'Terça-feira',
            'qua' => 'Quarta-feira',
            'qui' => 'Quinta-feira',
            'sex' => 'Sexta-feira',
            'sab' => 'Sábado',
            'dom' => 'Domingo',
        ];

        $businessHours = [];

        foreach ($dayMap as $dayCode => $dayName) {
            $enabled = $data["business_hours_{$dayCode}_enabled"] ?? false;

            if ($enabled) {
                $open = $data["business_hours_{$dayCode}_open"] ?? '18:00';
                $close = $data["business_hours_{$dayCode}_close"] ?? '23:00';
                $businessHours[$dayName] = "{$open} - {$close}";
            } else {
                $businessHours[$dayName] = 'Fechado';
            }
        }

        return $businessHours;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // ⭐ PRIMEIRO: Salvar logo do marketplace no Tenant
        \Log::info('🔍 handleRecordUpdate called', [
            'data_keys' => array_keys($data),
            'has_tenant_logo' => array_key_exists('tenant_logo', $data),
        ]);

        if (array_key_exists('tenant_logo', $data) && $data['tenant_logo']) {
            $tenant = tenancy()->tenant;
            if ($tenant) {
                \Log::info('💾 Salvando logo no Tenant', [
                    'tenant_id' => $tenant->id,
                    'logo' => $data['tenant_logo'],
                ]);

                $tenant->logo = $data['tenant_logo'];
                $tenant->save();

                \Log::info('✅ Logo salvo no Tenant');
            }

            // Remover do array para não tentar salvar no Settings
            unset($data['tenant_logo']);
        }

        // DEPOIS: Salvar o resto no Settings e retornar
        $record->update($data);

        return $record;
    }

    public function mount(int | string $record = null): void
    {
        // Sempre usa o primeiro registro ou cria um novo
        $settings = Settings::current();

        // Passa o ID do registro para o método mount do pai
        parent::mount($settings->id);

        // Processar ações de token DEPOIS do mount
        $this->processTokenActions();
    }

    protected function processTokenActions(): void
    {
        $user = auth()->user();

        // Verificar se é uma solicitação de geração de token
        if (request()->has('generateToken')) {
            try {
                // Revogar tokens existentes do bridge primeiro
                $user->tokens()->where('name', 'bridge-app')->delete();

                // Criar novo token com validade de 1 ano
                $token = $user->createToken('bridge-app', ['*'], now()->addYear())->plainTextToken;

                // Mostrar notificação com o token (só será exibido uma vez)
                Notification::make()
                    ->title('🔑 Token Gerado com Sucesso!')
                    ->success()
                    ->body("**IMPORTANTE:** Copie este token AGORA (ele só será exibido uma vez):\n\n`{$token}`")
                    ->persistent()
                    ->duration(null) // Não fecha automaticamente
                    ->send();
            } catch (\Exception $e) {
                // ❌ Tabela personal_access_tokens não existe no schema tenant
                Notification::make()
                    ->title('❌ Erro ao Gerar Token')
                    ->danger()
                    ->body('A tabela de tokens não está configurada neste restaurante. Entre em contato com o suporte.')
                    ->send();
            }

            // Redirecionar para remover o parâmetro da URL (usando JS)
            $this->js("window.history.replaceState({}, '', window.location.pathname)");
            return;
        }

        // Verificar se é uma solicitação de revogação de token
        if (request()->has('revokeToken')) {
            try {
                $deletedCount = $user->tokens()->where('name', 'bridge-app')->delete();

                Notification::make()
                    ->title('🗑️ Token Revogado')
                    ->success()
                    ->body($deletedCount > 0
                        ? 'O token do YumGo Bridge foi revogado com sucesso. O aplicativo não poderá mais se conectar.'
                        : 'Nenhum token ativo foi encontrado.')
                    ->send();
            } catch (\Exception $e) {
                // ❌ Tabela personal_access_tokens não existe no schema tenant
                Notification::make()
                    ->title('⚠️ Nenhum Token Encontrado')
                    ->warning()
                    ->body('Não há tokens ativos para revogar.')
                    ->send();
            }

            // Redirecionar para remover o parâmetro da URL (usando JS)
            $this->js("window.history.replaceState({}, '', window.location.pathname)");
            return;
        }
    }
}
