# 🎯 Plano de Integração Asaas - Passo a Passo

## ✅ ETAPAS CONCLUÍDAS

- [x] **1. Sub-contas Asaas criadas** (5/5 restaurantes)
- [x] **2. Token de webhook gerado** e salvo no .env
- [x] **3. Endpoint webhook configurado** (/api/v1/webhooks/asaas)
- [x] **4. Autenticação Sanctum corrigida** (rota payment protegida)
- [x] **5. Documentação completa** (WEBHOOK-ASAAS.md)

---

## ⏳ PRÓXIMAS ETAPAS (FAZER AGORA)

### **ETAPA 3: Configurar Webhook no Painel do Asaas**
⏱️ **Tempo:** 5 minutos
🔴 **Prioridade:** CRÍTICA

#### Passo a passo:

1. Acesse: https://sandbox.asaas.com/login
   - Faça login com suas credenciais

2. Vá em: **Configurações → Integrações → Webhooks**
   - OU acesse direto: https://sandbox.asaas.com/config/webhooks

3. Clique em **"Adicionar Webhook"**

4. Preencha:
   ```
   Nome: DeliveryPro - Produção

   URL: https://pizzaria-bella.eliseus.com.br/api/v1/webhooks/asaas

   Token: 31883ed23a392fe169b23bf684c56e1fab6a941f4a921e54790d45237c2b61b8

   Eventos (marque TODOS):
   ☑️ PAYMENT_CREATED
   ☑️ PAYMENT_UPDATED
   ☑️ PAYMENT_CONFIRMED ⭐
   ☑️ PAYMENT_RECEIVED ⭐
   ☑️ PAYMENT_OVERDUE
   ☑️ PAYMENT_DELETED
   ☑️ PAYMENT_RESTORED
   ☑️ PAYMENT_REFUNDED

   ☑️ Enviar e-mail em caso de erro
   ```

5. Clique em **"Salvar"**

6. ✅ **Verificar:** O webhook deve aparecer na lista como "Ativo"

---

### **ETAPA 4: Testar Pagamento PIX Completo**
⏱️ **Tempo:** 10 minutos
🟡 **Prioridade:** ALTA

#### Passo a passo:

**4.1. Criar Pedido**

1. Acesse: https://pizzaria-bella.eliseus.com.br/login
2. Faça login (ou registre-se se não tiver conta)
3. Adicione produtos ao carrinho:
   - Pelo menos 2 produtos diferentes
   - Total acima de R$ 30,00 (para testar cashback)
4. Clique em "Finalizar Pedido"

**4.2. Escolher PIX**

1. Na tela de checkout:
   - Preencha endereço de entrega
   - Selecione **PIX** como forma de pagamento
   - Clique em "Confirmar Pedido"

2. ✅ **Verificar:**
   - Sistema cria pedido
   - Redireciona para `/pedido/{id}/pagamento`
   - Mostra QR Code PIX
   - Mostra código PIX Copia e Cola

**4.3. Simular Pagamento no Asaas**

1. Acesse painel do Asaas: https://sandbox.asaas.com/myFinance/index

2. Encontre a cobrança recém-criada:
   - Status: "Pendente"
   - Valor: igual ao total do pedido
   - Tipo: PIX

3. Clique na cobrança

4. Clique em **"Marcar como recebido"** (ou "Receber")

5. Confirme a ação

**4.4. Verificar Webhook Automático**

1. ✅ Asaas envia webhook automaticamente
2. ✅ Sistema recebe notificação
3. ✅ Pedido é confirmado
4. ✅ Status muda para "confirmed"
5. ✅ Cliente é redirecionado para `/pedido/{id}/confirmado`

**4.5. Comandos para Verificar:**

```bash
# Ver logs do webhook
tail -f storage/logs/laravel.log | grep -i webhook

# Verificar status do pedido
php artisan tinker
$order = App\Models\Order::latest()->first();
echo "Status: " . $order->status . "\n";
echo "Payment Status: " . $order->payment_status . "\n";

# Verificar pagamento
$payment = $order->payments()->latest()->first();
echo "Payment Status: " . $payment->status . "\n";
echo "Paid At: " . $payment->paid_at . "\n";
```

---

### **ETAPA 5: Validar Cashback Automático**
⏱️ **Tempo:** 5 minutos
🟢 **Prioridade:** MÉDIA

#### Passo a passo:

**5.1. Verificar Cashback Ganho**

1. Acesse: https://pizzaria-bella.eliseus.com.br/
2. Clique no **nome do usuário** (canto superior direito)
3. Verifique o saldo de cashback:
   - Deve aparecer o valor ganho (ex: 5% do pedido)

**5.2. Verificar no Banco de Dados:**

```bash
php artisan tinker

# Ver último pedido
$order = App\Models\Order::latest()->first();
echo "Cashback Earned: R$ " . number_format($order->cashback_earned, 2) . "\n";

# Ver saldo do cliente
$customer = $order->customer;
echo "Cliente: " . $customer->name . "\n";
echo "Saldo: R$ " . number_format($customer->cashback_balance, 2) . "\n";

# Ver transações de cashback
$transactions = App\Models\CashbackTransaction::latest()->take(5)->get();
foreach ($transactions as $t) {
    echo "- " . $t->type . " | R$ " . $t->amount . " | " . $t->description . "\n";
}
```

**5.3. Testar Uso do Cashback:**

1. Faça um novo pedido
2. Na tela de checkout, deve aparecer:
   - "Você tem R$ X,XX de cashback"
   - Campo para informar quanto quer usar
3. Use parte ou todo o cashback
4. Total do pedido deve diminuir

---

### **ETAPA 6: Verificar Split de Pagamento (97% + 3%)**
⏱️ **Tempo:** 5 minutos
🟢 **Prioridade:** MÉDIA

#### Passo a passo:

**6.1. Acessar Painel do Asaas**

1. Acesse: https://sandbox.asaas.com/myFinance/index

2. Encontre o pagamento confirmado

3. Clique na cobrança

4. Vá em **"Detalhes" → "Split de pagamento"**

**6.2. Verificar Divisão:**

Para um pedido de **R$ 100,00**:

```
✅ Restaurante (Sub-conta): R$ 97,00 (97%)
✅ Plataforma (Você): R$ 3,00 (3%)
```

**6.3. Verificar Saldos:**

```bash
# Acessar conta da plataforma
https://sandbox.asaas.com/myFinance/index

# Verificar saldo recebido
Ir em: "Minha Conta" → "Extratos"
Filtrar: "Recebimentos via Split"
```

**6.4. Verificar Sub-conta do Restaurante:**

```bash
# No painel do Asaas
Ir em: "Minha Conta" → "Sub-contas"
Clicar em: "Pizzaria Bella"
Ver: "Extrato" → Deve mostrar R$ 97,00 recebido
```

---

### **ETAPA 7: Preparar para Produção** 🎯
⏱️ **Tempo:** 15 minutos
🔴 **Prioridade:** ALTA

#### Passo a passo:

**7.1. Migrar do Sandbox para Produção**

1. Criar conta **PRODUÇÃO** no Asaas:
   - Acesse: https://www.asaas.com/cadastro
   - Complete o cadastro
   - Valide documentos

2. Gerar nova API Key de **PRODUÇÃO**:
   - Acesse: https://www.asaas.com/config/api
   - Copie a API Key de produção

3. Atualizar `.env`:
   ```bash
   ASAAS_URL=https://api.asaas.com/v3  # Remover 'sandbox'
   ASAAS_API_KEY=sua_api_key_de_producao
   ASAAS_PLATFORM_WALLET_ID=seu_wallet_id_producao
   ```

4. Recriar sub-contas em produção:
   ```bash
   php artisan tinker

   # Limpar asaas_account_id
   App\Models\Tenant::query()->update(['asaas_account_id' => null]);

   # Recriar sub-contas
   $asaas = app(App\Services\AsaasService::class);
   $tenants = App\Models\Tenant::all();
   foreach ($tenants as $tenant) {
       $walletId = $asaas->createSubAccount($tenant);
       if ($walletId) {
           $tenant->update(['asaas_account_id' => $walletId]);
       }
   }
   ```

5. Reconfigurar webhook em PRODUÇÃO:
   - Acesse: https://www.asaas.com/config/webhooks
   - Adicionar webhook com mesma configuração

**7.2. Testes em Produção**

1. Fazer pedido real com PIX
2. Pagar de verdade (valor baixo, ex: R$ 10,00)
3. Verificar webhook
4. Verificar split
5. Verificar cashback

**7.3. Monitoramento**

```bash
# Criar comando para monitorar
php artisan make:command MonitorPayments

# Adicionar ao cron (rodar a cada 5 minutos)
* * * * * php artisan schedule:run
```

---

## 📊 CHECKLIST FINAL

Antes de considerar 100% pronto:

### Sandbox (Testes)
- [ ] Webhook configurado no painel Asaas Sandbox
- [ ] Pedido criado com PIX
- [ ] QR Code gerado corretamente
- [ ] Pagamento simulado no Asaas
- [ ] Webhook recebido (200 OK)
- [ ] Pedido confirmado automaticamente
- [ ] Cashback creditado ao cliente
- [ ] Split 97%/3% funcionando
- [ ] Cliente consegue usar cashback em novo pedido

### Produção
- [ ] API Key de produção configurada
- [ ] Sub-contas recriadas em produção
- [ ] Webhook configurado em produção
- [ ] Teste com pagamento real (R$ 10,00)
- [ ] Monitoramento ativo
- [ ] Logs funcionando
- [ ] Backup dos dados

---

## 🚨 TROUBLESHOOTING

### Problema: Webhook não dispara

**Solução:**
1. Verificar URL no painel Asaas
2. Testar manualmente: `/tmp/test-webhook.sh`
3. Verificar token: `grep ASAAS_WEBHOOK_TOKEN .env`

### Problema: Split não acontece

**Solução:**
1. Verificar se tenant tem `asaas_account_id`
2. Ver logs do Asaas
3. Verificar AsaasService::createPayment()

### Problema: Cashback não credita

**Solução:**
1. Verificar CashbackService
2. Ver tabela `cashback_transactions`
3. Verificar regras de cashback

---

## 📞 PRÓXIMO PASSO

**AGORA:** Configure o webhook no painel do Asaas (Etapa 3)

**URL:** https://sandbox.asaas.com/config/webhooks

**Depois me avise quando terminar para prosseguirmos para Etapa 4!** ✅
