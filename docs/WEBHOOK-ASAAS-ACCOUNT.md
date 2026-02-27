# Webhook Asaas - Aprovação Automática de Conta

## 📋 Como Funciona

Quando você cadastra os dados de recebimento no painel, o sistema:

1. ✅ Cria uma **sub-conta** no Asaas via API
2. ⏳ Status fica **"Aguardando Aprovação"**
3. 🔔 Asaas analisa os dados (1-3 dias úteis)
4. ✅ Quando **aprovado**, Asaas envia webhook
5. 🚀 Sistema **atualiza automaticamente** o status
6. 💰 Restaurante pode começar a receber!

## 🔗 URL do Webhook

Configure no painel do Asaas:

```
https://food.eliseus.com.br/api/webhooks/asaas/account
```

## 📡 Eventos Tratados

### `ACCOUNT_STATUS_UPDATED`

Enviado quando a conta muda de status:
- `AWAITING_APPROVAL` → Aguardando aprovação
- `ACTIVE` → Conta aprovada ✅
- `REJECTED` → Conta rejeitada ❌

## 🎯 Payload do Webhook

```json
{
  "event": "ACCOUNT_STATUS_UPDATED",
  "account": {
    "id": "4a4751de-55c5-42e2-be86-6174058576f7",
    "status": "ACTIVE",
    "name": "Marmitaria da Gi",
    "email": "contato@marmitariadagi.com.br"
  }
}
```

## ⚙️ Configuração no Asaas

### Produção

1. Acesse: https://www.asaas.com/config/webhooks
2. Clique em **"Adicionar Webhook"**
3. **URL:** `https://food.eliseus.com.br/api/webhooks/asaas/account`
4. **Eventos:** Marque `ACCOUNT_STATUS_UPDATED`
5. Salve

### Sandbox (Testes)

1. Acesse: https://sandbox.asaas.com/config/webhooks
2. Clique em **"Adicionar Webhook"**
3. **URL:** `https://food.eliseus.com.br/api/webhooks/asaas/account`
4. **Eventos:** Marque `ACCOUNT_STATUS_UPDATED`
5. Salve

## 🧪 Testar o Webhook

### Método 1: Via Asaas (Sandbox)

No painel do Asaas sandbox, você pode enviar um webhook de teste.

### Método 2: Manualmente (curl)

```bash
curl -X POST https://food.eliseus.com.br/api/webhooks/asaas/account \
  -H "Content-Type: application/json" \
  -d '{
    "event": "ACCOUNT_STATUS_UPDATED",
    "account": {
      "id": "4a4751de-55c5-42e2-be86-6174058576f7",
      "status": "ACTIVE"
    }
  }'
```

### Método 3: Aprovar Manualmente (Sandbox)

```bash
php artisan tinker

# Dentro do tinker:
$tenant = App\Models\Tenant::where('id', 'marmitaria-gi')->first();
$tenant->update(['asaas_status' => 'APPROVED']);
```

## 📊 O Que Acontece Quando Aprovado

1. ✅ Status atualizado para **"APPROVED"**
2. 🎉 Painel mostra mensagem de sucesso verde
3. 💳 Cards de PIX, Cartão aparecem
4. 💰 Pedidos começam a gerar cobranças automaticamente
5. 📧 TODO: Enviar email de confirmação (a implementar)

## 🔍 Logs e Debug

Ver logs do webhook:

```bash
tail -f storage/logs/laravel.log | grep "Webhook Asaas"
```

Verificar status da conta:

```bash
php artisan tinker
$tenant = App\Models\Tenant::find('marmitaria-gi');
echo $tenant->asaas_status; // APPROVED, PENDING_APPROVAL, REJECTED
```

## 🚨 Troubleshooting

### Webhook não está chegando?

1. **Verificar URL no Asaas:** Certifique-se que está configurado corretamente
2. **Testar manualmente:** Use curl para testar
3. **Ver logs:** `tail -f storage/logs/laravel.log`
4. **Firewall:** Certifique-se que Asaas pode acessar a URL

### Status não atualiza?

1. Verificar se `asaas_account_id` está correto no banco
2. Ver logs do webhook
3. Testar manualmente a aprovação

## 📝 Próximos Passos (TODO)

- [ ] Enviar email quando conta for aprovada
- [ ] Enviar email quando conta for rejeitada (com motivo)
- [ ] Notificação no painel (database notifications)
- [ ] SMS de confirmação
- [ ] Webhook para outros eventos (PAYMENT_RECEIVED, etc)

---

**Última atualização:** 22/02/2026
