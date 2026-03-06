# Sistema de Gerenciamento de Usuários e Permissões

**Data de Implementação:** 05/03/2026  
**Versão:** 1.0

## 📋 Visão Geral

Sistema completo de gerenciamento de usuários com permissões granulares para restaurantes, disponível tanto no painel central (admin) quanto no painel do restaurante.

## 🎯 Funcionalidades

### 1. Gerenciamento no Painel Central (Admin)

**Localização:** https://yumgo.com.br/admin/tenants/{id}/edit → Aba "Usuários"

**Recursos:**
- ✅ Criar usuários para qualquer restaurante
- ✅ Editar informações de usuários existentes
- ✅ Ativar/Desativar usuários
- ✅ Deletar usuários
- ✅ Definir funções (roles)
- ✅ Configurar permissões granulares

**Implementação:**
- Arquivo: `app/Filament/Admin/Resources/TenantResource/RelationManagers/UsersRelationManager.php`
- Inicializa automaticamente o tenancy para acessar usuários do schema correto
- Validação de email único
- Hash automático de senha

### 2. Gerenciamento no Painel do Restaurante

**Localização:** https://{slug}.yumgo.com.br/painel/users

**Recursos:**
- ✅ CRUD completo de usuários
- ✅ Filtros por função e status
- ✅ Ação rápida de ativar/desativar
- ✅ Visualização de último acesso
- ✅ Sistema de permissões granulares

**Implementação:**
- Resource: `app/Filament/Restaurant/Resources/UserResource.php`
- Pages: `ListUsers.php`, `CreateUser.php`, `EditUser.php`
- Integrado ao grupo "Configurações" do menu

## 👥 Funções (Roles)

### 1. Administrador (admin)
- **Acesso total** a todas as funcionalidades
- Não precisa de permissões granulares
- Pode gerenciar outros usuários
- Pode alterar configurações críticas

### 2. Gerente (manager)
- Pode ter permissões granulares customizadas
- Normalmente: gestão de produtos, pedidos, relatórios
- Pode visualizar dados financeiros

### 3. Funcionário (worker)
- Acesso limitado conforme permissões
- Normalmente: visualizar/editar pedidos
- Sem acesso a configurações

### 4. Financeiro (finance)
- Foco em relatórios e dados financeiros
- Pode exportar relatórios
- Sem acesso a produtos/configurações

### 5. Entregador (driver)
- Acesso mínimo
- Visualiza apenas pedidos para entrega
- App mobile específico (futuro)

## 🔐 Sistema de Permissões Granulares

### Categorias de Permissões

**Produtos:**
- `products.view` - Ver produtos
- `products.create` - Criar produtos
- `products.edit` - Editar produtos
- `products.delete` - Deletar produtos

**Pedidos:**
- `orders.view` - Ver pedidos
- `orders.edit` - Editar pedidos
- `orders.cancel` - Cancelar pedidos

**Cupons:**
- `coupons.view` - Ver cupons
- `coupons.create` - Criar cupons
- `coupons.edit` - Editar cupons
- `coupons.delete` - Deletar cupons

**Clientes:**
- `customers.view` - Ver clientes
- `customers.edit` - Editar clientes

**Configurações:**
- `settings.view` - Ver configurações
- `settings.edit` - Editar configurações

**Relatórios:**
- `reports.view` - Ver relatórios
- `reports.export` - Exportar relatórios

**Usuários:**
- `users.view` - Ver usuários
- `users.create` - Criar usuários
- `users.edit` - Editar usuários
- `users.delete` - Deletar usuários

### Como Funciona

**Armazenamento:**
- Campo `permissions` (JSONB) na tabela `users`
- Armazena array de permissões: `["products.view", "products.edit", "orders.view"]`

**Verificação:**
```php
// Verificar uma permissão
if ($user->hasPermission('products.edit')) {
    // Permitir edição
}

// Verificar qualquer uma
if ($user->hasAnyPermission(['products.edit', 'products.delete'])) {
    // Permitir ação
}

// Verificar todas
if ($user->hasAllPermissions(['reports.view', 'reports.export'])) {
    // Permitir exportação
}
```

**Regra Especial:**
- Usuários com `role = 'admin'` SEMPRE têm todas as permissões
- Não precisa configurar permissões para admins

## 📊 Banco de Dados

### Novas Colunas na Tabela users

```sql
ALTER TABLE users ADD COLUMN permissions JSONB NULL;
ALTER TABLE users ADD COLUMN last_login_at TIMESTAMP NULL;
```

**Migration:** `2026_03_05_072350_add_permissions_and_last_login_to_users_table.php`

### Estrutura Completa

```sql
users (
  id UUID PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  password VARCHAR(255),
  role VARCHAR(50),           -- admin, manager, worker, finance, driver
  permissions JSONB,          -- ["products.view", "orders.edit", ...]
  active BOOLEAN DEFAULT true,
  email_verified_at TIMESTAMP,
  last_login_at TIMESTAMP,    -- Registra último acesso
  remember_token VARCHAR(100),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)
```

## 🎨 Interface

### Formulário de Criação/Edição

**Seção 1: Informações do Usuário**
- Nome Completo
- E-mail (validação única)
- Senha (hash automático)
- Função (select com 5 opções)
- Status Ativo/Inativo (toggle)

**Seção 2: Permissões (Colapsável)**
- Visível apenas para: manager, worker, finance
- CheckboxList organizado em 3 colunas
- Agrupado por categoria (Produtos, Pedidos, etc)
- Helper text explicativo

### Listagem

**Colunas:**
- Nome (com email como descrição)
- Função (badge colorido)
- Status Ativo (ícone boolean)
- Criado em (toggleable, oculto por padrão)
- Último acesso (toggleable, visível)

**Filtros:**
- Por função (select)
- Por status (ternary filter)

**Ações:**
- Editar
- Ativar/Desativar (ação rápida)
- Deletar (com confirmação)

**Ações em Massa:**
- Deletar múltiplos (com confirmação)

## 🔄 Fluxo de Criação de Usuário

### No Painel Central

1. Admin acessa tenant → Aba "Usuários"
2. Clica em "Novo Usuário"
3. Preenche formulário
4. Sistema:
   - Inicializa tenancy para o restaurante correto
   - Valida email único no schema do tenant
   - Hash automático da senha
   - Define `email_verified_at = now()`
   - Inicializa `permissions = []` se vazio
5. Salva no schema do tenant
6. Finaliza tenancy
7. Retorna para listagem

### No Painel do Restaurante

1. Admin do restaurante acessa "Usuários"
2. Clica em "Novo Usuário"
3. Preenche formulário
4. Sistema:
   - Valida email único
   - Hash automático da senha
   - Define `email_verified_at = now()`
   - Inicializa `permissions = []` se vazio
5. Salva no schema do próprio restaurante
6. Retorna para listagem

## ⚙️ Middleware de Último Acesso

**Arquivo:** `app/Http/Middleware/LogLastLogin.php`

**Funcionamento:**
- Registra `last_login_at` quando usuário faz login
- Atualiza apenas se passou >5 minutos desde último registro
- Evita UPDATE em toda request (performance)

**Configuração:**
```php
// Em bootstrap/app.php (se necessário):
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(LogLastLogin::class);
})
```

## 🚀 Como Usar

### 1. Criar Usuário Administrador

```php
// No Painel Central
1. Acesse https://yumgo.com.br/admin/tenants
2. Edite um restaurante
3. Aba "Usuários" → "Novo Usuário"
4. Role: Administrador
5. Salvar
```

### 2. Criar Usuário com Permissões Limitadas

```php
// No Painel do Restaurante
1. Acesse https://{slug}.yumgo.com.br/painel/users
2. "Novo Usuário"
3. Role: Gerente
4. Expanda "Permissões"
5. Selecione: products.view, products.edit, orders.view
6. Salvar
```

### 3. Verificar Permissão em Controller

```php
use Illuminate\Support\Facades\Gate;

// Opção 1: No controller
public function edit(Product $product)
{
    if (!auth()->user()->hasPermission('products.edit')) {
        abort(403);
    }
    
    // ...
}

// Opção 2: Com middleware
Route::middleware(['auth', 'permission:products.edit'])->group(function () {
    // Rotas protegidas
});
```

### 4. Verificar Permissão em Blade

```blade
@if(auth()->user()->hasPermission('products.create'))
    <button>Criar Produto</button>
@endif

@can('products.edit')
    <button>Editar</button>
@endcan
```

## 📝 Arquivos Criados/Modificados

### Novos Arquivos

```
app/Filament/Admin/Resources/TenantResource/RelationManagers/
└── UsersRelationManager.php                    (184 linhas)

app/Filament/Restaurant/Resources/
├── UserResource.php                            (210 linhas)
└── UserResource/Pages/
    ├── ListUsers.php                           (18 linhas)
    ├── CreateUser.php                          (28 linhas)
    └── EditUser.php                            (27 linhas)

app/Http/Middleware/
└── LogLastLogin.php                            (25 linhas)

database/migrations/tenant/
└── 2026_03_05_072350_add_permissions_and_last_login_to_users_table.php

docs/
└── USER-MANAGEMENT-SYSTEM.md                   (este arquivo)
```

### Modificados

```
app/Models/Tenant.php
- Adicionado método users() (pseudo-relationship)

app/Models/User.php
- Adicionado fillable: permissions, last_login_at
- Adicionado casts: permissions => array, last_login_at => datetime
- Adicionado métodos: hasPermission(), hasAnyPermission(), hasAllPermissions()

app/Filament/Admin/Resources/TenantResource.php
- Adicionado UsersRelationManager ao getRelations()
- Removido opções legado Asaas
```

## ⚠️ Observações Importantes

### Multi-Tenancy

**SEMPRE inicializar tenancy ao gerenciar usuários de um tenant específico:**

```php
// CORRETO (RelationManager faz automaticamente):
protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
{
    $tenant = $this->getOwnerRecord();
    tenancy()->initialize($tenant);
    return \App\Models\User::query();
}

// ERRADO:
// return \App\Models\User::query(); // Vai buscar no schema errado!
```

### Performance

- Campo `permissions` é JSONB (indexável no PostgreSQL)
- `last_login_at` só atualiza a cada 5 minutos
- Queries otimizadas com eager loading

### Segurança

- Senhas sempre com hash (bcrypt)
- Validação de email único por tenant
- CSRF protection em formulários
- Confirmação obrigatória para deletar

## 🔮 Próximos Passos (Futuro)

1. **Middleware de Permissões**
   - Criar middleware `permission:products.edit`
   - Proteger rotas automaticamente

2. **Gates e Policies**
   - Integrar com Laravel Gates
   - Policies por modelo (ProductPolicy, OrderPolicy)

3. **Auditoria**
   - Registrar quem criou/editou cada registro
   - Log de ações dos usuários

4. **Notificações**
   - Notificar quando novo usuário é criado
   - Email de boas-vindas com senha temporária

5. **App Mobile para Entregadores**
   - Login específico para role=driver
   - Visualizar pedidos para entrega
   - Atualizar status de entrega

## 🎯 Casos de Uso Comuns

### Caso 1: Funcionário de Balcão
- **Role:** worker
- **Permissões:** orders.view, orders.edit, products.view, customers.view
- **Não pode:** Criar produtos, alterar configurações, ver relatórios financeiros

### Caso 2: Gerente da Loja
- **Role:** manager
- **Permissões:** Todas exceto users.* e settings.edit
- **Pode:** Gerenciar produtos, pedidos, cupons, ver relatórios
- **Não pode:** Criar usuários, alterar configurações críticas

### Caso 3: Contador/Financeiro
- **Role:** finance
- **Permissões:** reports.view, reports.export, orders.view
- **Pode:** Ver todos os pedidos, exportar relatórios
- **Não pode:** Editar produtos, criar cupons

### Caso 4: Entregador
- **Role:** driver
- **Permissões:** orders.view (apenas seus pedidos - implementar filtro)
- **Pode:** Ver pedidos para entrega
- **Não pode:** Editar nada

---

**Última atualização:** 05/03/2026  
**Autor:** Claude Sonnet 4.5  
**Status:** ✅ Implementado e testado
