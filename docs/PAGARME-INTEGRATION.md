# 💳 Integração Pagar.me - DeliveryPro

## 📋 Visão Geral

O DeliveryPro usa **Pagar.me** como gateway de pagamento principal para processar transações PIX e Cartão de Crédito/Débito, com **split automático** entre restaurante e plataforma.

## 🎯 Por que Pagar.me?

### Comparação de Custos REAL (Ticket Médio R$ 50)

| Método | Asaas | Pagar.me | Vencedor |
|--------|-------|----------|----------|
| **PIX (R$ 30)** | R$ 0,99 | R$ 0,29 | Pagar.me ⭐ |
| **PIX (R$ 50)** | R$ 0,99 | R$ 0,49 | Pagar.me ⭐ |
| **PIX (R$ 100)** | R$ 0,99 | R$ 0,99 | Empate |
| **Cartão (R$ 50)** | R$ 1,99 | R$ 2,49 | Asaas ⭐ |
| **Cartão (R$ 100)** | R$ 3,48 | R$ 4,99 | Asaas ⭐ |

**Cenário Real (1000 pedidos/mês, ticket R$ 50, 70% PIX):**
- Asaas: R$ 1.290/mês
- Pagar.me: R$ 1.093/mês
- **Economia: R$ 197/mês com Pagar.me** ⭐

**Pagar.me vence porque:**
- ✅ **Predominância de PIX** no delivery (60-80%)
- ✅ **Tickets médios/baixos** (R$ 30-80)
- ✅ **Split nativo** (sem custo adicional)
- ✅ **Antifraude robusto** (reduz chargebacks)
- ✅ **Dashboard moderno** + API v5
- ✅ **Melhor custo-benefício para delivery**

## 🔧 Configuração

### 1. Criar Conta Pagar.me

1. Acesse https://pagar.me
2. Crie conta empresarial
3. Complete o cadastro (documentos, dados bancários)

### 2. Obter Credenciais

No dashboard Pagar.me:
1. Vá em **Configurações → API Keys**
2. Copie:
   - **API Key** (sk_...)
   - **Encryption Key** (ek_...)

### 3. Configurar .env

```bash
# Pagar.me Payment Gateway
PAGARME_URL=https://api.pagar.me/core/v5
PAGARME_API_KEY=sk_test_... # Sandbox: sk_test_ | Produção: sk_live_
PAGARME_ENCRYPTION_KEY=ek_test_...
PAGARME_PLATFORM_RECIPIENT_ID=re_... # ID do recebedor da plataforma
PAGARME_WEBHOOK_TOKEN=seu_token_secreto_aqui
```

### 4. Criar Recebedor da Plataforma

```bash
php artisan tinker

$service = new \App\Services\PagarMeService();
$result = $service->createRecipient([
    'name' => 'YumGo Plataforma',
    'email' => 'financeiro@yumgo.com.br',
    'document' => '12345678000190', // CNPJ da plataforma
    'type' => 'company',
    'phone' => '11999999999',
    'bank_account' => [
        'holder_name' => 'YumGo LTDA',
        'holder_type' => 'company',
        'holder_document' => '12345678000190',
        'bank' => '001', // Código do banco
        'branch_number' => '0001',
        'branch_check_digit' => '0',
        'account_number' => '12345678',
        'account_check_digit' => '9',
        'type' => 'checking',
    ],
]);

// Copie o recipient_id e adicione ao .env
echo $result['id']; // re_xxxxxxxxxxxxx
```

## 🏪 Fluxo de Pagamento

### 1. Cliente Faz Pedido

```php
// OrderService cria pedido
$order = Order::create([
    'customer_id' => $customer->id,
    'total' => 100.00,
    'payment_method' => 'pix', // ou 'credit_card'
]);
```

### 2. Sistema Cria Transação no Pagar.me

```php
$pagarmeService = app(PagarMeService::class);

$payment = $pagarmeService->createPayment($order, [
    'payment_method' => 'pix',
]);

// Retorna:
// - order_id (Pagar.me)
// - pix_qr_code (base64)
// - pix_qr_code_url (imagem)
```

### 3. Split Automático

O split é configurado **na transação**:

```php
'split' => [
    [
        'recipient_id' => $tenant->pagarme_recipient_id, // Restaurante
        'amount' => 9700, // R$ 97,00 em centavos (97%)
        'type' => 'flat',
        'options' => [
            'charge_processing_fee' => true, // Restaurante paga taxas
            'liable' => true, // Responsável por chargebacks
        ],
    ],
    [
        'recipient_id' => config('services.pagarme.platform_recipient_id'), // Plataforma
        'amount' => 300, // R$ 3,00 em centavos (3%)
        'type' => 'flat',
        'options' => [
            'charge_remainder' => true, // Recebe resto (arredondamento)
        ],
    ],
]
```

### 4. Cliente Paga PIX

```php
// Frontend exibe QR Code
<img :src="payment.pix_qr_code_url" />
<p>{{ payment.pix_qr_code }}</p> // Copia e Cola
```

### 5. Webhook Confirma Pagamento

```php
// PagarMeWebhookController recebe evento
Route::post('/api/webhooks/pagarme', [PagarMeWebhookController::class, 'handle']);

// Eventos processados:
// - order.paid → Marca pedido como pago, processa cashback
// - charge.paid → Similar a order.paid
// - order.payment_failed → Marca como falhou
```

## 🔐 Webhook

### URL do Webhook

Configure no dashboard Pagar.me:

```
https://yumgo.com.br/api/webhooks/pagarme
```

### Validação de Assinatura

```php
// Pagar.me envia header X-Hub-Signature
$signature = $request->header('X-Hub-Signature'); // sha256=<hash>

// Validar
$payload = $request->getContent();
$expectedSignature = hash_hmac('sha256', $payload, $webhookToken);

if (!hash_equals($expectedSignature, str_replace('sha256=', '', $signature))) {
    abort(403, 'Invalid signature');
}
```

### Eventos

| Evento | Descrição | Ação |
|--------|-----------|------|
| `order.paid` | Pedido pago | Confirmar pedido, processar cashback |
| `charge.paid` | Cobrança paga | Confirmar pagamento |
| `order.payment_failed` | Pagamento falhou | Cancelar pedido |
| `charge.refunded` | Estornado | Estornar cashback |

## 🏦 Dados Bancários do Restaurante

### Criar Recebedor

No painel do restaurante (**Dados para Recebimento**):

1. Preencher dados pessoais/empresa
2. Preencher dados bancários:
   - Código do banco (001, 237, 341...)
   - Agência + dígito
   - Conta + dígito
   - Tipo (corrente/poupança)
3. Clicar **"Criar Recebedor Pagar.me"**

O sistema cria o recebedor automaticamente via API.

### Códigos de Bancos Principais

| Banco | Código |
|-------|--------|
| Banco do Brasil | 001 |
| Bradesco | 237 |
| Itaú | 341 |
| Santander | 033 |
| Caixa Econômica | 104 |
| Nubank | 260 |
| Inter | 077 |
| PicPay | 380 |

## 🧪 Testes

### Sandbox (Ambiente de Testes)

```bash
PAGARME_URL=https://api.pagar.me/core/v5
PAGARME_API_KEY=sk_test_...
```

### Cartões de Teste

| Número | Bandeira | Resultado |
|--------|----------|-----------|
| 4111111111111111 | Visa | Aprovado |
| 5555555555554444 | Mastercard | Aprovado |
| 4000000000000010 | Visa | Recusado |

### PIX de Teste

No sandbox, todos os PIX são aprovados automaticamente após 10 segundos.

## 📊 Monitoramento

### Logs

```bash
# Ver logs de pagamentos
tail -f storage/logs/laravel.log | grep "Pagar.me"

# Ver webhooks recebidos
tail -f storage/logs/laravel.log | grep "Webhook Pagar.me"
```

### Dashboard Filament

- **Admin Central**: Ver todos os recebedores configurados
- **Painel Restaurante**: Ver status da conta (Configurada, Pendente, etc)

## 🚨 Troubleshooting

### Erro: "Recipient not found"

```bash
# Verificar se restaurante tem recipient_id
php artisan tinker
$tenant = Tenant::where('slug', 'pizzaria-bella')->first();
echo $tenant->pagarme_recipient_id; // Deve ter valor
```

### Webhook não está sendo recebido

1. Verificar URL no dashboard Pagar.me
2. Verificar firewall/Cloudflare
3. Testar manualmente:

```bash
curl -X POST https://yumgo.com.br/api/webhooks/pagarme \
  -H "Content-Type: application/json" \
  -d '{"type":"order.paid","data":{"id":"or_test"}}'
```

### Pagamento não confirma

1. Verificar logs: `storage/logs/laravel.log`
2. Verificar webhook está configurado
3. Verificar split está correto (soma deve dar 100%)

## 🔄 Migração de Asaas para Pagar.me

### Para Restaurantes Existentes

```bash
# 1. Backup
php artisan backup:run

# 2. Adicionar novos campos
php artisan migrate

# 3. Configurar recebedor Pagar.me no painel
# (Não precisa migrar dados antigos do Asaas)
```

## 📚 Referências

- [Documentação Oficial Pagar.me](https://docs.pagar.me)
- [API Reference v5](https://docs.pagar.me/reference/api-reference)
- [Split de Pagamentos](https://docs.pagar.me/docs/split-de-pagamentos)
- [Webhooks](https://docs.pagar.me/docs/webhooks-1)

## ✅ Checklist de Produção

- [ ] Credenciais de produção configuradas (`sk_live_`, `ek_live_`)
- [ ] Recebedor da plataforma criado
- [ ] Webhook configurado e testado
- [ ] SSL/HTTPS ativo
- [ ] Logs monitorados
- [ ] Backup automático configurado
- [ ] Teste end-to-end (PIX + Cartão)

---

**Data:** 27/02/2026
**Versão:** 1.0.0
