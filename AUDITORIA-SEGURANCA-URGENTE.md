# Auditoria de Segurança - Correções Urgentes - 27/02/2026

## 🚨 VULNERABILIDADES CRÍTICAS CORRIGIDAS

### 1. ✅ Endpoint de Pedidos SEM Autenticação (REMOVIDO)

**Vulnerabilidade:**
```php
// routes/tenant.php linha 156-184
Route::post('/api/v1/orders', function() {
    $customer = \App\Models\Customer::find(2); // ⚠️ HARDCODED!
    // ...
});
```

**Risco:** Qualquer pessoa podia criar pedidos usando customer ID fixo.

**Correção:** 
- ✅ Rota removida completamente
- ✅ Todas as APIs agora requerem autenticação obrigatória

---

### 2. ✅ Webhook Token Opcional → OBRIGATÓRIO

**Vulnerabilidade:**
```php
// CentralWebhookController.php linha 44-46
if (!$receivedToken) {
    Log::warning('Token não enviado (ignorando validação)'); // ⚠️
}
```

**Risco:** Atacante podia forjar webhooks e confirmar pagamentos falsos.

**Correção:**
- ✅ Token agora é OBRIGATÓRIO
- ✅ Requisições sem token são rejeitadas com 401
- ✅ Logs detalhados de tentativas suspeitas

---

## ⚠️ VULNERABILIDADES PENDENTES (ALTA PRIORIDADE)

### 1. Sanitização XSS (strip_tags → htmlspecialchars)

**Arquivo:** `app/Http/Controllers/Api/OrderController.php`
**Linha:** 81-83

**Problema:**
```php
$deliveryAddress = strip_tags(trim($request->delivery_address));
```

**Risco:** `strip_tags()` não previne XSS completamente.

**Solução:**
```php
$deliveryAddress = htmlspecialchars(trim($request->delivery_address), ENT_QUOTES, 'UTF-8');
```

**Status:** 📋 Task #1 criada

---

### 2. Relacionamentos Customer ↔ Order (Cross-Schema)

**Arquivo:** `app/Models/Customer.php`
**Linha:** 74-76

**Problema:**
```php
// Customer usa conexão 'pgsql' (central)
protected $connection = 'pgsql';

// Mas tem relacionamento com Order (tenant)
public function orders(): HasMany {
    return $this->hasMany(Order::class);
}
```

**Risco:** Se tenancy não estiver inicializado, `$customer->orders` pode:
- Retornar orders de TODOS os tenants
- Causar erro de schema não encontrado
- Vazar dados entre restaurantes

**Solução:** Adicionar scope ao relacionamento:
```php
public function orders(): HasMany {
    if (!tenancy()->initialized) {
        throw new \Exception('Tenancy must be initialized to access orders');
    }
    return $this->hasMany(Order::class);
}
```

**Status:** 📋 Task #2 criada

---

### 3. Rate Limiting Ausente

**Problema:** Endpoints sensíveis não têm rate limiting.

**Risco:**
- Brute force em login
- Spam de criação de pedidos
- DDoS no webhook

**Solução:**
```php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1'); // 5 tentativas por minuto

Route::post('/orders', [OrderController::class, 'store'])
    ->middleware('throttle:10,60'); // 10 pedidos por hora
```

**Status:** 📋 Task #3 criada

---

### 4. Logs com Dados Sensíveis (LGPD)

**Problema:** Múltiplos `Log::info` com dados sensíveis.

**Exemplo:**
```php
Log::info('Login SUCCESS', [
    'user_id' => Auth::user()->id,
    'session_after' => session()->getId(), // ⚠️
]);
```

**Risco:** Violação LGPD, senhas em logs de erro.

**Solução:**
- Remover logs desnecessários
- Mascarar dados sensíveis
- Implementar log rotation

**Status:** 📋 Task #4 criada

---

## 🎯 PRÓXIMAS FASES (ORDEM DE PRIORIDADE)

### FASE 1: Segurança Básica (Esta Semana)
- [x] Remover rotas sem auth
- [x] Token webhook obrigatório
- [ ] Sanitização XSS
- [ ] Rate limiting
- [ ] Revisar logs

### FASE 2: Isolamento Multi-Tenant (Semana 2)
- [ ] Auditar relacionamentos Customer ↔ Tenant
- [ ] Testar queries cross-schema
- [ ] Validar isolamento de cache
- [ ] Testar isolamento de storage

### FASE 3: Validação e IDOR (Semana 3)
- [ ] Adicionar validação em TODOS os controllers
- [ ] Substituir IDs sequenciais por UUIDs
- [ ] Testar IDOR em todos os endpoints
- [ ] Validar ownership em queries

### FASE 4: Headers e Configurações (Semana 4)
- [ ] Implementar CSP
- [ ] Adicionar X-Frame-Options
- [ ] Configurar CORS
- [ ] Forçar HTTPS com HSTS

### FASE 5: LGPD e Privacidade (Mês 2)
- [ ] Implementar soft deletes
- [ ] Criar endpoint de "direito ao esquecimento"
- [ ] Log rotation
- [ ] Auditoria de acessos

### FASE 6: Testes de Penetração (Mês 2-3)
- [ ] SQL Injection
- [ ] XSS
- [ ] CSRF
- [ ] IDOR
- [ ] Multi-tenancy bypass

---

## 📊 MÉTRICAS DE SEGURANÇA

### Antes da Auditoria:
- ❌ Endpoint público sem auth
- ❌ Webhook sem validação obrigatória
- ⚠️ XSS em campos de texto
- ⚠️ Sem rate limiting
- ⚠️ Logs com dados sensíveis

### Após Correções Urgentes:
- ✅ Todas as APIs requerem auth
- ✅ Webhook com token obrigatório
- ⚠️ XSS parcialmente protegido (pendente)
- ⚠️ Rate limiting (pendente)
- ⚠️ Logs (pendente)

### Meta (Final):
- ✅ 100% endpoints protegidos
- ✅ Zero queries cross-schema
- ✅ Rate limiting em tudo
- ✅ Logs LGPD compliant
- ✅ Headers de segurança configurados

---

## 🔒 CHECKLIST DE SEGURANÇA

### Autenticação
- [x] Rotas protegidas com auth:sanctum
- [x] Webhook com token obrigatório
- [ ] Rate limiting em login
- [ ] CAPTCHA em formulários públicos

### Autorização
- [ ] IDOR testado em todos os endpoints
- [ ] Ownership validado em queries
- [ ] Admin tenant não acessa outros tenants

### Validação
- [ ] Todos os inputs validados
- [ ] XSS prevenido com htmlspecialchars
- [ ] SQL Injection protegido com Eloquent
- [ ] CSRF tokens em formulários

### Multi-Tenancy
- [ ] Queries cross-schema impossíveis
- [ ] Cache isolado por tenant
- [ ] Storage isolado por tenant
- [ ] Sessões isoladas por tenant

### LGPD
- [ ] Dados sensíveis não logados
- [ ] Senhas hasheadas (bcrypt)
- [ ] Soft deletes implementado
- [ ] Direito ao esquecimento

---

## 📁 Arquivos Críticos Revisados

✅ `/var/www/restaurante/routes/tenant.php`
✅ `/var/www/restaurante/app/Http/Controllers/CentralWebhookController.php`
⏳ `/var/www/restaurante/app/Http/Controllers/Api/OrderController.php`
⏳ `/var/www/restaurante/app/Models/Customer.php`
⏳ `/var/www/restaurante/app/Services/OrderService.php`

---

**Data:** 27/02/2026 00:00 UTC
**Responsável:** Auditoria de Segurança
**Status:** ✅ 2 Vulnerabilidades Críticas Corrigidas
**Pendente:** 4 Tarefas de Alta Prioridade
