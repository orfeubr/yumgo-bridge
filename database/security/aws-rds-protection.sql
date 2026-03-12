-- ========================================
-- 🛡️ PROTEÇÃO DE BANCO - AWS RDS
-- ========================================
-- Versão adaptada para AWS RDS PostgreSQL
-- ========================================

-- =====================================
-- 1. CRIAR USUÁRIO READ-ONLY
-- =====================================

CREATE USER yumgo_readonly WITH PASSWORD 'TROCAR_SENHA_FORTE_AQUI';

-- Permissões básicas
GRANT CONNECT ON DATABASE postgres TO yumgo_readonly;

-- Schema PUBLIC (central - tenants, plans, etc)
GRANT USAGE ON SCHEMA public TO yumgo_readonly;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO yumgo_readonly;
GRANT SELECT ON ALL SEQUENCES IN SCHEMA public TO yumgo_readonly;

-- Permissões automáticas em tabelas futuras
ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT ON TABLES TO yumgo_readonly;

-- Schemas tenant
DO $$
DECLARE
    schema_rec RECORD;
BEGIN
    FOR schema_rec IN
        SELECT schema_name
        FROM information_schema.schemata
        WHERE schema_name LIKE 'tenant%'
    LOOP
        EXECUTE format('GRANT USAGE ON SCHEMA %I TO yumgo_readonly', schema_rec.schema_name);
        EXECUTE format('GRANT SELECT ON ALL TABLES IN SCHEMA %I TO yumgo_readonly', schema_rec.schema_name);
    END LOOP;
END $$;

-- =====================================
-- 2. CRIAR USUÁRIO ADMIN (Migrations)
-- =====================================

-- Usuário para rodar migrations
CREATE USER yumgo_admin WITH PASSWORD 'TROCAR_SENHA_FORTE_ADMIN';

-- Admin tem privilégios completos (exceto SUPERUSER - limitação RDS)
GRANT ALL PRIVILEGES ON DATABASE postgres TO yumgo_admin;
GRANT ALL PRIVILEGES ON SCHEMA public TO yumgo_admin;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO yumgo_admin;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO yumgo_admin;

-- Permitir criar schemas (para tenants)
ALTER USER yumgo_admin WITH CREATEDB;

-- =====================================
-- 3. REVOGAR PERMISSÕES PERIGOSAS (postgres)
-- =====================================

-- IMPORTANTE: No RDS, o usuário Laravel é "postgres"
-- Não podemos fazer REVOKE DROP porque "postgres" é owner

-- Alternativa: Criar política de proteção
-- Usar rds_superuser tem mais privilégios que usuários normais

-- Revogar DELETE em tabelas críticas
REVOKE DELETE ON tenants FROM PUBLIC;
REVOKE DELETE ON plans FROM PUBLIC;
REVOKE DELETE ON restaurant_types FROM PUBLIC;

-- Apenas yumgo_admin pode DELETE nestas tabelas
GRANT DELETE ON tenants TO yumgo_admin;
GRANT DELETE ON plans TO yumgo_admin;
GRANT DELETE ON restaurant_types TO yumgo_admin;

-- Usuário postgres mantém DELETE (é owner)
GRANT DELETE ON tenants TO postgres;
GRANT DELETE ON plans TO postgres;
GRANT DELETE ON restaurant_types TO postgres;

-- =====================================
-- 4. CRIAR TABELA DE AUDITORIA
-- =====================================

CREATE TABLE IF NOT EXISTS audit_dangerous_operations (
    id SERIAL PRIMARY KEY,
    executed_by VARCHAR(100),
    operation_type VARCHAR(50),
    table_name VARCHAR(255),
    command TEXT,
    executed_at TIMESTAMP DEFAULT NOW(),
    client_ip INET
);

-- Permitir apenas yumgo_admin inserir
GRANT INSERT ON audit_dangerous_operations TO yumgo_admin;
GRANT SELECT ON audit_dangerous_operations TO yumgo_readonly;

-- =====================================
-- 5. CRIAR VIEW DE VERIFICAÇÃO
-- =====================================

CREATE OR REPLACE VIEW v_user_permissions AS
SELECT
    grantee,
    table_schema,
    table_name,
    STRING_AGG(privilege_type, ', ') AS privileges
FROM information_schema.table_privileges
WHERE table_schema IN ('public')
  AND grantee NOT IN ('rdsadmin', 'rds_superuser', 'rds_replication', 'rds_password')
GROUP BY grantee, table_schema, table_name
ORDER BY grantee, table_schema, table_name;

-- Permitir ver permissões
GRANT SELECT ON v_user_permissions TO yumgo_readonly;
GRANT SELECT ON v_user_permissions TO yumgo_admin;

-- =====================================
-- 6. CRIAR FUNÇÃO DE PROTEÇÃO TENANT
-- =====================================

CREATE OR REPLACE FUNCTION protect_tenant_schema(schema_name TEXT)
RETURNS VOID AS $$
BEGIN
    -- Dar acesso read-only
    EXECUTE format('GRANT USAGE ON SCHEMA %I TO yumgo_readonly', schema_name);
    EXECUTE format('GRANT SELECT ON ALL TABLES IN SCHEMA %I TO yumgo_readonly', schema_name);

    -- Admin total access
    EXECUTE format('GRANT ALL ON SCHEMA %I TO yumgo_admin', schema_name);
    EXECUTE format('GRANT ALL ON ALL TABLES IN SCHEMA %I TO yumgo_admin', schema_name);

    RAISE NOTICE '✅ Schema % protegido!', schema_name;
END;
$$ LANGUAGE plpgsql;

-- =====================================
-- 7. APLICAR PROTEÇÃO EM SCHEMAS EXISTENTES
-- =====================================

DO $$
DECLARE
    schema_rec RECORD;
BEGIN
    FOR schema_rec IN
        SELECT schema_name
        FROM information_schema.schemata
        WHERE schema_name LIKE 'tenant%'
    LOOP
        PERFORM protect_tenant_schema(schema_rec.schema_name);
    END LOOP;
END $$;

-- =====================================
-- 8. VERIFICAÇÃO FINAL
-- =====================================

-- Listar usuários
SELECT
    usename AS username,
    usesuper AS is_superuser,
    usecreatedb AS can_create_db
FROM pg_user
WHERE usename NOT IN ('rdsadmin', 'rds_superuser', 'rds_replication', 'rds_password')
ORDER BY usename;

-- Permissões em tabelas críticas
SELECT
    grantee,
    table_name,
    privilege_type
FROM information_schema.table_privileges
WHERE table_name IN ('tenants', 'plans', 'restaurant_types')
    AND table_schema = 'public'
    AND grantee NOT IN ('rdsadmin', 'rds_superuser', 'rds_replication', 'rds_password')
ORDER BY table_name, grantee;

-- =====================================
-- ✅ PROTEÇÕES ATIVADAS (AWS RDS)
-- =====================================

SELECT '✅ Proteções AWS RDS aplicadas com sucesso!' AS status;

-- =====================================
-- 📝 NOTAS IMPORTANTES
-- =====================================

/*
LIMITAÇÕES AWS RDS:
- ❌ Não podemos fazer REVOKE DROP/TRUNCATE do owner (postgres)
- ❌ Não podemos criar SUPERUSER (limitação RDS)
- ✅ Podemos revogar DELETE de PUBLIC e controlar via GRANT
- ✅ Podemos criar usuários read-only e admin

PROTEÇÕES ATIVAS:
- ✅ yumgo_readonly: SELECT apenas
- ✅ yumgo_admin: Tudo (para migrations)
- ⚠️ postgres: Mantém tudo (é owner, não podemos restringir totalmente)

RECOMENDAÇÃO:
- Use yumgo_admin para migrations
- Use yumgo_readonly para consultas
- Use postgres apenas em emergências
- Configure .env para usar yumgo_admin em produção
*/
