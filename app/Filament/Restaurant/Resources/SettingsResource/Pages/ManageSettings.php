<?php

namespace App\Filament\Restaurant\Resources\SettingsResource\Pages;

use App\Filament\Restaurant\Resources\SettingsResource;
use App\Models\Settings;
use Filament\Actions;
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

    public function mount(int | string $record = null): void
    {
        // Sempre usa o primeiro registro ou cria um novo
        $settings = Settings::current();

        // Passa o ID do registro para o método mount do pai
        parent::mount($settings->id);
    }
}
