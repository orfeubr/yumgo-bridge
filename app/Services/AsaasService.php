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
     *
     * @param Tenant|array $data - Tenant model ou array com dados
     * @return array|null - Retorna ['id' => ..., 'walletId' => ...] ou null
     */
    public function createSubAccount($data): ?array
    {
        // Se receber Tenant, converte para array
        if ($data instanceof Tenant) {
            $tenant = $data;
            $data = [
                'name' => $tenant->name,
                'email' => $tenant->email,
                'cpfCnpj' => $tenant->cpf_cnpj ?? $tenant->cnpj ?? null,
                'phone' => $tenant->phone ?? null,
                'id' => $tenant->id,
            ];
        }

        // Remove formatação do CPF/CNPJ (apenas números)
        $cpfCnpj = preg_replace('/[^0-9]/', '', $data['cpfCnpj'] ?? '');

        // Se não tiver CPF/CNPJ, usa um CNPJ único de teste
        if (empty($cpfCnpj)) {
            $id = $data['id'] ?? rand(1000, 9999);
            $cpfCnpj = '11222333' . str_pad((string) $id, 4, '0', STR_PAD_LEFT) . '81';
        }

        // Preparar payload
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'cpfCnpj' => $cpfCnpj,
            'companyType' => $data['companyType'] ?? 'MEI',
            'mobilePhone' => preg_replace('/[^0-9]/', '', $data['mobilePhone'] ?? $data['phone'] ?? '11912345678'),
            'incomeValue' => 5000.00,  // Faturamento mensal padrão
        ];

        // Adicionar birthDate se fornecido
        if (!empty($data['birthDate'])) {
            $payload['birthDate'] = $data['birthDate'];
        }

        // Adicionar endereço se fornecido
        if (!empty($data['address'])) {
            $payload['address'] = $data['address'];
            $payload['addressNumber'] = $data['addressNumber'] ?? '';
            $payload['province'] = $data['province'] ?? '';
            $payload['postalCode'] = $data['postalCode'] ?? '';
        }

        if (!empty($data['complement'])) {
            $payload['complement'] = $data['complement'];
        }

        if (!empty($data['phone'])) {
            $payload['phone'] = preg_replace('/[^0-9]/', '', $data['phone']);
        }

        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/accounts", $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            return [
                'id' => $responseData['id'] ?? null,
                'walletId' => $responseData['walletId'] ?? null,
            ];
        }

        // Log de erro para debug
        \Log::error('Erro ao criar sub-conta Asaas', [
            'name' => $data['name'] ?? 'N/A',
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Cadastra dados bancários da sub-conta
     *
     * @param string $accountId - ID da conta Asaas
     * @param array $bankData - Dados bancários
     * @return array|null
     */
    public function createBankAccount(string $accountId, array $bankData): ?array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/accounts/{$accountId}/bankAccounts", $bankData);

        if ($response->successful()) {
            return $response->json();
        }

        \Log::error('Erro ao cadastrar dados bancários no Asaas', [
            'account_id' => $accountId,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Obtém informações da conta Asaas
     */
    public function getAccountInfo(): ?array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
        ])->get("{$this->baseUrl}/myAccount");

        if ($response->successful()) {
            return $response->json();
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

        // Comissão da plataforma (padrão 1% - sendo definido)
        $commissionPercentage = $tenant->plan->commission_percentage ?? 1.00;
        $platformValue = ($order->total * $commissionPercentage) / 100;
        $restaurantValue = $order->total - $platformValue;

        $payload = [
            'customer' => $this->getOrCreateCustomer($order->customer),
            'billingType' => $data['payment_method'] ?? 'PIX', // PIX, CREDIT_CARD, DEBIT_CARD
            'value' => $order->total,
            'dueDate' => now()->format('Y-m-d'),
            'description' => "Pedido #{$order->order_number}",
            'externalReference' => $tenant->id . ':' . $order->id, // tenant_id:order_id

            // TODO: Implementar split de pagamento corretamente
            // Split temporariamente desabilitado para testes
            // 'split' => [
            //     [
            //         'walletId' => $tenant->asaas_account_id,
            //         'fixedValue' => $restaurantValue,
            //         'status' => 'PENDING',
            //     ],
            //     [
            //         'walletId' => config('services.asaas.platform_wallet_id'),
            //         'fixedValue' => $platformValue,
            //         'status' => 'PENDING',
            //     ],
            // ],
        ];

        // Se for cartão de crédito, adiciona dados
        if (isset($data['card'])) {
            $payload['creditCard'] = $data['card'];
            $payload['creditCardHolderInfo'] = $data['card_holder'];
        }

        // PROTEÇÃO: Timeout de 10 segundos
        $response = Http::timeout(10)->withHeaders([
            'access_token' => $this->apiKey, // Usa API Key Master
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/payments", $payload);

        if ($response->successful()) {
            return $response->json();
        }

        // Log de erro detalhado
        \Log::error('Erro ao criar pagamento Asaas', [
            'order_id' => $order->id,
            'status' => $response->status(),
            'body' => $response->body(),
            'payload' => $payload,
        ]);

        return null;
    }

    /**
     * Obtém ou cria cliente no Asaas
     * PROTEÇÃO: Funciona mesmo sem CPF (gera automaticamente no sandbox)
     */
    private function getOrCreateCustomer($customer): string
    {
        $tenant = tenant();

        // PROTEÇÃO: Garantir que customer tem email
        if (empty($customer->email)) {
            throw new \Exception('Cliente não possui email cadastrado');
        }

        // Preparar CPF primeiro (pode precisar atualizar cliente existente)
        $cpf = preg_replace('/[^0-9]/', '', $customer->cpf ?? '');

        // Se não tiver CPF e estiver no sandbox, gera um válido
        if (empty($cpf) && str_contains($this->baseUrl, 'sandbox')) {
            $cpf = $this->generateValidCPF();

            // PROTEÇÃO: Salvar CPF apenas se o customer for um Model
            try {
                if (method_exists($customer, 'update')) {
                    $customer->update(['cpf' => $cpf]);
                }
            } catch (\Exception $e) {
                \Log::warning('Não foi possível salvar CPF gerado', ['error' => $e->getMessage()]);
            }
        }

        // PROTEÇÃO: Timeout de 5 segundos para não travar
        // Busca cliente existente
        $response = Http::timeout(5)->withHeaders([
            'access_token' => $this->apiKey,
        ])->get("{$this->baseUrl}/customers", [
            'email' => $customer->email,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['data'][0]['id'])) {
                $asaasCustomerId = $data['data'][0]['id'];

                // Se encontrou mas não tem CPF, atualiza
                if ($cpf && empty($data['data'][0]['cpfCnpj'])) {
                    Http::withHeaders([
                        'access_token' => $this->apiKey,
                        'Content-Type' => 'application/json',
                    ])->put("{$this->baseUrl}/customers/{$asaasCustomerId}", [
                        'cpfCnpj' => $cpf,
                    ]);
                }

                return $asaasCustomerId;
            }
        }

        // Cria novo cliente (CPF já foi preparado acima)
        $response = Http::timeout(5)->withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/customers", [
            'name' => $customer->name ?? 'Cliente',
            'email' => $customer->email,
            'mobilePhone' => preg_replace('/[^0-9]/', '', $customer->phone ?? '11999999999'),
            'cpfCnpj' => $cpf ?: null,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['id'];
        }

        // PROTEÇÃO: Log detalhado do erro
        \Log::error('Erro ao criar cliente no Asaas', [
            'status' => $response->status(),
            'body' => $response->body(),
            'customer_email' => $customer->email,
        ]);

        throw new \Exception('Erro ao criar cliente no Asaas: ' . $response->body());
    }

    /**
     * Consulta status do pagamento
     */
    public function getPaymentStatus(string $paymentId): ?array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
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
            \Log::warning('Webhook Asaas: evento ou payment ausente', $data);
            return false;
        }

        // Extrai tenant_id:order_id do externalReference
        $externalReference = $paymentData['externalReference'] ?? null;
        if (!$externalReference) {
            \Log::warning('Webhook Asaas: externalReference ausente', $paymentData);
            return false;
        }

        // Parse do formato "tenant_id:order_id"
        $parts = explode(':', $externalReference);
        if (count($parts) !== 2) {
            \Log::warning('Webhook Asaas: externalReference inválido', ['reference' => $externalReference]);
            return false;
        }

        [$tenantId, $orderId] = $parts;

        // Busca o tenant
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            \Log::error('Webhook Asaas: tenant não encontrado', ['tenant_id' => $tenantId]);
            return false;
        }

        // Inicializa o tenant para acessar seu schema
        tenancy()->initialize($tenant);

        try {
            // Busca o pedido no schema do tenant
            $order = Order::find($orderId);
            if (!$order) {
                \Log::error('Webhook Asaas: pedido não encontrado', [
                    'tenant_id' => $tenantId,
                    'order_id' => $orderId,
                ]);
                tenancy()->end();
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

                        \Log::info('Webhook Asaas: pagamento confirmado', [
                            'tenant_id' => $tenantId,
                            'order_id' => $orderId,
                            'payment_id' => $paymentData['id'],
                        ]);
                    }
                    break;

                case 'PAYMENT_OVERDUE':
                case 'PAYMENT_DELETED':
                    $payment = $order->payments()->where('transaction_id', $paymentData['id'])->first();
                    if ($payment) {
                        $payment->update([
                            'status' => 'failed',
                        ]);

                        \Log::info('Webhook Asaas: pagamento falhou', [
                            'tenant_id' => $tenantId,
                            'order_id' => $orderId,
                            'event' => $event,
                        ]);
                    }
                    break;
            }

            tenancy()->end();
            return true;
        } catch (\Exception $e) {
            tenancy()->end();
            \Log::error('Webhook Asaas: erro ao processar', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId,
                'order_id' => $orderId,
            ]);
            return false;
        }
    }

    /**
     * Cria PIX QR Code
     */
    public function getPixQrCode(string $paymentId): ?array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
        ])->get("{$this->baseUrl}/payments/{$paymentId}/pixQrCode");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Gera CPF válido para testes (apenas sandbox)
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

        return "{$n1}{$n2}{$n3}{$n4}{$n5}{$n6}{$n7}{$n8}{$n9}{$d1}{$d2}";
    }
}
