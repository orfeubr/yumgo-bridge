-- ========================================
-- 🧪 TESTES DE PROTEÇÃO
-- ========================================
-- Testa se as proteções estão funcionando
-- ========================================

-- =====================================
-- TESTE 1: Operações Permitidas (www-data)
-- =====================================

-- Conectar como: psql -U www-data -d yumgo_production

-- ✅ Deve funcionar: SELECT
SELECT COUNT(*) FROM tenants;

-- ✅ Deve funcionar: INSERT
-- (não vamos testar agora para não sujar banco)

-- ✅ Deve funcionar: UPDATE
-- (não vamos testar agora para não sujar banco)

-- =====================================
-- TESTE 2: Operações BLOQUEADAS (www-data)
-- =====================================

-- ❌ Deve FALHAR: DROP TABLE
-- DROP TABLE tenants;
-- Erro esperado: "permission denied"

-- ❌ Deve FALHAR: TRUNCATE
-- TRUNCATE TABLE tenants;
-- Erro esperado: "permission denied"

-- ❌ Deve FALHAR: DELETE em tabelas críticas
-- DELETE FROM tenants WHERE id = 'xxx';
-- Erro esperado: "permission denied"

-- =====================================
-- TESTE 3: Usuário READ-ONLY
-- =====================================

-- Conectar como: psql -U yumgo_readonly -d yumgo_production

-- ✅ Deve funcionar: SELECT
-- SELECT * FROM tenants LIMIT 1;

-- ❌ Deve FALHAR: INSERT
-- INSERT INTO tenants (id, name) VALUES (gen_random_uuid(), 'teste');
-- Erro esperado: "permission denied"

-- ❌ Deve FALHAR: UPDATE
-- UPDATE tenants SET name = 'teste' WHERE id = 'xxx';
-- Erro esperado: "permission denied"

-- ❌ Deve FALHAR: DELETE
-- DELETE FROM tenants WHERE id = 'xxx';
-- Erro esperado: "permission denied"

-- =====================================
-- TESTE 4: Usuário ADMIN
-- =====================================

-- Conectar como: psql -U yumgo_admin -d yumgo_production

-- ✅ Deve funcionar: TUDO (incluindo DROP)
-- (não vamos testar DROP para não apagar dados!)

-- =====================================
-- VERIFICAÇÃO DE PERMISSÕES
-- =====================================

-- Ver permissões de www-data
SELECT
    grantee,
    table_name,
    privilege_type
FROM information_schema.table_privileges
WHERE grantee = 'www-data'
    AND table_schema = 'public'
    AND table_name IN ('tenants', 'plans', 'restaurant_types')
ORDER BY table_name, privilege_type;

-- Ver permissões de yumgo_readonly
SELECT
    grantee,
    table_name,
    privilege_type
FROM information_schema.table_privileges
WHERE grantee = 'yumgo_readonly'
    AND table_schema = 'public'
ORDER BY table_name, privilege_type;

-- =====================================
-- ✅ RESULTADO ESPERADO
-- =====================================

/*
www-data:
✅ SELECT - OK
✅ INSERT - OK
✅ UPDATE - OK
❌ DELETE (tabelas críticas) - BLOQUEADO
❌ DROP - BLOQUEADO
❌ TRUNCATE - BLOQUEADO

yumgo_readonly:
✅ SELECT - OK
❌ INSERT - BLOQUEADO
❌ UPDATE - BLOQUEADO
❌ DELETE - BLOQUEADO
❌ DROP - BLOQUEADO
❌ TRUNCATE - BLOQUEADO

yumgo_admin:
✅ TUDO - OK
*/
