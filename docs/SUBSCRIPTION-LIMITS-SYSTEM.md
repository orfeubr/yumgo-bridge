# Sistema de Limites de Assinatura (Subscription Limits)

Sistema completo de limites baseados no plano de assinatura do restaurante.

## 📋 Visão Geral

O sistema controla automaticamente:
- **Máximo de Produtos**: Quantos produtos o restaurante pode cadastrar
- **Máximo de Pedidos/Mês**: Quantos pedidos podem ser processados mensalmente
- **Funcionalidades (Features)**: Recursos disponíveis por plano

## 🎯 Configuração de Limites

### Admin Central: Criar/Editar Plano

**Arquivo:** `app/Filament/Admin/Resources/PlanResource.php`

**Campos configuráveis:**
- `max_products` (nullable): Número máximo de produtos (null = ilimitado)
- `max_orders_per_month` (nullable): Máximo de pedidos/mês (null = ilimitado)
- `features` (array): Lista de funcionalidades incluídas

**Exemplo:**
```php
// Plano Starter
'max_products' => 50,
'max_orders_per_month' => 100,
'features' => ['cashback', 'cupons', 'whatsapp'],

// Plano Pro
'max_products' => 200,
'max_orders_per_month' => 500,
'features' => ['cashback', 'cupons', 'whatsapp', 'relatorios', 'api'],

// Plano Enterprise
'max_products' => null, // Ilimitado
'max_orders_per_month' => null, // Ilimitado
'features' => ['cashback', 'cupons', 'whatsapp', 'relatorios', 'api', 'white_label'],
```

## 🔒 Verificações de Limite

### Trait: HasSubscriptionLimits

**Arquivo:** `app/Models/Concerns/HasSubscriptionLimits.php`

O `Tenant` model usa este trait que fornece métodos:

#### 1. `canCreateProduct(): bool`
Verifica se pode cadastrar mais produtos.

```php
$tenant = tenancy()->tenant;

if (!$tenant->canCreateProduct()) {
    // Bloqueado - limite atingido
}
```

**Lógica:**
- Busca plano ativo
- Se `max_products = null` → sempre permite (ilimitado)
- Se `max_products = N` → compara com total de produtos cadastrados
- Retorna `true` se pode criar, `false` se atingiu limite

#### 2. `canCreateOrder(): bool`
Verifica se pode processar mais pedidos este mês.

```php
$tenant = tenancy()->tenant;

if (!$tenant->canCreateOrder()) {
    // Bloqueado - limite mensal atingido
}
```

**Lógica:**
- Busca plano ativo
- Se `max_orders_per_month = null` → sempre permite (ilimitado)
- Se `max_orders_per_month = N` → compara com pedidos deste mês
- Retorna `true` se pode criar, `false` se atingiu limite

#### 3. `hasFeature(string $feature): bool`
Verifica se o plano tem uma funcionalidade específica.

```php
if ($tenant->hasFeature('api')) {
    // Plano tem acesso à API
}
```

#### 4. `usageStats(): array`
Retorna estatísticas de uso vs limites.

```php
$stats = $tenant->usageStats();
// [
//     'products' => [
//         'current' => 45,
//         'limit' => 50,
//         'percentage' => 90,
//     ],
//     'orders_this_month' => [
//         'current' => 230,
//         'limit' => 500,
//         'percentage' => 46,
//     ],
// ]
```

## 🛡️ Bloqueios Implementados

### 1. Filament Admin (Painel do Restaurante)

#### ProductResource

**Arquivo:** `app/Filament/Restaurant/Resources/ProductResource.php`

**Método `canCreate()`:**
- Verifica `$tenant->canCreateProduct()`
- Se limite atingido:
  - Bloqueia botão "Novo Produto"
  - Exibe notificação persistente
  - Oferece link para upgrade

**Badge no menu:**
- Exibe contador: `45/50` (uso/limite)
- Cor:
  - Verde: < 80%
  - Amarelo: 80-99%
  - Vermelho: 100%

#### OrderResource

**Arquivo:** `app/Filament/Restaurant/Resources/OrderResource.php`

**Método `canCreate()`:**
- Verifica `$tenant->canCreateOrder()`
- Se limite mensal atingido:
  - Bloqueia botão "Novo Pedido"
  - Exibe notificação persistente
  - Oferece link para upgrade

**Badge no menu:**
- Exibe contador mensal: `230/500`
- Cor:
  - Azul: < 80%
  - Amarelo: 80-99%
  - Vermelho: 100%

### 2. API REST (Mobile/Frontend)

#### POST /api/v1/orders

**Arquivo:** `app/Http/Controllers/Api/OrderController.php`

**Validação no início do `store()`:**
```php
if ($tenant && !$tenant->canCreateOrder()) {
    return response()->json([
        'message' => 'O restaurante atingiu o limite de pedidos deste mês.',
        'error' => 'order_limit_reached',
        'limit_reached' => true,
    ], 402); // HTTP 402 Payment Required
}
```

**Status HTTP 402:**
- Indica que é problema de pagamento/assinatura
- Cliente pode exibir mensagem apropriada
- Diferente de 403 (sem permissão) ou 429 (rate limit)

## 📊 Widget de Dashboard

**Arquivo:** `app/Filament/Restaurant/Widgets/SubscriptionLimitsWidget.php`

Exibe no dashboard do restaurante:

**Informações:**
- Nome do plano (badge colorido por status)
- Link para "Gerenciar Assinatura"

**Card de Produtos:**
- Ícone + título "Produtos"
- Badge com contador: `45/50`
- Barra de progresso visual
- Alertas:
  - 80-99%: ⚠️ "Atenção! Você está próximo do limite"
  - 100%: ❌ "Limite atingido! Faça upgrade"

**Card de Pedidos:**
- Ícone + título "Pedidos"
- Indicador do mês atual
- Badge com contador mensal: `230/500`
- Barra de progresso visual
- Alertas similares ao de produtos

**Trial:**
- Se status = `trialing`, exibe banner azul
- Informa data de término do trial

## 🎨 Cores e Estados

### Badges de Status do Plano
- `active` → Verde (success)
- `trialing` → Azul (info)
- `past_due` → Vermelho (danger)
- `canceled` → Cinza (gray)

### Cores de Progresso
- < 80%: Verde/Azul (success/primary)
- 80-99%: Amarelo (warning)
- ≥100%: Vermelho (danger)
- Ilimitado: Azul (primary)

## 🚀 Fluxo de Upgrade

**Quando usuário atinge limite:**

1. Notificação persistente aparece
2. Botão "🚀 Fazer Upgrade"
3. Redireciona para: `/painel/manage-subscription`
4. Página exibe:
   - Plano atual
   - Status da assinatura
   - Botões de ação:
     - "Sincronizar Status" (Pagar.me)
     - "Cancelar Assinatura"

## 📐 Estrutura de Dados

### Tabela: plans

```sql
id              ULID PRIMARY KEY
name            VARCHAR (Starter, Pro, Enterprise)
slug            VARCHAR
price_monthly   DECIMAL(10,2)
commission_percentage DECIMAL(5,2)
max_products    INT NULL (null = ilimitado)
max_orders_per_month INT NULL (null = ilimitado)
features        JSONB (array de strings)
```

### Tabela: subscriptions

```sql
id              ULID PRIMARY KEY
tenant_id       VARCHAR (FK)
plan_id         ULID (FK)
status          VARCHAR (active, trialing, past_due, canceled)
pagarme_subscription_id VARCHAR NULL
starts_at       TIMESTAMP
ends_at         TIMESTAMP NULL
trial_ends_at   TIMESTAMP NULL
amount          DECIMAL(10,2)
```

## 🧪 Testes

**Testar limite de produtos:**
```bash
# 1. Criar tenant com plano Starter (max_products = 50)
# 2. Criar 50 produtos
# 3. Tentar criar 51º produto
# Esperado: Bloqueado + notificação de upgrade
```

**Testar limite de pedidos:**
```bash
# 1. Configurar plano com max_orders_per_month = 100
# 2. Criar 100 pedidos no mês atual
# 3. Tentar criar 101º pedido (via API)
# Esperado: HTTP 402 + erro "order_limit_reached"
```

**Testar ilimitado:**
```bash
# 1. Configurar plano com max_products = null
# 2. Tentar criar 1000 produtos
# Esperado: Todos criados, badge não exibido
```

## ⚡ Performance

**Otimizações implementadas:**

1. **Cache de assinatura:** `activeSubscription()` busca com eager loading
2. **Contagem eficiente:** Usa `count()` direto no DB
3. **Validação apenas quando necessário:** Não valida em `index()`
4. **Widget condicional:** `canView()` evita queries desnecessárias

## 📱 Mobile/Frontend

**Tratamento no cliente:**

```javascript
// Criar pedido
try {
  const response = await api.post('/orders', orderData);
} catch (error) {
  if (error.response?.status === 402) {
    // Limite de pedidos atingido
    showAlert({
      title: 'Limite Atingido',
      message: error.response.data.message,
      type: 'warning',
    });
  }
}
```

## 🔄 Sincronização com Pagar.me

**Webhook atualiza status da assinatura:**
- `subscription.activated` → `status = 'active'`
- `subscription.canceled` → `status = 'canceled'`
- `subscription.payment_failed` → `status = 'past_due'`

**Quando status muda para `past_due` ou `canceled`:**
- Middleware `CheckSubscription` bloqueia acesso ao painel
- API retorna HTTP 402 para criação de pedidos
- Frontend exibe mensagem apropriada

## 📝 Notas Importantes

1. **Limites são opcionais:** Planos podem ter campos `null` = ilimitado
2. **Reset mensal:** Limite de pedidos reseta todo mês (usa `whereMonth()`)
3. **Trial tem limites:** Mesmo em trial, os limites do plano se aplicam
4. **Upgrade imediato:** Ao trocar plano, limites mudam instantaneamente
5. **Downgrade:** Se downgrade excede limite, não deleta dados, apenas bloqueia criação

## 🛠️ Manutenção

**Adicionar novo limite:**

1. Adicionar campo na migration de `plans`
2. Adicionar método no trait `HasSubscriptionLimits`
3. Adicionar validação no Resource/Controller
4. Atualizar widget de dashboard
5. Atualizar esta documentação

**Exemplo: Limite de usuários**
```php
// Migration
$table->integer('max_users')->nullable();

// Trait
public function canCreateUser(): bool {
    $maxUsers = $this->plan->max_users ?? null;
    if ($maxUsers === null) return true;
    $currentCount = \DB::table('users')->count();
    return $currentCount < $maxUsers;
}

// Resource
public static function canCreate(): bool {
    return tenancy()->tenant->canCreateUser();
}
```

---

**Versão:** 1.0
**Data:** 08/03/2026
**Autor:** Sistema de Assinaturas DeliveryPro
