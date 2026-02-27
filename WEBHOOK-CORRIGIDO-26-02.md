# Correção do Sistema de Webhook e Polling - 26/02/2026

## 🐛 Problema Relatado

**Sintoma:** Página de pagamento não atualizava automaticamente após confirmação do PIX.

**URL afetada:** `/pedido/{orderNumber}/pagamento`

**Comportamento:** Polling rodando mas não redirecionando para página de confirmação.

## 🔍 Investigação

### 1. Verificação do Status no Banco
- Order 20260226-7AF0CC estava com `payment_status: pending`
- Webhook não estava atualizando o banco de dados

### 2. Análise do Código

**Problema 1: API não retornava `payment_status`**
- Arquivo: `app/Http/Controllers/Api/OrderController.php`
- Método: `formatOrder()`
- **Causa:** Campo `payment_status` não estava sendo incluído no retorno da API
- **Impacto:** Polling não conseguia verificar se o pagamento foi confirmado

**Problema 2: Polling verificava campo errado**
- Arquivo: `resources/views/tenant/payment.blade.php`
- **Causa:** Verificava `data.status === 'paid'` mas este campo nunca tem valor 'paid'
- **Correto:** Verificar `data.payment_status === 'paid'`

**Problema 3: CentralWebhookController falhando silenciosamente**
- Arquivo: `app/Http/Controllers/CentralWebhookController.php`
- **Causa:** Query `whereHas('payments')` não estava funcionando corretamente
- **Causa 2:** Falta de logs detalhados dificultava debug

## ✅ Correções Aplicadas

### 1. OrderController.php (Linha 233-247)

**ANTES:**
```php
$data = [
    'id' => $order->id,
    'order_number' => $order->order_number,
    'status' => $order->status,
    'status_label' => $this->getStatusLabel($order->status),
    // ... outros campos
];
```

**DEPOIS:**
```php
$data = [
    'id' => $order->id,
    'order_number' => $order->order_number,
    'status' => $order->status,
    'payment_status' => $order->payment_status, // ⭐ ADICIONADO
    'status_label' => $this->getStatusLabel($order->status),
    // ... outros campos
];
```

### 2. payment.blade.php (Linha 270-276)

**ANTES:**
```javascript
if (response.ok) {
    const data = await response.json();
    if (data.status === 'confirmed' || data.status === 'paid') {
        clearInterval(this.checkInterval);
        window.location.href = `/pedido/${this.orderNumber}/confirmado`;
    }
}
```

**DEPOIS:**
```javascript
if (response.ok) {
    const data = await response.json();
    console.log('🔍 Status do pedido:', {
        status: data.status,
        payment_status: data.payment_status
    });

    if (data.payment_status === 'paid' || data.status === 'confirmed') {
        console.log('✅ Pagamento confirmado! Redirecionando...');
        clearInterval(this.checkInterval);
        window.location.href = `/pedido/${this.orderNumber}/confirmado`;
    }
}
```

### 3. CentralWebhookController.php (Refatoração Completa)

**ANTES:**
```php
// Buscar order com whereHas (problemático)
$order = \App\Models\Order::whereHas('payments', function($query) use ($paymentId) {
    $query->where('transaction_id', $paymentId);
})->first();

// Buscar payment novamente (duplicado)
$payment = $order->payments()->where('transaction_id', $paymentId)->first();
```

**DEPOIS:**
```php
// 1. Logs detalhados em cada etapa
Log::info('🔄 Inicializando tenancy', ['tenant_id' => $tenant->id]);
tenancy()->initialize($tenant);
Log::info('✅ Tenancy inicializado');

// 2. Buscar payment diretamente (mais eficiente)
Log::info('🔍 Buscando payment por transaction_id', ['payment_id' => $paymentId]);
$payment = \App\Models\Payment::where('transaction_id', $paymentId)->first();

if (!$payment) {
    Log::error('❌ Payment não encontrado');
    return response()->json(['error' => 'Payment não encontrado'], 404);
}

Log::info('✅ Payment encontrado', ['order_id' => $payment->order_id]);

// 3. Buscar order pelo payment (direto)
$order = \App\Models\Order::find($payment->order_id);

Log::info('✅ Order encontrado', [
    'order_number' => $order->order_number,
    'current_status' => $order->status,
    'current_payment_status' => $order->payment_status,
]);

// 4. Processar eventos
switch ($event) {
    case 'PAYMENT_CONFIRMED':
    case 'PAYMENT_RECEIVED':
        $payment->update(['status' => 'confirmed', 'paid_at' => now()]);
        $order->update(['payment_status' => 'paid', 'status' => 'confirmed']);
        Log::info('✅ Webhook: Pagamento confirmado e order atualizado');
        break;
    // ...
}
```

## 🧪 Teste Realizado

**Script de teste:** `test-webhook-payment.php`

```bash
php test-webhook-payment.php
```

**Resultado:**
```
✅ Webhook processado com sucesso!
Order Status: confirmed
Payment Status: paid
Paid At: 2026-02-26 22:04:07
```

## 📊 Fluxo Corrigido

```
1. Cliente paga PIX no Asaas
2. Asaas dispara webhook para: https://yumgo.com.br/api/webhooks/asaas
3. CentralWebhookController recebe webhook
4. Identifica tenant pelo externalReference (144c5973-f985-4309-8f9a-c404dd11feae:20260226-7AF0CC)
5. Inicializa tenancy do restaurante correto
6. Busca payment por transaction_id (pay_1s1wo1vufp7esvea)
7. Atualiza payment: status = 'confirmed', paid_at = now()
8. Atualiza order: payment_status = 'paid', status = 'confirmed'
9. Retorna HTTP 200 para o Asaas
10. Página de pagamento faz polling a cada 5 segundos
11. API retorna order com payment_status = 'paid'
12. Frontend detecta payment_status === 'paid'
13. Redireciona para /pedido/{orderNumber}/confirmado
```

## 📝 Arquivos Modificados

```
✅ app/Http/Controllers/Api/OrderController.php (linha 238)
✅ resources/views/tenant/payment.blade.php (linhas 270-282)
✅ app/Http/Controllers/CentralWebhookController.php (linhas 6, 76-122, 159)
```

## 🎯 Resultado Final

✅ Webhook processa pagamentos corretamente
✅ API retorna payment_status para polling
✅ Página redireciona automaticamente em até 5 segundos
✅ Logs detalhados para debug futuro
✅ Código mais robusto e eficiente

## 🔧 Comandos Úteis

### Verificar status de um pedido:
```bash
php check-order-status.php
```

### Testar webhook manualmente:
```bash
php test-webhook-payment.php
```

### Monitorar logs em tempo real:
```bash
tail -f storage/logs/laravel.log | grep -i "webhook"
```

### Limpar logs:
```bash
echo "" > storage/logs/laravel.log
```

## ⚠️ Notas Importantes

1. **externalReference é crucial**: Formato `{tenant_id}:{order_number}`
2. **Polling verifica dois campos**: `payment_status === 'paid'` OU `status === 'confirmed'`
3. **Logs ajudam muito**: Todos os passos do webhook são logados com emojis para facilitar debug
4. **Tenancy DEVE ser inicializado**: Sem isso, não consegue acessar tabelas do tenant

---

**Data:** 26/02/2026 22:05 UTC
**Status:** ✅ RESOLVIDO E TESTADO
**Testado por:** Webhook simulado + Verificação manual no banco
