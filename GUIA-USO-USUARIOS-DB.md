# 📘 Guia de Uso dos Usuários de Banco de Dados

**Criado:** 12/03/2026
**Ambiente:** AWS RDS PostgreSQL

---

## 👥 3 Usuários, 3 Propósitos

### **1. postgres (Owner/Emergências)**

**Usado em:** `.env` (aplicação web principal)

**Quando usar:**
- ✅ Aplicação web (Laravel rodando normalmente)
- ✅ Operações do dia a dia
- ⚠️ Emergências (se yumgo_admin não funcionar)

**Permissões:**
- ✅ SELECT, INSERT, UPDATE, DELETE
- ✅ DROP, TRUNCATE (cuidado!)
- ✅ CREATE TABLE, ALTER TABLE

**Comandos:**
```bash
# Aplicação web (padrão)
php artisan serve
php artisan tinker
php artisan queue:work

# Tudo usa .env que tem DB_USERNAME=postgres
```

---

### **2. yumgo_admin (Migrations/Manutenção)**

**Usado em:** `.env.migration`

**Quando usar:**
- ✅ Rodar migrations
- ✅ Criar/alterar schemas
- ✅ Operações de manutenção
- ✅ Deletar dados (se necessário)

**Permissões:**
- ✅ SELECT, INSERT, UPDATE, DELETE
- ✅ DROP, TRUNCATE
- ✅ CREATE DATABASE, CREATE SCHEMA
- ❌ SUPERUSER (limitação AWS RDS)

**Comandos:**
```bash
# Rodar migrations
php artisan migrate --env=migration

# Rollback
php artisan migrate:rollback --env=migration

# Fresh (CUIDADO!)
php artisan migrate:fresh --env=migration

# Tinker como admin
php artisan tinker --env=migration

# Seeder
php artisan db:seed --env=migration --class=NomeSeeder
```

---

### **3. yumgo_readonly (Consultas/BI)**

**Usado em:** `.env.readonly`

**Quando usar:**
- ✅ Consultas de dados
- ✅ Relatórios e BI
- ✅ Debugging (sem risco)
- ✅ Acessos externos (dashboards)
- ✅ Testes de queries

**Permissões:**
- ✅ SELECT apenas
- ❌ INSERT, UPDATE, DELETE
- ❌ DROP, TRUNCATE
- ❌ CREATE

**Comandos:**
```bash
# Tinker read-only
php artisan tinker --env=readonly

# Dentro do tinker:
Tenant::all();  ✅ Funciona
Order::count(); ✅ Funciona

Tenant::create([...]); ❌ Erro: permission denied
Order::where('id', 'x')->delete(); ❌ Erro: permission denied

# Query direta no banco
PGPASSWORD='senha_readonly' psql \
  -h labourtek.cdow004sgzwg.us-west-2.rds.amazonaws.com \
  -U yumgo_readonly \
  -d postgres

# SQL
SELECT COUNT(*) FROM tenants; ✅ Funciona
UPDATE tenants SET name='x'; ❌ Erro: permission denied
```

---

## 🎯 Casos de Uso Práticos

### **Caso 1: Rodar Migration**

```bash
# ❌ ERRADO (usa postgres - funciona mas não é ideal)
php artisan migrate

# ✅ CORRETO (usa yumgo_admin)
php artisan migrate --env=migration
```

**Por quê usar yumgo_admin?**
- Separação de responsabilidades
- Logs de auditoria mais claros
- Se tiver erro, não afeta aplicação web

---

### **Caso 2: Consultar Dados (BI)**

```bash
# ✅ CORRETO (usa yumgo_readonly)
php artisan tinker --env=readonly

# Dentro:
>>> Tenant::count()
=> 6

>>> Order::where('payment_status', 'paid')->sum('total')
=> 12543.50

# ❌ Se tentar modificar:
>>> Tenant::where('id', 'x')->delete()
# Erro: permission denied ✅ Proteção funciona!
```

---

### **Caso 3: Deletar Tenant (Manutenção)**

```bash
# Opção 1: Soft delete (RECOMENDADO) - usa postgres
php artisan tinker
>>> Tenant::where('slug', 'teste')->update(['is_active' => false]);

# Opção 2: Hard delete - usa yumgo_admin
php artisan tinker --env=migration
>>> Tenant::where('slug', 'teste')->delete();
>>> DB::statement("DROP SCHEMA IF EXISTS tenant_teste CASCADE");
```

---

### **Caso 4: Debugging de Problema**

```bash
# 1. Consultar dados (read-only - seguro)
php artisan tinker --env=readonly
>>> $order = Order::find('uuid-do-pedido');
>>> $order->payment_status; // Ver status

# 2. Se precisar corrigir (admin)
php artisan tinker --env=migration
>>> $order = Order::find('uuid-do-pedido');
>>> $order->update(['payment_status' => 'paid']);
```

---

### **Caso 5: Exportar Relatório**

```bash
# Usar read-only (não trava aplicação)
php artisan tinker --env=readonly

# Exportar CSV
>>> $orders = Order::whereBetween('created_at', ['2026-01-01', '2026-12-31'])->get();
>>> $csv = $orders->map(fn($o) => [
      $o->id,
      $o->total,
      $o->payment_status,
      $o->created_at
    ]);
>>> file_put_contents('relatorio.csv', $csv->toCsv());
```

---

## 📝 Checklist de Decisão

**Qual usuário usar?**

| Vou fazer... | Usuário | Arquivo .env |
|--------------|---------|--------------|
| Rodar aplicação web | postgres | `.env` |
| Rodar migration | yumgo_admin | `.env.migration` |
| Consultar dados | yumgo_readonly | `.env.readonly` |
| Deletar dados | yumgo_admin | `.env.migration` |
| Criar schema | yumgo_admin | `.env.migration` |
| Exportar relatório | yumgo_readonly | `.env.readonly` |
| Debugging (só ler) | yumgo_readonly | `.env.readonly` |
| Debugging (modificar) | yumgo_admin | `.env.migration` |
| Emergência (DROP) | postgres ou yumgo_admin | `.env` ou `.env.migration` |

---

## 🔐 TROCAR SENHAS (IMPORTANTE!)

**Antes de usar .env.migration e .env.readonly, TROCAR SENHAS!**

### **Passo 1: Gerar Senhas Fortes**

```bash
# Gerar senha para yumgo_admin
openssl rand -base64 32

# Gerar senha para yumgo_readonly
openssl rand -base64 32

# Copiar as senhas geradas
```

### **Passo 2: Atualizar no Banco**

```bash
# Conectar como postgres
PGPASSWORD=jNPSDGuUwdggg4VXOU0E psql \
  -h labourtek.cdow004sgzwg.us-west-2.rds.amazonaws.com \
  -U postgres \
  -d postgres

# Trocar senhas
ALTER USER yumgo_admin WITH PASSWORD 'cola_senha_gerada_1_aqui';
ALTER USER yumgo_readonly WITH PASSWORD 'cola_senha_gerada_2_aqui';

# Sair
\q
```

### **Passo 3: Atualizar .env.migration e .env.readonly**

```bash
# Editar .env.migration
nano .env.migration

# Trocar linha:
DB_PASSWORD=TROCAR_SENHA_FORTE_ADMIN_AQUI
# Por:
DB_PASSWORD=senha_gerada_1_aqui

# Salvar (Ctrl+O, Enter, Ctrl+X)

# Editar .env.readonly
nano .env.readonly

# Trocar linha:
DB_PASSWORD=TROCAR_SENHA_FORTE_READONLY_AQUI
# Por:
DB_PASSWORD=senha_gerada_2_aqui

# Salvar
```

### **Passo 4: Testar**

```bash
# Testar .env.migration
php artisan tinker --env=migration
>>> Tenant::count()
# Deve funcionar ✅

# Testar .env.readonly
php artisan tinker --env=readonly
>>> Tenant::count()
# Deve funcionar ✅

>>> Tenant::first()->update(['name' => 'x'])
# Deve dar erro: permission denied ✅
```

---

## 🛡️ Proteções Ativas

### **postgres:**
- ⚠️ Pode fazer DROP/TRUNCATE (cuidado!)
- ✅ Usa aplicação web normalmente

### **yumgo_admin:**
- ✅ Pode fazer DROP/TRUNCATE
- ✅ Pode CREATE DATABASE/SCHEMA
- ✅ Ideal para migrations

### **yumgo_readonly:**
- ✅ SELECT apenas
- ❌ Bloqueado: INSERT, UPDATE, DELETE, DROP

---

## 📊 Exemplos de Comandos Comuns

### **Migrations**
```bash
# Todas as migrations
php artisan migrate --env=migration

# Migration específica
php artisan migrate --path=database/migrations/tenant --env=migration

# Rollback última
php artisan migrate:rollback --env=migration --step=1

# Status
php artisan migrate:status --env=migration
```

### **Seeders**
```bash
# Rodar seeder
php artisan db:seed --env=migration --class=RestaurantTypeSeeder

# Refresh + seed (CUIDADO!)
php artisan migrate:fresh --seed --env=migration
```

### **Tinker (Consultas)**
```bash
# Read-only (seguro)
php artisan tinker --env=readonly

# Admin (modificar dados)
php artisan tinker --env=migration

# Web (padrão)
php artisan tinker
```

### **SQL Direto**
```bash
# Admin
PGPASSWORD='senha_admin' psql \
  -h labourtek.cdow004sgzwg.us-west-2.rds.amazonaws.com \
  -U yumgo_admin \
  -d postgres \
  -c "SELECT COUNT(*) FROM tenants;"

# Read-only
PGPASSWORD='senha_readonly' psql \
  -h labourtek.cdow004sgzwg.us-west-2.rds.amazonaws.com \
  -U yumgo_readonly \
  -d postgres \
  -c "SELECT name FROM tenants LIMIT 5;"
```

---

## 🎓 Boas Práticas

### **✅ FAÇA:**
- Use yumgo_admin para migrations
- Use yumgo_readonly para consultas/BI
- Troque as senhas antes de usar
- Documente qual usuário foi usado em cada operação
- Faça backup antes de migrations grandes

### **❌ NÃO FAÇA:**
- Não use postgres para migrations (use yumgo_admin)
- Não compartilhe senha do yumgo_admin
- Não rode migrations em produção sem backup
- Não use yumgo_readonly para modificar dados (vai dar erro)

---

## 🆘 Troubleshooting

### **Erro: "permission denied"**

**Problema:** Tentou modificar dados com yumgo_readonly

**Solução:** Use yumgo_admin
```bash
php artisan tinker --env=migration
```

---

### **Erro: "role yumgo_admin does not exist"**

**Problema:** Senha não foi trocada ou arquivo .env.migration não foi criado

**Solução:**
1. Verificar que usuário existe: `\du` no psql
2. Trocar senha conforme Passo 2 acima
3. Verificar .env.migration existe

---

### **Erro: "could not connect to database"**

**Problema:** Senha errada no .env.migration

**Solução:**
1. Verificar senha foi trocada no banco
2. Verificar senha no .env.migration está correta
3. Testar conexão direta com psql

---

## 📚 Arquivos Relacionados

```
/var/www/restaurante/
├── .env                    ← postgres (aplicação web)
├── .env.migration          ← yumgo_admin (migrations) ⭐ NOVO
├── .env.readonly           ← yumgo_readonly (consultas) ⭐ NOVO
├── PROTECOES-AWS-RDS-APLICADAS.md
├── GUIA-USO-USUARIOS-DB.md ← ESTE ARQUIVO ⭐
└── database/security/
    ├── aws-rds-protection.sql
    └── README.md
```

---

## ✅ Checklist Final

- [ ] Senhas trocadas (yumgo_admin e yumgo_readonly)
- [ ] .env.migration criado e testado
- [ ] .env.readonly criado e testado
- [ ] Testado migration com yumgo_admin
- [ ] Testado consulta com yumgo_readonly
- [ ] Equipe notificada sobre novos usuários
- [ ] Senhas salvas em gerenciador seguro

---

**Criado por:** Claude Sonnet 4.5
**Data:** 12/03/2026
**Status:** ✅ Pronto para Uso
