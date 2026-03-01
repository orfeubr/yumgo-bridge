# 🔥 Flare - Monitoramento de Erros (Spatie)

## 🎯 Por Que Flare?

**Custo-Benefício Imbatível:**
- ✅ **€19/mês** (~R$ 110) vs $26-80/mês do Sentry
- ✅ **10.000 erros/mês** vs 5.000 do Sentry
- ✅ Focado 100% em Laravel
- ✅ Interface linda e simples
- ✅ Multi-tenant suportado
- ✅ Criado pela Spatie (mesma do laravel-permission)

## 📋 Configuração (5 minutos)

### 1️⃣ Criar Conta

1. Acesse: https://flareapp.io/register
2. Crie sua conta (€19/mês)
3. Crie um novo projeto: **DeliveryPro**

### 2️⃣ Obter API Key

No dashboard do Flare:
1. Acesse **Settings** → **Projects**
2. Copie a **Flare Key** (começa com `flare_...`)

### 3️⃣ Configurar .env

```bash
FLARE_KEY=flare_your_api_key_here
```

**Pronto!** Flare já está capturando erros automaticamente. 🎉

## 🏪 Contexto Multi-Tenant

O Flare **automaticamente** captura contexto do Laravel, incluindo:

- ✅ Tenant ID atual
- ✅ Request completo (URL, headers, payload)
- ✅ Session data
- ✅ User autenticado
- ✅ Stack trace completo
- ✅ SQL queries executadas
- ✅ Logs relacionados

**Adicionando contexto customizado:**

```php
// Em qualquer lugar do código
Flare::context('tenant_slug', tenant('slug'));
Flare::context('payment_gateway', tenant('payment_gateway'));
Flare::context('order_id', $order->id);
```

## 📊 Dashboard do Flare

**O que você vê:**

1. **Erros Agrupados** - Mesma exception = 1 grupo
2. **Tenant Tag** - Filtra por restaurante
3. **Timeline** - Quando os erros aconteceram
4. **Stack Trace** - Linha exata do erro
5. **Request Context** - O que o usuário estava fazendo
6. **SQL Queries** - Queries que rodaram antes do erro
7. **Breadcrumbs** - Histórico de ações do usuário

## 🔍 Filtrando por Tenant

No dashboard do Flare, você pode criar **filtros** para separar erros por restaurante:

1. **Tags**: `tenant_slug:marmitaria-gi`
2. **Tags**: `payment_gateway:pagarme`
3. **Tags**: `environment:production`

## 🚨 Notificações

Configure notificações para erros críticos:

1. **Email** - Receba email quando erro acontecer
2. **Slack** - Integração direta com Slack
3. **Discord** - Para equipe de desenvolvimento
4. **Webhooks** - Para sistemas customizados

## 📈 Melhorando Performance

**Evite enviar TUDO para o Flare:**

```php
// config/logging.php (já configurado no Laravel)

'flare' => [
    'driver' => 'flare',
    'level' => env('LOG_LEVEL_FLARE', 'error'), // Apenas errors, não debug
],
```

**Ignorar erros conhecidos:**

```php
// app/Exceptions/Handler.php

protected $dontReport = [
    \Illuminate\Auth\AuthenticationException::class,
    \Illuminate\Validation\ValidationException::class,
    // Adicione exceptions que você NÃO quer reportar
];
```

## 🎯 Casos de Uso

### 1. Erro no Checkout

**Contexto enviado:**
- Tenant: Marmitaria da Gi
- Customer ID: 15
- Cart Items: 3 produtos
- Payment Gateway: Pagar.me
- Total: R$ 45,00
- Stack trace: `OrderService.php:145`

**Você vê no Flare:**
- Linha exata do erro
- Todos os dados do pedido
- SQL queries executadas
- Timeline do que aconteceu antes

### 2. Erro na Emissão de NFC-e

**Contexto enviado:**
- Tenant: Parker Pizzaria
- Order ID: #1234
- SEFAZ Environment: production
- NCM: 19059090
- Stack trace: `SefazService.php:89`

**Você vê no Flare:**
- Resposta da SEFAZ
- XML enviado
- Certificado usado
- Retry attempts

### 3. Erro no Webhook Pagar.me

**Contexto enviado:**
- Tenant: Marmitaria da Gi
- Webhook Event: order.paid
- Transaction ID: txn_abc123
- Stack trace: `PagarMeWebhookController.php:67`

**Você vê no Flare:**
- Payload completo do webhook
- Validação da assinatura
- Estado do pedido antes/depois

## 💰 Custo Real (1000 erros/mês)

| Item | Custo |
|------|-------|
| Plano Base | €19/mês (~R$ 110) |
| Erros extras | R$ 0 (incluso 10.000) |
| **Total** | **R$ 110/mês** |

**vs Sentry:** Economia de R$ 60-400/mês! 💰

## 🔐 Segurança

**Dados sensíveis NÃO são enviados:**

```php
// Laravel automaticamente oculta:
- Passwords
- API Keys (qualquer campo com 'key', 'token', 'secret')
- Credit card data
- Campos no .env

// Para ocultar campos customizados:
'flare' => [
    'reporting' => [
        'anonymize_ips' => true,
        'report_query_bindings' => false, // Não enviar valores de queries
    ],
],
```

## 📚 Links Úteis

- **Dashboard**: https://flareapp.io/dashboard
- **Documentação**: https://flareapp.io/docs
- **Pricing**: https://flareapp.io/pricing
- **GitHub**: https://github.com/spatie/laravel-ignition

## ✅ Checklist Pós-Configuração

- [ ] Criar conta no Flare (€19/mês)
- [ ] Adicionar `FLARE_KEY` no `.env`
- [ ] Testar erro manualmente: `throw new \Exception('Test Flare');`
- [ ] Verificar erro no dashboard do Flare
- [ ] Configurar notificações (email/Slack)
- [ ] Criar filtros por tenant
- [ ] Adicionar contexto customizado se necessário

## 🎓 Dicas

1. **Use Tags** para filtrar erros por tenant
2. **Configure Slack** para notificações em tempo real
3. **Ignore erros conhecidos** (401, validação) para economizar quota
4. **Adicione contexto** em operações críticas (checkout, NFC-e, webhooks)
5. **Revise semanalmente** os erros mais frequentes

---

**Data de criação:** 01/03/2026
**Custo mensal:** €19 (~R$ 110)
**Limite de erros:** 10.000/mês
**Suporte:** https://flareapp.io/support
