# 🏗️ Arquitetura de Clientes Centralizada

**Data:** 14/03/2026
**Status:** ✅ Implementado

## 📋 Problema Resolvido

### ❌ Erro Original:
```
SQLSTATE[42P01]: Undefined table: 7 ERROR: relation "personal_access_tokens" does not exist
LINE 1: select exists(select * from "personal_access_tokens" where "...
```

**Causa:** Modelo `Customer` e `User` usando Sanctum (`HasApiTokens`) mas tabela `personal_access_tokens` não existia no schema TENANT.

---

## 🎯 Solução Implementada

### 1️⃣ **Clientes Centralizados**

**Schema PUBLIC (central):**
```sql
-- Tabela única de clientes (login único)
customers (id, name, email, phone, password, provider, avatar, birth_date, cpf)

-- Relacionamento N:N com dados específicos por restaurante
customer_tenant (
  customer_id,    -- FK: public.customers.id
  tenant_id,      -- FK: tenants.id
  cashback_balance,
  loyalty_tier,
  total_orders,
  total_spent,
  first_order_at,
  last_order_at
)

-- Tokens Sanctum para API mobile (Customer)
personal_access_tokens (tokenable_type='App\Models\Customer', tokenable_id, token)
```

**Schema TENANT_* (por restaurante):**
```sql
-- Pedidos do restaurante
orders (id, customer_id, total, status)
  -- customer_id referencia public.customers.id (sem FK - cross-schema)

-- Transações de cashback
cashback_transactions (id, customer_id, amount, type)

-- Tokens Sanctum para painel admin (User)
personal_access_tokens (tokenable_type='App\Models\User', tokenable_id, token)
```

### 2️⃣ **Mudanças Realizadas**

#### ✅ Modelo Customer
```php
// app/Models/Customer.php
class Customer extends Authenticatable
{
    use HasApiTokens; // Tokens salvos em public.personal_access_tokens

    protected $connection = 'pgsql'; // SEMPRE usa schema CENTRAL
    protected $table = 'customers';
}
```

#### ✅ Modelo User
```php
// app/Models/User.php
class User extends Authenticatable
{
    use HasApiTokens; // Tokens salvos em tenant_*.personal_access_tokens

    // Sem $connection definido → Usa schema TENANT
    protected $table = 'users';
}
```

#### ✅ Migration TENANT
```php
// database/migrations/tenant/2026_03_14_151025_create_personal_access_tokens_table.php
Schema::create('personal_access_tokens', function (Blueprint $table) {
    $table->id();
    $table->morphs('tokenable');
    $table->string('name');
    $table->string('token', 64)->unique();
    $table->text('abilities')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});
```

#### ✅ Tabela Antiga Deletada
```sql
-- ANTES (confuso):
tenantmarmitariadagi.customers (duplicata - LIXO!)

-- DEPOIS (limpo):
-- Tabela removida! Dados migrados para:
-- - public.customers (login)
-- - public.customer_tenant (cashback/tier por restaurante)
```

---

## 🎯 Como Funciona

### Fluxo de Cadastro/Login:

1. **Cliente cria conta:**
   ```php
   // POST /api/v1/register (rota tenant)
   $customer = Customer::create([...]); // Salva em public.customers
   $customer->getOrCreateTenantRelation(tenant('id')); // Cria em customer_tenant
   $token = $customer->createToken('mobile-app'); // Salva em public.personal_access_tokens
   ```

2. **Cliente faz pedido em outro restaurante:**
   ```php
   // Mesmo customer_id, novo relacionamento
   $customer->getOrCreateTenantRelation('parker-pizzaria'); // Novo registro em customer_tenant
   ```

3. **Admin gera token para impressão:**
   ```php
   // Painel Filament (guard: web → User do tenant)
   $user = Auth::user(); // App\Models\User (schema TENANT)
   $token = $user->createToken('bridge-app'); // Salva em tenant_*.personal_access_tokens
   ```

### Exemplo Real:

**Cliente: João Silva**
```
public.customers:
  id: 1
  email: joao@email.com
  phone: (11) 99999-9999

public.customer_tenant:
  - customer_id=1, tenant_id='marmitariadagi', cashback=10.00, tier='gold'
  - customer_id=1, tenant_id='parker-pizzaria', cashback=25.50, tier='platinum'

tenantmarmitariadagi.orders:
  - id=1, customer_id=1, total=50.00
  - id=2, customer_id=1, total=35.00

tenantparkerpizzaria.orders:
  - id=1, customer_id=1, total=120.00
  - id=2, customer_id=1, total=80.00
```

**Resultado:**
- ✅ João tem UMA conta (email/senha única)
- ✅ Cashback isolado por restaurante
- ✅ Histórico de pedidos separado
- ✅ Pode usar mesma conta em N restaurantes

---

## ✅ Vantagens

| Aspecto | Antes (Isolado) | Depois (Centralizado) |
|---------|----------------|----------------------|
| **Login** | 1 conta POR restaurante ❌ | 1 conta GLOBAL ✅ |
| **UX** | Cliente cria conta N vezes ❌ | Cria conta 1 vez ✅ |
| **Cashback** | Isolado ✅ | Isolado via pivot ✅ |
| **Escalabilidade** | Duplica dados ❌ | Normalizado ✅ |
| **Manutenção** | Complexo ❌ | Simples ✅ |

---

## 🔒 Segurança Multi-Tenant

**Isolamento mantido:**
- ✅ Pedidos em schemas separados
- ✅ Cashback isolado (customer_tenant)
- ✅ Sem JOIN entre schemas (previne vazamento)
- ✅ Foreign Keys substituídas por índices + validação em código

**Exemplo de proteção:**
```php
// app/Models/Customer.php
public function orders(): Builder
{
    if (!tenancy()->initialized) {
        throw new \Exception('Tenancy must be initialized. Prevents cross-tenant data leakage.');
    }
    return Order::where('customer_id', $this->id); // Busca no schema TENANT correto
}
```

---

## 📊 Comandos de Verificação

```bash
# Verificar clientes centrais
psql -c "SELECT COUNT(*) FROM customers;"

# Verificar relacionamentos
psql -c "SELECT * FROM customer_tenant WHERE tenant_id='marmitariadagi';"

# Verificar se tabela tenant foi deletada (deve dar erro)
psql -c "SELECT * FROM tenantmarmitariadagi.customers;" # Esperado: relation does not exist

# Verificar tokens do Customer (central)
psql -c "SELECT COUNT(*) FROM personal_access_tokens WHERE tokenable_type='App\\Models\\Customer';"

# Verificar tokens do User (tenant)
psql -c "SELECT COUNT(*) FROM tenantmarmitariadagi.personal_access_tokens WHERE tokenable_type='App\\Models\\User';"
```

---

## 📝 Arquivos Modificados

```
✅ app/Models/Customer.php (adicionado $connection = 'pgsql')
✅ database/migrations/tenant/2026_03_14_151025_create_personal_access_tokens_table.php (criada)
✅ app/Filament/Restaurant/Pages/PrinterSettings.php (código original restaurado)
❌ tenantmarmitariadagi.customers (tabela deletada)
```

---

## 🚀 Próximos Passos (se necessário)

- [ ] Criar outros tenants e validar funcionamento
- [ ] Testar login do cliente em múltiplos restaurantes
- [ ] Validar tokens da API mobile
- [ ] Validar tokens do Bridge app (impressão)
- [ ] Monitorar logs para garantir zero erros

---

## ✅ Status Final

**Data de conclusão:** 14/03/2026
**Testes realizados:** ✅ PrinterSettings (sem erro)
**Logs:** ✅ Sem erros de `personal_access_tokens`
**Arquitetura:** ✅ Clientes centralizados + dados isolados

**Conclusão:** Sistema funcionando com arquitetura multi-tenant limpa e escalável! 🎉
