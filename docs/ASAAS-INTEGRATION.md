# Integração Asaas - Sistema de Pagamentos com Split

## 📋 Visão Geral

O DeliveryPro utiliza o **Asaas** como gateway de pagamento com **split automático** de comissão sobre pedidos.

### Por que Asaas?

- **PIX**: R$ 0,99 por transação (vs R$ 1,19% Efi ou 4,99% Mercado Pago)
- **Cartão de Crédito**: 2,99% + R$ 0,49 (vs 4,99% Mercado Pago)
- **Split automático**: 1 transação = 1 taxa (sem duplicação de custos)
- **Sub-contas**: Isolamento perfeito para cada restaurante
- **Economia**: ~R$ 1.500/mês em 1000 pedidos!

## 🔧 Configuração

### 1. Variáveis de Ambiente (.env)

```bash
# Asaas Configuration
ASAAS_URL=https://sandbox.asaas.com/api/v3
ASAAS_API_KEY=seu_token_aqui
ASAAS_PLATFORM_WALLET_ID=id_da_carteira_plataforma
ASAAS_WEBHOOK_TOKEN=token_secreto_webhook
```

**Produção**: Alterar URL para `https://api.asaas.com/v3`

### 2. Obter API Key

1. Acesse https://www.asaas.com
2. Faça login ou crie uma conta
3. Vá em **Integrações → Chave API**
4. Copie o token e adicione no `.env`

### 3. Criar Sub-contas (Restaurantes)

Quando um novo restaurante se cadastra:

```php
$tenant = Tenant::create([...]);
$asaasWalletId = app(AsaasService::class)->createSubAccount($tenant);
$tenant->update(['asaas_account_id' => $asaasWalletId]);
```

Isso cria uma sub-conta isolada no Asaas para o restaurante.

## 💳 Fluxo de Pagamento

### 1. Cliente Faz Pedido

```
POST /api/v1/orders

{
  "items": [...],
  "payment_method": "pix",  // pix, credit_card, debit_card, cash
  "use_cashback": 10.00,
  "delivery_address": "..."
}
```

### 2. Sistema Calcula Split

```
Pedido: R$ 100,00
Cashback usado: R$ 10,00
Total a pagar: R$ 90,00

Split:
├─ Restaurante (97%): R$ 87,30
└─ Plataforma (3%):   R$ 2,70
```

### 3. Cria Pagamento no Asaas

```php
AsaasService::createPayment($order, [
    'payment_method' => 'PIX',
]);
```

**Request para Asaas**:
```json
{
  "customer": "cus_...",
  "billingType": "PIX",
  "value": 90.00,
  "dueDate": "2026-02-21",
  "description": "Pedido #20260221-ABC123",
  "externalReference": "order_id_123",
  "split": [
    {
      "walletId": "wallet_restaurante_xyz",
      "fixedValue": 87.30
    },
    {
      "walletId": "wallet_plataforma_main",
      "fixedValue": 2.70
    }
  ]
}
```

### 4. PIX QR Code (se aplicável)

Se pagamento for PIX, sistema obtém QR Code:

```php
$pixData = AsaasService::getPixQrCode($paymentId);
```

**Response**:
```json
{
  "order": {...},
  "payment": {
    "method": "pix",
    "qrcode_image": "data:image/png;base64,...",
    "qrcode_text": "00020126580014br.gov.bcb.pix...",
    "transaction_id": "pay_..."
  }
}
```

### 5. Cliente Paga

Cliente escaneia QR Code ou cola código PIX no app do banco.

### 6. Webhook Asaas Notifica

```
POST https://seu-dominio.com/api/v1/webhooks/asaas

{
  "event": "PAYMENT_CONFIRMED",
  "payment": {
    "id": "pay_...",
    "status": "RECEIVED",
    "value": 90.00,
    "externalReference": "order_id_123"
  }
}
```

### 7. Sistema Processa Confirmação

```php
// AsaasService::handleWebhook()
1. Valida webhook token
2. Localiza pedido pelo externalReference
3. Atualiza payment status → confirmed
4. Atualiza order status → confirmed
5. Adiciona cashback ganho ao cliente
6. Atualiza tier do cliente
7. Notifica restaurante
```

## 🔐 Segurança

### Validação de Webhook

```php
$webhookToken = config('services.asaas.webhook_token');
if ($request->header('asaas-access-token') !== $webhookToken) {
    return response()->json(['message' => 'Unauthorized'], 401);
}
```

### Isolamento de Dados

- Cada restaurante = 1 sub-conta Asaas
- Impossível acessar dados de outro restaurante
- Split acontece na mesma transação (seguro e auditável)

## 📊 Monitoramento

### Logs

```php
Log::info('Pagamento criado', [
    'order_id' => $order->id,
    'payment_id' => $paymentData['id'],
    'value' => $order->total,
    'split' => $split,
]);
```

### Dashboard Asaas

Acesse https://www.asaas.com/dashboard para:
- Ver transações em tempo real
- Monitorar splits
- Verificar repasses
- Gerar relatórios

## 🚀 Endpoints da API

### Criar Pedido com Pagamento
```
POST /api/v1/orders
```

### Consultar QR Code PIX
```
GET /api/v1/orders/{id}/payment
```

### Webhook Asaas
```
POST /api/v1/webhooks/asaas
```

## 💰 Custos Estimados

### 1000 Pedidos/Mês (Ticket Médio R$ 50)

| Gateway | PIX | Cartão | Total/Mês |
|---------|-----|--------|-----------|
| **Asaas** | R$ 990 | R$ 1.494 | **R$ 2.484** |
| Mercado Pago | R$ 2.495 | R$ 2.495 | **R$ 4.990** |
| Efi/PagSeguro | R$ 1.190 | R$ 1.995 | **R$ 3.185** |

**Economia com Asaas**: R$ 1.505/mês! 🎉

## 🧪 Testes

### Ambiente Sandbox

Use cartões de teste do Asaas:
- **Aprovado**: 4000 0000 0000 0010
- **Recusado**: 4000 0000 0000 0028
- **PIX**: Aprovado automaticamente após 5s

### Simular Webhook

```bash
curl -X POST https://seu-tenant.localhost/api/v1/webhooks/asaas \
  -H "Content-Type: application/json" \
  -H "asaas-access-token: seu_webhook_token" \
  -d '{
    "event": "PAYMENT_CONFIRMED",
    "payment": {
      "id": "pay_test123",
      "status": "RECEIVED",
      "value": 90.00,
      "externalReference": "order_id_123"
    }
  }'
```

## 📚 Referências

- [Documentação Asaas](https://docs.asaas.com)
- [API Reference](https://docs.asaas.com/reference)
- [Split de Pagamento](https://docs.asaas.com/reference/criar-cobranca-com-split)
- [Webhooks](https://docs.asaas.com/reference/webhooks)

---

**Última atualização**: 21/02/2026
