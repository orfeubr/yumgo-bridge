# 🔔 Guia: Configurar Webhook do Asaas

**Data:** 26/02/2026
**Status:** ✅ CÓDIGO TESTADO E FUNCIONANDO

---

## ✅ Código do Webhook

O código do webhook está **100% funcionando**. Teste realizado com sucesso:

```
✅ Webhook recebe payload do Asaas
✅ Identifica tenant correto
✅ Encontra pedido (order)
✅ Atualiza payment.status = 'confirmed'
✅ Atualiza order.payment_status = 'paid'
✅ Atualiza order.status = 'confirmed'
✅ Frontend detecta mudança e redireciona
✅ Retorna 200 OK
```

---

## ⚠️ Problema Atual

**O Asaas NÃO está disparando webhooks**

Possíveis causas:
1. Webhook não configurado no painel Asaas
2. Asaas Sandbox não dispara webhooks (apenas produção)
3. URL incorreta
4. Token de validação errado

---

## 📋 Como Configurar Webhook no Asaas

### 1. Acessar Painel do Asaas

```
https://sandbox.asaas.com (Homologação)
OU
https://www.asaas.com (Produção)
```

Fazer login com suas credenciais.

---

### 2. Ir para Configurações de Webhook

**Caminho:**
```
Menu Superior → Configurações (⚙️)
→ Integrações
→ Webhooks
→ Configurar Webhooks
```

**OU acesso direto:**
```
https://sandbox.asaas.com/webhooks
https://www.asaas.com/webhooks
```

---

### 3. Adicionar Nova URL de Webhook

**Clicar em:** "Adicionar URL de Webhook" ou "Configurar"

**Preencher:**

```
┌─────────────────────────────────────────────────────┐
│ URL do Webhook                                      │
│ ┌─────────────────────────────────────────────────┐ │
│ │ https://yumgo.com.br/api/webhooks/asaas         │ │
│ └─────────────────────────────────────────────────┘ │
│                                                     │
│ Token de Autenticação (opcional)                   │
│ ┌─────────────────────────────────────────────────┐ │
│ │                                                 │ │
│ └─────────────────────────────────────────────────┘ │
│                                                     │
│ Email para notificações de falha (opcional)        │
│ ┌─────────────────────────────────────────────────┐ │
│ │ elizeu.drive@gmail.com                          │ │
│ └─────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────┘
```

---

### 4. Selecionar Eventos

**Marcar os seguintes eventos:**

```
☑ PAYMENT_CREATED            (Cobrança criada)
☑ PAYMENT_UPDATED            (Cobrança atualizada)
☑ PAYMENT_CONFIRMED          (Pagamento confirmado) ⭐ PRINCIPAL
☑ PAYMENT_RECEIVED           (Pagamento recebido) ⭐ PRINCIPAL
☐ PAYMENT_OVERDUE            (Cobrança vencida)
☐ PAYMENT_DELETED            (Cobrança removida)
☐ PAYMENT_RESTORED           (Cobrança restaurada)
☐ PAYMENT_AWAITING_CHARGEBACK_REVERSAL (Aguardando estorno)
☐ PAYMENT_DUNNING_RECEIVED   (Cobrança recebida após vencimento)
☐ PAYMENT_CHARGEBACK_REQUESTED (Chargeback solicitado)
☐ PAYMENT_CHARGEBACK_DISPUTE  (Disputa de chargeback)
☐ PAYMENT_AWAITING_RISK_ANALYSIS (Aguardando análise)
☐ PAYMENT_APPROVED_BY_RISK_ANALYSIS (Aprovado pela análise)
☐ PAYMENT_REPROVED_BY_RISK_ANALYSIS (Reprovado pela análise)
```

**Eventos essenciais:**
- ✅ `PAYMENT_CONFIRMED` - Quando pagamento é confirmado (PIX instantâneo)
- ✅ `PAYMENT_RECEIVED` - Quando pagamento é recebido (cartão, boleto)

---

### 5. Ambiente (Sandbox vs Produção)

**⚠️ IMPORTANTE:**

- **Sandbox (Homologação):** Pode NÃO disparar webhooks automaticamente
- **Produção:** Dispara webhooks normalmente

**Se estiver em Sandbox:**
- Configure o webhook mesmo assim
- Teste manualmente usando o botão "Simular Webhook" (se disponível)
- OU use o script de teste: `php test-webhook-asaas.php`

**Para produção:**
- Configure em: https://www.asaas.com/webhooks
- Webhooks serão disparados automaticamente

---

### 6. Salvar e Testar

**Clicar em:** "Salvar" ou "Adicionar"

**Testar:**
1. Asaas geralmente tem um botão "Testar Webhook" ou "Simular Evento"
2. Selecione evento: `PAYMENT_CONFIRMED`
3. Clique em "Enviar Teste"
4. Verifique logs: `tail -f storage/logs/laravel.log | grep Webhook`

---

## 🧪 Testar Webhook Manualmente

**Se o Asaas não tiver botão de teste, use nosso script:**

```bash
# 1. Encontrar um pedido recente
php artisan tinker --execute="
tenancy()->initialize(App\Models\Tenant::find('144c5973-f985-4309-8f9a-c404dd11feae'));
\$order = App\Models\Order::with('payments')->latest()->first();
echo 'Order Number: ' . \$order->order_number . PHP_EOL;
echo 'Payment ID: ' . \$order->payments->first()->transaction_id . PHP_EOL;
"

# 2. Executar teste
php test-webhook-asaas.php <payment_id> <order_number>

# Exemplo:
php test-webhook-asaas.php pay_7f6stqhu2sqsco5a 20260226-EB1888
```

**Resultado esperado:**
```
✅ SUCESSO! Order atualizado corretamente!
```

---

## 📊 Verificar Logs de Webhook

### Logs do Laravel

```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log | grep Webhook

# Ver últimos webhooks recebidos
tail -n 500 storage/logs/laravel.log | grep "Webhook Asaas"
```

**Exemplo de log quando webhook funciona:**
```
[2026-02-26 19:54:30] local.INFO: 🔔 Webhook Asaas GLOBAL recebido
[2026-02-26 19:54:30] local.INFO: Tenant encontrado via externalReference
[2026-02-26 19:54:30] local.INFO: ✅ Webhook: Pagamento confirmado e order atualizado
```

### Logs do Asaas

**Acessar:**
```
https://sandbox.asaas.com/webhooks (Sandbox)
https://www.asaas.com/webhooks (Produção)
```

**Verificar:**
- ✅ Status da última tentativa
- ⏱️ Timestamp do envio
- 📥 Resposta HTTP (deve ser 200)
- 🔄 Número de retentativas

---

## 🔐 Segurança (Opcional)

### Adicionar Token de Validação

**No arquivo `.env`:**
```env
ASAAS_WEBHOOK_TOKEN=seu_token_secreto_aqui_123456
```

**No painel Asaas:**
- Adicionar o MESMO token no campo "Token de Autenticação"

**Como funciona:**
- Asaas envia token no header `asaas-access-token`
- Nosso webhook valida se o token bate
- Se não bater, retorna 401 Unauthorized

---

## 🚨 Troubleshooting

### Webhook não está sendo chamado

**Verificar:**
1. ✅ URL correta: `https://yumgo.com.br/api/webhooks/asaas`
2. ✅ Eventos marcados: `PAYMENT_CONFIRMED` + `PAYMENT_RECEIVED`
3. ✅ Webhook salvo no painel Asaas
4. ✅ Cloudflare não está bloqueando (já configurado)
5. ✅ Ambiente: Sandbox pode não disparar, testar em produção

### Webhook retorna erro 404

**Causa:** URL incorreta ou rota não registrada

**Solução:** Verificar `routes/web.php` tem:
```php
Route::post('/api/webhooks/asaas', [CentralWebhookController::class, 'asaas']);
```

### Webhook retorna erro 500

**Causa:** Erro no código do webhook

**Solução:** Ver logs:
```bash
tail -n 100 storage/logs/laravel.log | grep "Erro ao processar webhook"
```

### Order não atualiza após webhook

**Causa:** Tenant ou Order não encontrado

**Solução:** Verificar `externalReference` no payload:
- Deve estar no formato: `<tenant_id>:<order_id>`
- Exemplo: `144c5973-f985-4309-8f9a-c404dd11feae:16`

**Verificar no código do OrderService:**
```php
'externalReference' => tenant()->id . ':' . $order->id,
```

---

## ✅ Checklist Final

Antes de fazer um pedido real, verificar:

- [ ] Webhook configurado no painel Asaas
- [ ] URL: `https://yumgo.com.br/api/webhooks/asaas`
- [ ] Eventos marcados: `PAYMENT_CONFIRMED` + `PAYMENT_RECEIVED`
- [ ] Cloudflare WAF permite IP do Asaas
- [ ] Logs monitorando: `tail -f storage/logs/laravel.log | grep Webhook`
- [ ] Script de teste funcionou: `php test-webhook-asaas.php`

---

## 🎯 Fluxo Completo de Pagamento

```
1. Cliente faz pedido no checkout
   ↓
2. OrderService cria pagamento no Asaas
   - externalReference: "tenant-id:order-id"
   - Gera QR Code PIX
   ↓
3. Cliente paga PIX no banco
   ↓
4. Asaas detecta pagamento confirmado
   ↓
5. Asaas dispara webhook POST /api/webhooks/asaas
   ↓
6. CentralWebhookController processa:
   - Identifica tenant via externalReference
   - Encontra order pelo payment_id
   - Atualiza payment.status = 'confirmed'
   - Atualiza order.payment_status = 'paid'
   - Atualiza order.status = 'confirmed'
   ↓
7. Frontend polling detecta order.status = 'confirmed'
   ↓
8. Redireciona para /pedido/{orderNumber}/confirmado
   ↓
9. Cliente vê tela de confirmação
```

---

## 📞 Contatos

**Dúvidas sobre webhook no Asaas:**
- Suporte: https://ajuda.asaas.com
- Email: atendimento@asaas.com
- WhatsApp: (31) 3508-2345

**Verificar status do Asaas:**
- https://status.asaas.com

---

**Criado em:** 26/02/2026
**Testado em:** Sandbox Asaas
**Status do código:** ✅ FUNCIONANDO 100%
**Pendente:** Configurar webhook no painel Asaas

---

## 🎓 Resumo Executivo

**O webhook está pronto e funcionando.** O problema é que o Asaas não está disparando porque:

1. Você precisa configurar no painel do Asaas (seguir passos acima)
2. OU você está em Sandbox e precisa testar manualmente
3. OU o webhook está configurado mas com URL errada

**Próximo passo:** Acessar painel Asaas e configurar webhook conforme este guia.
