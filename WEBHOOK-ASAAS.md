# 🔔 Configuração do Webhook Asaas

## ✅ O QUE FOI CRIADO

### Webhook Central Inteligente
- ✅ **UM único webhook** para TODOS os restaurantes
- ✅ Identifica automaticamente qual restaurante pelo pedido
- ✅ Confirma pagamentos automaticamente
- ✅ Adiciona cashback automaticamente
- ✅ Atualiza status do pedido

---

## 📋 DADOS PARA CONFIGURAR NO ASAAS

### 1️⃣ Acesse o Painel do Asaas

**Sandbox (Testes):**
https://sandbox.asaas.com/webhooks

**Produção:**
https://www.asaas.com/webhooks

---

### 2️⃣ Criar Novo Webhook

Clique em **"Novo Webhook"** ou **"Adicionar Webhook"**

---

### 3️⃣ Preencha os Dados

**URL do Webhook:**
```
https://food.eliseus.com.br/api/webhooks/asaas
```

**Eventos a Marcar:**
- ✅ PAYMENT_RECEIVED (Pagamento PIX recebido)
- ✅ PAYMENT_CONFIRMED (Pagamento confirmado)
- ✅ PAYMENT_UPDATED (Pagamento atualizado)
- ✅ PAYMENT_OVERDUE (Pagamento vencido)
- ✅ PAYMENT_DELETED (Pagamento cancelado)

**Formato de Envio:**
- ✅ application/json

---

## 🧪 TESTAR O WEBHOOK

### Confirmar Pagamento Manual (Testes):

```bash
php artisan payment:confirm pizzaria-bella 40
```

**Parâmetros:**
- `pizzaria-bella` = ID do tenant
- `40` = ID do pedido

---

## 🔍 VERIFICAR SE ESTÁ FUNCIONANDO

### Ver Logs:
```bash
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log | grep "Webhook"
```

### Testar Endpoint:
```bash
curl https://food.eliseus.com.br/api/webhooks/asaas/test
```

---

**✅ PRONTO PARA USO!**
