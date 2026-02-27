# Adminer - Gerenciador de Banco de Dados - 26/02/2026

## ✅ Instalado com Sucesso!

O **Adminer** foi instalado e configurado no painel admin central.

### 📍 Como Acessar

**URL:** https://yumgo.com.br/admin

1. Faça login no painel admin
2. No menu lateral, clique em **"Sistema" → "Banco de Dados"**
3. O Adminer abrirá automaticamente conectado ao PostgreSQL

### 🔐 Credenciais (Auto-login)

O sistema já faz login automaticamente com as credenciais do `.env`:

```
Servidor: localhost (ou seu host PostgreSQL)
Usuário: deliverypro_user (do .env)
Banco: deliverypro_db (do .env)
Senha: [preenchida automaticamente]
```

### 🎯 O que você pode fazer

#### 1. **Visualizar Schemas**
```
Schema PUBLIC:
- tenants (restaurantes)
- plans (planos)
- subscriptions
- invoices
- domains
- platform_users

Schema TENANT_* (por restaurante):
- customers
- orders
- products
- payments
- etc...
```

#### 2. **Executar Queries SQL**
Clique em **"SQL Query"** no menu superior

```sql
-- Ver todos os tenants
SELECT id, name, slug, status FROM tenants;

-- Ver pedidos de um tenant específico
SET search_path TO tenant144c5973f9854309_8f9ac404dd11feae;
SELECT * FROM orders ORDER BY created_at DESC LIMIT 10;

-- Ver pagamentos pendentes
SELECT o.order_number, p.status, p.amount 
FROM orders o 
JOIN payments p ON p.order_id = o.id 
WHERE p.status = 'pending';
```

#### 3. **Exportar Dados**
- Clique em uma tabela
- Botão "Export" no topo
- Escolha formato (SQL, CSV, etc.)

#### 4. **Editar Registros**
⚠️ **CUIDADO!** Editar direto no banco pode quebrar a aplicação.
- Clique em "edit" ao lado do registro
- Modifique os campos
- Clique em "Save"

### 🛡️ Segurança

✅ **Acesso restrito ao painel admin**
- Apenas usuários autenticados no `/admin` podem acessar
- Middleware protege a rota

✅ **Bloqueio de acesso direto**
- `.htaccess` bloqueia acesso direto a `/adminer/`
- Só funciona via Laravel

✅ **Não instalado no tenant**
- Adminer está APENAS no painel central
- Restaurantes não têm acesso

### 📊 Atalhos Rápidos (na interface)

A página tem 3 atalhos úteis:

1. **Schema PUBLIC** - Ver dados da plataforma
2. **SQL Query** - Executar queries personalizadas
3. **Nova Janela** - Abrir Adminer em tela cheia

### 🎨 Interface do Adminer

```
┌────────────────────────────────────────┐
│ Adminer 4.8.1 - PostgreSQL             │
├────────────────────────────────────────┤
│ Server: localhost                      │
│ Database: deliverypro_db               │
│ Schema: [selecione]                    │
├────────────────────────────────────────┤
│ Tables:                                │
│  ✓ tenants                             │
│  ✓ plans                               │
│  ✓ subscriptions                       │
│  ✓ orders (tenant schema)              │
│  ✓ products (tenant schema)            │
│  ...                                   │
└────────────────────────────────────────┘
```

### 🔧 Comandos Úteis

#### Ver todos os schemas (tenants):
```sql
SELECT schema_name 
FROM information_schema.schemata 
WHERE schema_name LIKE 'tenant%'
ORDER BY schema_name;
```

#### Mudar para schema de um tenant:
```sql
SET search_path TO tenant144c5973f9854309_8f9ac404dd11feae;
```

#### Ver tabelas de um schema:
```sql
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'tenant144c5973f9854309_8f9ac404dd11feae';
```

#### Contar registros de todas as tabelas:
```sql
SELECT 
    schemaname,
    tablename,
    (xpath('/row/cnt/text()', 
        xml_count))[1]::text::int as row_count
FROM (
    SELECT 
        schemaname, 
        tablename,
        query_to_xml(
            format('SELECT COUNT(*) as cnt FROM %I.%I', 
            schemaname, tablename), 
            false, true, ''
        ) as xml_count
    FROM pg_tables
    WHERE schemaname = 'public'
) t
ORDER BY row_count DESC;
```

### ⚠️ Avisos Importantes

1. **BACKUP antes de alterar**
   - Sempre faça backup antes de DELETE/UPDATE em massa
   
2. **Não altere estrutura de tabelas**
   - Use migrations do Laravel
   - Adminer é para visualizar/query, não para DDL

3. **Cuidado com foreign keys**
   - Deletar registro pode quebrar relacionamentos

4. **Multi-tenant**
   - Lembre-se: dados de tenant estão em schemas separados
   - Use `SET search_path` para mudar de schema

### 📁 Arquivos Criados

```
✅ public/adminer/index.php
✅ public/adminer/.htaccess
✅ app/Http/Controllers/AdminerController.php
✅ app/Filament/Admin/Pages/DatabaseManager.php
✅ resources/views/filament/admin/pages/database-manager.blade.php
✅ routes/web.php (linha adicionada)
```

### 🚀 Alternativas

Se preferir outra ferramenta no futuro:

- **pgAdmin 4** - Mais robusto, mas pesado
- **DBeaver** - Desktop, grátis
- **TablePlus** - Pago, mas muito bom
- **Navicat** - Pago, completo

**Mas o Adminer é perfeito para este caso!** ✅

---

**Data:** 26/02/2026 22:35 UTC
**Status:** ✅ INSTALADO E FUNCIONANDO
**Acesso:** https://yumgo.com.br/admin → Banco de Dados
**Versão:** Adminer 4.8.1
