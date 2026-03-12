# 🛡️ Proteção de Banco de Dados - PostgreSQL

**Objetivo:** Prevenir DROP/TRUNCATE acidentais em produção

---

## 📋 O Que Foi Implementado

### **Proteções Ativas:**
1. ✅ Usuário `www-data` **NÃO PODE** fazer DROP/TRUNCATE
2. ✅ Usuário `www-data` **NÃO PODE** fazer DELETE em tabelas críticas (tenants, plans, restaurant_types)
3. ✅ Usuário `yumgo_readonly` criado (apenas SELECT)
4. ✅ Usuário `yumgo_admin` criado (migrations e operações privilegiadas)
5. ✅ Trigger de auditoria para logar operações perigosas
6. ✅ Função para proteger schemas tenant automaticamente
7. ✅ Views de verificação de permissões

---

## 🚀 Como Aplicar as Proteções

### **1. Conectar no PostgreSQL como SUPERUSER**

```bash
sudo -u postgres psql yumgo_production
```

### **2. Executar Script de Proteção**

```bash
# Via arquivo
sudo -u postgres psql yumgo_production < database/security/production-database-protection.sql

# Ou copiar/colar comandos diretamente no psql
```

### **3. Trocar Senhas**

**IMPORTANTE:** Trocar as senhas padrão!

```sql
-- Senha do usuário read-only
ALTER USER yumgo_readonly WITH PASSWORD 'SUA_SENHA_FORTE_AQUI';

-- Senha do usuário admin
ALTER USER yumgo_admin WITH PASSWORD 'SUA_SENHA_FORTE_ADMIN_AQUI';
```

### **4. Atualizar .env (se necessário)**

Se você usar usuários diferentes para migrations:

```env
# Usuário padrão (aplicação web)
DB_USERNAME=www-data
DB_PASSWORD=senha_atual

# Usuário admin (migrations)
DB_ADMIN_USERNAME=yumgo_admin
DB_ADMIN_PASSWORD=senha_forte_admin
```

---

## 👥 Usuários Criados

| Usuário | Permissões | Uso | Pode DROP? |
|---------|------------|-----|------------|
| `www-data` | INSERT, UPDATE, SELECT | Aplicação Laravel | ❌ NÃO |
| `yumgo_admin` | SUPERUSER (tudo) | Migrations, manutenção | ✅ SIM |
| `yumgo_readonly` | SELECT apenas | BI, relatórios, debugging | ❌ NÃO |

---

## 🧪 Como Testar as Proteções

### **Teste 1: www-data NÃO pode fazer DROP**

```bash
# Conectar como www-data
psql -U www-data -d yumgo_production

# Tentar DROP (deve falhar)
DROP TABLE tenants;
```

**Resultado esperado:**
```
ERROR: permission denied for table tenants
```

### **Teste 2: www-data NÃO pode fazer TRUNCATE**

```sql
TRUNCATE TABLE tenants;
```

**Resultado esperado:**
```
ERROR: permission denied for table tenants
```

### **Teste 3: www-data NÃO pode DELETE em tabelas críticas**

```sql
DELETE FROM tenants WHERE id = 'xxx';
```

**Resultado esperado:**
```
ERROR: permission denied for table tenants
```

### **Teste 4: www-data PODE fazer SELECT/INSERT/UPDATE normais**

```sql
-- ✅ Deve funcionar
SELECT * FROM tenants LIMIT 1;

-- ✅ Deve funcionar
INSERT INTO orders (id, total) VALUES (gen_random_uuid(), 100);

-- ✅ Deve funcionar
UPDATE orders SET status = 'paid' WHERE id = 'xxx';
```

### **Teste 5: yumgo_readonly só pode SELECT**

```bash
# Conectar como read-only
psql -U yumgo_readonly -d yumgo_production

# ✅ Deve funcionar
SELECT * FROM tenants;

# ❌ Deve falhar
INSERT INTO tenants (id, name) VALUES (gen_random_uuid(), 'teste');
```

---

## 🔧 Como Proteger Novos Tenants

**Automático:** A função `protect_tenant_schema()` é chamada automaticamente quando um tenant é criado (via Observer).

**Manual:**
```sql
-- Proteger schema específico
SELECT protect_tenant_schema('tenant_novo_restaurante');

-- Proteger todos schemas tenant_*
DO $$
DECLARE
    schema_rec RECORD;
BEGIN
    FOR schema_rec IN
        SELECT schema_name
        FROM information_schema.schemata
        WHERE schema_name LIKE 'tenant_%'
    LOOP
        PERFORM protect_tenant_schema(schema_rec.schema_name);
    END LOOP;
END $$;
```

---

## 📊 Verificar Permissões Atuais

```sql
-- Ver permissões de www-data
SELECT * FROM v_user_permissions WHERE grantee = 'www-data';

-- Ver permissões de yumgo_readonly
SELECT * FROM v_user_permissions WHERE grantee = 'yumgo_readonly';

-- Ver auditoria de operações perigosas
SELECT * FROM audit_dangerous_operations ORDER BY executed_at DESC LIMIT 10;
```

---

## 🔓 Como Reverter as Proteções (EMERGÊNCIA)

**⚠️ USE APENAS EM EMERGÊNCIA!**

```bash
sudo -u postgres psql yumgo_production < database/security/rollback-protection.sql
```

Isso remove:
- ❌ Trigger de auditoria
- ❌ Usuários yumgo_readonly e yumgo_admin
- ❌ Restrições DROP/TRUNCATE
- ❌ Proteção de DELETE em tabelas críticas

---

## 🎯 O Que Ainda Funciona Normalmente

**Laravel continua funcionando 100%!**

✅ **Aplicação web** (`www-data`):
- INSERT em qualquer tabela
- UPDATE em qualquer tabela
- DELETE em tabelas normais (orders, products, etc)
- SELECT em tudo

✅ **Migrations** (rodar como `yumgo_admin`):
```bash
# No .env ou temporariamente:
DB_USERNAME=yumgo_admin php artisan migrate
```

✅ **Seeders, Tinker, tudo normal!**

---

## ❌ O Que Foi Bloqueado

**www-data NÃO PODE:**
- ❌ `DROP TABLE tenants`
- ❌ `DROP DATABASE`
- ❌ `TRUNCATE TABLE tenants`
- ❌ `DELETE FROM tenants` (tabela crítica)
- ❌ `DELETE FROM plans` (tabela crítica)
- ❌ `DELETE FROM restaurant_types` (tabela crítica)

**yumgo_readonly NÃO PODE:**
- ❌ INSERT, UPDATE, DELETE
- ❌ DROP, TRUNCATE
- ✅ Apenas SELECT

---

## 🔐 Boas Práticas

### **1. Uso dos Usuários**

```bash
# Desenvolvimento local
DB_USERNAME=postgres

# Produção - Aplicação
DB_USERNAME=www-data

# Produção - Migrations
DB_USERNAME=yumgo_admin

# BI/Relatórios
DB_USERNAME=yumgo_readonly
```

### **2. Migrations em Produção**

```bash
# Sempre usar usuário admin
DB_USERNAME=yumgo_admin php artisan migrate

# Ou criar arquivo .env.migration
DB_USERNAME=yumgo_admin
DB_PASSWORD=senha_admin

# Rodar com:
php artisan migrate --env=migration
```

### **3. Backup Antes de Mudanças**

```bash
# SEMPRE fazer backup antes de migrations
pg_dump yumgo_production > backup_$(date +%Y%m%d).sql

# Ou usar script automático
./database/security/backup-before-migrate.sh
```

---

## 📝 Auditoria

**Logs de operações perigosas:**

```sql
-- Ver últimas operações perigosas
SELECT
    executed_at,
    executed_by,
    operation_type,
    table_name,
    LEFT(command, 100) AS command_preview
FROM audit_dangerous_operations
ORDER BY executed_at DESC
LIMIT 20;

-- Quem tentou DROP/TRUNCATE?
SELECT
    executed_by,
    COUNT(*) AS attempts,
    MAX(executed_at) AS last_attempt
FROM audit_dangerous_operations
WHERE operation_type IN ('DROP TABLE', 'TRUNCATE')
GROUP BY executed_by
ORDER BY attempts DESC;
```

---

## 🆘 Troubleshooting

### **Erro: "permission denied" ao rodar migrations**

**Causa:** Migrations rodando com `www-data` em vez de `yumgo_admin`

**Solução:**
```bash
# Temporariamente usar admin
DB_USERNAME=yumgo_admin php artisan migrate

# Ou atualizar .env permanentemente para migrations
```

### **Erro: "relation does not exist" com yumgo_readonly**

**Causa:** Schema não concedido ao usuário read-only

**Solução:**
```sql
GRANT USAGE ON SCHEMA tenant_xxx TO yumgo_readonly;
GRANT SELECT ON ALL TABLES IN SCHEMA tenant_xxx TO yumgo_readonly;
```

### **Preciso fazer DELETE em tabela crítica**

**Opção 1:** Usar yumgo_admin temporariamente
```bash
DB_USERNAME=yumgo_admin php artisan tinker
>>> Tenant::where('id', 'xxx')->delete();
```

**Opção 2:** Conceder permissão temporária (não recomendado)
```sql
GRANT DELETE ON tenants TO www-data;
-- fazer o DELETE
REVOKE DELETE ON tenants FROM www-data;
```

---

## ✅ Checklist de Segurança

- [ ] Proteções aplicadas (`production-database-protection.sql`)
- [ ] Senhas trocadas (yumgo_admin, yumgo_readonly)
- [ ] Testado DROP/TRUNCATE (deve falhar com www-data)
- [ ] Testado SELECT normal (deve funcionar)
- [ ] Testado migrations com yumgo_admin (deve funcionar)
- [ ] Backup automático configurado
- [ ] Auditoria funcionando (`audit_dangerous_operations`)
- [ ] Documentação lida pela equipe

---

## 📞 Suporte

**Arquivos:**
- `production-database-protection.sql` - Aplicar proteções
- `rollback-protection.sql` - Reverter proteções (emergência)
- `test-protection.sql` - Testar proteções
- `README.md` - Este arquivo

**Comandos úteis:**
```bash
# Ver usuários
psql -U postgres -d yumgo_production -c "\du"

# Ver permissões
psql -U postgres -d yumgo_production -c "SELECT * FROM v_user_permissions;"

# Auditoria
psql -U postgres -d yumgo_production -c "SELECT * FROM audit_dangerous_operations ORDER BY executed_at DESC LIMIT 10;"
```

---

**Criado por:** Claude Sonnet 4.5
**Data:** 12/03/2026
**Status:** ✅ Pronto para Uso
