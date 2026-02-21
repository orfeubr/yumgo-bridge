<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Tenant;
use App\Models\Order;

class AsaasService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.asaas.url', 'https://api.asaas.com/v3');
        $this->apiKey = config('services.asaas.api_key');
    }

    /**
     * Cria sub-conta para o tenant (restaurante)
     */
    public function createSubAccount(Tenant $tenant): ?string
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/accounts", [
            'name' => $tenant->name,
            'email' => $tenant->email,
            'cpfCnpj' => $tenant->cpf_cnpj ?? '',
            'companyType' => 'MEI',
            'mobilePhone' => $tenant->phone,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['walletId'] ?? null;
        }

        return null;
    }

    /**
     * Cria cobrança com split automático
     */
    public function createPayment(Order $order, array $data): ?array
    {
        $tenant = tenant();
        
        if (!$tenant->asaas_account_id) {
            throw new \Exception('Tenant não possui conta Asaas');
        }

        $commissionPercentage = $tenant->plan->commission_percentage ?? 3.00;
        $platformValue = ($order->total * $commissionPercentage) / 100;
        $restaurantValue = $order->total - $platformValue;

        $payload = [
            'customer' => $this->getOrCreateCustomer($order->customer),
            'billingType' => $data['payment_method'] ?? 'PIX', // PIX, CREDIT_CARD, DEBIT_CARD
            'value' => $order->total,
            'dueDate' => now()->format('Y-m-d'),
            'description' => "Pedido #{$order->order_number}",
            'externalReference' => (string) $order->id,
            
            // Split de pagamento
            'split' => [
                [
                    'walletId' => $tenant->asaas_account_id,
                    'fixedValue' => $restaurantValue,
                    'status' => 'PENDING',
                ],
                [
                    'walletId' => config('services.asaas.platform_wallet_id'),
                    'fixedValue' => $platformValue,
                    'status' => 'PENDING',
                ],
            ],
        ];

        // Se for cartão de crédito, adiciona dados
        if (isset($data['card'])) {
            $payload['creditCard'] = $data['card'];
            $payload['creditCardHolderInfo'] = $data['card_holder'];
        }

        $response = Http::withHeaders([
            'access_token' => $tenant->asaas_account_id, // Usa token da sub-conta
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/payments", $payload);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Obtém ou cria cliente no Asaas
     */
    private function getOrCreateCustomer($customer): string
    {
        $tenant = tenant();

        // Busca cliente existente
        $response = Http::withHeaders([
            'access_token' => $tenant->asaas_account_id,
        ])->get("{$this->baseUrl}/customers", [
            'email' => $customer->email,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['data'][0]['id'])) {
                return $data['data'][0]['id'];
            }
        }

        // Cria novo cliente
        $response = Http::withHeaders([
            'access_token' => $tenant->asaas_account_id,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/customers", [
            'name' => $customer->name,
            'email' => $customer->email,
            'mobilePhone' => $customer->phone,
            'cpfCnpj' => $customer->cpf,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['id'];
        }

        throw new \Exception('Erro ao criar cliente no Asaas');
    }

    /**
     * Consulta status do pagamento
     */
    public function getPaymentStatus(string $paymentId): ?array
    {
        $tenant = tenant();

        $response = Http::withHeaders([
            'access_token' => $tenant->asaas_account_id,
        ])->get("{$this->baseUrl}/payments/{$paymentId}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Processa webhook do Asaas
     */
    public function handleWebhook(array $data): bool
    {
        $event = $data['event'] ?? null;
        $paymentData = $data['payment'] ?? null;

        if (!$event || !$paymentData) {
            return false;
        }

        $orderId = $paymentData['externalReference'] ?? null;
        if (!$orderId) {
            return false;
        }

        $order = Order::find($orderId);
        if (!$order) {
            return false;
        }

        switch ($event) {
            case 'PAYMENT_CONFIRMED':
            case 'PAYMENT_RECEIVED':
                $payment = $order->payments()->where('transaction_id', $paymentData['id'])->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'confirmed',
                        'paid_at' => now(),
                    ]);

                    app(OrderService::class)->confirmPayment($order);
                }
                break;

            case 'PAYMENT_OVERDUE':
            case 'PAYMENT_DELETED':
                $payment = $order->payments()->where('transaction_id', $paymentData['id'])->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'failed',
                    ]);
                }
                break;
        }

        return true;
    }

    /**
     * Cria PIX QR Code
     */
    public function getPixQrCode(string $paymentId): ?array
    {
        $tenant = tenant();

        $response = Http::withHeaders([
            'access_token' => $tenant->asaas_account_id,
        ])->get("{$this->baseUrl}/payments/{$paymentId}/pixQrCode");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
