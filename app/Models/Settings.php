<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'logo', 'banner', 'primary_color', 'secondary_color', 'accent_color',
        'phone', 'whatsapp', 'email', 'address',  'instagram', 'facebook',
        'is_open_now', 'holiday_message', 'business_hours',
        'allow_delivery', 'allow_pickup', 'minimum_order_value',
        'payment_pix_enabled', 'payment_credit_card_enabled', 'payment_debit_card_enabled',
        'payment_on_delivery_enabled', 'accept_cash_on_delivery', 'accept_card_on_delivery',
        'accept_vr_on_delivery', 'accept_va_on_delivery', 'accept_sodexo_on_delivery',
        'accept_alelo_on_delivery', 'accept_ticket_on_delivery', 'min_change_value',
        'delivery_payment_instructions', 'auto_accept_orders', 'preparation_time',
        'require_customer_phone', 'require_customer_cpf', 'order_instructions',
        'notify_email_new_order', 'notification_email', 'notify_sms_new_order',
        'notify_whatsapp_new_order', 'notification_phone', 'enable_reviews',
        'enable_loyalty_program', 'enable_coupons', 'enable_scheduled_orders',
        'terms_of_service', 'privacy_policy', 'return_policy',
        'tenant_logo', // ⭐ Campo temporário para aceitar upload (removido via hook)
    ];

    protected $casts = [
        'business_hours' => 'array',
        'is_open_now' => 'boolean',
        'allow_delivery' => 'boolean',
        'allow_pickup' => 'boolean',
        'payment_pix_enabled' => 'boolean',
        'payment_credit_card_enabled' => 'boolean',
        'payment_debit_card_enabled' => 'boolean',
        'payment_on_delivery_enabled' => 'boolean',
        'accept_cash_on_delivery' => 'boolean',
        'accept_card_on_delivery' => 'boolean',
        'accept_vr_on_delivery' => 'boolean',
        'accept_va_on_delivery' => 'boolean',
        'accept_sodexo_on_delivery' => 'boolean',
        'accept_alelo_on_delivery' => 'boolean',
        'accept_ticket_on_delivery' => 'boolean',
        'auto_accept_orders' => 'boolean',
        'require_customer_phone' => 'boolean',
        'require_customer_cpf' => 'boolean',
        'notify_email_new_order' => 'boolean',
        'notify_sms_new_order' => 'boolean',
        'notify_whatsapp_new_order' => 'boolean',
        'enable_reviews' => 'boolean',
        'enable_loyalty_program' => 'boolean',
        'enable_coupons' => 'boolean',
        'enable_scheduled_orders' => 'boolean',
    ];

    // ⭐ Accessor: Carregar logo do Tenant (somente leitura)
    public function getTenantLogoAttribute()
    {
        return tenancy()->tenant?->logo;
    }

    // ⚠️ Mutator REMOVIDO - agora usamos hooks do Filament (EditSettings/CreateSettings)
    // para salvar tenant_logo diretamente no Tenant

    public static function current()
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
