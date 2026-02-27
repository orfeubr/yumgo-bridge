# 🏦 Status: Pagar.me Sandbox

**Data:** 27/02/2026

---

## ✅ O Que Foi Configurado

### Credenciais Adicionadas ao .env

```bash
PAGARME_URL=https://api.pagar.me/core/v5
PAGARME_API_KEY=sk_2ceaef4a4119491cbee57abf2f9c2d2f
PAGARME_ENCRYPTION_KEY=pk_ZL5EYKJSQaTbM1Bz
PAGARME_WEBHOOK_TOKEN=<gerado automaticamente>
```

✅ Cache limpo
✅ Credenciais testadas
✅ Permissões corrigidas

---

## ⚠️ Problema Encontrado

### Erro ao Criar Recebedor:

```json
{
  "status": 412,
  "message": "The account must have split settings enabled, in order to create a recipient"
}
```

**O que significa:**
A conta sandbox do Pagar.me **não tem Split de Pagamentos habilitado**.

Split é necessário para:
- Dividir pagamento entre restaurante e plataforma
- Criar recebedores (recipients)
- Processar comissões automaticamente

---

## 🔧 Como Resolver

### **1. Habilitar Split no Dashboard**

1. **Acesse:** https://dashboard.pagar.me/login
2. **Faça login** com as credenciais da conta sandbox
3. **Vá em:** Configurações → Integração → Split de Pagamentos
4. **Ative** a funcionalidade de Split
5. **Aguarde** alguns minutos para processar

### **2. Contatar Suporte (Se não encontrar a opção)**

**Email:** suporte@pagar.me
**Assunto:** Habilitar Split de Pagamentos - Conta Sandbox

**Mensagem:**
```
Olá,

Preciso habilitar a funcionalidade de Split de Pagamentos na minha conta sandbox para testes de integração.

API Key: sk_2ceaef4a4119491cbee57abf2f9c2d2f

Obrigado!
```

### **3. Criar Conta Real (Alternativa)**

Se precisar urgentemente:
- Crie uma conta **real** (não sandbox)
- Contas reais já vêm com Split habilitado
- Pode fazer testes mesmo sem validar documentos
- **URL:** https://dashboard.pagar.me/signup

---

## 🔄 Após Habilitar o Split

Execute este comando para criar o recebedor da plataforma:

```bash
php artisan tinker --execute="
\$service = new \App\Services\PagarMeService();
\$result = \$service->createRecipient([
    'name' => 'YumGo Plataforma LTDA',
    'email' => 'financeiro@yumgo.com.br',
    'document' => '12345678000190',
    'type' => 'company',
    'phone' => '+5511999999999',
    'bank_account' => [
        'holder_name' => 'YumGo Plataforma LTDA',
        'holder_type' => 'company',
        'holder_document' => '12345678000190',
        'bank' => '341',
        'branch_number' => '0001',
        'branch_check_digit' => '0',
        'account_number' => '12345678',
        'account_check_digit' => '9',
        'type' => 'checking',
    ],
]);

echo 'Recipient ID: ' . \$result['id'];
"
```

Ou use o script interativo:
```bash
php configure-pagarme.php
```

---

## 💼 Alternativa: Usar Asaas Enquanto Isso

O **Asaas já está 100% configurado** e funcionando!

```bash
✅ ASAAS_URL=https://sandbox.asaas.com/api/v3
✅ ASAAS_API_KEY=configurado
✅ ASAAS_PLATFORM_WALLET_ID=aea707c7-020c-449b-a820-80fedfc18e92
✅ Split automático funcionando
✅ Pronto para processar pagamentos
```

Você pode:
1. Usar Asaas agora para testes
2. Resolver o Pagar.me em paralelo
3. Trocar quando estiver pronto

---

## 📊 Comparação

| Item | Asaas (Atual) | Pagar.me (Aguardando Split) |
|------|---------------|------------------------------|
| Status | ✅ Funcionando | ⏳ Aguardando configuração |
| Split | ✅ Ativo | ❌ Precisa habilitar |
| Sandbox | ✅ Configurado | ✅ Configurado |
| Credenciais | ✅ Válidas | ✅ Válidas |
| Recebedor | ✅ Criado | ⏳ Aguarda split |

---

## 🎯 Próximos Passos

1. **URGENTE:** Habilitar Split no Pagar.me (dashboard ou suporte)
2. **Depois:** Criar recebedor da plataforma
3. **Testar:** Fazer pedido de teste com PIX sandbox
4. **Produção:** Quando pronto, trocar para credenciais live

---

## 📚 Documentação

- **Pagar.me Split:** https://docs.pagar.me/docs/split-de-pagamentos
- **Dashboard:** https://dashboard.pagar.me
- **Suporte:** suporte@pagar.me

---

## ✅ Resumo do Status

```
✅ Credenciais Pagar.me adicionadas
✅ Sistema preparado
✅ Asaas funcionando 100%
⏳ Aguardando: Habilitar Split no Pagar.me
```

**Quando habilitar o Split, me avise que eu termino a configuração!** 🚀
