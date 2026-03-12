-- ========================================
-- 🛡️ PROTEÇÃO DE BANCO DE DADOS - PRODUÇÃO
-- ========================================
-- Data: 12/03/2026
-- Objetivo: Prevenir DROP/TRUNCATE acidentais
-- ========================================

-- =====================================
-- 1. CRIAR USUÁRIO READ-ONLY
-- =====================================

-- Usuário apenas para consultas (BI, relatórios, debugging)
CREATE USER yumgo_readonly WITH PASSWORD 'TROCAR_SENHA_FORTE_AQUI';

-- Permissões básicas
GRANT CONNECT ON DATABASE yumgo_production TO yumgo_readonly;

-- Schema PUBLIC (central - tenants, plans, etc)
GRANT USAGE ON SCHEMA public TO yumgo_readonly;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO yumgo_readonly;
GRANT SELECT ON ALL SEQUENCES IN SCHEMA public TO yumgo_readonly;

-- Permissões automáticas em tabelas futuras
ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT ON TABLES TO yumgo_readonly;

-- =====================================
-- 2. REVOGAR PERMISSÕES PERIGOSAS
-- =====================================

-- IMPORTANTE: www-data é o usuário usado pelo PHP-FPM
-- Verificar nome correto: SELECT current_user; no Laravel

-- Schema PUBLIC (tabelas centrais)
REVOKE DROP ON ALL TABLES IN SCHEMA public FROM www-data;
REVOKE TRUNCATE ON ALL TABLES IN SCHEMA public FROM www-data;

-- Tabelas críticas: Proteção extra
REVOKE DELETE ON tenants FROM www-data;
REVOKE DELETE ON plans FROM www-data;
REVOKE DELETE ON restaurant_types FROM www-data;

-- ⚠️ NOTA: DELETE em outras tabelas ainda permitido (necessário para operações normais)

-- =====================================
-- 3. CRIAR ROLE DE ADMIN (Migrations)
-- =====================================

-- Usuário para rodar migrations e operações privilegiadas
CREATE USER yumgo_admin WITH PASSWORD 'TROCAR_SENHA_FORTE_ADMIN';

-- Admin tem TODOS os privilégios
GRANT ALL PRIVILEGES ON DATABASE yumgo_production TO yumgo_admin;
GRANT ALL PRIVILEGES ON SCHEMA public TO yumgo_admin;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO yumgo_admin;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO yumgo_admin;

-- Admin é SUPERUSER (pode DROP, TRUNCATE, etc)
ALTER USER yumgo_admin WITH SUPERUSER;

-- =====================================
-- 4. CRIAR USUÁRIO TENANT (Schemas Isolados)
-- =====================================

-- Usuário específico para schemas de tenant
-- (opcional - se quiser separar ainda mais)
CREATE USER yumgo_tenant WITH PASSWORD 'TROCAR_SENHA_FORTE_TENANT';

GRANT CONNECT ON DATABASE yumgo_production TO yumgo_tenant;
GRANT USAGE, CREATE ON SCHEMA public TO yumgo_tenant;

-- Permissões em schemas tenant_*
-- (será aplicado dinamicamente via código)

-- =====================================
-- 5. FUNÇÕES DE AUDITORIA
-- =====================================

-- Tabela de auditoria de comandos perigosos
CREATE TABLE IF NOT EXISTS audit_dangerous_operations (
    id SERIAL PRIMARY KEY,
    executed_by VARCHAR(100),
    operation_type VARCHAR(50),
    table_name VARCHAR(255),
    command TEXT,
    executed_at TIMESTAMP DEFAULT NOW(),
    client_ip INET
);

-- Trigger para logar DROP/TRUNCATE (se conseguir executar)
CREATE OR REPLACE FUNCTION log_dangerous_operation()
RETURNS EVENT_trigger AS $$
BEGIN
    INSERT INTO audit_dangerous_operations (
        executed_by,
        operation_type,
        command,
        client_ip
    ) VALUES (
        current_user,
        TG_TAG,
        current_query(),
        inet_client_addr()
    );

    RAISE WARNING '⚠️ OPERAÇÃO PERIGOSA EXECUTADA: % por %', TG_TAG, current_user;
END;
$$ LANGUAGE plpgsql;

-- Aplicar trigger em DROP
CREATE EVENT TRIGGER log_drop_command
    ON ddl_command_start
    WHEN TAG IN ('DROP TABLE', 'DROP DATABASE', 'TRUNCATE')
    EXECUTE FUNCTION log_dangerous_operation();

-- =====================================
-- 6. VERIFICAÇÃO DE PERMISSÕES
-- =====================================

-- Query para verificar permissões atuais
CREATE OR REPLACE VIEW v_user_permissions AS
SELECT
    grantee,
    table_schema,
    table_name,
    STRING_AGG(privilege_type, ', ') AS privileges
FROM information_schema.table_privileges
WHERE table_schema IN ('public')
GROUP BY grantee, table_schema, table_name
ORDER BY grantee, table_schema, table_name;

-- Verificar permissões
-- SELECT * FROM v_user_permissions WHERE grantee = 'www-data';

-- =====================================
-- 7. PROTEÇÃO DE SCHEMAS TENANT
-- =====================================

-- Função para proteger schemas tenant dinamicamente
CREATE OR REPLACE FUNCTION protect_tenant_schema(schema_name TEXT)
RETURNS VOID AS $$
BEGIN
    -- Revogar DROP/TRUNCATE do usuário web
    EXECUTE format('REVOKE DROP ON ALL TABLES IN SCHEMA %I FROM www-data', schema_name);
    EXECUTE format('REVOKE TRUNCATE ON ALL TABLES IN SCHEMA %I FROM www-data', schema_name);

    -- Dar acesso read-only
    EXECUTE format('GRANT USAGE ON SCHEMA %I TO yumgo_readonly', schema_name);
    EXECUTE format('GRANT SELECT ON ALL TABLES IN SCHEMA %I TO yumgo_readonly', schema_name);

    RAISE NOTICE '✅ Schema % protegido!', schema_name;
END;
$$ LANGUAGE plpgsql;

-- Exemplo de uso:
-- SELECT protect_tenant_schema('tenant_marmitaria_gi');

-- =====================================
-- 8. APLICAR PROTEÇÃO EM TODOS TENANT SCHEMAS
-- =====================================

-- Script para proteger todos schemas tenant_* existentes
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

-- =====================================
-- 9. USUÁRIOS E SENHAS
-- =====================================

/*
USUÁRIOS CRIADOS:

1. yumgo_admin (SUPERUSER)
   - Usa: Migrations, operações privilegiadas
   - Pode: DROP, TRUNCATE, tudo
   - Senha: TROCAR_SENHA_FORTE_ADMIN

2. www-data (USUÁRIO WEB - Laravel)
   - Usa: PHP-FPM, aplicação web
   - Pode: INSERT, UPDATE, DELETE (exceto tabelas críticas), SELECT
   - NÃO PODE: DROP, TRUNCATE
   - Senha: (mantém a atual)

3. yumgo_readonly (READ-ONLY)
   - Usa: BI, relatórios, debugging
   - Pode: SELECT apenas
   - NÃO PODE: Modificar dados
   - Senha: TROCAR_SENHA_FORTE_AQUI
*/

-- =====================================
-- 10. VERIFICAÇÃO FINAL
-- =====================================

-- Listar todos usuários
SELECT
    usename AS username,
    usesuper AS is_superuser,
    usecreatedb AS can_create_db,
    useconfig AS config
FROM pg_user
ORDER BY usename;

-- Permissões em tabelas críticas
SELECT
    grantee,
    table_name,
    privilege_type
FROM information_schema.table_privileges
WHERE table_name IN ('tenants', 'plans', 'restaurant_types')
    AND table_schema = 'public'
ORDER BY table_name, grantee;

-- =====================================
-- ✅ PROTEÇÕES ATIVADAS!
-- =====================================

SELECT '✅ Proteções de banco de dados aplicadas com sucesso!' AS status;
