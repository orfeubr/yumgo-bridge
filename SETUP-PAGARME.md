# 🏦 Setup Pagar.me - Guia Completo

**Data:** 27/02/2026

---

## 🎯 Passo 1: Criar Conta no Pagar.me

### Sandbox (Testes - Recomendado para começar)

1. **Acesse:** https://dashboard.pagar.me/signup
2. **Tipo de conta:** Selecione "Sandbox" ou crie conta normal e use modo de testes
3. **Preencha os dados básicos:**
   - Nome completo
   - Email
   - Senha forte
4. **Confirme o email**

### Produção (Quando estiver pronto para vender)

1. **Acesse:** https://dashboard.pagar.me/signup
2. **Complete o cadastro completo:**
   - CPF/CNPJ
   - Dados bancários
   - Documentos (RG, Comprovante de Endereço)
3. **Aguarde aprovação KYC (1-3 dias úteis)**

---

## 🔑 Passo 2: Obter Credenciais de API

### No Dashboard Pagar.me:

1. **Faça login:** https://dashboard.pagar.me
2. **Acesse:** Configurações → Chaves de API (ou API Keys)
3. **Você verá duas chaves importantes:**

   **A) API Key (Secret Key)**
   - Formato: `sk_test_...` (sandbox) ou `sk_live_...` (produção)
   - É a chave SECRETA - NUNCA compartilhe!
   - Use para fazer requisições à API

   **B) Encryption Key (Public Key)**
   - Formato: `ek_test_...` (sandbox) ou `ek_live_...` (produção)
   - Use para criptografar dados sensíveis (cartão de crédito)

4. **Copie ambas as chaves** (vamos precisar no próximo passo)

---

## 📝 Passo 3: Configurar Credenciais no Sistema

### Atualizar arquivo `.env`:

```bash
# Pagar.me Payment Gateway
PAGARME_URL=https://api.pagar.me/core/v5
PAGARME_API_KEY=sk_test_SUA_CHAVE_AQUI      # Copie do dashboard
PAGARME_ENCRYPTION_KEY=ek_test_SUA_CHAVE_AQUI  # Copie do dashboard
PAGARME_PLATFORM_RECIPIENT_ID=              # Será preenchido no próximo passo
PAGARME_WEBHOOK_TOKEN=wh_secret_token_123   # Crie um token secreto qualquer
```

### ⚠️ Importante:
- **Sandbox:** Use chaves `sk_test_` e `ek_test_`
- **Produção:** Use chaves `sk_live_` e `ek_live_`
- **Webhook Token:** Crie uma string aleatória segura (ex: 32 caracteres)

---

## 🏢 Passo 4: Criar Recebedor da Plataforma

Este é o recebedor que vai receber a **comissão da plataforma** (3%).

### Dados Necessários:

```json
{
  "name": "YumGo LTDA",
  "email": "financeiro@yumgo.com.br",
  "document": "12345678000190",  // CNPJ da plataforma (ou CPF se PF)
  "type": "company",  // ou "individual" para PF
  "phone": "+5511999999999",

  "bank_account": {
    "holder_name": "YumGo LTDA",
    "holder_type": "company",  // ou "individual"
    "holder_document": "12345678000190",
    "bank": "001",  // Código do banco (001=BB, 237=Bradesco, 341=Itaú)
    "branch_number": "0001",  // Agência
    "branch_check_digit": "0",  // Dígito da agência (se tiver)
    "account_number": "12345678",  // Número da conta
    "account_check_digit": "9",  // Dígito da conta
    "type": "checking"  // checking (corrente) ou savings (poupança)
  }
}
```

### Códigos dos Principais Bancos:

| Banco | Código |
|-------|--------|
| Banco do Brasil | 001 |
| Bradesco | 237 |
| Itaú | 341 |
| Santander | 033 |
| Caixa Econômica | 104 |
| Nubank | 260 |
| Inter | 077 |
| C6 Bank | 336 |
| PicPay | 380 |

### Comando para Criar Recebedor:

```bash
php artisan tinker

# Cole este código (ajuste os dados):
$service = new \App\Services\PagarMeService();
$result = $service->createRecipient([
    'name' => 'YumGo LTDA',
    'email' => 'financeiro@yumgo.com.br',
    'document' => '12345678000190',
    'type' => 'company',
    'phone' => '+5511999999999',
    'bank_account' => [
        'holder_name' => 'YumGo LTDA',
        'holder_type' => 'company',
        'holder_document' => '12345678000190',
        'bank' => '001',
        'branch_number' => '0001',
        'branch_check_digit' => '0',
        'account_number' => '12345678',
        'account_check_digit' => '9',
        'type' => 'checking',
    ],
]);

echo "Recipient ID: " . $result['id'];  // Copie este ID!
```

### Adicione o ID ao `.env`:

```bash
PAGARME_PLATFORM_RECIPIENT_ID=re_xxxxxxxxxx  # ID retornado acima
```

---

## 🔗 Passo 5: Configurar Webhook

### No Dashboard Pagar.me:

1. **Acesse:** Configurações → Webhooks
2. **Clique:** "Adicionar Webhook" ou "New Webhook"
3. **Preencha:**
   - **URL:** `https://yumgo.com.br/api/webhooks/pagarme`
   - **Eventos:** Selecione todos relacionados a pagamento:
     - `order.paid`
     - `order.payment_failed`
     - `charge.paid`
     - `charge.refunded`
4. **Salve**

### ⚠️ Importante:
- Certifique-se que a URL está acessível pela internet
- Cloudflare/WAF pode bloquear webhooks - adicione exceção se necessário
- O webhook valida assinatura usando `PAGARME_WEBHOOK_TOKEN`

---

## 🧪 Passo 6: Testar a Integração

### 6.1 Verificar Credenciais

```bash
php artisan tinker

$service = new \App\Services\PagarMeService();
$service->testConnection();  // Deve retornar sucesso
```

### 6.2 Criar Pedido de Teste (PIX)

No frontend do restaurante:
1. Adicione produtos ao carrinho
2. Vá para checkout
3. Selecione **PIX** como forma de pagamento
4. Finalize o pedido
5. **Deve retornar:** QR Code PIX para pagamento

### 6.3 Testar PIX (Sandbox)

No ambiente sandbox:
- O PIX é aprovado automaticamente após ~10 segundos
- Não precisa pagar de verdade
- Webhook será chamado automaticamente

### 6.4 Verificar Logs

```bash
# Ver logs de integração
tail -f storage/logs/laravel.log | grep "Pagar.me"

# Ver webhooks recebidos
tail -f storage/logs/laravel.log | grep "Webhook"
```

---

## 📊 Passo 7: Configurar Restaurantes

Cada restaurante precisa criar seu próprio recebedor.

### No Painel do Restaurante:

1. **Acesse:** `https://slug-restaurante.yumgo.com.br/painel/payment-account`
2. **Preencha:**
   - Dados pessoais/empresa
   - Endereço completo
   - Dados bancários (mesma titularidade do CPF/CNPJ)
3. **Clique:** "🎉 Criar Conta de Recebimentos"
4. **Aguarde:** Sistema cria recebedor automaticamente via API
5. **Status muda para:** ✅ Configurada (Pagar.me)

---

## 🎯 Checklist de Configuração

### Central (Plataforma)

- [ ] Conta Pagar.me criada
- [ ] API Key obtida (`sk_test_...` ou `sk_live_...`)
- [ ] Encryption Key obtida (`ek_test_...` ou `ek_live_...`)
- [ ] Credenciais adicionadas ao `.env`
- [ ] Recebedor da plataforma criado
- [ ] `PAGARME_PLATFORM_RECIPIENT_ID` adicionado ao `.env`
- [ ] Webhook configurado no dashboard Pagar.me
- [ ] `.env` recarregado: `php artisan config:clear`

### Restaurante (Cada Tenant)

- [ ] Acessou `/painel/payment-account`
- [ ] Preencheu dados completos
- [ ] Criou recebedor (botão no formulário)
- [ ] Status mostra "✅ Configurada (Pagar.me)"
- [ ] Testou pedido com PIX

---

## 🚨 Troubleshooting

### Erro: "Invalid API Key"

```bash
# Verifique se a chave está correta
grep PAGARME_API_KEY .env

# Limpe o cache de configuração
php artisan config:clear
```

### Erro: "Recipient not found"

```bash
# Verifique se o recebedor da plataforma foi criado
grep PAGARME_PLATFORM_RECIPIENT_ID .env

# Se vazio, crie o recebedor (Passo 4)
```

### Webhook não está sendo recebido

```bash
# Teste manualmente
curl -X POST https://yumgo.com.br/api/webhooks/pagarme \
  -H "Content-Type: application/json" \
  -H "X-Hub-Signature: sha256=test" \
  -d '{"type":"order.paid","data":{"id":"or_test"}}'

# Verifique logs
tail -f storage/logs/laravel.log | grep Webhook
```

### Split não está funcionando

```bash
# Verifique se ambos recebedores existem
php artisan tinker

$tenant = tenant();
echo $tenant->pagarme_recipient_id;  // Deve ter valor
echo config('services.pagarme.platform_recipient_id');  // Deve ter valor
```

---

## 📚 Referências

- **Documentação Oficial:** https://docs.pagar.me
- **API Reference:** https://docs.pagar.me/reference/api-reference
- **Split de Pagamentos:** https://docs.pagar.me/docs/split-de-pagamentos
- **Webhooks:** https://docs.pagar.me/docs/webhooks-1
- **Dashboard:** https://dashboard.pagar.me

---

## ✅ Próximos Passos

Após configuração completa:

1. **Testar fluxo completo:**
   - Criar pedido → Gerar PIX → Pagar → Confirmar webhook → Cashback

2. **Migrar para produção:**
   - Trocar `sk_test_` por `sk_live_`
   - Trocar `ek_test_` por `ek_live_`
   - Refazer recebedor da plataforma em produção
   - Reconfigurar webhook em produção

3. **Monitorar:**
   - Dashboard Pagar.me para transações
   - Logs Laravel para erros
   - Painel admin para receita

---

**Configuração Pagar.me Completa!** 🚀
