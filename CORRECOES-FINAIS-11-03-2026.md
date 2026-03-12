# ✅ Correções Finais - 11/03/2026

## 🎯 Problemas Resolvidos

### 1️⃣ Plano do Tenant vs Assinatura Dessincronizados

**Problema:**
- Painel Central mostrava: **Enterprise**
- Página do Restaurante mostrava: **Trial**
- Dados não batiam!

**Causa:**
- Duas tabelas diferentes:
  - `tenants.plan_id` (usado no admin central)
  - `subscriptions.plan_id` (usado na página do restaurante)
- Quando mudava no admin, não atualizava a subscription

**Solução:**
- ✅ Criado **Observer automático** no `TenantObserver.php`
- ✅ Quando `plan_id` do tenant muda, sincroniza subscription automaticamente
- ✅ Atualiza assinatura existente OU cria nova se não existir

**Arquivo modificado:**
- `app/Observers/TenantObserver.php` - Método `syncSubscription()`

**Teste:**
```bash
# Agora quando você mudar o plano no admin central:
# 1. Vai para: https://yumgo.com.br/admin/tenants/marmitariadagi/edit
# 2. Muda o campo "Plano"
# 3. Salva
# 4. Automaticamente atualiza a subscription
# 5. A página https://marmitariadagi.yumgo.com.br/painel/manage-subscription
#    vai mostrar o plano correto!
```

---

### 2️⃣ Tipos de Culinária Não Salvavam

**Problema:**
- Selecionava checkboxes no admin central
- Salvava
- Ao recarregar, estava vazio

**Causa:**
- **Double Encoding** JSON:
  - Filament enviava: `["pizza","marmitex"]`
  - Cast 'json' do Laravel encodava DE NOVO: `"[\"pizza\",\"marmitex\"]"`
  - Salvava no banco como string dupla

**Solução:**
- ✅ Removido cast automático do Laravel
- ✅ Criado **Accessor e Mutator customizados**
- ✅ Accessor decodifica JSON → array
- ✅ Mutator encodifica array → JSON

**Arquivo modificado:**
- `app/Models/Tenant.php` - Métodos `getCuisineTypesAttribute()` e `setCuisineTypesAttribute()`

**Teste:**
```bash
php artisan tinker

$tenant = \App\Models\Tenant::find('marmitariadagi');
$tenant->cuisine_types = ['pizza', 'marmitex', 'brasileira'];
$tenant->save();

$tenant->refresh();
var_dump($tenant->cuisine_types); // array(3) { [0] => "pizza" ... }
```

---

### 3️⃣ Logo Não Aparecia no Site do Restaurante

**Problema:**
- Logo configurada no admin
- Não aparecia no site https://marmitariadagi.yumgo.com.br/

**Causa:**
- View usava rota `route('stancl.tenancy.asset')` que retornava 404
- Logo está no storage central: `/storage/app/public/tenants/logos/`
- Rota tenancy.asset não estava configurada corretamente

**Solução:**
- ✅ Mudado para usar path direto: `asset('storage/' . $settings->logo)`
- ✅ Funciona porque symlink `/public/storage` aponta para `/storage/app/public`

**Arquivo modificado:**
- `resources/views/restaurant-home.blade.php` - 3 substituições

**Teste:**
- Acesse: https://marmitariadagi.yumgo.com.br/
- Logo deve aparecer no header
- Logo deve aparecer no rodapé

---

## 📊 Resumo Técnico

### Arquivos Modificados

1. **app/Observers/TenantObserver.php**
   - Adicionado método `syncSubscription()`
   - Sincroniza subscription quando `plan_id` muda
   - Logs automáticos de criação/atualização

2. **app/Models/Tenant.php**
   - Removido cast `'cuisine_types' => 'json'`
   - Adicionado accessor `getCuisineTypesAttribute()`
   - Adicionado mutator `setCuisineTypesAttribute()`

3. **resources/views/restaurant-home.blade.php**
   - Substituído `route('stancl.tenancy.asset')` por `asset('storage/')`
   - 3 ocorrências (apple-touch-icon, logo header, logo footer)

---

## 🧪 Testes de Verificação

### Teste 1: Sincronização de Plano

```bash
php artisan tinker --execute="
\$tenant = \App\Models\Tenant::find('marmitariadagi');
\$sub = \App\Models\Subscription::where('tenant_id', 'marmitariadagi')->first();

echo '🏢 Tenant Plano: ' . \$tenant->plan->name . PHP_EOL;
echo '📄 Subscription Plano: ' . \$sub->plan->name . PHP_EOL;

if (\$tenant->plan_id === \$sub->plan_id) {
    echo '✅ SINCRONIZADOS!';
} else {
    echo '❌ DESSINCRONIZADOS!';
}
"
```

**Resultado esperado:** `✅ SINCRONIZADOS!`

---

### Teste 2: Tipos de Culinária

```bash
php artisan tinker --execute="
\$tenant = \App\Models\Tenant::find('marmitariadagi');

echo 'Tipos atuais: ' . json_encode(\$tenant->cuisine_types) . PHP_EOL;
echo 'É array? ' . (is_array(\$tenant->cuisine_types) ? 'SIM' : 'NÃO') . PHP_EOL;
echo 'Count: ' . count(\$tenant->cuisine_types) . PHP_EOL;
"
```

**Resultado esperado:** Array válido, não string

---

### Teste 3: Logo no Site

```bash
# Teste via curl
curl -I "https://marmitariadagi.yumgo.com.br/storage/tenants/logos/01KKDBHC7JTRQMSZ15GR99RJ1S.png"

# Deve retornar:
# HTTP/2 200
# Content-Type: image/png
```

**Teste visual:**
- Acesse: https://marmitariadagi.yumgo.com.br/
- Logo deve aparecer no canto superior esquerdo
- Logo deve aparecer no rodapé

---

## 🔄 Fluxo Completo Agora

### Quando você edita um tenant no painel central:

```
1. Admin acessa: https://yumgo.com.br/admin/tenants/marmitariadagi/edit

2. Muda campos:
   ├─ Plano: Enterprise
   ├─ Tipos de Culinária: Pizza, Marmitex, Bebidas
   └─ Logo: (upload nova imagem)

3. Clica em "Salvar"

4. TenantObserver detecta mudanças:
   ├─ plan_id mudou? → Sincroniza subscription ✅
   ├─ cuisine_types mudou? → Salva como array ✅
   └─ logo mudou? → Salva no storage central ✅

5. Dados refletem IMEDIATAMENTE em:
   ├─ https://yumgo.com.br/admin/tenants (lista)
   ├─ https://marmitariadagi.yumgo.com.br/painel/manage-subscription
   └─ https://marmitariadagi.yumgo.com.br/ (site público)
```

---

## ⚠️ Importante: Cache

Após fazer mudanças, sempre limpar cache:

```bash
php artisan optimize:clear
php artisan view:clear
php artisan filament:clear-cached-components
```

---

## 📝 Logs Automáticos

Agora o sistema gera logs automáticos quando:

### Subscription Criada/Atualizada:
```
[info] ✅ Assinatura atualizada automaticamente
[tenant_id] => marmitariadagi
[old_plan_id] => 4
[new_plan_id] => 3
[new_plan_name] => Enterprise
```

### Tipos de Culinária Salvos:
```bash
# Ver logs
tail -f storage/logs/laravel.log | grep cuisine_types
```

---

## 🎯 URLs para Testar

### Painel Central (Admin)
- **Editar Tenant:** https://yumgo.com.br/admin/tenants/marmitariadagi/edit
- **Lista de Assinaturas:** https://yumgo.com.br/admin/subscriptions

### Painel do Restaurante
- **Manage Subscription:** https://marmitariadagi.yumgo.com.br/painel/manage-subscription
- **Dashboard:** https://marmitariadagi.yumgo.com.br/painel

### Site Público
- **Home:** https://marmitariadagi.yumgo.com.br/
- **Logo Direta:** https://marmitariadagi.yumgo.com.br/storage/tenants/logos/01KKDBHC7JTRQMSZ15GR99RJ1S.png

---

**Status:** ✅ TODOS OS PROBLEMAS RESOLVIDOS!

**Data:** 11/03/2026 - 03:00 UTC
