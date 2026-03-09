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

        // ⭐ VALIDAÇÃO OBRIGATÓRIA - Previne erros silenciosos
        if (empty($this->apiKey)) {
            \Log::error('❌ Pagar.me: API key não configurada!');
            throw new \Exception(
                'Pagar.me não configurado. Configure PAGARME_API_KEY no arquivo .env do projeto. ' .
                'Obtenha sua chave em: https://dashboard.pagar.me → Configurações → Chaves de API'
            );
        }

        \Log::info('✅ Pagar.me: Service inicializado com sucesso', [
            'base_url' => $this->baseUrl,
            'has_encryption_key' => !empty($this->encryptionKey),
        ]);
    }

    /**
     * Cria recebedor (recipient) para o tenant (restaurante)
     *
     * Equivalente ao createSubAccount do Asaas
     *
     * O recipient é necessário para split automático de pagamentos:
     * - Plataforma recebe comissão configurada no plano
     * - Restaurante recebe o restante automaticamente
     *
     * Dados obrigatórios:
     * - name: Nome do restaurante
     * - email: Email válido
     * - document: CPF (11 dígitos) ou CNPJ (14 dígitos)
     * - type: 'individual' ou 'company'
     * - bank_account: Conta bancária completa
     *
     * @param Tenant|array $data Tenant model ou array com dados
     * @return array|null ['id' => recipient_id, 'status' => 'active'] ou null em erro
     *
     * @throws \Exception Se API key não estiver configurada
     *
     * @see https://docs.pagar.me/reference/criar-recebedor
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
     * Cria transação de pagamento (PIX ou Cartão) com split automático
     *
     * Split automático:
     * - Plataforma recebe comissão definida no plano do tenant
     * - Restaurante recebe o restante
     * - Dinheiro é dividido automaticamente pelo Pagar.me
     *
     * Suporta:
     * - PIX: Gera QR Code base64 + código copia-cola
     * - Cartão de Crédito/Débito: Requer tokenização prévia (card_id)
     *
     * @param Order $order Pedido a processar pagamento
     * @param array $data Dados do pagamento
     *   - payment_method: 'pix' | 'credit_card' | 'debit_card'
     *   - card_id: Token do cartão (obrigatório para cartão)
     *   - installments: Parcelas (opcional, padrão 1)
     *
     * @return array|null ['id' => charge_id, 'status' => 'pending', 'qr_code' => ...] ou null em erro
     *
     * @throws \Exception Se tenant não tiver recipient configurado
     * @throws \Exception Se método de pagamento for inválido
     *
     * @see processCardPayment() Para pagamento direto com cartão tokenizado
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
                'name' => $customer['name'] ?? $order->customer->name ?? 'Cliente',
                'email' => $customer['email'] ?? $order->customer->email,
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
        } elseif ($data['payment_method'] === 'credit_card' || $data['payment_method'] === 'debit_card') {
            // 🔐 ATENÇÃO: Este método NÃO deve ser usado para pagamentos com cartão
            // Use processCardPayment() com tokenização segura!
            \Log::warning('⚠️ createPayment() chamado com cartão - use processCardPayment() com token!', [
                'order_id' => $order->id,
                'method' => $data['payment_method'],
            ]);

            throw new \Exception('Pagamentos com cartão devem usar tokenização segura. Use o fluxo correto.');
        }

        // PROTEÇÃO: Timeout de 15 segundos
        $response = Http::timeout(15)
            ->withBasicAuth($this->apiKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/orders", $payload);

        if ($response->successful()) {
            $result = $response->json();

            // ⭐ Log de sucesso com detalhes
            \Log::info('✅ Pagar.me: Pagamento criado com sucesso', [
                'order_id' => $result['id'] ?? null,
                'status' => $result['status'] ?? null,
                'amount' => $result['amount'] ?? null,
                'has_charges' => !empty($result['charges']),
            ]);

            // Extrai QR Code PIX se disponível
            $qrCode = $result['charges'][0]['last_transaction']['qr_code'] ?? null;
            $qrCodeUrl = $result['charges'][0]['last_transaction']['qr_code_url'] ?? null;

            if ($qrCode && $qrCodeUrl) {
                \Log::info('✅ Pagar.me: QR Code PIX obtido na mesma chamada', [
                    'has_qr_code' => true,
                    'has_url' => true,
                ]);
            }

            // Extrai informações importantes
            return [
                'id' => $result['id'] ?? null,
                'status' => $result['status'] ?? null,
                'amount' => $result['amount'] ?? null,
                'charges' => $result['charges'] ?? [],
                'pix_qr_code' => $qrCode,
                'pix_qr_code_url' => $qrCodeUrl,
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
     * IMPORTANTE: A soma dos items deve ser igual ao total do pedido
     */
    private function formatOrderItems(Order $order): array
    {
        $items = [];

        // Adicionar produtos
        foreach ($order->items as $item) {
            // Calcular amount em centavos (mínimo 1 centavo)
            $amountInCents = (int)round($item->unit_price * $item->quantity * 100);
            $amountInCents = max($amountInCents, 1); // Garantir mínimo 1 centavo

            $items[] = [
                'amount' => $amountInCents,
                'description' => $item->product_name ?: 'Produto',
                'quantity' => (int)$item->quantity,
                'code' => (string)$item->product_id,
            ];
        }

        // Adicionar taxa de entrega (se houver)
        if ($order->delivery_fee > 0) {
            $items[] = [
                'amount' => (int)round($order->delivery_fee * 100),
                'description' => 'Taxa de Entrega',
                'quantity' => 1,
                'code' => 'DELIVERY_FEE',
            ];
        }

        // ⭐ NÃO enviar desconto/cashback como items negativos
        // Pagar.me não aceita amount < 1 nos items
        // O total já está calculado corretamente no $order->total
        // Então ajustamos proporcionalmente os items se houver desconto

        $totalDescontos = $order->discount + $order->cashback_used;

        if ($totalDescontos > 0) {
            // Calcular total antes dos descontos
            $totalAntesDesconto = $order->subtotal + $order->delivery_fee;

            // Ajustar proporcionalmente o primeiro item para compensar o desconto
            if (!empty($items) && $totalAntesDesconto > 0) {
                $descontoEmCentavos = (int)round($totalDescontos * 100);
                $items[0]['amount'] = max(1, $items[0]['amount'] - $descontoEmCentavos);

                // Adicionar nota sobre desconto na descrição
                $items[0]['description'] .= ' (c/ desconto)';
            }
        }

        return $items;
    }

    /**
     * Obtém ou cria cliente no Pagar.me
     */
    private function getOrCreateCustomer($customer): array
    {
        // PROTEÇÃO: Garantir que customer tem email (usa email do restaurante se vazio)
        $email = $customer->email;
        if (empty($email)) {
            // Gera email usando domínio do restaurante
            // Ex: cliente-2@marmitaria-gi.yumgo.com.br
            $tenant = tenant();
            $email = "cliente-{$customer->id}@{$tenant->slug}.yumgo.com.br";

            \Log::info('💡 Cliente sem email, usando email do restaurante', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'fallback_email' => $email,
                'restaurante' => $tenant->name,
            ]);
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
                'email' => $email, // ⭐ Usa email processado (real ou temp)
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
            'email' => $email, // ⭐ Usa email processado (real ou temp)
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
        \Log::info('🔔 Webhook Pagar.me recebido no Service', ['data' => $data]);

        $event = $data['type'] ?? null;
        $orderData = $data['data'] ?? null;

        \Log::info('📋 Webhook - Event: ' . $event, ['has_orderData' => !empty($orderData)]);

        if (!$event || !$orderData) {
            \Log::warning('Webhook Pagar.me inválido', ['data' => $data]);
            return false;
        }

        try {
            // ⭐ Determinar tipo de webhook: order/charge OU subscription
            $isSubscriptionEvent = strpos($event, 'subscription.') === 0;

            if ($isSubscriptionEvent) {
                // ==================== SUBSCRIPTION EVENTS ====================
                \Log::info('📦 Webhook de Subscription recebido', ['event' => $event]);

                $subscriptionId = $orderData['id'] ?? null;
                if (!$subscriptionId) {
                    \Log::warning('Webhook subscription sem ID', ['data' => $orderData]);
                    return false;
                }

                // Buscar subscription no banco
                $subscription = \App\Models\Subscription::where('pagarme_subscription_id', $subscriptionId)->first();
                if (!$subscription) {
                    \Log::warning('Subscription não encontrada', ['pagarme_subscription_id' => $subscriptionId]);
                    return false;
                }

                \Log::info('✅ Subscription encontrada', [
                    'subscription_id' => $subscription->id,
                    'tenant_id' => $subscription->tenant_id,
                ]);

                // Processar eventos de subscription
                switch ($event) {
                    case 'subscription.created':
                        $subscription->update([
                            'status' => $orderData['status'] ?? 'trialing',
                        ]);
                        \Log::info('✅ Subscription criada', ['subscription_id' => $subscription->id]);
                        break;

                    case 'subscription.charge.success':
                        // Cobrança bem-sucedida (após trial ou renovação mensal)
                        $subscription->update([
                            'status' => 'active',
                            'trial_ends_at' => null, // Trial acabou
                            'current_period_start' => now(),
                            'current_period_end' => now()->addMonth(),
                        ]);
                        \Log::info('✅ Subscription ativada - pagamento recebido', [
                            'subscription_id' => $subscription->id,
                            'tenant_id' => $subscription->tenant_id,
                        ]);
                        break;

                    case 'subscription.charge.failed':
                        // Pagamento falhou - colocar em past_due
                        $subscription->update([
                            'status' => 'past_due',
                        ]);
                        \Log::warning('⚠️ Falha no pagamento da subscription', [
                            'subscription_id' => $subscription->id,
                            'tenant_id' => $subscription->tenant_id,
                        ]);

                        // TODO: Enviar email notificando falha no pagamento
                        break;

                    case 'subscription.canceled':
                        // Subscription cancelada
                        $subscription->update([
                            'status' => 'canceled',
                            'ends_at' => now(),
                        ]);
                        \Log::info('❌ Subscription cancelada', [
                            'subscription_id' => $subscription->id,
                            'tenant_id' => $subscription->tenant_id,
                        ]);
                        break;
                }

                return true;
            }

            // ==================== ORDER/CHARGE EVENTS ====================
            // Busca order pelo metadata
            $metadata = $orderData['metadata'] ?? [];
            $orderId = $metadata['order_id'] ?? null;
            $tenantId = $metadata['tenant_id'] ?? null;

            \Log::alert('🔍 Metadata extraído', ['order_id' => $orderId, 'tenant_id' => $tenantId]);

            if (!$orderId || !$tenantId) {
                \Log::warning('Webhook sem order_id ou tenant_id', ['metadata' => $metadata]);
                return false;
            }

            // Inicializa tenancy
            \Log::alert('🔍 Buscando tenant: ' . $tenantId);
            $tenant = \App\Models\Tenant::find($tenantId);
            if (!$tenant) {
                \Log::error('Tenant não encontrado no webhook', ['tenant_id' => $tenantId]);
                return false;
            }

            \Log::alert('✅ Tenant encontrado: ' . $tenant->name);
            tenancy()->initialize($tenant);

            // Busca order
            \Log::alert('🔍 Buscando order: ' . $orderId);
            $order = Order::find($orderId);
            if (!$order) {
                \Log::error('Order não encontrada no webhook', ['order_id' => $orderId]);
                return false;
            }

            \Log::alert('✅ Order encontrada: #' . $order->order_number);

            // Processa evento
            \Log::alert('🔄 Processando evento: ' . $event);
            switch ($event) {
                case 'order.paid':
                case 'charge.paid':
                    \Log::alert('💳 Atualizando status do pagamento para paid');
                    // Atualiza pagamento
                    $order->payments()->where('transaction_id', $orderData['id'])->update([
                        'status' => 'paid',
                    ]);

                    \Log::alert('✅ Chamando confirmPayment');
                    // Confirma pedido e processa cashback automaticamente
                    app(\App\Services\OrderService::class)->confirmPayment($order);

                    \Log::info('Pagamento confirmado via webhook Pagar.me', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'cashback_earned' => $order->cashback_earned,
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
        \Log::info('🔍 Pagar.me: Buscando QR Code PIX', ['order_id' => $orderId]);

        $orderData = $this->getPaymentStatus($orderId);

        if (!$orderData) {
            \Log::error('❌ Pagar.me: Não foi possível obter dados do pedido', ['order_id' => $orderId]);
            return null;
        }

        $charges = $orderData['charges'] ?? [];
        if (empty($charges)) {
            \Log::warning('Pagar.me: Nenhuma cobrança encontrada', ['order_id' => $orderId]);
            return null;
        }

        $lastTransaction = $charges[0]['last_transaction'] ?? null;
        if (!$lastTransaction) {
            \Log::warning('Pagar.me: Nenhuma transação encontrada', ['order_id' => $orderId]);
            return null;
        }

        $qrCodeString = $lastTransaction['qr_code'] ?? null;
        $qrCodeUrl = $lastTransaction['qr_code_url'] ?? null;

        // Se tiver URL da imagem, buscar e converter para base64
        $encodedImage = null;
        if ($qrCodeUrl) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(10)->get($qrCodeUrl);
                if ($response->successful()) {
                    $encodedImage = 'data:image/png;base64,' . base64_encode($response->body());
                    \Log::info('Pagar.me: Imagem QR Code baixada com sucesso', ['url' => $qrCodeUrl]);
                }
            } catch (\Exception $e) {
                \Log::warning('Pagar.me: Erro ao baixar imagem QR Code', [
                    'url' => $qrCodeUrl,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Se não conseguiu a imagem, gerar QR Code a partir da string
        if (!$encodedImage && $qrCodeString) {
            // Usar API pública para gerar QR Code (fallback)
            $encodedImage = $this->generateQrCodeFromString($qrCodeString);
        }

        // Log para debug
        \Log::info('Pagar.me: Dados da transação PIX', [
            'order_id' => $orderId,
            'transaction_type' => $lastTransaction['transaction_type'] ?? 'unknown',
            'has_qr_code' => isset($lastTransaction['qr_code']),
            'has_qr_code_url' => isset($lastTransaction['qr_code_url']),
            'has_encoded_image' => !empty($encodedImage),
        ]);

        // Retornar no formato esperado pelo OrderService (compatível com Asaas)
        $result = [
            'encodedImage' => $encodedImage,
            'payload' => $qrCodeString, // QR code string para copiar/colar
            'qr_code_url' => $qrCodeUrl,
            'expirationDate' => $lastTransaction['expires_at'] ?? null,
        ];

        \Log::info('✅ Pagar.me: QR Code retornado com sucesso', [
            'order_id' => $orderId,
            'has_image' => !empty($encodedImage),
            'has_payload' => !empty($qrCodeString),
            'has_expiration' => !empty($result['expirationDate']),
        ]);

        return $result;
    }

    /**
     * Gera QR Code em base64 a partir de uma string usando API pública
     */
    private function generateQrCodeFromString(string $text): ?string
    {
        try {
            // Usar API pública do QR Server
            $url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($text);

            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);

            if ($response->successful()) {
                return 'data:image/png;base64,' . base64_encode($response->body());
            }
        } catch (\Exception $e) {
            \Log::warning('Erro ao gerar QR Code via API pública', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * 🔐 Processa pagamento com cartão tokenizado (SEGURO)
     *
     * ⚠️ IMPORTANTE: Este método aceita APENAS tokens (card_id)
     * ❌ NUNCA aceita dados brutos de cartão (número, CVV, etc.)
     *
     * @param Order $order - Pedido a ser pago
     * @param array $cardData - DEVE conter 'card_id' (token do Pagar.me)
     * @return array|null
     * @throws \Exception
     */
    public function processCardPayment(Order $order, array $cardData): ?array
    {
        $tenant = tenant();

        if (!$tenant->pagarme_recipient_id) {
            throw new \Exception('Tenant não possui recebedor Pagar.me');
        }

        // 🔒 VALIDAÇÃO DE SEGURANÇA: Garantir que recebemos TOKEN (não dados brutos)
        if (!isset($cardData['card_id']) || empty($cardData['card_id'])) {
            \Log::alert('❌ TENTATIVA DE ENVIAR DADOS DE CARTÃO SEM TOKENIZAÇÃO!', [
                'order_id' => $order->id,
                'tenant_id' => $tenant->id,
                'has_number' => isset($cardData['number']),
                'has_cvv' => isset($cardData['cvv']),
            ]);

            throw new \Exception('Dados do cartão devem ser tokenizados no frontend. Erro de segurança.');
        }

        // 🔒 SEGURANÇA EXTRA: Bloquear se tentarem enviar dados brutos junto com token
        if (isset($cardData['number']) || isset($cardData['cvv']) || isset($cardData['exp_month'])) {
            \Log::alert('❌ TENTATIVA DE ENVIAR DADOS BRUTOS DE CARTÃO!', [
                'order_id' => $order->id,
                'tenant_id' => $tenant->id,
                'ip' => request()->ip(),
            ]);

            throw new \Exception('Violação de segurança: dados sensíveis não devem ser enviados.');
        }

        // Comissão da plataforma
        $commissionPercentage = $tenant->plan->commission_percentage ?? 1.00;
        $platformValue = ($order->total * $commissionPercentage) / 100;
        $restaurantValue = $order->total - $platformValue;

        // Cliente
        $customer = $this->getOrCreateCustomer($order->customer);

        // 🔐 CONVERSÃO TOKEN → CARD_ID
        // O frontend tokeniza o cartão e envia um token
        // Backend converte esse token em um card_id permanente
        $token = $cardData['card_id'];

        \Log::info('🔐 Convertendo token em card_id', [
            'token' => substr($token, 0, 12) . '...',
            'customer_id' => $customer['id'],
        ]);

        // Criar cartão usando o token (POST /customers/{id}/cards)
        $cardResponse = Http::timeout(15)
            ->withBasicAuth($this->apiKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/customers/{$customer['id']}/cards", [
                'token' => $token,
            ]);

        if (!$cardResponse->successful()) {
            \Log::error('❌ Erro ao criar cartão do token', [
                'status' => $cardResponse->status(),
                'body' => $cardResponse->body(),
            ]);
            throw new \Exception('Erro ao processar cartão. Verifique os dados.');
        }

        $card = $cardResponse->json();
        $realCardId = $card['id'];

        \Log::info('✅ Cartão criado do token', [
            'card_id' => $realCardId,
            'brand' => $card['brand'] ?? null,
            'last_digits' => $card['last_four_digits'] ?? null,
        ]);

        // Usar o card_id real no pagamento
        $cardData['card_id'] = $realCardId;

        // Payload base
        $payload = [
            'customer' => [
                'id' => $customer['id'],
                'name' => $customer['name'] ?? $order->customer->name ?? 'Cliente',
                'email' => $customer['email'] ?? $order->customer->email,
            ],
            'items' => $this->formatOrderItems($order),
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'tenant_id' => $tenant->id,
            ],
        ];

        // Split de pagamento
        $payload['split'] = [
            [
                'recipient_id' => $tenant->pagarme_recipient_id,
                'amount' => (int)($restaurantValue * 100),
                'type' => 'flat',
                'options' => [
                    'charge_processing_fee' => true,
                    'charge_remainder' => false,
                    'liable' => true,
                ],
            ],
            [
                'recipient_id' => config('services.pagarme.platform_recipient_id'),
                'amount' => (int)($platformValue * 100),
                'type' => 'flat',
                'options' => [
                    'charge_processing_fee' => false,
                    'charge_remainder' => true,
                    'liable' => false,
                ],
            ],
        ];

        // Método de pagamento
        $method = $cardData['method'] ?? 'credit_card';

        // 🔐 Configuração SEGURA: Usa TOKEN (não dados brutos)
        $cardInfo = [
            'card_id' => $cardData['card_id'], // ✅ Token gerado pelo Pagar.me JS SDK
            'statement_descriptor' => substr($tenant->name, 0, 13),
        ];

        // Adicionar installments apenas para crédito (débito não tem parcelamento)
        if ($method === 'credit_card') {
            $cardInfo['installments'] = (int)($cardData['installments'] ?? 1);
        }

        $payload['payments'] = [[
            'payment_method' => $method,
            $method => $cardInfo, // ⭐ Usa chave dinâmica: 'credit_card' ou 'debit_card'
        ]];

        \Log::info('🔐 Processando pagamento com cartão TOKENIZADO (seguro)', [
            'order_id' => $order->id,
            'method' => $method,
            'installments' => $cardInfo['installments'] ?? 1,
            'card_id' => substr($cardData['card_id'], 0, 12) . '...', // Log parcial do token
        ]);

        // Enviar para Pagar.me
        $response = Http::timeout(30)
            ->withBasicAuth($this->apiKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/orders", $payload);

        if ($response->successful()) {
            $result = $response->json();

            \Log::info('✅ Pagar.me: Pagamento com cartão processado', [
                'order_id' => $result['id'] ?? null,
                'status' => $result['status'] ?? null,
                'amount' => $result['amount'] ?? null,
            ]);

            return [
                'id' => $result['id'] ?? null,
                'status' => $result['status'] ?? null,
                'amount' => $result['amount'] ?? null,
                'charges' => $result['charges'] ?? [],
            ];
        }

        // Log de erro DETALHADO
        $errorBody = $response->body();
        $errorData = json_decode($errorBody, true);

        \Log::error('❌ Erro ao processar pagamento com cartão - Pagar.me', [
            'order_id' => $order->id,
            'http_status' => $response->status(),
            'error_message' => $errorData['message'] ?? 'Sem mensagem',
            'errors' => $errorData['errors'] ?? [],
            'full_response' => $errorBody,
        ]);

        // Retornar erro mais amigável para o usuário
        $userMessage = $errorData['message'] ?? 'Erro ao processar pagamento no gateway';
        if (isset($errorData['errors']) && is_array($errorData['errors'])) {
            $firstError = reset($errorData['errors']);
            if (is_array($firstError) && isset($firstError[0])) {
                $userMessage = $firstError[0];
            }
        }

        throw new \Exception($userMessage);
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

    // ==================== MÉTODOS DE ASSINATURAS ====================

    /**
     * Cria um cliente no Pagar.me para assinaturas
     *
     * @param \App\Models\Tenant $tenant
     * @return array|null - ['id' => customer_id, 'status' => ...]
     */
    public function createCustomer(\App\Models\Tenant $tenant): ?array
    {
        $cpfCnpj = preg_replace('/[^0-9]/', '', $tenant->cpf_cnpj ?? $tenant->cnpj ?? '');

        // Se não tiver CPF/CNPJ, gera um CNPJ único
        if (empty($cpfCnpj)) {
            $cpfCnpj = '11222333' . str_pad((string) $tenant->id, 4, '0', STR_PAD_LEFT) . '81';
        }

        $phone = preg_replace('/[^0-9]/', '', $tenant->phone ?? '11912345678');
        $ddd = substr($phone, 0, 2);
        $number = substr($phone, 2);

        $payload = [
            'name' => $tenant->name,
            'email' => $tenant->email,
            'document' => $cpfCnpj,
            'type' => strlen($cpfCnpj) === 11 ? 'individual' : 'company',
            'phones' => [
                'mobile_phone' => [
                    'country_code' => '55',
                    'area_code' => $ddd,
                    'number' => $number,
                ],
            ],
        ];

        \Log::info('🔵 Pagar.me: Criando customer para tenant', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
        ]);

        $response = Http::withBasicAuth($this->apiKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/customers", $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            \Log::info('✅ Pagar.me: Customer criado com sucesso', [
                'customer_id' => $responseData['id'],
            ]);

            return [
                'id' => $responseData['id'],
                'status' => $responseData['status'] ?? 'active',
            ];
        }

        \Log::error('❌ Pagar.me: Erro ao criar customer', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return null;
    }

    /**
     * Cria plano de assinatura no Pagar.me
     *
     * @param \App\Models\Plan $plan
     * @return array|null - ['id' => plan_id, ...]
     */
    public function createPlan(\App\Models\Plan $plan): ?array
    {
        // Verifica se já existe um plano criado
        if ($plan->pagarme_plan_id) {
            return ['id' => $plan->pagarme_plan_id];
        }

        $payload = [
            'name' => $plan->name,
            'interval' => 'month',
            'interval_count' => 1,
            'billing_type' => 'prepaid', // Pré-pago (cobra antes)
            'payment_methods' => ['credit_card', 'boleto'],
            'installments' => [1], // Apenas 1x
            'items' => [
                [
                    'name' => "Assinatura {$plan->name}",
                    'quantity' => 1,
                    'pricing_scheme' => [
                        'price' => (int) ($plan->price_monthly * 100), // Centavos
                    ],
                ],
            ],
            'metadata' => [
                'plan_id' => $plan->id,
                'type' => 'restaurant_subscription',
            ],
        ];

        \Log::info('🔵 Pagar.me: Criando plano de assinatura', [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'price' => $plan->price_monthly,
        ]);

        $response = Http::withBasicAuth($this->apiKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/plans", $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            \Log::info('✅ Pagar.me: Plano criado com sucesso', [
                'plan_id' => $responseData['id'],
            ]);

            return [
                'id' => $responseData['id'],
                'status' => $responseData['status'] ?? 'active',
            ];
        }

        \Log::error('❌ Pagar.me: Erro ao criar plano', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return null;
    }

    /**
     * Cria assinatura no Pagar.me
     *
     * @param \App\Models\Subscription $subscription
     * @param array $paymentData - ['card_id' => ..., 'payment_method' => ...]
     * @return array|null
     */
    public function createSubscription(\App\Models\Subscription $subscription, array $paymentData): ?array
    {
        $tenant = $subscription->tenant;
        $plan = $subscription->plan;

        // 1. Criar ou obter customer
        $customerId = $tenant->pagarme_customer_id;
        if (!$customerId) {
            $customerData = $this->createCustomer($tenant);
            if (!$customerData) {
                return null;
            }
            $customerId = $customerData['id'];
            $tenant->update(['pagarme_customer_id' => $customerId]);
        }

        // 2. Criar ou obter plano
        $planId = $plan->pagarme_plan_id;
        if (!$planId) {
            $planData = $this->createPlan($plan);
            if (!$planData) {
                return null;
            }
            $planId = $planData['id'];
            $plan->update(['pagarme_plan_id' => $planId]);
        }

        // 3. Preparar payload da assinatura
        $payload = [
            'customer_id' => $customerId,
            'plan_id' => $planId,
            'payment_method' => $paymentData['payment_method'] ?? 'credit_card',
            'metadata' => [
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
            ],
        ];

        // Adicionar dados do cartão se for credit_card
        if ($payload['payment_method'] === 'credit_card') {
            if (isset($paymentData['card_id'])) {
                // Token do cartão
                $payload['card_id'] = $paymentData['card_id'];
            } else {
                \Log::error('❌ Pagar.me: card_id não fornecido para assinatura');
                return null;
            }
        }

        \Log::info('🔵 Pagar.me: Criando assinatura', [
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'customer_id' => $customerId,
            'payment_method' => $payload['payment_method'],
        ]);

        $response = Http::withBasicAuth($this->apiKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/subscriptions", $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            \Log::info('✅ Pagar.me: Assinatura criada com sucesso', [
                'subscription_id' => $responseData['id'],
                'status' => $responseData['status'],
            ]);

            return [
                'id' => $responseData['id'],
                'status' => $responseData['status'],
                'current_cycle' => $responseData['current_cycle'] ?? null,
                'next_billing_at' => $responseData['next_billing_at'] ?? null,
            ];
        }

        \Log::error('❌ Pagar.me: Erro ao criar assinatura', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return null;
    }

    /**
     * Cancela assinatura no Pagar.me
     *
     * @param string $subscriptionId - ID da assinatura no Pagar.me
     * @return bool
     */
    public function cancelSubscription(string $subscriptionId): bool
    {
        \Log::info('🔵 Pagar.me: Cancelando assinatura', [
            'subscription_id' => $subscriptionId,
        ]);

        $response = Http::withBasicAuth($this->apiKey, '')
            ->delete("{$this->baseUrl}/subscriptions/{$subscriptionId}");

        if ($response->successful()) {
            \Log::info('✅ Pagar.me: Assinatura cancelada com sucesso');
            return true;
        }

        \Log::error('❌ Pagar.me: Erro ao cancelar assinatura', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return false;
    }

    /**
     * Obtém informações de uma assinatura
     *
     * @param string $subscriptionId
     * @return array|null
     */
    public function getSubscriptionInfo(string $subscriptionId): ?array
    {
        $response = Http::withBasicAuth($this->apiKey, '')
            ->get("{$this->baseUrl}/subscriptions/{$subscriptionId}");

        if ($response->successful()) {
            return $response->json();
        }

        \Log::error('❌ Pagar.me: Erro ao obter assinatura', [
            'subscription_id' => $subscriptionId,
            'status' => $response->status(),
        ]);

        return null;
    }

    /**
     * Processa webhook de eventos de assinatura
     *
     * @param array $data - Dados do webhook
     * @return bool
     */
    public function handleSubscriptionWebhook(array $data): bool
    {
        $event = $data['type'] ?? null;
        $subscriptionData = $data['data'] ?? null;

        if (!$event || !$subscriptionData) {
            \Log::warning('⚠️ Pagar.me Webhook: Dados incompletos', ['data' => $data]);
            return false;
        }

        \Log::info('📨 Pagar.me Webhook: Evento de assinatura recebido', [
            'event' => $event,
            'subscription_id' => $subscriptionData['id'] ?? null,
        ]);

        // Buscar assinatura local
        $subscription = \App\Models\Subscription::where('pagarme_subscription_id', $subscriptionData['id'])->first();
        if (!$subscription) {
            \Log::warning('⚠️ Assinatura não encontrada no banco', [
                'pagarme_subscription_id' => $subscriptionData['id'],
            ]);
            return false;
        }

        // Processar eventos
        switch ($event) {
            case 'subscription.created':
                $subscription->update([
                    'pagarme_status' => $subscriptionData['status'],
                    'status' => 'active',
                ]);
                break;

            case 'subscription.paid':
                $subscription->update([
                    'pagarme_status' => 'active',
                    'status' => 'active',
                    'last_payment_date' => now(),
                    'next_billing_date' => $subscriptionData['next_billing_at'] ?? null,
                ]);
                \Log::info('✅ Assinatura paga com sucesso', ['tenant_id' => $subscription->tenant_id]);
                break;

            case 'subscription.payment_failed':
                $subscription->update([
                    'pagarme_status' => 'unpaid',
                    'status' => 'past_due',
                ]);
                \Log::warning('⚠️ Pagamento de assinatura falhou', ['tenant_id' => $subscription->tenant_id]);
                break;

            case 'subscription.canceled':
                $subscription->update([
                    'pagarme_status' => 'canceled',
                    'status' => 'canceled',
                    'canceled_at' => now(),
                    'ends_at' => now(),
                ]);
                \Log::info('🚫 Assinatura cancelada', ['tenant_id' => $subscription->tenant_id]);
                break;

            default:
                \Log::info('ℹ️ Evento de assinatura não tratado', ['event' => $event]);
                break;
        }

        return true;
    }

}
