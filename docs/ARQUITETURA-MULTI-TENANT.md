# 🏗️ Arquitetura Multi-Tenant - DeliveryPro

**Data:** 28/02/2026
**Versão:** 1.0
**Status:** ✅ IMPLEMENTADO E FUNCIONANDO

---

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Schema Multi-Tenant](#schema-multi-tenant)
3. [Sistema de Login Centralizado](#sistema-de-login-centralizado)
4. [Sincronização de Customers](#sincronização-de-customers)
5. [Email Fallback para Gateways](#email-fallback-para-gateways)
6. [Sistema de Cashback Isolado](#sistema-de-cashback-isolado)
7. [Padrões de Validação](#padrões-de-validação)
8. [Fluxo Completo de Pedido](#fluxo-completo-de-pedido)
9. [Arquivos Principais](#arquivos-principais)

---

## 🎯 Visão Geral

### Decisão Arquitetural: Schemas PostgreSQL

**Por que escolhemos schemas ao invés de databases separados?**

✅ **Vantagens:**
- **Isolamento total de dados** entre tenants (restaurantes)
- **Performance superior** (mesma conexão, queries otimizadas)
- **Backup único** (todos schemas em um dump)
- **Migrations centralizadas** (aplica em todos schemas de uma vez)
- **Custo reduzido** (1 servidor PostgreSQL para N restaurantes)
- **Segurança nativa** (PostgreSQL garante isolamento por schema)

❌ **Desvantagens (que aceitamos):**
- Queries complexas precisam especificar schema
- Impossível usar `FOREIGN KEY` entre schemas
- Requer sincronização manual de dados compartilhados

### Estrutura de Schemas

```
┌─────────────────────────────────────────────────────┐
│                   PostgreSQL                        │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Schema: PUBLIC                                     │
│  ┌───────────────────────────────────────────┐    │
│  │ Dados da Plataforma (Central)             │    │
│  │ - tenants                                 │    │
│  │ - central_customers (login único)         │    │
│  │ - plans                                   │    │
│  │ - subscriptions                           │    │
│  │ - invoices                                │    │
│  │ - domains                                 │    │
│  │ - platform_users                          │    │
│  │ - audit_logs                              │    │
│  └───────────────────────────────────────────┘    │
│                                                     │
│  Schema: TENANT_marmitaria-gi                      │
│  ┌───────────────────────────────────────────┐    │
│  │ Dados do Restaurante 1                    │    │
│  │ - customers (ID diferente do central!)    │    │
│  │ - orders                                  │    │
│  │ - products                                │    │
│  │ - cashback_transactions                   │    │
│  │ - cashback_settings                       │    │
│  │ - payments                                │    │
│  └───────────────────────────────────────────┘    │
│                                                     │
│  Schema: TENANT_parker-pizzaria                    │
│  ┌───────────────────────────────────────────┐    │
│  │ Dados do Restaurante 2                    │    │
│  │ - customers (ID diferente do central!)    │    │
│  │ - orders                                  │    │
│  │ - products                                │    │
│  │ - cashback_transactions                   │    │
│  │ - cashback_settings                       │    │
│  │ - payments                                │    │
│  └───────────────────────────────────────────┘    │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## 🔐 Sistema de Login Centralizado

### Como Funciona

**1. Cliente faz cadastro/login:**
```
Cliente acessa: https://marmitaria-gi.yumgo.com.br
1. Clica em "Entrar com WhatsApp"
2. Autentica via WhatsApp (login social)
3. Sistema cria/busca registro em: PUBLIC.central_customers
4. Token Sanctum gerado para autenticação
```

**2. Estrutura da Tabela Central:**
```sql
-- Schema: PUBLIC
CREATE TABLE central_customers (
    id BIGSERIAL PRIMARY KEY,                    -- ⭐ ID único global
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,                   -- Pode ser NULL (WhatsApp)
    phone VARCHAR(20),                           -- Obrigatório para WhatsApp
    password VARCHAR(255),                       -- Pode ser NULL (login social)
    google_id VARCHAR(255),
    whatsapp_id VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Exemplo de registro:
id: 2
name: "Elizeu Santos"
email: NULL                                      -- Login via WhatsApp
phone: "+5519912345678"
password: NULL
whatsapp_id: "5519912345678"
```

**3. Autenticação Multi-Restaurante:**

**O mesmo cliente pode comprar em VÁRIOS restaurantes:**
```
Central Customer (ID: 2)
├─ Marmitaria da Gi → Tenant Customer (ID: 3)
├─ Parker Pizzaria → Tenant Customer (ID: 1)
└─ Burger King → Tenant Customer (ID: 5)
```

**Por que IDs diferentes?**
- Cada restaurante tem seu próprio schema
- Cada schema tem sua própria sequence para IDs
- **Isolamento total** impede vazamento de dados

---

## 🔄 Sincronização de Customers

### Problema Resolvido

**ANTES (quebrado):**
```php
// OrderController tentava usar customer do central
$customer = $request->user(); // ID: 2 (central)

// Criava order no tenant
$order = Order::create([
    'customer_id' => $customer->id, // ❌ ID: 2 não existe no tenant!
]);

// Relação quebrada
$order->customer; // NULL ❌
```

**DEPOIS (funcionando):**
```php
// OrderService sincroniza automaticamente
$tenantCustomer = $this->syncCustomer($centralCustomer);
// Central ID: 2 → Tenant ID: 3

$order = Order::create([
    'customer_id' => $tenantCustomer->id, // ✅ ID: 3 existe no tenant!
]);

$order->customer; // Customer encontrado ✅
```

### Implementação

**Arquivo:** `app/Services/OrderService.php`

```php
public function createOrder(Customer $customer, array $data): Order
{
    // 🔧 PROTEÇÃO: Garantir que temos customer do TENANT (não do central)
    $tenantCustomer = Customer::where('email', $customer->email)
        ->orWhere('phone', $customer->phone)
        ->first();

    if (!$tenantCustomer) {
        // ✨ Cria customer no schema do tenant
        $tenantCustomer = Customer::create([
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'cpf' => $customer->cpf ?? null,
            'cashback_balance' => 0,          // ⭐ Cashback isolado
            'loyalty_tier' => 'bronze',
            'total_orders' => 0,
            'total_spent' => 0,
            'is_active' => true,
        ]);

        \Log::info('✨ Customer criado no tenant', [
            'central_id' => $customer->id,      // ID: 2
            'tenant_id' => $tenantCustomer->id, // ID: 3
            'name' => $tenantCustomer->name,
        ]);
    }

    // ✅ Usa customer do tenant (ID correto)
    $customer = $tenantCustomer;

    // ... resto da criação do pedido
}
```

### Exemplo de Sincronização

```
┌────────────────────────────────────────────────────────────────┐
│ 1. Cliente faz login (Central)                                │
├────────────────────────────────────────────────────────────────┤
│ Domain: marmitaria-gi.yumgo.com.br                            │
│ Auth: Central Customer (ID: 2)                                │
│   - name: "Elizeu Santos"                                     │
│   - phone: "+5519912345678"                                   │
│   - email: NULL                                               │
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│ 2. Cliente adiciona produto ao carrinho                       │
├────────────────────────────────────────────────────────────────┤
│ Produto: Marmitex P (R$ 18,00)                               │
│ Quantity: 2                                                    │
│ Subtotal: R$ 36,00                                            │
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│ 3. OrderService.createOrder() é chamado                       │
├────────────────────────────────────────────────────────────────┤
│ Recebe: Central Customer (ID: 2)                              │
│                                                                │
│ Busca por email/phone no tenant:                              │
│   SELECT * FROM customers                                     │
│   WHERE phone = '+5519912345678'                              │
│                                                                │
│ Resultado: NULL (primeira compra neste restaurante)           │
│                                                                │
│ Cria novo customer no tenant:                                 │
│   INSERT INTO customers (name, phone, email, ...)             │
│   VALUES ('Elizeu Santos', '+5519912345678', NULL, ...)       │
│                                                                │
│ Retorna: Tenant Customer (ID: 3) ✅                           │
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│ 4. Order criado com ID correto                                │
├────────────────────────────────────────────────────────────────┤
│ INSERT INTO orders (customer_id, total, ...)                  │
│ VALUES (3, 36.00, ...)        ← ID do tenant ✅               │
│                                                                │
│ Relação funciona:                                              │
│   $order->customer → Customer encontrado ✅                   │
└────────────────────────────────────────────────────────────────┘
```

---

## 📧 Email Fallback para Gateways

### Problema Resolvido

**Pagar.me EXIGE email do cliente:**
```json
{
  "customer": {
    "name": "Elizeu Santos",
    "email": "required@email.com",  ← OBRIGATÓRIO
    "type": "individual"
  }
}
```

**Mas clientes podem fazer login APENAS com WhatsApp** (sem email).

### Solução: Email Fallback

**Arquivo:** `app/Services/PagarMeService.php`

```php
// PROTEÇÃO: Garantir que customer tem email (usa email do restaurante se vazio)
$email = $customer->email;

if (empty($email)) {
    // Gera email usando domínio do restaurante
    // Ex: cliente-2@marmitaria-gi.yumgo.com.br
    $tenant = tenant();
    $email = "cliente-{$customer->id}@{$tenant->slug}.yumgo.com.br";

    \Log::info('💡 Cliente sem email, usando email do restaurante', [
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'fallback_email' => $email,
        'restaurante' => $tenant->name,
    ]);
}

// Todas ocorrências de $customer->email substituídas por $email
$pagarmeCustomer = $this->getOrCreateCustomer([
    'name' => $customer->name,
    'email' => $email,  // ✅ Sempre preenchido
    'type' => 'individual',
    'document' => $customer->cpf ?? '00000000000',
    'phones' => [
        'mobile_phone' => [
            'country_code' => '55',
            'area_code' => substr($customer->phone, 3, 2),
            'number' => substr($customer->phone, 5),
        ],
    ],
]);
```

### Exemplos de Fallback

**Cliente COM email:**
```
Central Customer:
  - id: 5
  - name: "João Silva"
  - email: "joao@gmail.com"

Pagar.me recebe:
  - email: "joao@gmail.com"  ✅ Email real
```

**Cliente SEM email (WhatsApp):**
```
Central Customer:
  - id: 2
  - name: "Elizeu Santos"
  - email: NULL
  - phone: "+5519912345678"

Tenant Customer (marmitaria-gi):
  - id: 3

Pagar.me recebe:
  - email: "cliente-3@marmitaria-gi.yumgo.com.br"  ✅ Fallback
```

### Vantagens

✅ **Domínio real**: yumgo.com.br (não fake)
✅ **Email único**: Baseado no ID do tenant customer
✅ **Profissional**: cliente-3@marmitaria-gi.yumgo.com.br
✅ **Compliance**: Não viola termos do Pagar.me
✅ **Rastreável**: Logs registram quando fallback é usado
✅ **Opcional inbox**: Restaurante pode configurar catch-all se quiser

### LGPD Compliance

✅ **Email gerado NÃO expõe dados pessoais:**
- Formato: `cliente-{ID}@{restaurante}.yumgo.com.br`
- ID numérico não identifica pessoa
- Não usa nome, CPF ou telefone

✅ **Dados armazenados:**
```sql
-- Tabela customers (schema tenant)
id: 3
name: "Elizeu Santos"
email: NULL  -- ✅ Continua NULL no banco
phone: "+5519912345678"

-- Pagar.me recebe (apenas na API):
email: "cliente-3@marmitaria-gi.yumgo.com.br"  -- ✅ Não salvo no banco
```

**Importante:**
- Email fallback NÃO é salvo no banco
- Gerado dinamicamente quando necessário
- Cliente pode adicionar email real depois

---

## 💰 Sistema de Cashback Isolado

### Decisão de Negócio: CASHBACK ISOLADO (NÃO Unificado) ⭐

**Por que cada restaurante tem seu próprio cashback?**

✅ **Vantagens do Isolamento:**
1. **Configuração própria**: Cada restaurante define % de cashback
2. **Evita subsídio cruzado**: Pizzaria não paga cashback que Marmitaria deu
3. **Incentiva fidelidade**: Cliente volta no MESMO restaurante
4. **Contabilidade separada**: Cada restaurante paga APENAS seu cashback
5. **Isolamento de dados**: Mantém arquitetura multi-tenant pura
6. **Justiça comercial**: Quem dá benefício recebe cliente de volta
7. **Simplicidade**: Sem sistema complexo de repasse entre restaurantes

✅ **Previne Abuso:**
```
❌ IMPOSSÍVEL: Gastar R$ 1.000 na Pizzaria → Comer grátis na Marmitaria
✅ REALIDADE: Gastar R$ 1.000 na Pizzaria → Cashback válido APENAS na Pizzaria
```

❌ **Desvantagens (que aceitamos conscientemente):**
- Cliente tem saldos separados em cada restaurante
- Não pode usar cashback da Pizzaria na Marmitaria
- Precisa acumular em cada lugar separadamente
- **Mas isso é FEATURE, não bug!** (incentiva fidelidade)

### Implementação

**Tabela Tenant Customers:**
```sql
-- Schema: TENANT_marmitaria-gi
CREATE TABLE customers (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    cashback_balance DECIMAL(10,2) DEFAULT 0,    -- ⭐ Saldo isolado
    loyalty_tier VARCHAR(20) DEFAULT 'bronze',
    total_orders INTEGER DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Schema: TENANT_parker-pizzaria
CREATE TABLE customers (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    cashback_balance DECIMAL(10,2) DEFAULT 0,    -- ⭐ Outro saldo isolado
    loyalty_tier VARCHAR(20) DEFAULT 'bronze',
    ...
);
```

**O mesmo cliente em 2 restaurantes:**
```
Central Customer (ID: 2) - Elizeu Santos
│
├─ Marmitaria da Gi (Tenant Customer ID: 3)
│  - cashback_balance: R$ 15,40
│  - loyalty_tier: 'prata'
│  - total_orders: 12
│
└─ Parker Pizzaria (Tenant Customer ID: 1)
   - cashback_balance: R$ 8,20
   - loyalty_tier: 'bronze'
   - total_orders: 3
```

### Toggle de Cashback

**UX Simplificada: Boolean ao invés de Input Numérico**

**ANTES (ruim):**
```vue
<input type="number" v-model="cashbackToUse" />
<!-- Usuário precisa digitar quanto quer usar -->
<!-- Mobile: teclado numérico abre, experiência ruim -->
```

**DEPOIS (bom):**
```vue
<label>
  <input type="checkbox" v-model="useCashback" />
  Usar meu cashback (R$ {{ customer.cashback_balance }})
</label>
<!-- Usuário só marca checkbox -->
<!-- Sistema usa TODO saldo automaticamente (limitado ao total) -->
```

**Backend:** `app/Http/Controllers/Api/OrderController.php`

```php
// Validação
$validated = $request->validate([
    'use_cashback' => 'nullable|boolean', // ⭐ TOGGLE: true = usar todo saldo
    // ...
]);

// Cálculo automático
$customer->refresh();
$cashbackBalance = (float) $customer->cashback_balance;

$useCashback = 0;
if ($request->use_cashback === true && $cashbackBalance > 0) {
    // 🎯 Usa TODO saldo disponível
    $useCashback = $cashbackBalance;

    \Log::info('💰 Cliente optou por usar cashback', [
        'customer_id' => $customer->id,
        'saldo_disponivel' => $cashbackBalance,
        'sera_usado' => $useCashback,
    ]);
}
```

**OrderService com proteção:**
```php
// Calcula total ANTES do cashback
$totalBeforeCashback = $subtotal + $deliveryFee - $discount;

// Limita cashback ao total (não pode ficar negativo)
$cashbackUsed = min($data['cashback_used'], $totalBeforeCashback);

\Log::info('💰 Cashback aplicado', [
    'solicitado' => $data['cashback_used'],
    'aplicado' => $cashbackUsed,
    'total_antes' => $totalBeforeCashback,
]);

// Total final
$total = $totalBeforeCashback - $cashbackUsed;
// Total NUNCA fica negativo ✅
```

### Exemplos de Cenários

**Cenário 1: Saldo MENOR que pedido**
```
Pedido: R$ 79,00
Saldo: R$ 1,54
Toggle: ✅ MARCADO

Cashback usado: R$ 1,54 (todo saldo)
Total a pagar: R$ 77,46
Saldo restante: R$ 0,00
```

**Cenário 2: Saldo MAIOR que pedido**
```
Pedido: R$ 79,00
Saldo: R$ 100,00
Toggle: ✅ MARCADO

Cashback usado: R$ 79,00 (limitado ao total!)
Total a pagar: R$ 0,00 (GRÁTIS!)
Saldo restante: R$ 21,00
```

**Cenário 3: Toggle DESMARCADO**
```
Pedido: R$ 79,00
Saldo: R$ 100,00
Toggle: ❌ NÃO MARCADO

Cashback usado: R$ 0,00
Total a pagar: R$ 79,00
Saldo restante: R$ 100,00
```

---

## 🔍 Padrões de Validação

### Problema: Validar Customer em Multi-Tenant

**Validação de segurança:**
```
Cliente logado pode acessar APENAS seus próprios pedidos.
```

**Desafio:**
```
$request->user()           → Central Customer (ID: 2)
$order->customer_id        → Tenant Customer (ID: 3)

Como comparar IDs de schemas diferentes?
```

### Solução: getTenantCustomer() Helper

**Arquivo:** `app/Http/Controllers/Api/OrderController.php`

```php
/**
 * 🔧 Helper para buscar customer do TENANT a partir do customer CENTRAL
 * Resolve problema de IDs diferentes entre schemas
 */
private function getTenantCustomer($loggedUser): ?\App\Models\Customer
{
    return \App\Models\Customer::where('email', $loggedUser->email)
        ->orWhere('phone', $loggedUser->phone)
        ->first();
}
```

**Uso em validações:**
```php
// 1. Buscar pedido por código
$order = Order::where('order_code', $orderCode)->firstOrFail();

// 2. Validar que pedido pertence ao cliente logado
$tenantCustomer = $this->getTenantCustomer($request->user());

if ($order->customer_id !== $tenantCustomer->id) {
    throw new \Exception('Pedido não encontrado');
}

// ✅ Validação segura!
```

### Locais Onde Aplicamos

**5 validações corrigidas em `OrderController`:**

1. **showOrder()** - Exibir detalhes do pedido
2. **showPayment()** - Exibir página de pagamento
3. **getPaymentStatus()** - Verificar status do pagamento
4. **cancelOrder()** - Cancelar pedido
5. **trackOrder()** - Rastrear pedido

**Antes (quebrado):**
```php
if ($order->customer_id !== $request->user()->id) {
    // Compara ID: 3 (tenant) com ID: 2 (central)
    // ❌ SEMPRE FALHA mesmo sendo o mesmo cliente!
}
```

**Depois (funcionando):**
```php
$tenantCustomer = $this->getTenantCustomer($request->user());

if ($order->customer_id !== $tenantCustomer->id) {
    // Compara ID: 3 (tenant) com ID: 3 (tenant traduzido)
    // ✅ Funciona corretamente!
}
```

---

## 🛒 Fluxo Completo de Pedido

### Passo a Passo End-to-End

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. AUTENTICAÇÃO                                                 │
├─────────────────────────────────────────────────────────────────┤
│ Cliente: Elizeu Santos                                          │
│ Domain: https://marmitaria-gi.yumgo.com.br                     │
│                                                                 │
│ Login WhatsApp:                                                 │
│   POST /api/auth/social/whatsapp/callback                      │
│   {                                                             │
│     "code": "AUTH_CODE_FROM_WHATSAPP"                          │
│   }                                                             │
│                                                                 │
│ Resposta:                                                       │
│   {                                                             │
│     "token": "4|ABC123...",                                    │
│     "user": {                                                   │
│       "id": 2,              ← Central Customer                 │
│       "name": "Elizeu Santos",                                 │
│       "email": null,                                            │
│       "phone": "+5519912345678"                                │
│     }                                                           │
│   }                                                             │
│                                                                 │
│ Token armazenado: localStorage.setItem('token', ...)           │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ 2. BUSCAR PRODUTOS                                              │
├─────────────────────────────────────────────────────────────────┤
│ GET /api/v1/products                                            │
│ Headers: Authorization: Bearer 4|ABC123...                      │
│                                                                 │
│ Resposta:                                                       │
│   [                                                             │
│     {                                                           │
│       "id": 1,                                                  │
│       "name": "Marmitex P",                                     │
│       "price": 18.00,                                           │
│       "category": "Marmitex"                                    │
│     },                                                          │
│     ...                                                         │
│   ]                                                             │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ 3. ADICIONAR AO CARRINHO (Frontend)                            │
├─────────────────────────────────────────────────────────────────┤
│ cart = [                                                        │
│   { product_id: 1, quantity: 2, price: 18.00 }                 │
│ ]                                                               │
│                                                                 │
│ Subtotal: R$ 36,00                                             │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ 4. BUSCAR SALDO DE CASHBACK                                    │
├─────────────────────────────────────────────────────────────────┤
│ GET /api/v1/customers/me                                        │
│ Headers: Authorization: Bearer 4|ABC123...                      │
│                                                                 │
│ 🔄 SINCRONIZAÇÃO AUTOMÁTICA:                                   │
│   1. Recebe: Central Customer (ID: 2)                          │
│   2. Busca em TENANT_marmitaria-gi.customers                   │
│      WHERE phone = '+5519912345678'                            │
│   3. Se não existe, CRIA:                                      │
│      INSERT INTO customers (name, phone, email, ...)           │
│      → Tenant Customer (ID: 3) criado                          │
│                                                                 │
│ Resposta:                                                       │
│   {                                                             │
│     "id": 3,                ← Tenant Customer ID               │
│     "name": "Elizeu Santos",                                   │
│     "cashback_balance": 1.54,                                  │
│     "loyalty_tier": "bronze"                                   │
│   }                                                             │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ 5. CHECKOUT - Cliente Marca Toggle de Cashback                 │
├─────────────────────────────────────────────────────────────────┤
│ Frontend:                                                       │
│   <input type="checkbox" v-model="useCashback" checked />      │
│   "Usar meu cashback (R$ 1,54)"                                │
│                                                                 │
│ Valores calculados:                                             │
│   - Subtotal: R$ 36,00                                         │
│   - Taxa entrega: R$ 5,00                                      │
│   - Total antes cashback: R$ 41,00                             │
│   - Cashback a usar: R$ 1,54 (toggle marcado)                 │
│   - TOTAL A PAGAR: R$ 39,46                                    │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ 6. CRIAR PEDIDO                                                 │
├─────────────────────────────────────────────────────────────────┤
│ POST /api/v1/orders                                             │
│ Headers: Authorization: Bearer 4|ABC123...                      │
│ Body:                                                           │
│   {                                                             │
│     "items": [                                                  │
│       { "product_id": 1, "quantity": 2 }                       │
│     ],                                                          │
│     "delivery_address": "Rua das Flores, 123",                 │
│     "delivery_neighborhood": "Centro",                          │
│     "delivery_fee": 5.00,                                       │
│     "payment_method": "pix",                                    │
│     "use_cashback": true   ← ⭐ TOGGLE MARCADO                 │
│   }                                                             │
│                                                                 │
│ 📋 PROCESSAMENTO NO BACKEND:                                   │
│                                                                 │
│ A. OrderController::store()                                     │
│   1. Valida request                                             │
│   2. Pega central customer (ID: 2)                             │
│   3. Verifica saldo:                                            │
│      $customer->refresh();                                      │
│      $cashbackBalance = 1.54                                    │
│   4. Calcula cashback a usar:                                   │
│      if (use_cashback === true && cashbackBalance > 0) {       │
│          $useCashback = 1.54;  ← TODO saldo                    │
│      }                                                          │
│   5. Chama OrderService::createOrder()                          │
│                                                                 │
│ B. OrderService::createOrder()                                  │
│   1. 🔄 Sincroniza customer:                                   │
│      $tenantCustomer = Customer::where('phone', '+5519...')    │
│      → Tenant Customer (ID: 3)                                 │
│                                                                 │
│   2. Enriquece items:                                           │
│      foreach ($data['items'] as $item) {                       │
│          $product = Product::find($item['product_id']);        │
│          $item['price'] = $product->price;                     │
│          $item['name'] = $product->name;                       │
│      }                                                          │
│                                                                 │
│   3. Calcula valores:                                           │
│      $subtotal = 36.00                                          │
│      $deliveryFee = 5.00                                        │
│      $totalBeforeCashback = 41.00                               │
│                                                                 │
│      // 🎯 LIMITA cashback ao total                            │
│      $cashbackUsed = min(1.54, 41.00) = 1.54                   │
│                                                                 │
│      $total = 41.00 - 1.54 = 39.46                             │
│                                                                 │
│   4. Usa cashback (debita saldo):                              │
│      CashbackService::useCashback(customer: 3, amount: 1.54)   │
│      → customer.cashback_balance: 1.54 → 0.00 ✅               │
│                                                                 │
│   5. Cria order:                                                │
│      INSERT INTO orders (                                       │
│          customer_id = 3,        ← Tenant Customer             │
│          order_code = '20260228-A1B2C3',                       │
│          subtotal = 36.00,                                      │
│          delivery_fee = 5.00,                                   │
│          cashback_used = 1.54,   ← ⭐ Cashback aplicado        │
│          total = 39.46,                                         │
│          payment_method = 'pix',                                │
│          status = 'pending',                                    │
│          payment_status = 'pending'                             │
│      )                                                          │
│                                                                 │
│   6. Cria order_items:                                          │
│      INSERT INTO order_items (                                  │
│          order_id = 47,                                         │
│          product_id = 1,                                        │
│          quantity = 2,                                          │
│          price = 18.00,                                         │
│          subtotal = 36.00                                       │
│      )                                                          │
│                                                                 │
│ Resposta:                                                       │
│   {                                                             │
│     "message": "Pedido criado com sucesso!",                   │
│     "order": {                                                  │
│       "id": 47,                                                 │
│       "order_code": "20260228-A1B2C3",                         │
│       "total": 39.46,                                           │
│       "cashback_used": 1.54,                                    │
│       "status": "pending"                                       │
│     }                                                           │
│   }                                                             │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ 7. GERAR PIX                                                    │
├─────────────────────────────────────────────────────────────────┤
│ GET /api/v1/orders/20260228-A1B2C3/payment                      │
│ Headers: Authorization: Bearer 4|ABC123...                      │
│                                                                 │
│ 🔒 VALIDAÇÃO DE SEGURANÇA:                                     │
│   1. Busca order:                                               │
│      $order = Order::where('order_code', '20260228-A1B2C3')    │
│                    ->firstOrFail();                             │
│                                                                 │
│   2. Traduz customer central → tenant:                          │
│      $tenantCustomer = getTenantCustomer($request->user());    │
│      // Central ID: 2 → Tenant ID: 3                           │
│                                                                 │
│   3. Valida ownership:                                          │
│      if ($order->customer_id !== $tenantCustomer->id) {        │
│          throw new Exception('Pedido não encontrado');         │
│      }                                                          │
│      // Compara: 3 === 3 ✅                                     │
│                                                                 │
│ 📧 EMAIL FALLBACK:                                             │
│   PagarMeService::createPixPayment()                            │
│                                                                 │
│   1. Verifica email:                                            │
│      $email = $customer->email; // NULL                        │
│                                                                 │
│   2. Gera fallback:                                             │
│      if (empty($email)) {                                       │
│          $tenant = tenant(); // marmitaria-gi                  │
│          $email = "cliente-3@marmitaria-gi.yumgo.com.br";     │
│          \Log::info('💡 Cliente sem email, usando fallback');  │
│      }                                                          │
│                                                                 │
│   3. Cria pagamento Pagar.me:                                   │
│      POST https://api.pagar.me/core/v5/orders                  │
│      {                                                          │
│        "customer": {                                            │
│          "name": "Elizeu Santos",                              │
│          "email": "cliente-3@marmitaria-gi.yumgo.com.br",     │
│          "type": "individual",                                 │
│          "document": "00000000000"                             │
│        },                                                       │
│        "items": [                                               │
│          {                                                      │
│            "amount": 3946,      ← R$ 39,46 em centavos         │
│            "description": "Pedido #20260228-A1B2C3",           │
│            "quantity": 1                                        │
│          }                                                      │
│        ],                                                       │
│        "payments": [                                            │
│          {                                                      │
│            "payment_method": "pix",                             │
│            "pix": {                                             │
│              "expires_in": 3600                                │
│            }                                                    │
│          }                                                      │
│        ]                                                        │
│      }                                                          │
│                                                                 │
│   4. Pagar.me retorna:                                          │
│      {                                                          │
│        "id": "or_ABC123",                                       │
│        "charges": [{                                            │
│          "last_transaction": {                                 │
│            "qr_code": "00020126...",                           │
│            "qr_code_url": "data:image/png;base64,iVBOR..."    │
│          }                                                      │
│        }]                                                       │
│      }                                                          │
│                                                                 │
│   5. Salva em payments:                                         │
│      INSERT INTO payments (                                     │
│          order_id = 47,                                         │
│          gateway = 'pagarme',                                   │
│          transaction_id = 'or_ABC123',                         │
│          amount = 39.46,                                        │
│          status = 'pending',                                    │
│          pix_qrcode = '00020126...',                           │
│          pix_qrcode_image = 'data:image/png;base64,iVBOR...'  │
│      )                                                          │
│                                                                 │
│ Resposta:                                                       │
│   {                                                             │
│     "payment": {                                                │
│       "method": "pix",                                          │
│       "qrcode_image": "data:image/png;base64,iVBOR...",        │
│       "qrcode_text": "00020126...",                            │
│       "expires_at": "2026-02-28 15:30:00"                      │
│     },                                                          │
│     "order": {                                                  │
│       "order_code": "20260228-A1B2C3",                         │
│       "total": 39.46,                                           │
│       "cashback_used": 1.54                                     │
│     }                                                           │
│   }                                                             │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ 8. CLIENTE PAGA PIX                                             │
├─────────────────────────────────────────────────────────────────┤
│ Cliente escaneia QR Code no app do banco                       │
│ Paga: R$ 39,46                                                  │
│                                                                 │
│ Banco processa:                                                 │
│   - Debita conta do cliente                                     │
│   - Credita Pagar.me                                            │
│   - Pagar.me notifica via webhook                              │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ 9. WEBHOOK PAGAR.ME                                             │
├─────────────────────────────────────────────────────────────────┤
│ POST /api/webhooks/pagarme                                      │
│ {                                                               │
│   "id": "hook_123",                                             │
│   "type": "charge.paid",                                        │
│   "data": {                                                     │
│     "id": "ch_ABC123",                                          │
│     "order": {                                                  │
│       "id": "or_ABC123"                                         │
│     },                                                          │
│     "status": "paid",                                           │
│     "amount": 3946                                              │
│   }                                                             │
│ }                                                               │
│                                                                 │
│ 📋 PROCESSAMENTO:                                              │
│   1. Busca payment:                                             │
│      $payment = Payment::where('transaction_id', 'or_ABC123')  │
│                       ->firstOrFail();                          │
│                                                                 │
│   2. Atualiza status:                                           │
│      UPDATE payments SET status = 'paid'                        │
│      WHERE id = payment.id;                                     │
│                                                                 │
│   3. Atualiza order:                                            │
│      UPDATE orders SET                                          │
│          payment_status = 'paid',                               │
│          status = 'confirmed'                                   │
│      WHERE id = 47;                                             │
│                                                                 │
│   4. 💰 CALCULA CASHBACK GANHO:                                │
│      CashbackService::calculateCashback()                       │
│                                                                 │
│      Regras (exemplo):                                          │
│      - Bronze: 2% cashback                                      │
│      - Total pago: R$ 39,46 (já descontado cashback usado)    │
│      - Cashback ganho: 39.46 × 0.02 = R$ 0,79                 │
│                                                                 │
│      UPDATE customers SET                                       │
│          cashback_balance = 0.00 + 0.79,                       │
│          total_orders = total_orders + 1,                       │
│          total_spent = total_spent + 39.46                     │
│      WHERE id = 3;                                              │
│                                                                 │
│      INSERT INTO cashback_transactions (                        │
│          customer_id = 3,                                       │
│          order_id = 47,                                         │
│          type = 'earned',                                       │
│          amount = 0.79,                                         │
│          balance_after = 0.79,                                  │
│          description = 'Cashback do pedido #20260228-A1B2C3'  │
│      );                                                         │
│                                                                 │
│   5. ✅ Notifica restaurante (cozinha)                         │
│   6. 📧 Envia email para cliente (se tiver)                    │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ 10. RESULTADO FINAL                                             │
├─────────────────────────────────────────────────────────────────┤
│ Cliente Elizeu Santos (Tenant ID: 3):                          │
│   - Pedido confirmado: #20260228-A1B2C3                        │
│   - Total pago: R$ 39,46 (com desconto de R$ 1,54)            │
│   - Cashback usado: R$ 1,54 (saldo zerado)                    │
│   - Cashback ganho: R$ 0,79 (novo saldo)                      │
│   - Total de pedidos: 1                                         │
│   - Total gasto: R$ 39,46                                      │
│                                                                 │
│ Restaurante Marmitaria da Gi:                                  │
│   - Receberá: R$ 39,46 (já descontado cashback)               │
│   - Comissão plataforma: R$ 1,18 (3%)                         │
│   - Líquido restaurante: R$ 38,28                             │
│                                                                 │
│ Histórico Cashback:                                             │
│   1. [28/02 14:30] Usado: -R$ 1,54 (Pedido #20260228-A1B2C3) │
│   2. [28/02 14:35] Ganho: +R$ 0,79 (Pedido #20260228-A1B2C3) │
│   → Saldo atual: R$ 0,79                                       │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📁 Arquivos Principais

### Controllers

**app/Http/Controllers/Api/OrderController.php**
- **Função**: API REST para criação e gerenciamento de pedidos
- **Decisões importantes**:
  - `getTenantCustomer()` helper para traduzir customer central → tenant
  - Validação de cashback usando `$customer->refresh()` (sempre do banco)
  - Toggle boolean `use_cashback` ao invés de input numérico
  - 5 validações de ownership usando `getTenantCustomer()`
- **Rotas**:
  - `POST /api/v1/orders` - Criar pedido
  - `GET /api/v1/orders/{orderCode}` - Detalhes do pedido
  - `GET /api/v1/orders/{orderCode}/payment` - Página de pagamento
  - `GET /api/v1/orders/{orderCode}/status` - Status do pagamento
  - `POST /api/v1/orders/{orderCode}/cancel` - Cancelar pedido

**app/Http/Controllers/Api/AuthController.php**
- **Função**: Autenticação centralizada (multi-tenant)
- **Decisões importantes**:
  - Login social (WhatsApp, Google)
  - Email opcional (pode ser NULL)
  - Token Sanctum para autenticação

**app/Http/Controllers/CentralWebhookController.php**
- **Função**: Recebe webhooks do Pagar.me
- **Decisões importantes**:
  - Atualiza payment e order status
  - Dispara cálculo de cashback ganho
  - Notifica restaurante e cliente

### Services

**app/Services/OrderService.php**
- **Função**: Lógica de negócio para criação de pedidos
- **Decisões importantes**:
  - Sincronização automática de customers (central → tenant)
  - Enriquecimento de items (busca product details)
  - Limita cashback ao total (evita total negativo)
  - Integração com CashbackService e PagarMeService

**app/Services/PagarMeService.php**
- **Função**: Integração com gateway Pagar.me
- **Decisões importantes**:
  - Email fallback usando domínio do restaurante
  - Cria/busca customer no Pagar.me
  - Gera QR Code PIX
  - Valida webhooks

**app/Services/CashbackService.php**
- **Função**: Gerenciamento de cashback
- **Decisões importantes**:
  - `useCashback()` - Debita saldo do cliente
  - `calculateCashback()` - Calcula cashback ganho
  - Regras por loyalty tier
  - Histórico em `cashback_transactions`

**app/Services/TenantService.php**
- **Função**: Gerenciamento de tenants (restaurantes)
- **Decisões importantes**:
  - Cria schema PostgreSQL para cada tenant
  - Roda migrations tenant
  - Configura domínio

### Models

**app/Models/Customer.php** (Tenant)
- **Schema**: TENANT_*
- **CRÍTICO**: NÃO definir `$connection` (usa tenancy automático)
- **Campos importantes**:
  - `cashback_balance` - Saldo isolado por restaurante
  - `loyalty_tier` - Bronze/Prata/Ouro/Platina
  - `email` - Pode ser NULL (login WhatsApp)

**app/Models/CentralCustomer.php** (novo modelo - sugerido)
- **Schema**: PUBLIC
- **Campos**:
  - `id` - ID único global
  - `email` - Único, pode ser NULL
  - `phone` - Obrigatório para WhatsApp
  - `password` - NULL para login social

**app/Models/Order.php**
- **Schema**: TENANT_*
- **Campos importantes**:
  - `customer_id` - Referência ao tenant customer (não central!)
  - `cashback_used` - Quanto foi usado neste pedido
  - `cashback_earned` - Quanto foi ganho (calculado depois)
  - `payment_status` - pending/paid/failed

**app/Models/Payment.php**
- **Schema**: TENANT_*
- **Campos importantes**:
  - `transaction_id` - ID do Pagar.me
  - `pix_qrcode` - Texto do QR Code
  - `pix_qrcode_image` - Base64 da imagem

### Migrations

**Críticas para Multi-Tenant:**

```
database/migrations/
├── 2019_09_15_000010_create_tenants_table.php       # PUBLIC
├── 2026_02_24_001007_create_central_customers_table.php  # PUBLIC
│
└── tenant/
    ├── 2026_02_21_003637_create_customers_table.php     # TENANT_*
    ├── 2026_02_21_003638_create_cashback_settings_table.php
    ├── 2026_02_21_003646_create_cashback_transactions_table.php
    ├── 2026_02_21_003643_create_orders_table.php
    └── 2026_02_21_003645_create_payments_table.php
```

### Rotas

**routes/api.php** (Central)
```php
// Login centralizado
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/social/{provider}/callback', [AuthController::class, 'socialCallback']);

// Webhook (central)
Route::post('/webhooks/pagarme', [CentralWebhookController::class, 'handle']);
```

**routes/tenant.php** (Restaurante)
```php
// API REST (APENAS middleware tenancy, SEM 'api')
Route::prefix('api/v1')->middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Públicas
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);

    // Protegidas
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{orderCode}', [OrderController::class, 'showOrder']);
        Route::get('/orders/{orderCode}/payment', [OrderController::class, 'showPayment']);
        Route::get('/customers/me', [CustomerController::class, 'me']);
    });
});
```

**⚠️ IMPORTANTE:**
- Rotas tenant já carregam com middleware 'web' (bootstrap/app.php)
- **NUNCA adicionar middleware 'api' em rotas tenant** (causa crash)
- Sanctum funciona perfeitamente com 'web' middleware

---

## 🔧 Comandos Úteis

### Migrations

```bash
# Central (PUBLIC)
php artisan migrate

# Tenants (TENANT_*)
php artisan tenants:migrate

# Rollback tenant específico
php artisan tenants:migrate-rollback --tenants=1
```

### Criar Tenant

```bash
php artisan tinker

$tenant = \App\Models\Tenant::create([
    'name' => 'Novo Restaurante',
    'slug' => 'novo-restaurante',
    'email' => 'contato@novorestaurante.com.br',
]);

$tenant->domains()->create([
    'domain' => 'novo-restaurante.yumgo.com.br',
]);
```

### Debugar Customer Sync

```bash
php artisan tinker

tenancy()->initialize(\App\Models\Tenant::first());

// Central customer
$central = App\Models\CentralCustomer::find(2);

// Buscar/criar no tenant
$tenant = \App\Models\Customer::where('phone', $central->phone)->first();

// Se não existe, cria
if (!$tenant) {
    $tenant = \App\Models\Customer::create([
        'name' => $central->name,
        'phone' => $central->phone,
        'email' => $central->email,
        'cashback_balance' => 0,
    ]);
}

echo "Central ID: {$central->id}\n";
echo "Tenant ID: {$tenant->id}\n";
```

### Verificar Cashback

```bash
php artisan tinker

tenancy()->initialize(\App\Models\Tenant::where('slug', 'marmitaria-gi')->first());

$customer = \App\Models\Customer::find(3);
echo "Saldo: R$ {$customer->cashback_balance}\n";

// Histórico
$transactions = $customer->cashbackTransactions()->latest()->get();
foreach ($transactions as $tx) {
    echo "[{$tx->created_at}] {$tx->type}: R$ {$tx->amount}\n";
}
```

---

## ✅ Checklist de Implementação

### Schemas Multi-Tenant
- [x] PostgreSQL com schemas separados
- [x] Migration tenant rodando em todos schemas
- [x] Isolamento total de dados
- [x] Impossível query cruzada entre tenants

### Login Centralizado
- [x] Tabela central_customers no PUBLIC
- [x] Login social (WhatsApp, Google)
- [x] Email opcional (pode ser NULL)
- [x] Token Sanctum funcionando

### Sincronização de Customers
- [x] OrderService sincroniza automático
- [x] Busca por email/phone
- [x] Cria customer no tenant se não existir
- [x] Logs informativos de sync

### Email Fallback
- [x] PagarMeService gera email fallback
- [x] Formato: cliente-{id}@{tenant-slug}.yumgo.com.br
- [x] Domínio real (yumgo.com.br)
- [x] LGPD compliance
- [x] Logs quando usa fallback

### Sistema de Cashback
- [x] Cashback isolado por restaurante
- [x] Toggle boolean (UX simplificado)
- [x] Limita ao total do pedido
- [x] Calcula cashback ganho após pagamento
- [x] Histórico completo em cashback_transactions

### Validações de Segurança
- [x] getTenantCustomer() helper
- [x] 5 validações corrigidas em OrderController
- [x] Compara IDs corretos (tenant com tenant)
- [x] Impossível acessar pedido de outro cliente

### Fluxo End-to-End
- [x] Login → Produtos → Carrinho → Checkout → PIX → Webhook
- [x] QR Code aparecendo corretamente
- [x] Cashback usado e ganho funcionando
- [x] Email fallback não quebrando pagamento

---

## 🎓 Lições Aprendidas

### 1. Multi-Tenant com Schemas PostgreSQL

✅ **O que funciona:**
- Schemas nativos do PostgreSQL
- Isolamento total sem performance loss
- Migrations centralizadas (tenants:migrate)
- Backup único (pg_dump)

❌ **O que não funciona:**
- FOREIGN KEY entre schemas
- Usar `$connection = 'pgsql'` em models tenant
- Queries cruzadas sem especificar schema

### 2. Login Centralizado vs Customers Isolados

✅ **Decisão correta:**
- Login central (1 token para todos restaurantes)
- Customers isolados (dados separados por restaurante)
- Sincronização automática (transparente para usuário)

❌ **Alternativa descartada:**
- Customer unificado (quebraria isolamento)
- Login separado por restaurante (UX ruim)

### 3. Email Fallback para Gateways

✅ **Solução elegante:**
- Domínio real do restaurante
- Email único (baseado em ID)
- LGPD compliance
- Gateway aceita sem problemas

❌ **Alternativas ruins:**
- Email fake (viola termos)
- Bloquear login sem email (perde clientes)
- Forçar email obrigatório (UX ruim)

### 4. Cashback Toggle vs Input Numérico

✅ **Toggle boolean:**
- Mais simples para usuário
- Mobile-friendly
- Menos erros
- Usa saldo completo automaticamente

❌ **Input numérico:**
- Usuário precisa digitar
- Teclado numérico no mobile
- Mais propenso a erros
- UX pior

### 5. Middleware 'web' vs 'api'

✅ **Rotas tenant com 'web':**
- Sanctum funciona perfeitamente
- Tenancy depende de sessões
- Laravel 11 não precisa de 'api'

❌ **Misturar 'web' + 'api':**
- Causa SIGSEGV (crash PHP-FPM)
- Conflito stateful vs stateless
- Impossível debugar

---

## 📞 Suporte

**Documentação relacionada:**
- `/docs/features/01-cashback-configuration.md` - Cashback configurável
- `/docs/features/02-payment-system.md` - Integração Pagar.me
- `/docs/database/01-schema-design.md` - Schema completo
- `/EMAIL-FALLBACK-28-02-2026.md` - Email fallback
- `/CASHBACK-AUTOMATICO-28-02-2026.md` - Toggle de cashback

**Arquivos de debug:**
- `/test-cashback-toggle.php` - Testa toggle de cashback
- `/test-pix-order.php` - Testa fluxo completo de pedido

---

**🚀 Arquitetura sólida, escalável e pronta para crescer!**

**Data última atualização:** 28/02/2026
