# 🔍 ONDE ENCONTRAR: Gerenciamento de Usuários

## 🎯 Opção 1: Painel Central (Admin)

### Passo a Passo com SCREENSHOTS

**1. Acesse:** https://yumgo.com.br/admin/tenants

**2. Você verá a listagem de restaurantes:**
```
┌─────────────────────────────────────────────┐
│  Restaurantes                               │
├─────────────────────────────────────────────┤
│  Logo │ Nome                │ Ações          │
│  [🍕] │ Marmitaria da Gi   │ [👁️] [✏️] [🗑️] │
│  [🍺] │ Boteco do Meu Rei  │ [👁️] [✏️] [🗑️] │
└─────────────────────────────────────────────┘
```

**3. Clique no ícone de EDITAR (lápis ✏️) em qualquer restaurante**

**4. Você será redirecionado para:**
`https://yumgo.com.br/admin/tenants/144c5973-f985-4309-8f9a-c404dd11feae/edit`

**5. Na página de edição, você verá ABAS no topo:**
```
┌─────────────────────────────────────────────┐
│  [Detalhes] [Usuários]                     │  ← ABAS AQUI!
├─────────────────────────────────────────────┤
│                                             │
│  (conteúdo da aba selecionada)              │
│                                             │
└─────────────────────────────────────────────┘
```

**6. Clique na aba "Usuários"**

**7. Você verá a listagem de usuários do restaurante:**
```
┌─────────────────────────────────────────────┐
│  Usuários                    [+ Novo]       │
├─────────────────────────────────────────────┤
│  Nome        │ Função    │ Ativo │ Ações   │
│  Admin Gi    │ Admin     │  ✓    │ [⋮]     │
└─────────────────────────────────────────────┘
```

**8. Clique em [+ Novo] para criar usuário**

---

## 🏪 Opção 2: Painel do Restaurante

### Passo a Passo

**1. Acesse o painel de QUALQUER restaurante:**
- https://marmitaria-gi.yumgo.com.br/painel
- OU https://botecodomeurei.yumgo.com.br/painel

**2. Faça login como admin do restaurante**

**3. No menu lateral ESQUERDO, procure o grupo "Configurações":**
```
Menu Lateral:
├─ 📊 Dashboard
├─ 📦 Pedidos
├─ 🍕 Produtos
├─ 🏷️ Categorias
├─ 🎟️ Cupons
├─ 👥 Clientes
├─ 📍 Bairros
└─ ⚙️ Configurações          ← ESTE GRUPO
    ├─ Dados do Restaurante
    ├─ Horários e Delivery
    ├─ Pagamentos
    ├─ Nota Fiscal
    └─ 👥 Usuários            ← AQUI!
```

**4. Clique em "Usuários" dentro de "Configurações"**

**5. Você será redirecionado para:**
`https://marmitaria-gi.yumgo.com.br/painel/users`

**6. Verá a mesma interface de gerenciamento:**
```
┌─────────────────────────────────────────────┐
│  Usuários                    [+ Novo Usuário]│
├─────────────────────────────────────────────┤
│  Nome        │ Função    │ Ativo │ Ações   │
│  Admin Gi    │ Admin     │  ✓    │ [⋮]     │
└─────────────────────────────────────────────┘
```

---

## ❓ Se NÃO Aparecer

### Problema 1: Aba "Usuários" não aparece no painel central

**Solução:**
```bash
cd /var/www/restaurante
php artisan filament:clear-cached-components
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear
```

Depois recarregue a página (Ctrl+Shift+R ou Cmd+Shift+R)

---

### Problema 2: Menu "Usuários" não aparece no painel do restaurante

**Verificação 1: Arquivo existe?**
```bash
ls -la /var/www/restaurante/app/Filament/Restaurant/Resources/UserResource.php
```
Deve retornar: `-rw-rw-r-- 1 ubuntu ubuntu 10003 Mar  5 10:22 UserResource.php`

**Verificação 2: Cache limpo?**
```bash
php artisan optimize:clear
```

**Verificação 3: Rota registrada?**
```bash
php artisan route:list | grep "painel/users"
```
Deve mostrar rotas como:
- GET /painel/users
- GET /painel/users/create
- POST /painel/users
- etc.

---

## 🧪 Teste Rápido AGORA

**Teste 1: URL Direta - Painel Central**
```
https://yumgo.com.br/admin/tenants/144c5973-f985-4309-8f9a-c404dd11feae/edit
```
(Substitua o UUID pelo ID de qualquer restaurante)

Deve mostrar a aba "Usuários"

**Teste 2: URL Direta - Painel Restaurante**
```
https://marmitaria-gi.yumgo.com.br/painel/users
```

Deve mostrar a listagem de usuários

---

## 📸 Como Deve Ficar

### Painel Central - Aba "Usuários"
```
┌────────────────────────────────────────────────────────────┐
│  Marmitaria da Gi                                          │
│  ┌──────────┬──────────┐                                  │
│  │ Detalhes │ Usuários │  ← CLIQUE AQUI                   │
│  └──────────┴──────────┘                                  │
│                                                            │
│  Usuários do Restaurante              [+ Novo Usuário]    │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Nome              │ E-mail           │ Função │ Ações││ │
│  ├──────────────────────────────────────────────────────┤ │
│  │ Admin Marmitaria  │ admin@marmi...   │ Admin  │ [⋮]  ││ │
│  └──────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────┘
```

### Painel Restaurante - Menu "Usuários"
```
┌────────┬───────────────────────────────────────────────────┐
│ MENU   │ Usuários                    [+ Novo Usuário]      │
│        ├───────────────────────────────────────────────────┤
│ 📊 Dash│ Nome              │ E-mail         │ Função │ Ações│
│ 📦 Pedi├───────────────────────────────────────────────────┤
│ 🍕 Prod│ Admin Gi          │ admin@gi.com   │ Admin  │ [⋮]  │
│        │                                                    │
│ ⚙️ Config                                                  │
│  ├─ Dados                                                  │
│  ├─ Horários                                               │
│  └─ 👥 Usuários  ← VOCÊ ESTÁ AQUI                         │
└────────┴───────────────────────────────────────────────────┘
```

---

## 🆘 Ainda Não Encontrou?

**Me envie:**
1. Screenshot do menu lateral do painel do restaurante
2. Screenshot da página de edição de tenant no admin
3. Resultado de: `ls -la /var/www/restaurante/app/Filament/Restaurant/Resources/ | grep User`

---

**Última atualização:** 05/03/2026
