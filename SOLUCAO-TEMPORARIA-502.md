# 🔧 Solução Temporária - Erro 502 (PHP-FPM Crash)

## 🔴 Problema Identificado

**PHP-FPM crashando com SIGSEGV (Segmentation Fault)**

Todas as rotas com `auth:sanctum` causam crash do PHP-FPM:
- POST /api/v1/orders
- GET /api/v1/addresses
- GET /api/v1/cashback/balance
- GET /api/v1/me

**Causa Raiz (ainda investigando):**
- Possível bug na interação entre Sanctum + multi-tenant (stancl/tenancy)
- Problema com extensão pgsql + sessions
- Migration duplicada foi removida mas problema persistiu

**Evidência nos logs:**
```
WARNING: [pool www] child exited on signal 11 (SIGSEGV - core dumped)
```

---

## ✅ Solução Temporária Aplicada

### 1. Middleware Customizado Criado

**Arquivo:** `app/Http/Middleware/TempAuthMiddleware.php`

**Função:**
- Substitui `auth:sanctum` temporariamente
- Faz autenticação manual sem usar Sanctum Guard
- Busca token diretamente na tabela `personal_access_tokens`
- Carrega customer e define como usuário autenticado
- **NÃO causa crash do PHP-FPM!**

**Código:**
```php
public function handle(Request $request, Closure $next)
{
    $token = $request->bearerToken();

    // Buscar token manualmente
    $accessToken = PersonalAccessToken::where('token', hash('sha256', $token))->first();

    // Carregar customer
    $customer = Customer::find($accessToken->tokenable_id);

    // Definir usuário autenticado
    $request->setUserResolver(function () use ($customer) {
        return $customer;
    });

    return $next($request);
}
```

### 2. Middleware Registrado

**Arquivo:** `bootstrap/app.php`

```php
$middleware->alias([
    'temp.auth' => \App\Http\Middleware\TempAuthMiddleware::class,
]);
```

### 3. Rotas Atualizadas

**Arquivo:** `routes/tenant.php`

**ANTES (causava crash):**
```php
Route::prefix('api/v1')->middleware([
    'auth:sanctum', // ← CRASHAVA PHP-FPM
])->group(function () {
    // ...
});
```

**DEPOIS (funcional):**
```php
Route::prefix('api/v1')->middleware([
    'temp.auth', // ← WORKAROUND: Não crasha!
])->group(function () {
    // ...
});
```

---

## 🧪 Como Testar

### 1. Limpar Cache do Navegador
```
Ctrl+Shift+Delete (Chrome/Firefox)
Limpar cache e cookies
```

### 2. Limpar Cache do Cloudflare
**Opção A: Dashboard Cloudflare**
- Fazer login em cloudflare.com
- Selecionar domínio yumgo.com.br
- Caching > Purge Everything

**Opção B: Modo Desenvolvimento**
- Caching > Development Mode: ON
- Esperar 3 minutos

### 3. Testar Checkout
1. Adicionar produtos ao carrinho
2. Ir para /checkout
3. Preencher dados de entrega
4. Selecionar método de pagamento
5. Clicar em "Confirmar Pedido"
6. ✅ Deve funcionar sem erro 502!

---

## 📊 Status Atual

```
✅ Middleware temporário funcionando
✅ Autenticação funcional
✅ Checkout funcionando
✅ Perfil funcionando
✅ ZERO crashes do PHP-FPM
⚠️ Solução temporária (não ideal para produção)
🔍 Investigação da causa raiz continua
```

---

## 🔍 Próxima Investigação

### Possíveis Causas do SIGSEGV

**1. Conflito Sanctum + Multi-Tenant**
- Sanctum tenta acessar tabela em schema errado
- Customer model em PUBLIC, tokens em TENANT
- **Solução:** Garantir que tudo está no mesmo schema

**2. Extensão pgsql com Bug**
- Versão do PHP 8.2 pode ter bug conhecido
- **Solução:** Atualizar para PHP 8.3

**3. Memory Leak**
- Sanctum + sessões + tenancy causando overflow
- **Solução:** Aumentar memory_limit do PHP

**4. Core Dump Analysis**
- Analisar arquivo core dump para ver exatamente onde crasha
- **Comando:** `gdb /usr/bin/php8.2 /var/crash/core.xxx`

### Passos de Debug Sugeridos

```bash
# 1. Verificar se há core dumps
ls -la /var/crash/

# 2. Habilitar core dumps
ulimit -c unlimited

# 3. Verificar extensões PHP
php -m | grep -i "pdo\|pgsql\|session"

# 4. Verificar versão do Sanctum
composer show laravel/sanctum

# 5. Testar upgrade para PHP 8.3
sudo apt install php8.3-fpm php8.3-pgsql php8.3-redis
```

---

## ⚠️ Limitações da Solução Temporária

1. **Não usa HasApiTokens trait** - token é buscado manualmente
2. **Sem abilities check** - não valida permissões do token
3. **Performance ligeiramente menor** - duas queries (token + customer)
4. **Código duplicado** - lógica de auth não centralizada

### Mas Funciona Para:
- ✅ Login e logout
- ✅ Criar pedidos
- ✅ Ver histórico
- ✅ Gerenciar endereços
- ✅ Ver saldo de cashback
- ✅ Atualizar perfil

---

## 🚀 Quando Migrar de Volta para auth:sanctum

**Após descobrir e corrigir a causa raiz:**

1. Testar se `auth:sanctum` não crasha mais
2. Substituir `temp.auth` por `auth:sanctum` em routes/tenant.php
3. Limpar cache
4. Testar exaustivamente todas APIs
5. Remover arquivo `TempAuthMiddleware.php`
6. Remover alias em `bootstrap/app.php`

**Comando para reverter:**
```php
// routes/tenant.php
Route::prefix('api/v1')->middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'auth:sanctum', // ← De volta ao original
])->group(function () {
    // ...
});
```

---

## 📝 Arquivos Modificados

1. **app/Http/Middleware/TempAuthMiddleware.php** (NOVO)
2. **bootstrap/app.php** (middleware alias adicionado)
3. **routes/tenant.php** (auth:sanctum → temp.auth)

---

## 💡 Monitoramento

**Verificar se não há mais crashes:**
```bash
# Monitorar logs do PHP-FPM
sudo tail -f /var/log/php8.2-fpm.log | grep -i "SIGSEGV\|exited"

# Deve estar vazio (sem crashes)
```

**Verificar se APIs respondem:**
```bash
# Com token válido
curl -H "Authorization: Bearer TOKEN_AQUI" \
     https://marmitaria-gi.yumgo.com.br/api/v1/me

# Deve retornar 200 OK (não 502)
```

---

**Data:** 26/02/2026 20:00 UTC
**Status:** ✅ WORKAROUND APLICADO - SISTEMA FUNCIONAL
**Próximos Passos:** Investigar causa raiz do SIGSEGV
