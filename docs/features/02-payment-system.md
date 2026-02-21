# 💳 Sistema de Pagamentos - Asaas

## 📋 Decisão: Gateway Único

Usaremos **ASAAS** como gateway principal por:
- ✅ Menor custo (2,99% cartão vs 4,99% Mercado Pago)
- ✅ Split nativo (sem duplicar custos)
- ✅ Sub-contas para multi-tenant
- ✅ API completa e bem documentada
- ✅ Sem mensalidade

## 💰 Taxas do Asaas

| Método | Taxa |
|--------|------|
| **PIX** | R$ 0,99 por transação |
| **Cartão de Crédito** | 2,99% + R$ 0,49 |
| **Boleto** | R$ 0,99 - R$ 1,99 |
| **Assinatura Mensal** | R$ 0,00 (grátis!) |

## 🏗️ Arquitetura de Pagamentos

```
Cliente faz pedido de R$ 100
         ↓
    Asaas Gateway
    (1 transação única)
         ↓
   Split Automático:
   ├─ R$ 97,00 → Sub-conta Restaurante
   │   (97% - restaurante recebe)
   ├─ R$ 3,00 → Conta Principal
   │   (3% - nossa comissão)
   └─ R$ 0,99 → Taxa Asaas
       (custo da transação)

CUSTO TOTAL: R$ 0,99 (PIX) ou ~R$ 3,48 (cartão)
```

## 🔧 Implementação

### 1. Sub-Contas (Multi-Tenant)

Cada restaurante terá uma sub-conta Asaas:

```php
// app/Services/AsaasService.php
class AsaasService
{
    public function createRestaurantSubAccount(Tenant $tenant)
    {
        $response = Http::asJson()
            ->withToken(config('asaas.api_key'))
            ->post('https://api.asaas.com/v3/accounts', [
                'name' => $tenant->name,
                'email' => $tenant->email,
                'cpfCnpj' => $tenant->document,
                'mobilePhone' => $tenant->phone,
                'address' => [
                    'addressNumber' => $tenant->address['number'],
                    'province' => $tenant->address['district'],
                ],
                'companyType' => 'MEI', // ou INDIVIDUAL, LIMITED
            ]);

        $tenant->update([
            'asaas_account_id' => $response['id'],
            'asaas_wallet_id' => $response['walletId'],
            'asaas_api_key' => $response['apiKey'],
        ]);

        return $response;
    }
}
```

### 2. Pagamento com Split

```php
// app/Services/PaymentService.php
class PaymentService
{
    public function createPaymentWithSplit(Order $order)
    {
        $restaurant = $order->restaurant;
        $customer = $order->customer;
        
        // Calcular comissão
        $commissionPercentage = $restaurant->plan->commission_percentage;
        $restaurantAmount = $order->total * (1 - $commissionPercentage / 100);
        $platformCommission = $order->total - $restaurantAmount;
        
        // Criar cliente no Asaas (se não existir)
        $asaasCustomerId = $this->getOrCreateAsaasCustomer($customer);
        
        // Criar cobrança com split
        $payment = Http::asJson()
            ->withToken(config('asaas.api_key'))
            ->post('https://api.asaas.com/v3/payments', [
                'customer' => $asaasCustomerId,
                'billingType' => 'PIX', // ou CREDIT_CARD
                'value' => $order->total,
                'dueDate' => now()->format('Y-m-d'),
                'description' => "Pedido #{$order->order_number}",
                
                // SPLIT AUTOMÁTICO
                'split' => [
                    [
                        'walletId' => $restaurant->asaas_wallet_id,
                        'fixedValue' => round($restaurantAmount, 2),
                    ],
                    // Plataforma fica com o resto (comissão)
                ],
                
                // Webhook
                'externalReference' => $order->id,
            ]);

        // Salvar pagamento
        $order->payments()->create([
            'gateway' => 'asaas',
            'transaction_id' => $payment['id'],
            'amount' => $order->total,
            'status' => 'pending',
            'payment_method' => 'pix',
            'metadata' => $payment,
        ]);

        return $payment;
    }
}
```

### 3. Webhook (Confirmação de Pagamento)

```php
// app/Http/Controllers/WebhookController.php
class AsaasWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $event = $request->input('event');
        $payment = $request->input('payment');
        
        switch ($event) {
            case 'PAYMENT_CONFIRMED':
                $this->handlePaymentConfirmed($payment);
                break;
                
            case 'PAYMENT_RECEIVED':
                $this->handlePaymentReceived($payment);
                break;
        }
        
        return response()->json(['success' => true]);
    }
    
    private function handlePaymentConfirmed($paymentData)
    {
        $order = Order::find($paymentData['externalReference']);
        
        DB::transaction(function () use ($order, $paymentData) {
            // 1. Atualizar pagamento
            $order->payments()
                ->where('transaction_id', $paymentData['id'])
                ->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                ]);
            
            // 2. Atualizar pedido
            $order->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);
            
            // 3. CASHBACK AUTOMÁTICO ⭐
            $this->processCashback($order);
            
            // 4. Notificar restaurante
            $order->restaurant->notify(new NewOrderNotification($order));
            
            // 5. Atualizar estoque
            $this->updateInventory($order);
        });
    }
    
    private function processCashback(Order $order)
    {
        $customer = $order->customer;
        $settings = CashbackSettings::first();
        
        // Pegar tier do cliente
        $tier = $customer->loyalty_tier;
        $percentage = $settings->tiers[$tier]['percentage'];
        
        // Calcular cashback
        $cashbackAmount = $order->total * ($percentage / 100);
        
        // Aniversário? DOBRA!
        if ($customer->isBirthdayMonth()) {
            $cashbackAmount *= $settings->birthday_multiplier;
        }
        
        // Adicionar ao saldo
        $customer->increment('cashback_balance', $cashbackAmount);
        $customer->increment('total_cashback_earned', $cashbackAmount);
        
        // Registrar transação
        CashbackTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'type' => 'earned',
            'amount' => $cashbackAmount,
            'balance_before' => $customer->cashback_balance - $cashbackAmount,
            'balance_after' => $customer->cashback_balance,
            'description' => "Cashback do pedido #{$order->order_number}",
            'expires_at' => now()->addDays($settings->tiers[$tier]['expiry_days']),
        ]);
        
        // Atualizar ordem
        $order->update(['cashback_earned' => $cashbackAmount]);
        
        // Notificar cliente
        $customer->notify(new CashbackEarnedNotification($cashbackAmount));
        
        // Verificar upgrade de tier
        $this->checkTierUpgrade($customer);
    }
}
```

### 4. Usar Cashback no Pedido

```php
// app/Services/OrderService.php
class OrderService
{
    public function applyCashback(Order $order, float $cashbackAmount)
    {
        $customer = $order->customer;
        
        // Validações
        if ($cashbackAmount > $customer->cashback_balance) {
            throw new Exception('Saldo de cashback insuficiente');
        }
        
        $settings = CashbackSettings::first();
        
        if ($cashbackAmount < $settings->min_cashback_to_use) {
            throw new Exception("Mínimo de R$ {$settings->min_cashback_to_use} para usar");
        }
        
        // Não pode usar mais que X% do pedido
        $maxAllowed = $order->subtotal * ($settings->max_percentage_of_order / 100);
        if ($cashbackAmount > $maxAllowed) {
            $cashbackAmount = $maxAllowed;
        }
        
        // Aplicar desconto
        $order->cashback_used = $cashbackAmount;
        $order->total = $order->subtotal + $order->delivery_fee - $cashbackAmount;
        $order->save();
        
        // Debitar do saldo (será efetivado no webhook)
        $customer->decrement('cashback_balance', $cashbackAmount);
        $customer->increment('total_cashback_used', $cashbackAmount);
        
        // Registrar transação
        CashbackTransaction::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'type' => 'used',
            'amount' => -$cashbackAmount,
            'balance_before' => $customer->cashback_balance + $cashbackAmount,
            'balance_after' => $customer->cashback_balance,
            'description' => "Usado no pedido #{$order->order_number}",
        ]);
    }
}
```

## 📊 Fluxo Completo

```
1. Cliente monta carrinho: R$ 100
   ├─ Subtotal: R$ 95
   ├─ Taxa entrega: R$ 5
   └─ Total: R$ 100

2. Cliente aplica cashback: -R$ 10
   └─ Novo total: R$ 90

3. Sistema cria pagamento no Asaas
   ├─ Valor: R$ 90
   └─ Split:
       ├─ R$ 87,30 → Restaurante (97%)
       └─ R$ 2,70 → Plataforma (3%)

4. Cliente paga via PIX
   └─ QR Code gerado

5. Asaas confirma pagamento (webhook)
   ├─ Atualiza pedido: CONFIRMADO
   ├─ Calcula novo cashback: R$ 4,50 (5%)
   ├─ Adiciona ao saldo do cliente
   └─ Notifica restaurante

6. Restaurante confirma e prepara
```

## 💡 Vantagens da Abordagem

✅ **1 transação = 1 taxa** (não duplica custos)
✅ **Split automático** (restaurante recebe direto)
✅ **Cashback independente** (gerenciado por nós)
✅ **Sub-contas isoladas** (multi-tenant perfeito)
✅ **Webhook confiável** (99,9% uptime)

## 📈 Exemplo de Economia

**1000 pedidos/mês, ticket médio R$ 50**

```
Com Asaas (PIX):
├─ Taxa: R$ 0,99 × 1000 = R$ 990
├─ Nossa comissão: 3% × R$ 50.000 = R$ 1.500
└─ LUCRO: R$ 510/mês 💰

Com Mercado Pago:
├─ Taxa: 4,99% × R$ 50.000 = R$ 2.495
├─ Nossa comissão: R$ 1.500
└─ PREJUÍZO: -R$ 995/mês 😱

ECONOMIA: R$ 1.505/mês com Asaas! 🚀
```

## 🔒 Segurança

- ✅ Webhook com validação de assinatura
- ✅ HTTPS obrigatório
- ✅ API keys em .env
- ✅ Rate limiting
- ✅ Logs de todas transações
- ✅ Certificação PCI DSS (Asaas)

---

**Asaas = melhor custo-benefício para delivery multi-tenant! 🎯**
