# 🏗️ Arquitetura de Customers: Dual Schema (Central + Tenant)

**Data:** 18/03/2026
**Status:** ✅ Implementado e Corrigido

---

## 📊 Visão Geral

O sistema usa **2 tabelas `customers`** em schemas diferentes para permitir:
- ✅ **Login único** em todos os restaurantes (uma senha para tudo)
- ✅ **Cashback isolado** por restaurante (cada um paga o seu)
- ✅ **Dados específicos** por restaurante (total_orders, total_spent)

---

## 🗄️ Estrutura de Dados

### Schema PUBLIC (Central)

**Tabela:** `customers`

**Função:** Autenticação e login único (Single Sign-On)

```sql
CREATE TABLE public.customers (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,

    -- Auth Social
    provider VARCHAR(50),          -- 'google', 'whatsapp', null
    provider_id VARCHAR(255),
    avatar TEXT,

    -- Verificação
    email_verified_at TIMESTAMP,
    phone_verified_at TIMESTAMP,
    verification_code VARCHAR(6),
    verification_code_expires_at TIMESTAMP,

    -- Dados básicos
    birth_date DATE,
    cpf VARCHAR(14) UNIQUE,

    -- Timestamps
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

**Exemplo:**
```
ID: 1
Nome: Elizeu Santos
Email: elizeu.drive@gmail.com
Provider: google
Password: (hash único para todos os restaurantes)
```

---

### Schema PUBLIC (Pivot)

**Tabela:** `customer_tenant`

**Função:** Rastrear quais tenants o customer já acessou

```sql
CREATE TABLE public.customer_tenant (
    customer_id BIGINT,     -- public.customers.id
    tenant_id VARCHAR(255), -- tenants.id
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Exemplo:**
```
Customer 1 (Elizeu) → Tenant 'marmitariadagi'
Customer 1 (Elizeu) → Tenant 'lospampas'
Customer 1 (Elizeu) → Tenant 'botecodomeurei'
```

---

### Schema TENANT_* (Isolado por Restaurante)

**Tabela:** `customers` (no schema de cada tenant)

**Função:** Dados específicos do restaurante

```sql
CREATE TABLE tenant_marmitariadagi.customers (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),           -- Sincronizado com central
    phone VARCHAR(20) UNIQUE,
    cpf VARCHAR(14) UNIQUE,
    password VARCHAR(255),         -- Mesma senha do central

    -- Auth (duplicado do central)
    provider VARCHAR(50),
    provider_id VARCHAR(255),
    avatar TEXT,

    -- Cashback & Loyalty (ISOLADO!)
    cashback_balance DECIMAL(10,2) DEFAULT 0.00,
    loyalty_tier VARCHAR(20) DEFAULT 'bronze',
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0.00,

    -- Timestamps
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

**Exemplo:**

```
Schema: tenant_marmitariadagi.customers
ID: 1
Email: elizeu.drive@gmail.com
Cashback: R$ 15,50
Total pedidos: 26
Total gasto: R$ 1.300,00

Schema: tenant_lospampas.customers
ID: 1
Email: elizeu.drive@gmail.com
Cashback: R$ 0,00
Total pedidos: 1
Total gasto: R$ 50,00

Schema: tenant_botecodomeurei.customers
ID: 1
Email: elizeu.drive@gmail.com
Cashback: R$ 0,00
Total pedidos: 0
Total gasto: R$ 0,00
```

---

## 🔄 Fluxo de Sincronização

### 1. Novo Customer (Primeiro Acesso)

```
1. Cliente faz login via Google em marmitariadagi.yumgo.com.br
   ↓
2. Sistema cria/atualiza em public.customers:
   - ID: 1
   - Email: elizeu@gmail.com
   - Provider: google
   ↓
3. Sistema cria em tenant_marmitariadagi.customers:
   - ID: 1 (pode ser diferente!)
   - Email: elizeu@gmail.com (sincronizado)
   - Cashback: R$ 0,00
   ↓
4. Sistema registra em customer_tenant:
   - customer_id: 1
   - tenant_id: 'marmitariadagi'
```

### 2. Customer Existente em Novo Restaurante

```
1. Cliente acessa lospampas.yumgo.com.br (mesma conta Google)
   ↓
2. Sistema identifica customer central (ID: 1)
   ↓
3. Sistema busca em tenant_lospampas.customers:
   - WHERE email = 'elizeu@gmail.com'
   ↓
4. NÃO encontrou? Cria novo registro:
   - ID: 1 (pode ser diferente!)
   - Email: elizeu@gmail.com
   - Cashback: R$ 0,00 (ISOLADO!)
   ↓
5. Sistema registra em customer_tenant:
   - customer_id: 1
   - tenant_id: 'lospampas'
```

---

## 🎯 Por Que Não Unificar Cashback?

### ❌ Problema de Centralizar

Se tivéssemos apenas `public.customers` com cashback único:

```
Elizeu tem R$ 50 de cashback (ganho na Marmitaria)
   ↓
Usa R$ 50 no Los Pampas (outra empresa!)
   ↓
Los Pampas paga o cashback que a Marmitaria gerou!
   ↓
❌ Subsídio cruzado entre empresas
```

### ✅ Solução: Cashback Isolado por Tenant

```
Marmitaria:
- Elizeu ganhou: R$ 50
- Elizeu pode usar: R$ 50
- Marmitaria paga: R$ 50

Los Pampas:
- Elizeu ganhou: R$ 0
- Elizeu pode usar: R$ 0
- Los Pampas paga: R$ 0

✅ Cada restaurante paga apenas o que gerou
✅ Incentiva fidelidade ao mesmo restaurante
```

---

## 🔑 Regras de Negócio

### 1. Login Único
- ✅ Customer usa **mesma senha** em todos os restaurantes
- ✅ Login via Google funciona em **todos os tenants**
- ✅ Dados básicos (nome, email, avatar) **sincronizados**

### 2. Cashback Isolado
- ✅ Cada restaurante tem **seu próprio saldo de cashback**
- ❌ Cliente **NÃO pode** usar cashback entre restaurantes
- ✅ Restaurante **só paga** cashback que ele gerou

### 3. Sincronização
- ✅ Email/Phone são **chave de sincronização**
- ✅ Nome/Avatar são **atualizados** do central → tenant
- ✅ Cashback/Orders são **independentes** por tenant

---

## 💻 Código de Sincronização

### Em Controllers (API)

```php
// ❌ ERRADO - Pega customer do central
$customer = $request->user();
$cashback = $customer->cashback_balance; // Schema CENTRAL (vazio!)

// ✅ CORRETO - Pega customer do tenant
$centralCustomer = $request->user();
$customer = \App\Models\Customer::where('email', $centralCustomer->email)
    ->orWhere('phone', $centralCustomer->phone)
    ->first();

// Se não existir no tenant, criar
if (!$customer) {
    $customer = \App\Models\Customer::create([
        'email' => $centralCustomer->email,
        'phone' => $centralCustomer->phone,
        'name' => $centralCustomer->name,
        'password' => $centralCustomer->password,
        'provider' => $centralCustomer->provider,
        'provider_id' => $centralCustomer->provider_id,
        'avatar' => $centralCustomer->avatar,
        'cashback_balance' => 0.00,
        'loyalty_tier' => 'bronze',
    ]);
}

$cashback = $customer->cashback_balance; // Schema TENANT (correto!)
```

---

## 🛠️ Migrations Importantes

### 1. Migration Original (CORRETA)
`2026_02_21_003637_create_customers_table.php`
- ✅ Cria tabela `customers` no schema tenant
- ✅ Inclui campos de cashback e loyalty

### 2. Migration Problemática (CORRIGIDA)
`2026_02_24_001104_update_tenant_tables_for_central_customers.php`
- ❌ **ANTES:** Deletava tabela customers do tenant (ERRO!)
- ✅ **DEPOIS:** Comentada linha `Schema::dropIfExists('customers')`
- ✅ Agora mantém tabela customers no tenant

### 3. Script de Restauração
`restore-customers-table.php`
- ✅ Recria tabela customers em tenants existentes
- ✅ Roda automaticamente para novos tenants

### 4. Script de Sincronização
`sync-customers-central-to-tenant.php`
- ✅ Sincroniza customer central → tenant
- ✅ Vincula pedidos órfãos ao customer correto

---

## 📋 Checklist para Novos Endpoints

Antes de criar endpoint que usa dados de customer:

- [ ] Busquei customer do schema TENANT (não central)?
- [ ] Usei padrão: `$centralCustomer = $request->user()` → `Customer::where('email'...)`?
- [ ] Criei customer no tenant se não existir?
- [ ] Estou usando cashback do tenant (não central)?
- [ ] Limpei cache após mudanças?

---

## 🧪 Como Testar

### Teste 1: Verificar Estrutura

```bash
# Schema central
php artisan tinker --execute="
\$count = DB::table('customers')->count();
echo \"Customers centrais: \$count\n\";
"

# Schema tenant
php artisan tinker --execute="
\$tenant = App\Models\Tenant::first();
tenancy()->initialize(\$tenant);
\$count = DB::table('customers')->count();
echo \"Customers tenant: \$count\n\";
"
```

### Teste 2: Verificar Cashback Isolado

```bash
php artisan tinker --execute="
\$central = DB::table('customers')->first();
echo \"Central cashback: \" . (\$central->cashback_balance ?? 'N/A') . \"\n\";

\$tenant = App\Models\Tenant::first();
tenancy()->initialize(\$tenant);
\$tenantCustomer = DB::table('customers')->first();
echo \"Tenant cashback: {\$tenantCustomer->cashback_balance}\n\";
"
```

---

## ✅ Status Atual (18/03/2026)

| Item | Status | Observação |
|------|--------|------------|
| Tabela central | ✅ OK | 1 customer (Elizeu) |
| Tabela tenant (Marmitaria) | ✅ OK | 1 customer, R$ 0 cashback |
| Tabela tenant (Los Pampas) | ✅ OK | 1 customer, R$ 0 cashback |
| Tabela tenant (Boteco) | ✅ OK | 1 customer, R$ 0 cashback |
| Migration corrigida | ✅ OK | Não deleta mais customers |
| Sincronização | ✅ OK | Scripts criados |
| Erro "customers not exist" | ✅ RESOLVIDO | Tabela recriada |

---

## 📚 Referências

- `MEMORY.md` - Decisões de arquitetura
- `CLAUDE.md` - Regras invioláveis (cashback isolado)
- `docs/ARQUITETURA-MULTI-TENANT.md` - Arquitetura completa
- `EMAIL-FALLBACK-28-02-2026.md` - Email fallback

---

**✅ Arquitetura validada e funcionando!**
