# 👥 Gestão de Usuários - DeliveryPro

## 🔑 3 Tipos de Usuários

### 1️⃣ **Administradores da PLATAFORMA** (Você)
- **Painel:** `https://food.eliseus.com.br/admin`
- **Modelo:** `App\Models\PlatformUser`
- **Gerencia:** Tenants, Planos, Assinaturas, Faturas
- **Tabela:** `platform_users` (schema PUBLIC)

### 2️⃣ **Administradores dos RESTAURANTES** (Seus clientes)
- **Painel:** `https://{slug}.eliseus.com.br/painel`
- **Modelo:** `App\Models\User` (tenant context)
- **Gerencia:** Produtos, Pedidos, Clientes, Equipe
- **Tabela:** `users` (schema TENANT_*)

### 3️⃣ **Clientes FINAIS** (Pessoas que fazem pedidos)
- **App:** `https://{slug}.eliseus.com.br`
- **Modelo:** `App\Models\Customer` (tenant context)
- **Fazem:** Pedidos, avaliam produtos, acumulam cashback
- **Tabela:** `customers` (schema TENANT_*)

---

## 🏗️ Fluxo Completo de Onboarding

### Quando um NOVO CLIENTE ASSINA UM PLANO:

```
1. Cliente acessa: food.eliseus.com.br
2. Clica em "Criar Conta" ou "Assinar Plano"
3. Preenche formulário:
   - Nome do Restaurante
   - E-mail
   - Telefone
   - CNPJ/CPF
   - Escolhe o plano (Starter/Pro/Enterprise)
4. Sistema cria AUTOMATICAMENTE:
   ├─ Tenant (tabela tenants)
   ├─ Domain (restaurante-slug.eliseus.com.br)
   ├─ Sub-conta Asaas
   ├─ Subscription (ativa)
   ├─ Schema PostgreSQL (TENANT_*)
   └─ Usuário Admin (email@restaurante.com)
5. Envia e-mail com:
   - URL do painel
   - Credenciais de acesso
   - Link para configurar a conta
```

---

## ✅ Como Criar Usuários ADMIN para Restaurantes Existentes

### Opção 1: Via Seeder (Automático)

```bash
php artisan db:seed --class=CreateInitialRestaurantAdmins
```

**O que faz:**
- Percorre todos os tenants
- Cria usuário admin se não existir
- E-mail: `admin@{tenant-id}.com`
- Senha: `password`

**Resultado da última execução:**
```
✅ Sushi House → admin@sushi-house.com / password
✅ Marmitaria da Gi → admin@marmitaria-gi.com / password
✅ Food Delivery → admin@122478a1-f809-4797-97a3-9b929df9854b.com / password
```

### Opção 2: Via Painel do Restaurante (Manual)

1. Acesse: `https://{slug}.eliseus.com.br/painel`
2. Faça login com admin existente
3. Menu → **Configurações** → **Equipe**
4. Clique em **+ Novo Usuário**
5. Preencha:
   - Nome: João da Silva
   - E-mail: joao@restaurante.com
   - Senha: ********
   - Função: Admin, Gerente, Cozinha, etc.
6. Salvar

### Opção 3: Via Tinker (Desenvolvimento)

```bash
php artisan tinker
```

```php
// Inicializa contexto do tenant
$tenant = App\Models\Tenant::find('marmitaria-gi');
tenancy()->initialize($tenant);

// Cria usuário admin
$user = App\Models\User::create([
    'name' => 'Admin Principal',
    'email' => 'admin@marmitariagi.com',
    'password' => Hash::make('senha123'),
    'role' => 'admin',
    'active' => true,
    'email_verified_at' => now(),
]);

echo "✅ Usuário criado: {$user->email}";
```

---

## 🆕 Como Criar NOVO TENANT (Cliente que Assinou)

### Opção 1: Via Painel Admin (Recomendado)

1. Acesse: `https://food.eliseus.com.br/admin`
2. Menu → **Tenants**
3. Clique em **+ Novo Tenant**
4. Preencha formulário:
   ```
   Nome: Pizzaria do Zé
   E-mail: contato@pizzariadoze.com
   Slug: pizzaria-do-ze (auto-gera domínio)
   Plano: Pro
   Status: Ativo
   ```
5. Salvar

**Sistema cria automaticamente:**
- ✅ Tenant no banco
- ✅ Domain `pizzaria-do-ze.eliseus.com.br`
- ✅ Sub-conta Asaas
- ✅ Schema PostgreSQL `tenant_pizzaria-do-ze`
- ✅ Todas as tabelas (products, orders, customers, etc.)

### Opção 2: Via Artisan (Desenvolvimento)

```bash
php artisan tenants:create
```

Responda as perguntas interativas.

### Opção 3: Via TenantService (Programático)

```php
use App\Services\TenantService;

$tenantService = app(TenantService::class);

$tenant = $tenantService->createTenant([
    'name' => 'Sushi Premium',
    'email' => 'contato@sushipremium.com',
    'plan_id' => 2, // Plan::where('name', 'Pro')->first()->id
]);

// Retorna o tenant criado com todas as configurações
```

---

## 📋 Credenciais Criadas Automaticamente

### Para cada tenant criado, o sistema gera:

**1. Acesso ao Painel:**
- URL: `https://{slug}.eliseus.com.br/painel`
- E-mail: `admin@{slug}.com`
- Senha: `password`

**2. Primeiros Dados de Teste:**
- 5 categorias
- 10 produtos
- Configurações padrão
- Cashback configurado

**3. Conta Asaas:**
- Sub-conta criada
- Webhook configurado
- Pronta para receber pagamentos

---

## 🔒 Segurança

### Recuperação de Senha:

Ainda não implementado, mas o fluxo seria:

```php
// Route em routes/tenant.php
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// Envia e-mail com token
// Link: https://{slug}.eliseus.com.br/reset-password/{token}
```

### Verificação de E-mail:

Já configurado no User model:
```php
public function canAccessPanel(Panel $panel): bool
{
    if ($panel->getId() === 'restaurant') {
        return $this->active && $this->hasVerifiedEmail();
    }
    return false;
}
```

---

## 📊 Estrutura de Permissões

### Roles Disponíveis (Filament Shield):

- **Admin** - Acesso total
- **Gerente** - Gestão operacional
- **Atendente** - Pedidos e clientes
- **Cozinha** - Ver pedidos e atualizar status
- **Entregador** - Ver entregas e atualizar status

### Configurar Permissões:

1. Menu → **Configurações** → **Funções**
2. Edite cada role
3. Marque/desmarque recursos
4. Salvar

---

## 🆘 Problemas Comuns

### "Não consigo fazer login no painel"

**Possíveis causas:**
1. E-mail não verificado → Rode seeder com `email_verified_at => now()`
2. Usuário inativo → `active = false`
3. Tenant não inicializado → Verifique domínio correto
4. Senha errada → Use `password` padrão ou resete

**Solução rápida:**
```bash
php artisan tinker

$tenant = App\Models\Tenant::find('seu-slug');
tenancy()->initialize($tenant);

$user = App\Models\User::where('email', 'admin@seu-slug.com')->first();
$user->password = Hash::make('nova-senha');
$user->email_verified_at = now();
$user->active = true;
$user->save();
```

### "Como adicionar mais usuários à equipe?"

Use o **UserResource** que criamos:
- Acesse: `{slug}.eliseus.com.br/painel/users`
- Clique em **+ Novo**
- Preencha dados
- Salvar

---

## ✅ Resumo de Comandos

```bash
# Criar usuários admin para todos os tenants
php artisan db:seed --class=CreateInitialRestaurantAdmins

# Criar novo tenant interativamente
php artisan tenants:create

# Listar todos os tenants
php artisan tenants:list

# Rodar migrations para todos os tenants
php artisan tenants:migrate

# Rodar migrations para tenant específico
php artisan tenants:migrate --tenants=marmitaria-gi

# Limpar cache
php artisan optimize:clear
```

---

## 🎯 Checklist de Onboarding

Quando um novo cliente assinar:

- [ ] Criar tenant via painel admin
- [ ] Verificar se domínio foi criado
- [ ] Verificar se schema PostgreSQL existe
- [ ] Criar usuário admin inicial
- [ ] Enviar e-mail com credenciais
- [ ] Configurar conta Asaas
- [ ] Importar produtos (se necessário)
- [ ] Treinar cliente no uso do painel
- [ ] Ativar webhook
- [ ] Testar pedido de ponta a ponta

---

**✅ Sistema de Gestão de Usuários Completo!**
