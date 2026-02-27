# Padronização de Schemas - 26/02/2026

## 📊 Situação Atual

Schemas inconsistentes no banco:
```
📂 tenant144c5973f9854309c404dd11feae  (UUID sem hífens - LONGO)
📂 tenantparkerpizzaria                (slug sem hífens - CURTO ✅)
```

## ✅ Padrão Correto

**Formato:** `tenant{slug-sem-hifens}`

**Exemplos:**
```
✅ tenantmarmitariagi
✅ tenantparkerpizzaria
✅ tenantburguermaster
```

## 🔧 Como Padronizar (Manual)

### Opção 1: Renomear Schema Manualmente

```sql
-- No Adminer ou pgAdmin
ALTER SCHEMA "tenant144c5973f9854309c404dd11feae" 
RENAME TO "tenantmarmitariagi";
```

### Opção 2: Criar Novo Tenant

1. Deletar tenant antigo (cuidado com dados!)
2. Criar novo com slug correto
3. Migrar dados se necessário

## 🎯 Benefícios do Padrão

✅ **Legível** - `tenantmarmitariagi` vs `tenant144c5973f98543098f9ac404dd11feae`
✅ **Curto** - Menos caracteres
✅ **Consistente** - Todos seguem mesmo padrão
✅ **Fácil debug** - Identificar schema rapidamente

## ⚙️ Configuração do Tenancy

**Arquivo:** `config/tenancy.php`

```php
'database' => [
    'prefix' => 'tenant',  // ✅ OK
    'suffix' => '',        // ✅ OK
],
```

**Como funciona:**
```
Tenant ID: marmitaria-gi
Schema: tenant + marmitaria-gi (sem hífens) = tenantmarmitariagi
```

## 📋 Próximos Tenants

Ao criar novos restaurantes, use slugs:

```php
Tenant::create([
    'id' => 'restaurante-abc',  // ← Slug como ID
    'name' => 'Restaurante ABC',
    'slug' => 'restaurante-abc',
    // ...
]);
```

**Schema criado:** `tenantrestauranteabc`

## 🚫 Evitar

❌ UUIDs como ID do tenant
❌ Hífens no nome do schema
❌ Caracteres especiais

## ✅ Recomendado

✅ Slugs curtos e descritivos
✅ Apenas letras e números (sem hífens)
✅ Lowercase

---

**Conclusão:** Novos tenants devem usar slugs. Para os existentes, pode deixar como está (funcionando) ou renomear manualmente se preferir.
