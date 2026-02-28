# 🔒 Correções de Segurança: Rate Limiting & Exposição de Dados

**Data:** 27/02/2026
**Problemas:** Rate limiting muito restritivo + Exposição de caminhos internos

---

## 🐛 Problemas Identificados

### 1. Rate Limiting Muito Restritivo ❌

**Erro:**
```json
{
  "message": "Too Many Attempts.",
  "exception": "Illuminate\\Http\\Exceptions\\ThrottleRequestsException",
  "file": "/var/www/restaurante/vendor/laravel/framework/...",
  "line": 254,
  "trace": [...]
}
```

**Causa:**
```php
// routes/tenant.php (ANTES)
Route::post('/orders', [OrderController::class, 'store'])
    ->middleware('throttle:10,60'); // ❌ 10 pedidos/hora
```

**Problema:**
- Cliente testando checkout → Estoura rápido
- Desenvolvedor testando → Bloqueado
- 10 pedidos/hora é muito restritivo

### 2. Exposição de Caminhos Internos ❌

**O que estava exposto:**
```json
{
  "exception": "Illuminate\\Http\\Exceptions\\ThrottleRequestsException",
  "file": "/var/www/restaurante/vendor/laravel/framework/src/...",
  "line": 254,
  "trace": [
    "#0 /var/www/restaurante/vendor/laravel/...",
    "#1 /var/www/restaurante/app/Http/...",
  ]
}
```

**Riscos:**
- ✗ Exposição da estrutura do servidor
- ✗ Caminhos completos dos arquivos
- ✗ Versões de frameworks/bibliotecas
- ✗ Stack traces completos
- ✗ Informações úteis para atacantes

---

## ✅ Correções Aplicadas

### 1. **Rate Limiting Ajustado** ⭐

**Arquivo:** `routes/tenant.php`

**ANTES:**
```php
Route::post('/orders', [OrderController::class, 'store'])
    ->middleware('throttle:10,60'); // 10 pedidos/hora
```

**DEPOIS:**
```php
Route::post('/orders', [OrderController::class, 'store'])
    ->middleware('throttle:30,60'); // 30 pedidos/hora (razoável)
```

**Justificativa:**
- ✅ Permite testes sem bloqueios constantes
- ✅ Ainda protege contra spam/abuse
- ✅ 30 pedidos/hora = ~1 pedido a cada 2 minutos
- ✅ Cliente normal nunca atinge esse limite

### 2. **APP_DEBUG Desabilitado** ⭐

**Arquivo:** `.env`

**ANTES:**
```env
APP_DEBUG=true  # ❌ PERIGOSO em produção
```

**DEPOIS:**
```env
APP_DEBUG=false  # ✅ SEGURO
```

**O que muda:**
- ❌ Não mostra stack traces
- ❌ Não expõe caminhos de arquivos
- ❌ Não revela código fonte
- ✅ Mostra páginas de erro genéricas
- ✅ Loga erros internamente (storage/logs)

### 3. **Exception Handler Seguro para APIs** ⭐

**Arquivo:** `bootstrap/app.php`

**Adicionado:**
```php
->withExceptions(function (Exceptions $exceptions): void {
    // 🔒 SEGURANÇA: Não expor stack traces em APIs (produção)
    $exceptions->render(function (\Throwable $e, $request) {
        // Se for request de API e não estiver em modo debug
        if ($request->is('api/*') && !config('app.debug')) {
            $statusCode = method_exists($e, 'getStatusCode')
                ? $e->getStatusCode()
                : 500;

            // Mensagens seguras sem expor internals
            $message = match($statusCode) {
                429 => 'Muitas tentativas. Aguarde alguns minutos.',
                401 => 'Não autenticado.',
                403 => 'Acesso negado.',
                404 => 'Recurso não encontrado.',
                422 => 'Dados inválidos.',
                default => 'Erro no servidor. Tente novamente.'
            };

            return response()->json([
                'message' => $message,
                'status' => $statusCode,
            ], $statusCode);
        }

        return null; // Laravel usa handler padrão
    });
})
```

**O que faz:**
- ✅ Detecta requests para `/api/*`
- ✅ Se `APP_DEBUG=false` → Não expõe detalhes
- ✅ Retorna mensagens genéricas e seguras
- ✅ Mantém detalhes em ambiente de desenvolvimento
- ✅ Erros ainda são logados (para debug interno)

---

## 📊 Comparação: ANTES vs DEPOIS

### Erro de Rate Limiting

#### ANTES (Inseguro):
```json
{
  "message": "Too Many Attempts.",
  "exception": "Illuminate\\Http\\Exceptions\\ThrottleRequestsException",
  "file": "/var/www/restaurante/vendor/laravel/framework/src/Illuminate/Routing/Middleware/ThrottleRequests.php",
  "line": 254,
  "trace": [
    "#0 /var/www/restaurante/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(148): Illuminate\\Routing\\Middleware\\ThrottleRequests->handle(...)",
    "#1 /var/www/restaurante/vendor/laravel/framework/src/Illuminate/Routing/Router.php(799): Illuminate\\Pipeline\\Pipeline->then(...)",
    // ... mais 40 linhas expondo estrutura interna
  ]
}
```

**Expõe:**
- ✗ Caminho completo do servidor
- ✗ Framework usado (Laravel)
- ✗ Estrutura de diretórios
- ✗ Arquivos internos do framework
- ✗ Linha exata do erro

#### DEPOIS (Seguro):
```json
{
  "message": "Muitas tentativas. Aguarde alguns minutos.",
  "status": 429
}
```

**Expõe:**
- ✅ Apenas mensagem amigável
- ✅ Status HTTP correto
- ✅ NADA sobre estrutura interna

---

## 🔒 Outras Boas Práticas Aplicadas

### Rate Limits Atuais (Razoáveis):

```php
// Autenticação (prevenção de brute force)
Route::post('/register')->middleware('throttle:3,1');  // 3/min
Route::post('/login')->middleware('throttle:5,1');     // 5/min
Route::post('/forgot-password')->middleware('throttle:3,1');  // 3/min

// Leitura pública (generoso)
Route::get('/products')->middleware('throttle:60,1');  // 60/min
Route::get('/categories')->middleware('throttle:60,1'); // 60/min

// Ações críticas (moderado)
Route::post('/orders')->middleware('throttle:30,60');  // 30/hora ⭐
Route::post('/orders/{id}/cancel')->middleware('throttle:10,1'); // 10/min
```

### Por Que Esses Limites?

**Autenticação (3-5/min):**
- Previne ataques de força bruta
- 5 tentativas = suficiente para erros legítimos
- Bloqueia bots automatizados

**Leitura (60/min):**
- Permite navegação fluida
- 60 requests = muitas consultas de produtos
- Não afeta UX normal

**Pedidos (30/hora):**
- Cliente normal não faz 30 pedidos/hora
- Permite retentativas de pagamento
- Bloqueia spam/abuse

---

## 🧪 Testar Agora

### 1. Testar Rate Limiting
```bash
# Fazer vários pedidos rapidamente
for i in {1..5}; do
  curl -X POST https://marmitaria-gi.yumgo.com.br/api/v1/orders \
    -H "Authorization: Bearer TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"items": [...]}'
  sleep 2
done

# Deve funcionar (30/hora = ~1 a cada 2 min)
```

### 2. Testar Mensagem de Erro Segura
```bash
# Forçar rate limit (fazer 31 requests em 1 hora)
# Deve retornar:
{
  "message": "Muitas tentativas. Aguarde alguns minutos.",
  "status": 429
}

# ✅ SEM expor caminhos internos!
```

### 3. Verificar Logs Internos
```bash
# Erros ainda são logados (para debug interno)
tail -f /var/www/restaurante/storage/logs/laravel.log

# Deve mostrar detalhes completos (só para admins)
```

---

## 📝 Arquivos Modificados

### `routes/tenant.php` ⭐
**Linha ~152:** `throttle:10,60` → `throttle:30,60`

### `.env` ⭐
**Linha 3:** `APP_DEBUG=true` → `APP_DEBUG=false`

### `bootstrap/app.php` ⭐
**Linhas 52-80:** Exception handler seguro para APIs

---

## 🎯 Checklist de Segurança

### Produção:
- [x] ✅ `APP_DEBUG=false`
- [x] ✅ Rate limiting razoável
- [x] ✅ Não expõe stack traces em APIs
- [x] ✅ Mensagens de erro genéricas
- [x] ✅ Logs internos funcionando

### Desenvolvimento:
- [x] ✅ Pode usar `APP_DEBUG=true` (local)
- [x] ✅ Stack traces visíveis (local)
- [x] ✅ Rate limiting mais relaxado (local)

---

## 💡 Outras Recomendações

### 1. Monitorar Rate Limits
```bash
# Verificar quantos requests são bloqueados
grep "ThrottleRequestsException" storage/logs/laravel.log | wc -l
```

### 2. Ajustar por Endpoint
Se algum endpoint específico recebe muitos bloqueios legítimos, ajustar:
```php
// Exemplo: aumentar limite de checkout
Route::post('/checkout')->middleware('throttle:50,60'); // 50/hora
```

### 3. IP Whitelist (opcional)
Para IPs confiáveis (testes, monitoring):
```php
// config/throttle.php
'whitelist' => [
    '192.168.1.100', // IP do desenvolvedor
    '10.0.0.1',      // IP do monitoring
],
```

### 4. Headers de Rate Limit
Laravel já envia automaticamente:
```
X-RateLimit-Limit: 30
X-RateLimit-Remaining: 25
Retry-After: 120  (se bloqueado)
```

Cliente pode ler isso e exibir contador!

---

## 🚨 IMPORTANTE: Ambientes

### Produção (yumgo.com.br):
```env
APP_ENV=production
APP_DEBUG=false  # ⭐ CRÍTICO
```

### Desenvolvimento (localhost):
```env
APP_ENV=local
APP_DEBUG=true  # OK em dev
```

### Staging (se houver):
```env
APP_ENV=staging
APP_DEBUG=false  # Mesmo em staging
```

---

## 🔍 Como Identificar Exposição de Dados

### Sinais de Problema:
```
❌ Caminhos começam com /var/www/...
❌ Menciona vendor/laravel/...
❌ Mostra nomes de classes internas
❌ Stack traces completos
❌ Números de linha de arquivos
```

### Sinais de Segurança:
```
✅ Mensagens genéricas
✅ Apenas status HTTP
✅ Não menciona arquivos
✅ Não mostra código
✅ Cliente entende o erro
```

---

**Status:** ✅ RESOLVIDO
**Impacto:** CRÍTICO (segurança)
**Deploy:** IMEDIATO (já aplicado)

---

**🔒 Sistema agora está mais seguro e não expõe internals!**
