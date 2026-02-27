# Correções Aplicadas - DeliveryPro

Data: 21/02/2026

## Problemas Encontrados e Soluções

### 1. ❌ ERRO 405 - Method Not Allowed no Login Admin

**Problema:**
- O painel admin estava configurado para usar o guard padrão (`web`)
- O guard `web` autentica com o modelo `User`
- Mas os usuários do admin estão no modelo `PlatformUser` com guard `platform`

**Solução:**
```php
// app/Providers/Filament/AdminPanelProvider.php
->authGuard('platform')  // ✅ Adicionado
```

### 2. ❌ ERRO 500 - Internal Server Error no Login

**Problema:**
- Permissões incorretas nos diretórios `storage/` e `bootstrap/cache`
- O servidor web (www-data) não conseguia escrever arquivos temporários

**Solução:**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 3. ❌ ERRO 500 - API /api/v1/products

**Problema:**
- Rotas da API estavam em `routes/api.php` (contexto central)
- Dados dos produtos estão nas tabelas de tenant (schemas separados)
- Faltava middleware de tenancy nas rotas da API

**Solução:**
- ✅ Movido todas as rotas da API para `routes/tenant.php`
- ✅ Aplicado middleware `InitializeTenancyByDomain`
- ✅ Limpado `routes/api.php` (agora só tem rotas centrais)

### 4. ⚙️ Configuração de Domínios

**Problema:**
- O domínio `food.eliseus.com.br` não estava configurado

**Solução:**
```php
// config/tenancy.php
'central_domains' => [
    '127.0.0.1',
    'localhost',
    'food.eliseus.com.br',  // ✅ Adicionado
    '*.deliverypro.local',
],
```

### 5. 🐛 Debug Mode

**Problema:**
- `APP_DEBUG=false` dificulta identificação de erros

**Solução:**
```env
APP_DEBUG=true  // ✅ Ativado
```

## Status Atual

### ✅ FUNCIONANDO
- **Admin Login**: `https://food.eliseus.com.br/admin/login`
  - Guard: `platform`
  - Model: `PlatformUser`
  - Status: ✅ HTTP 200

### ⚠️ PRECISA CONFIGURAÇÃO
- **API**: `https://food.eliseus.com.br/api/v1/products`
  - Status: ❌ HTTP 404
  - Motivo: Domínio `food.eliseus.com.br` está nos `central_domains`
  - As rotas da API agora exigem domínio de tenant

## Como Configurar a API

### Opção 1: Criar Domínio para Tenant (Recomendado)

```bash
php artisan tinker
```

```php
// Criar domínio para o tenant
$tenant = \Stancl\Tenancy\Database\Models\Tenant::find('pizza-express');
$tenant->domains()->create([
    'domain' => 'pizza.food.eliseus.com.br'
]);
```

Depois, acessar a API via:
```
https://pizza.food.eliseus.com.br/api/v1/products
```

### Opção 2: Usar Identificação por Header

Modificar `routes/tenant.php` para usar `InitializeTenancyByRequestData`:

```php
Route::prefix('api/v1')->middleware([
    'api',
    InitializeTenancyByRequestData::class, // ✅ Por header
])->group(function () {
    // ...
});
```

E enviar no request:
```bash
curl -H "X-Tenant: pizza-express" https://food.eliseus.com.br/api/v1/products
```

### Opção 3: Manter API no Contexto Central

Se os produtos devem ser compartilhados entre todos os tenants:

1. Mover tabela `products` para o schema `public`
2. Mover rotas da API de volta para `routes/api.php`
3. Remover middleware de tenancy

## Tenants Existentes

```
1. pizza-express
2. burger-master
3. sushi-house
```

**⚠️ Nenhum domínio cadastrado ainda!**

## Próximos Passos

1. ✅ Decidir arquitetura da API (por domínio, header ou central)
2. ⬜ Configurar domínios para os tenants existentes
3. ⬜ Executar migrations de tenant se necessário:
   ```bash
   php artisan tenants:migrate
   ```
4. ⬜ Popular dados de teste (categorias, produtos)
5. ⬜ Testar fluxo completo de autenticação e pedidos

## Comandos Úteis

```bash
# Listar tenants
php artisan tinker --execute="DB::table('tenants')->get();"

# Listar domínios
php artisan tinker --execute="DB::table('domains')->get();"

# Migrar tenants
php artisan tenants:migrate

# Limpar caches
php artisan optimize:clear

# Ver rotas
php artisan route:list
```

## Arquivos Modificados

```
✏️  app/Providers/Filament/AdminPanelProvider.php
✏️  routes/tenant.php (adicionadas rotas da API)
✏️  routes/api.php (limpado)
✏️  config/tenancy.php (adicionado food.eliseus.com.br)
✏️  .env (APP_DEBUG=true)
```

## Logs

Verificar erros em:
```
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

---

**Autor:** Claude Code
**Data:** 21/02/2026 03:55 UTC
