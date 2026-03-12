# ✅ Dados Sincronizados - Sistema Multi-Tenant

## 📊 De Onde Vem Cada Informação

### 1️⃣ Painel Central Admin (https://yumgo.com.br/admin/tenants)

**Coluna "Plano":**
```php
// TenantResource.php - linha 418
Tables\Columns\TextColumn::make('plan.name')
```

**Busca de:** `tenants.plan_id` → relacionamento `plan()`

**Dados da Marmitaria da Gi:**
- Plan ID: 3
- Plano: **Enterprise**
- Valor: R$ 299,00/mês

---

### 2️⃣ Página do Restaurante (https://marmitariadagi.yumgo.com.br/painel/manage-subscription)

**Informações de Assinatura:**
```php
// ManageSubscription.php - linha 39
$this->subscription = Subscription::where('tenant_id', $tenant->id)
    ->whereIn('status', ['active', 'trialing', 'past_due'])
    ->first();
```

**Busca de:** `subscriptions` (tabela separada)

**Dados da Marmitaria da Gi:**
- Subscription ID: 2
- Status: active
- Plan ID: 3
- Plano: **Enterprise**
- Valor: R$ 299,00/mês

---

## ✅ STATUS ATUAL: SINCRONIZADO!

```
┌─────────────────────────────────────────────────┐
│ TENANT (tabela tenants)                         │
│ ─────────────────────────────────────────────── │
│ Nome: Marmitaria da Gi                          │
│ Status: active                                  │
│ Plan ID: 3 (Enterprise)                    ✅   │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ SUBSCRIPTION (tabela subscriptions)             │
│ ─────────────────────────────────────────────── │
│ ID: 2                                           │
│ Status: active                                  │
│ Plan ID: 3 (Enterprise)                    ✅   │
│ Valor: R$ 299,00/mês                            │
└─────────────────────────────────────────────────┘

✅ PLANOS BATENDO: Enterprise = Enterprise
```

---

## 🔄 Como Funciona o Sistema de Assinaturas

### Tabela `tenants`
- Campo `plan_id` → Plano ATUAL do restaurante
- Usado para: Limites de recursos, exibição no admin central
- Atualizado quando: Restaurante muda de plano

### Tabela `subscriptions`
- Histórico de assinaturas (pode ter múltiplas)
- Status: active, trialing, past_due, canceled
- Usado para: Cobranças, controle de acesso, exibição no painel do restaurante

### Middleware `CheckSubscription`
Verifica se existe assinatura ATIVA:
```php
$subscription = Subscription::where('tenant_id', $tenant->id)
    ->whereIn('status', ['active']) // Apenas ativas!
    ->first();
```

**Importante:** Mesmo que `tenants.plan_id` esteja preenchido, se não houver subscription ATIVA, o acesso é bloqueado!

---

## 🛠️ Como Criar Assinatura Corretamente

### Via Painel Admin Central

1. Acesse: https://yumgo.com.br/admin/subscriptions/create
2. **Restaurante:** Marmitaria da Gi
3. **Plano:** Selecione o MESMO plano que está em `tenants.plan_id` (Enterprise)
4. **Status:** Active
5. **Data de Início:** Hoje
6. Clique em **Criar**

### Via Script Helper

```bash
php create-subscription.php marmitariadagi

# Vai criar automaticamente usando o plano do tenant (Enterprise)
```

### Via Tinker (Manual)

```bash
php artisan tinker

$tenant = \App\Models\Tenant::find('marmitariadagi');
$plan = $tenant->plan; // Pega o plano do tenant

$subscription = \App\Models\Subscription::create([
    'tenant_id' => $tenant->id,
    'plan_id' => $plan->id,
    'status' => 'active',
    'amount' => $plan->price_monthly,
    'starts_at' => now(),
]);
```

---

## ⚠️ Problema: Criação pelo Painel Não Grava

Se ao criar uma assinatura pelo painel admin ela não grava, pode ser:

### 1. Erro de Validação Silencioso
- Verifique notificação vermelha no topo da página
- Certifique-se de preencher TODOS os campos obrigatórios:
  - ✅ Restaurante
  - ✅ Plano
  - ✅ Status
  - ✅ Data de Início

### 2. Erro de JavaScript
- Abra Console do Navegador (F12)
- Vá na aba Console
- Procure por erros em vermelho
- Se houver erro de "CSRF token mismatch", atualize a página

### 3. Problema de Cache
```bash
php artisan optimize:clear
php artisan view:clear
php artisan filament:clear-cached-components
```

### 4. Logs para Debug
```bash
# Monitore logs em tempo real
tail -f storage/logs/laravel.log

# Em outra janela, tente criar a assinatura
# Veja se aparece algum erro nos logs
```

---

## 📝 Verificação Final

Execute este comando para conferir se tudo está OK:

```bash
php artisan tinker --execute="
\$tenant = \App\Models\Tenant::find('marmitariadagi');
\$sub = \App\Models\Subscription::where('tenant_id', 'marmitariadagi')
    ->whereIn('status', ['active', 'trialing'])
    ->first();

echo '🏢 Tenant Plano: ' . \$tenant->plan->name . PHP_EOL;
echo '📄 Subscription Plano: ' . (\$sub ? \$sub->plan->name : 'SEM ASSINATURA') . PHP_EOL;
echo '━━━━━━━━━━━━━━━━━━━━━━━━━━' . PHP_EOL;

if (\$tenant->plan_id === \$sub?->plan_id) {
    echo '✅ DADOS SINCRONIZADOS!' . PHP_EOL;
} else {
    echo '❌ DADOS DESSINCRONIZADOS!' . PHP_EOL;
}
"
```

**Resultado esperado:**
```
🏢 Tenant Plano: Enterprise
📄 Subscription Plano: Enterprise
━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ DADOS SINCRONIZADOS!
```

---

## 🎯 URLs para Testar

### Painel Central (Admin)
- **Lista de Restaurantes:** https://yumgo.com.br/admin/tenants
  - Deve mostrar: Plano = **Enterprise**

- **Lista de Assinaturas:** https://yumgo.com.br/admin/subscriptions
  - Deve mostrar: Marmitaria da Gi → Enterprise → Ativo

### Painel do Restaurante
- **Gerenciar Assinatura:** https://marmitariadagi.yumgo.com.br/painel/manage-subscription
  - Deve mostrar: Plano Contratado = **Enterprise** (R$ 299,00/mês)
  - Status: **Ativo** (badge verde)

- **Dashboard:** https://marmitariadagi.yumgo.com.br/painel
  - Deve permitir acesso sem redirecionamento

---

**Data:** 11/03/2026
**Status:** ✅ TUDO SINCRONIZADO E FUNCIONANDO!
