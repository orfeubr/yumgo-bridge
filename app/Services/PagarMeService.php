<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Tenant;
use App\Models\Order;

class PagarMeService
{
    private string $baseUrl;
    private string $apiKey;
    private string $encryptionKey;

    public function __construct()
    {
        $this->baseUrl = config('services.pagarme.url', 'https://api.pagar.me/core/v5');
        $this->apiKey = config('services.pagarme.api_key');
        $this->encryptionKey = config('services.pagarme.encryption_key');
    }

    /**
     * Cria recebedor (recipient) para o tenant (restaurante)
     * Equivalente ao createSubAccount do Asaas
     *
     * @param Tenant|array $data - Tenant model ou array com dados
     * @return array|null - Retorna ['id' => ..., 'status' => ...] ou null
     */
    public function createRecipient($data): ?array
    {
        // Se receber Tenant, converte para array
        if ($data instanceof Tenant) {
            $tenant = $data;
            $cpfCnpj = preg_replace('/[^0-9]/', '', $tenant->cpf_cnpj ?? $tenant->cnpj ?? '');

            // Se não tiver CPF/CNPJ, gera um CNPJ único de teste
            if (empty($cpfCnpj)) {
                $cpfCnpj = '11222333' . str_pad((string) $tenant->id, 4, '0', STR_PAD_LEFT) . '81';
            }

            $data = [
                'name' => $tenant->name,
                'email' => $tenant->email,
                'document' => $cpfCnpj,
                'type' => strlen($cpfCnpj) === 11 ? 'individual' : 'company',
                'phone' => preg_replace('/[^0-9]/', '', $tenant->phone ?? '11912345678'),
                'bank_account' => [
                    'holder_name' => $tenant->name,
                    'holder_type' => strlen($cpfCnpj) === 11 ? 'individual' : 'company',
                    'holder_document' => $cpfCnpj,
                    'bank' => $tenant->bank_code ?? '001', // Banco do Brasil por padrão
                    'branch_number' => $tenant->bank_branch ?? '0001',
                    'branch_check_digit' => $tenant->bank_branch_digit ?? '0',
                    'account_number' => $tenant->bank_account ?? '00000001',
                    'account_check_digit' => $tenant->bank_account_digit ?? '0',
                    'type' => 'checking', // checking ou savings
                ],
            ];
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'document' => $data['document'],
            'type' => $data['type'], // individual ou company
            'default_bank_account' => $data['bank_account'] ?? null,
            'transfer_settings' => [
                'transfer_enabled' => true,
                'transfer_interval' => 'daily', // daily, weekly, monthly
                'transfer_day' => 0, // 0 = todo dia
            ],
            'automatic_anticipation_settings' => [
                'enabled' => false, // Antecipação automática desabilitada
            ],
        ];

        $response = Http::withBasicAuth($this->apiKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/recipients", $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            return [
                'id' => $responseData['id'] ?? null,
                'status' => $responseData['status'] ?? null,
            ];
        }

        // Log de erro para debug
        \Log::error('Erro ao criar recebedor Pagar.me', [
            'name' => $data['name'] ?? 'N/A',
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Atualiza dados bancários do recebedor
     *
     * @param string $recipientId - ID do recebedor Pagar.me
     * @param array $bankData - Dados bancários
     * @return array|null
     */
    public function updateBankAccount(string $recipientId, array $bankData): ?array
    {
        $response = Http::withBasicAuth($this->apiKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->put("{$this->baseUrl}/recipients/{$recipientId}/default-bank-account", $bankData);

        if ($response->successful()) {
            return $response->json();
        }

        \Log::error('Erro ao atualizar dados bancários no Pagar.me', [
            'recipient_id' => $recipientId,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Obtém informações do recebedor
     */
    public function getRecipientInfo(string $recipientId): ?array
    {
        $response = Http::withBasicAuth($this->apiKey, '')
            ->get("{$this->baseUrl}/recipients/{$recipientId}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Cria transação (pedido) com split automático
     * Pagar.me usa "split_rules" na transação
     */
    public function createPayment(Order $order, array $data): ?array
    {
        $tenant = tenant();

        if (!$tenant->pagarme_recipient_id) {
            throw new \Exception('Tenant não possui recebedor Pagar.me');
        }

        // Comissão da plataforma (padrão 1%)
        $commissionPercentage = $tenant->plan->commission_percentage ?? 1.00;
        $platformValue = ($order->total * $commissionPercentage) / 100;
        $restaurantValue = $order->total - $platformValue;

        // Cliente
        $customer = $this->getOrCreateCustomer($order->customer);

        // Payload base
        $payload = [
            'customer' => [
                'id' => $customer['id'],
            ],
            'items' => $this->formatOrderItems($order),
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'tenant_id' => $tenant->id,
            ],
        ];

        // Split de pagamento (restaurante + plataforma)
        $payload['split'] = [
            [
                'recipient_id' => $tenant->pagarme_recipient_id,
                'amount' => (int)($restaurantValue * 100), // Centavos
                'type' => 'flat', // flat (valor fixo) ou percentage
                'options' => [
                    'charge_processing_fee' => true, // Restaurante paga taxas
                    'charge_remainder' => false,
                    'liable' => true, // Responsável por chargebacks
                ],
            ],
            [
                'recipient_id' => config('services.pagarme.platform_recipient_id'),
                'amount' => (int)($platformValue * 100), // Centavos
                'type' => 'flat',
                'options' => [
                    'charge_processing_fee' => false,
                    'charge_remainder' => true, // Plataforma recebe restante
                    'liable' => false,
                ],
            ],
        ];

        // Método de pagamento: PIX ou Cartão
        if ($data['payment_method'] === 'pix') {
            $payload['payments'] = [[
                'payment_method' => 'pix',
                'pix' => [
                    'expires_in' => 3600, // 1 hora
                ],
            ]];
        } elseif ($data['payment_method'] === 'credit_card') {
            $payload['payments'] = [[
                'payment_method' => 'credit_card',
                'credit_card' => [
                    'card' => [
                        'number' => $data['card']['number'],
                        'holder_name' => $data['card']['holder_name'],
                        'exp_month' => $data['card']['exp_month'],
                        'exp_year' => $data['card']['exp_year'],
                        'cvv' => $data['card']['cvv'],
                        'billing_address' => [
                            'line_1' => $order->delivery_address ?? 'Rua Principal, 123',
                            'zip_code' => preg_replace('/[^0-9]/', '', $order->delivery_zipcode ?? '01310100'),
                            'city' => $order->delivery_city ?? 'São Paulo',
                            'state' => $order->delivery_state ?? 'SP',
                            'country' => 'BR',
                        ],
                    ],
                    'installments' => $data['installments'] ?? 1,
                    'statement_descriptor' => substr($tenant->name, 0, 13), // Até 13 caracteres
                ],
            ]];
        }

        // PROTEÇÃO: Timeout de 15 segundos
        $response = Http::timeout(15)
            ->withBasicAuth($this->apiKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/orders", $payload);

        if ($response->successful()) {
            $result = $response->json();

            // Extrai informações importantes
            return [
                'id' => $result['id'] ?? null,
                'status' => $result['status'] ?? null,
                'amount' => $result['amount'] ?? null,
                'charges' => $result['charges'] ?? [],
                'pix_qr_code' => $result['charges'][0]['last_transaction']['qr_code'] ?? null,
                'pix_qr_code_url' => $result['charges'][0]['last_transaction']['qr_code_url'] ?? null,
            ];
        }

        // Log de erro detalhado
        \Log::error('Erro ao criar transação Pagar.me', [
            'order_id' => $order->id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Formata itens do pedido para o formato Pagar.me
     */
    private function formatOrderItems(Order $order): array
    {
        $items = [];

        foreach ($order->items as $item) {
            $items[] = [
                'amount' => (int)($item->price * 100), // Centavos
                'description' => $item->product_name,
                'quantity' => $item->quantity,
                'code' => (string)$item->product_id,
            ];
        }

        return $items;
    }

    /**
     * Obtém ou cria cliente no Pagar.me
     */
    private function getOrCreateCustomer($customer): array
    {
        // PROTEÇÃO: Garantir que customer tem email
        if (empty($customer->email)) {
            throw new \Exception('Cliente não possui email cadastrado');
        }

        // Preparar CPF
        $cpf = preg_replace('/[^0-9]/', '', $customer->cpf ?? '');

        // Se não tiver CPF, gera um válido para sandbox
        if (empty($cpf)) {
            $cpf = $this->generateValidCPF();
        }

        // Busca cliente existente por email
        $response = Http::timeout(5)
            ->withBasicAuth($this->apiKey, '')
            ->get("{$this->baseUrl}/customers", [
                'email' => $customer->email,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            if (!empty($data['data']) && count($data['data']) > 0) {
                return $data['data'][0]; // Retorna primeiro cliente encontrado
            }
        }

        // Se não existe, cria novo
        $phone = preg_replace('/[^0-9]/', '', $customer->phone ?? '11912345678');

        $payload = [
            'name' => $customer->name ?? 'Cliente',
            'email' => $customer->email,
            'type' => 'individual',
            'document' => $cpf,
            'document_type' => 'CPF',
            'phones' => [
                'mobile_phone' => [
                    'country_code' => '55',
                    'area_code' => substr($phone, 0, 2),
                    'number' => substr($phone, 2),
                ],
            ],
        ];

        $response = Http::timeout(5)
            ->withBasicAuth($this->apiKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/customers", $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Não foi possível criar cliente no Pagar.me');
    }

    /**
     * Obtém status do pagamento
     */
    public function getPaymentStatus(string $orderId): ?array
    {
        $response = Http::withBasicAuth($this->apiKey, '')
            ->get("{$this->baseUrl}/orders/{$orderId}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Processa webhook do Pagar.me
     *
     * Eventos principais:
     * - order.paid - Pedido pago
     * - order.payment_failed - Pagamento falhou
     * - charge.paid - Cobrança paga
     */
    public function handleWebhook(array $data): bool
    {
        $event = $data['type'] ?? null;
        $orderData = $data['data'] ?? null;

        if (!$event || !$orderData) {
            \Log::warning('Webhook Pagar.me inválido', ['data' => $data]);
            return false;
        }

        try {
            // Busca order pelo metadata
            $metadata = $orderData['metadata'] ?? [];
            $orderId = $metadata['order_id'] ?? null;
            $tenantId = $metadata['tenant_id'] ?? null;

            if (!$orderId || !$tenantId) {
                \Log::warning('Webhook sem order_id ou tenant_id', ['metadata' => $metadata]);
                return false;
            }

            // Inicializa tenancy
            $tenant = \App\Models\Tenant::find($tenantId);
            if (!$tenant) {
                \Log::error('Tenant não encontrado no webhook', ['tenant_id' => $tenantId]);
                return false;
            }

            tenancy()->initialize($tenant);

            // Busca order
            $order = Order::find($orderId);
            if (!$order) {
                \Log::error('Order não encontrada no webhook', ['order_id' => $orderId]);
                return false;
            }

            // Processa evento
            switch ($event) {
                case 'order.paid':
                case 'charge.paid':
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                        'paid_at' => now(),
                    ]);

                    // Processar cashback (se configurado)
                    if ($order->cashback_earned > 0) {
                        app(\App\Services\CashbackService::class)->processCashback($order);
                    }

                    \Log::info('Pagamento confirmado via webhook Pagar.me', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                    ]);
                    break;

                case 'order.payment_failed':
                case 'charge.payment_failed':
                    $order->update([
                        'payment_status' => 'failed',
                        'status' => 'canceled',
                    ]);

                    \Log::warning('Pagamento falhou via webhook Pagar.me', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                    ]);
                    break;
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Erro ao processar webhook Pagar.me', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Obtém QR Code PIX
     */
    public function getPixQrCode(string $orderId): ?array
    {
        $orderData = $this->getPaymentStatus($orderId);

        if (!$orderData) {
            return null;
        }

        $charges = $orderData['charges'] ?? [];
        if (empty($charges)) {
            return null;
        }

        $lastTransaction = $charges[0]['last_transaction'] ?? null;
        if (!$lastTransaction || $lastTransaction['transaction_type'] !== 'pix') {
            return null;
        }

        return [
            'qr_code' => $lastTransaction['qr_code'] ?? null,
            'qr_code_url' => $lastTransaction['qr_code_url'] ?? null,
            'expires_at' => $lastTransaction['expires_at'] ?? null,
        ];
    }

    /**
     * Gera CPF válido para testes em sandbox
     */
    private function generateValidCPF(): string
    {
        $n1 = rand(0, 9);
        $n2 = rand(0, 9);
        $n3 = rand(0, 9);
        $n4 = rand(0, 9);
        $n5 = rand(0, 9);
        $n6 = rand(0, 9);
        $n7 = rand(0, 9);
        $n8 = rand(0, 9);
        $n9 = rand(0, 9);

        $d1 = $n9 * 2 + $n8 * 3 + $n7 * 4 + $n6 * 5 + $n5 * 6 + $n4 * 7 + $n3 * 8 + $n2 * 9 + $n1 * 10;
        $d1 = 11 - ($d1 % 11);
        if ($d1 >= 10) {
            $d1 = 0;
        }

        $d2 = $d1 * 2 + $n9 * 3 + $n8 * 4 + $n7 * 5 + $n6 * 6 + $n5 * 7 + $n4 * 8 + $n3 * 9 + $n2 * 10 + $n1 * 11;
        $d2 = 11 - ($d2 % 11);
        if ($d2 >= 10) {
            $d2 = 0;
        }

        return '' . $n1 . $n2 . $n3 . $n4 . $n5 . $n6 . $n7 . $n8 . $n9 . $d1 . $d2;
    }
}
