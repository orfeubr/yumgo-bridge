<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        // Identidade Visual
        'logo',
        'banner',
        'primary_color',
        'secondary_color',
        'accent_color',

        // Informações do Estabelecimento
        'business_name',
        'trade_name',
        'cnpj',
        'state_registration',
        'municipal_registration',
        'segment',

        // Contato
        'phone',
        'whatsapp',
        'email',
        'address',
        'address_number',
        'address_complement',
        'neighborhood',
        'city',
        'state',
        'zipcode',
        'instagram',
        'facebook',
        'website',

        // Horários
        'business_hours',
        'is_open_now',
        'holiday_message',

        // Delivery
        'delivery_fee',
        'free_delivery_above',
        'minimum_order_value',
        'delivery_radius_km',
        'min_delivery_time',
        'max_delivery_time',
        'allow_pickup',
        'allow_delivery',
        'delivery_zones',
        'neighborhoods',
        'delivery_by_restaurant',
        'delivery_by_customer',
        'delivery_by_motoboy',

        // Pagamentos
        'accept_pix',
        'accept_credit_card',
        'accept_debit_card',
        'accept_cash',
        'accept_voucher',
        'accept_payment_on_delivery',
        'cash_on_delivery',
        'card_on_delivery',
        'cash_change_for',

        // Configuração de Pagamentos (Novo Sistema)
        'payment_pix_enabled',
        'payment_credit_card_enabled',
        'payment_debit_card_enabled',
        'payment_on_delivery_enabled',
        'payment_on_delivery_options',

        // Pagamento na Entrega - Opções Configuráveis
        'delivery_payment_methods',
        'accept_cash_on_delivery',
        'accept_card_on_delivery',
        'accept_vr_on_delivery',
        'accept_va_on_delivery',
        'accept_sodexo_on_delivery',
        'accept_alelo_on_delivery',
        'accept_ticket_on_delivery',
        'min_change_value',
        'delivery_payment_instructions',

        // Impressora
        'printer_type',
        'printer_ip',
        'printer_port',
        'printer_model',
        'paper_width',
        'auto_print_orders',
        'print_copies',

        // Pedidos
        'auto_accept_orders',
        'preparation_time',
        'require_customer_phone',
        'require_customer_cpf',
        'order_instructions',
        'order_number_prefix',
        'order_number_start',
        'order_number_current',
        'order_number_padding',
        'reset_order_number_daily',

        // NFCe
        'nfce_enabled',
        'nfce_environment',
        'nfce_certificate_path',
        'nfce_certificate_password',
        'nfce_series',
        'nfce_last_number',
        'nfce_csc',
        'nfce_csc_id',
        'nfce_tax_regime',
        'nfce_auto_emit',
        'nfce_additional_info',

        // Notificações
        'notify_email_new_order',
        'notify_sms_new_order',
        'notify_whatsapp_new_order',
        'notification_email',
        'notification_phone',

        // Políticas
        'terms_of_service',
        'privacy_policy',
        'return_policy',

        // SEO
        'meta_title',
        'meta_description',
        'meta_keywords',

        // Recursos
        'enable_reviews',
        'enable_loyalty_program',
        'enable_coupons',
        'enable_scheduled_orders',
    ];

    protected $casts = [
        'business_hours' => 'array',
        'delivery_zones' => 'array',
        'neighborhoods' => 'array',
        'is_open_now' => 'boolean',
        'delivery_fee' => 'decimal:2',
        'free_delivery_above' => 'decimal:2',
        'minimum_order_value' => 'decimal:2',
        'cash_change_for' => 'decimal:2',
        'allow_pickup' => 'boolean',
        'allow_delivery' => 'boolean',
        'delivery_by_restaurant' => 'boolean',
        'delivery_by_customer' => 'boolean',
        'delivery_by_motoboy' => 'boolean',
        'accept_pix' => 'boolean',
        'accept_credit_card' => 'boolean',
        'accept_debit_card' => 'boolean',
        'accept_cash' => 'boolean',
        'accept_voucher' => 'boolean',
        'accept_payment_on_delivery' => 'boolean',
        'cash_on_delivery' => 'boolean',
        'card_on_delivery' => 'boolean',
        'payment_pix_enabled' => 'boolean',
        'payment_credit_card_enabled' => 'boolean',
        'payment_debit_card_enabled' => 'boolean',
        'payment_on_delivery_enabled' => 'boolean',
        'payment_on_delivery_options' => 'array',
        'delivery_payment_methods' => 'array',
        'accept_cash_on_delivery' => 'boolean',
        'accept_card_on_delivery' => 'boolean',
        'accept_vr_on_delivery' => 'boolean',
        'accept_va_on_delivery' => 'boolean',
        'accept_sodexo_on_delivery' => 'boolean',
        'accept_alelo_on_delivery' => 'boolean',
        'accept_ticket_on_delivery' => 'boolean',
        'min_change_value' => 'decimal:2',
        'auto_print_orders' => 'boolean',
        'auto_accept_orders' => 'boolean',
        'require_customer_phone' => 'boolean',
        'require_customer_cpf' => 'boolean',
        'reset_order_number_daily' => 'boolean',
        'nfce_enabled' => 'boolean',
        'nfce_auto_emit' => 'boolean',
        'notify_email_new_order' => 'boolean',
        'notify_sms_new_order' => 'boolean',
        'notify_whatsapp_new_order' => 'boolean',
        'enable_reviews' => 'boolean',
        'enable_loyalty_program' => 'boolean',
        'enable_coupons' => 'boolean',
        'enable_scheduled_orders' => 'boolean',
    ];

    /**
     * Obter ou criar configurações do tenant atual
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'primary_color' => '#EA1D2C',
            'secondary_color' => '#333333',
            'accent_color' => '#FFA500',
            'business_hours' => static::defaultBusinessHours(),
        ]);
    }

    /**
     * Horários padrão de funcionamento
     */
    public static function defaultBusinessHours(): array
    {
        return [
            'Segunda-feira' => '18:00 - 23:00',
            'Terça-feira' => '18:00 - 23:00',
            'Quarta-feira' => '18:00 - 23:00',
            'Quinta-feira' => '18:00 - 23:00',
            'Sexta-feira' => '18:00 - 23:30',
            'Sábado' => '18:00 - 23:30',
            'Domingo' => '18:00 - 23:00',
        ];
    }

    /**
     * Mapear dia da semana em inglês para português
     */
    public static function getDayNameInPortuguese(string $englishDay): string
    {
        $days = [
            'monday' => 'Segunda-feira',
            'tuesday' => 'Terça-feira',
            'wednesday' => 'Quarta-feira',
            'thursday' => 'Quinta-feira',
            'friday' => 'Sexta-feira',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo',
        ];

        return $days[strtolower($englishDay)] ?? $englishDay;
    }

    /**
     * Verificar se está aberto agora
     */
    public function isOpenNow(): bool
    {
        if (!$this->is_open_now) {
            return false;
        }

        $now = now();
        $dayOfWeek = strtolower($now->format('l')); // monday, tuesday...
        $dayInPortuguese = static::getDayNameInPortuguese($dayOfWeek);
        $currentTime = $now->format('H:i');

        $hours = $this->business_hours[$dayInPortuguese] ?? null;

        if (!$hours) {
            return false;
        }

        // Formato: "18:00 - 23:00"
        if (is_string($hours) && str_contains($hours, ' - ')) {
            [$open, $close] = explode(' - ', $hours);
            return $currentTime >= trim($open) && $currentTime <= trim($close);
        }

        // Formato antigo (compatibilidade)
        if (is_array($hours)) {
            if (!($hours['enabled'] ?? false)) {
                return false;
            }
            return $currentTime >= $hours['open'] && $currentTime <= $hours['close'];
        }

        return false;
    }

    /**
     * Obter cor primária com fallback
     */
    public function getPrimaryColorAttribute($value): string
    {
        return $value ?? '#EA1D2C';
    }
}
