# Schemas - Por que COM hífens? - 26/02/2026

## ✅ Formato Correto (MANTER)

```
✅ tenant144c5973-f985-4309-8f9a-c404dd11feae
✅ tenantparker-pizzaria
```

## ❌ Por que SEM hífens NÃO funciona?

O pacote `stancl/tenancy` gera o nome do schema assim:

```php
$schemaName = config('tenancy.database.prefix') . $tenant->id;

// Exemplo:
// Prefix: "tenant"
// Tenant ID: "144c5973-f985-4309-8f9a-c404dd11feae"
// Schema: "tenant144c5973-f985-4309-8f9a-c404dd11feae"
```

**Não remove os hífens!** Então o schema DEVE ter hífens se o ID do tenant tiver.

## 🎯 Solução para Schemas Curtos

Se quiser schemas curtos, o **tenant ID** precisa ser um slug:

```php
// Tenant com slug como ID
Tenant ID: "marmitaria-gi"
Schema: "tenantmarmitaria-gi"  ✅ Curto!

// Tenant com UUID como ID
Tenant ID: "144c5973-f985-4309-8f9a-c404dd11feae"
Schema: "tenant144c5973-f985-4309-8f9a-c404dd11feae"  ❌ Longo
```

## 📋 Situação Atual

### Marmitaria da Gi:
- **Tenant ID:** `144c5973-f985-4309-8f9a-c404dd11feae` (UUID)
- **Schema:** `tenant144c5973-f985-4309-8f9a-c404dd11feae`
- **Domain:** `marmitariadagi.yumgo.com.br`
- **Status:** ✅ Funcionando

### Parker Pizzaria:
- **Tenant ID:** `parker-pizzaria` (slug)
- **Schema:** `tenantparker-pizzaria`
- **Domain:** `parker-pizzaria.yumgo.com.br`
- **Status:** ✅ Funcionando

## 🔧 Se Quiser Padronizar (Opcional)

**Opção 1:** Manter como está (RECOMENDADO)
- Funciona perfeitamente
- Não precisa migrar dados
- Menos risco

**Opção 2:** Migrar Marmitaria para slug
1. Criar novo tenant com ID `marmitaria-gi`
2. Copiar todos os dados
3. Atualizar domains
4. Deletar tenant antigo
5. Schema ficaria: `tenantmarmitaria-gi`

**Complexidade:** Alta
**Risco:** Médio
**Benefício:** Schema mais curto

## ✅ Recomendação Final

**MANTER COMO ESTÁ!**
- Schemas estão funcionando
- Dados estão seguros
- Apenas garantir que novos tenants usem slugs curtos

**Para novos restaurantes:**
```php
Tenant::create([
    'id' => 'restaurante-abc',  // ← Slug curto
    // ...
]);
```

---

**Conclusão:** Os dois padrões podem coexistir. O importante é que o **schema tenha o MESMO formato que o tenant ID** (com hífens).
