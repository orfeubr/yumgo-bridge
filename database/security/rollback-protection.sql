-- ========================================
-- 🔓 ROLLBACK - REMOVER PROTEÇÕES
-- ========================================
-- Use APENAS se precisar reverter as proteções
-- ⚠️ CUIDADO: Isso remove toda a segurança!
-- ========================================

-- =====================================
-- 1. REMOVER TRIGGER DE AUDITORIA
-- =====================================

DROP EVENT TRIGGER IF EXISTS log_drop_command;
DROP FUNCTION IF EXISTS log_dangerous_operation();
DROP TABLE IF EXISTS audit_dangerous_operations;

-- =====================================
-- 2. REMOVER VIEW DE VERIFICAÇÃO
-- =====================================

DROP VIEW IF EXISTS v_user_permissions;

-- =====================================
-- 3. RESTAURAR PERMISSÕES www-data
-- =====================================

-- Schema PUBLIC
GRANT DROP ON ALL TABLES IN SCHEMA public TO www-data;
GRANT TRUNCATE ON ALL TABLES IN SCHEMA public TO www-data;
GRANT DELETE ON ALL TABLES IN SCHEMA public TO www-data;

-- =====================================
-- 4. REMOVER USUÁRIOS
-- =====================================

-- Read-only
DROP USER IF EXISTS yumgo_readonly;

-- Admin (CUIDADO!)
-- DROP USER IF EXISTS yumgo_admin;

-- =====================================
-- 5. REMOVER FUNÇÃO DE PROTEÇÃO TENANT
-- =====================================

DROP FUNCTION IF EXISTS protect_tenant_schema(TEXT);

-- =====================================
-- ✅ PROTEÇÕES REMOVIDAS!
-- =====================================

SELECT '⚠️ Proteções removidas - banco sem proteção!' AS status;
