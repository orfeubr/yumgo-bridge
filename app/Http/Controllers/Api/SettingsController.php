<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Settings;

class SettingsController extends Controller
{
    /**
     * Obter configurações do tenant
     * GET /api/v1/settings
     */
    public function index()
    {
        $settings = Settings::current();
        $cashbackSettings = \App\Models\CashbackSettings::first();

        return response()->json([
            'success' => true,
            'settings' => [
                // Identidade
                'business_name' => $settings->business_name,
                'logo' => $settings->logo,
                'primary_color' => $settings->primary_color,

                // Horários
                'is_open_now' => $settings->isOpenNow(),
                'business_hours' => $settings->business_hours,

                // Delivery
                'delivery_fee' => (float) $settings->delivery_fee,
                'free_delivery_above' => $settings->free_delivery_above ? (float) $settings->free_delivery_above : null,
                'minimum_order_value' => (float) $settings->minimum_order_value,
                'min_delivery_time' => $settings->min_delivery_time,
                'max_delivery_time' => $settings->max_delivery_time,
                'allow_pickup' => $settings->allow_pickup,
                'allow_delivery' => $settings->allow_delivery,

                // Cashback (simplificado - sem tiers)
                'cashback' => $cashbackSettings ? [
                    'is_active' => $cashbackSettings->is_active,
                    'percentage' => (float) $cashbackSettings->bronze_percentage,
                    'min_order_value_to_earn' => (float) $cashbackSettings->min_order_value_to_earn,
                    'min_cashback_to_use' => (float) $cashbackSettings->min_cashback_to_use,
                    'birthday_bonus_enabled' => $cashbackSettings->birthday_bonus_enabled,
                    'birthday_multiplier' => (float) $cashbackSettings->birthday_multiplier,
                ] : [
                    'is_active' => false,
                ],

                // Pagamentos
                'payment_methods' => [
                    'pix' => [
                        'enabled' => $settings->payment_pix_enabled ?? true,
                        'label' => 'PIX',
                        'type' => 'online',
                    ],
                    'credit_card' => [
                        'enabled' => $settings->payment_credit_card_enabled ?? true,
                        'label' => 'Cartão de Crédito',
                        'type' => 'online',
                    ],
                    'debit_card' => [
                        'enabled' => $settings->payment_debit_card_enabled ?? true,
                        'label' => 'Cartão de Débito',
                        'type' => 'online',
                    ],
                    'on_delivery' => [
                        'enabled' => $settings->payment_on_delivery_enabled ?? true,
                        'label' => 'Pagar na Entrega',
                        'type' => 'on_delivery',
                        'options' => $settings->payment_on_delivery_options ?? [
                            'cash' => true,
                            'alelo' => false,
                            'sodexo' => false,
                            'vr' => false,
                            'ticket' => false,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Obter apenas métodos de pagamento
     * GET /api/v1/settings/payment-methods
     */
    public function paymentMethods()
    {
        $settings = Settings::current();

        $methods = [];

        // PIX
        if ($settings->payment_pix_enabled ?? true) {
            $methods[] = [
                'key' => 'pix',
                'label' => 'PIX',
                'type' => 'online',
                'logo' => '/images/pix-logo.png',
            ];
        }

        // Crédito
        if ($settings->payment_credit_card_enabled ?? true) {
            $methods[] = [
                'key' => 'credit_card',
                'label' => 'Crédito',
                'type' => 'online',
                'logos' => [
                    '/images/visa-logo.png',
                    '/images/mastercard-logo.png',
                ],
            ];
        }

        // Débito
        if ($settings->payment_debit_card_enabled ?? true) {
            $methods[] = [
                'key' => 'debit_card',
                'label' => 'Débito',
                'type' => 'online',
                'logos' => [
                    '/images/visa-logo.png',
                    '/images/mastercard-logo.png',
                ],
            ];
        }

        // Pagar na Entrega
        if ($settings->payment_on_delivery_enabled ?? true) {
            $deliveryOptions = [];

            // Dinheiro
            if ($settings->accept_cash_on_delivery ?? true) {
                $deliveryOptions[] = [
                    'key' => 'cash',
                    'label' => 'Dinheiro',
                    'icon' => '💵',
                    'requires_change' => true,
                    'max_change' => $settings->min_change_value ? (float) $settings->min_change_value : null,
                ];
            }

            // Cartão na Entrega (Maquininha)
            if ($settings->accept_card_on_delivery ?? false) {
                $deliveryOptions[] = [
                    'key' => 'card_on_delivery',
                    'label' => 'Cartão (Maquininha)',
                    'icon' => '💳',
                ];
            }

            // VR Benefícios (Vale Refeição)
            if ($settings->accept_vr_on_delivery ?? false) {
                $deliveryOptions[] = [
                    'key' => 'vr',
                    'label' => 'VR Benefícios',
                    'icon' => '🎫',
                    'type' => 'meal_voucher',
                ];
            }

            // Vale Alimentação (Genérico)
            if ($settings->accept_va_on_delivery ?? false) {
                $deliveryOptions[] = [
                    'key' => 'va',
                    'label' => 'Vale Alimentação',
                    'icon' => '🍽️',
                    'type' => 'food_voucher',
                ];
            }

            // Sodexo
            if ($settings->accept_sodexo_on_delivery ?? false) {
                $deliveryOptions[] = [
                    'key' => 'sodexo',
                    'label' => 'Sodexo',
                    'icon' => '🟢',
                    'logo' => '/images/sodexo-logo.png',
                    'type' => 'meal_voucher',
                ];
            }

            // Alelo
            if ($settings->accept_alelo_on_delivery ?? false) {
                $deliveryOptions[] = [
                    'key' => 'alelo',
                    'label' => 'Alelo',
                    'icon' => '🔵',
                    'logo' => '/images/alelo-logo.png',
                    'type' => 'meal_voucher',
                ];
            }

            // Ticket
            if ($settings->accept_ticket_on_delivery ?? false) {
                $deliveryOptions[] = [
                    'key' => 'ticket',
                    'label' => 'Ticket',
                    'icon' => '🟡',
                    'logo' => '/images/ticket-logo.png',
                    'type' => 'meal_voucher',
                ];
            }

            if (!empty($deliveryOptions)) {
                $methods[] = [
                    'key' => 'on_delivery',
                    'label' => 'Pagar na Entrega',
                    'type' => 'on_delivery',
                    'options' => $deliveryOptions,
                    'instructions' => $settings->delivery_payment_instructions,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }
}
