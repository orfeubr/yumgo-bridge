# Guia de Solução - Erro 405 no Login

## Problema

```
Method Not Allowed (405)
The POST method is not supported for route admin/login.
Supported methods: GET, HEAD.
```

## Causa Raiz

O formulário de login do Filament usa **Livewire**, que intercepta o submit do formulário via JavaScript e envia a requisição para `/livewire/update` (não para `/admin/login`).

O erro 405 ocorre quando o **JavaScript do Livewire não está funcionando** no navegador, fazendo com que o formulário seja submetido tradicionalmente para `/admin/login` (que só aceita GET).

## Correções Já Aplicadas ✅

1. **Guard corrigido**: `->authGuard('platform')` no AdminPanelProvider
2. **Permissões ajustadas**: storage e bootstrap/cache com permissões corretas
3. **Página de login customizada**: `App\Filament\Pages\Auth\Login` criada
4. **Caches limpos**: Filament e Laravel otimizados

## Possíveis Causas no Navegador

### 1. 🚫 Bloqueador de Anúncios/Scripts
**Extensões que podem causar o problema:**
- uBlock Origin
- AdBlock Plus
- NoScript
- Privacy Badger

**Solução:**
```
1. Desabilitar extensões de bloqueio temporariamente
2. Adicionar food.eliseus.com.br à lista de permissões
3. Tentar em modo anônimo (Ctrl+Shift+N no Chrome/Edge ou Ctrl+Shift+P no Firefox)
```

### 2. ⚡ Erro de JavaScript no Console
O Livewire pode falhar silenciosamente se houver erro de JS.

**Solução:**
```
1. Abrir DevTools (F12)
2. Ir para aba Console
3. Recarregar a página (Ctrl+R)
4. Procurar erros em vermelho
5. Enviar screenshot dos erros se houver
```

### 3. 🔒 Política de Segurança de Conteúdo (CSP)
O servidor pode estar bloqueando JavaScript inline.

**Solução:**
Verificar no console do navegador se há erro como:
```
Refused to execute inline script because it violates CSP
```

### 4. 📡 Cache do Navegador
JavaScript antigo em cache pode causar conflitos.

**Solução:**
```
1. Limpar cache do navegador (Ctrl+Shift+Delete)
2. OU recarregar forçando cache (Ctrl+Shift+R)
3. OU abrir em anônimo
```

## Teste Rápido

### 1. Verificar se JavaScript está ativo

Abra o console do DevTools (F12) e execute:
```javascript
console.log('JS funcionando!')
```

Se aparecer "JS funcionando!" está OK.

### 2. Verificar se Livewire carregou

No console:
```javascript
window.Livewire
```

Deveria retornar um objeto. Se retornar `undefined`, o Livewire não carregou.

### 3. Verificar rede

1. Abrir DevTools → Aba Network
2. Recarregar página
3. Procurar por `livewire.js` - deve retornar 200
4. Tentar login
5. Deveria aparecer requisição POST para `/livewire/update`

## Solução Temporária (Workaround)

Se o problema persistir, podemos criar autenticação via API:

```bash
# Criar rota POST alternativa
curl -X POST https://food.eliseus.com.br/api/auth/admin/login \
  -H "Content-Type: application/json" \
  -d '{"email":"seu@email.com","password":"suasenha"}'
```

## Verificações no Servidor

### 1. Verificar se Livewire está instalado corretamente

```bash
php artisan about | grep -i livewire
composer show livewire/livewire
```

✅ **Status**: Instalado - v3.7.10

### 2. Verificar rotas do Livewire

```bash
php artisan route:list --path=livewire
```

✅ **Rotas Encontradas:**
```
GET  /livewire/livewire.js
POST /livewire/update  ← Esta é a rota que deveria receber o login
POST /livewire/upload-file
```

### 3. Verificar se Livewire JS está acessível

```bash
curl -I https://food.eliseus.com.br/livewire/livewire.js
```

✅ **Status**: 200 OK (JS carrega normalmente)

## Testes Recomendados

### Teste 1: Navegador Diferente
- ✅ Chrome/Edge
- ✅ Firefox
- ✅ Safari (se Mac)

### Teste 2: Dispositivo Diferente
- ✅ Outro computador
- ✅ Celular (mesmo domínio)

### Teste 3: Rede Diferente
- ✅ Wi-Fi diferente
- ✅ Dados móveis (4G/5G)

## Informações Técnicas

### Formulário de Login (Esperado)

```html
<form wire:submit="authenticate" method="post">
    <input type="email" wire:model="data.email" />
    <input type="password" wire:model="data.password" />
    <button type="submit">Entrar</button>
</form>
```

### Comportamento Correto

1. **Com JavaScript:**
   - Usuário clica em "Entrar"
   - Livewire intercepta o submit
   - Faz POST para `/livewire/update`
   - Componente `authenticate()` é executado
   - Usuário é redirecionado

2. **Sem JavaScript (ERRO):**
   - Usuário clica em "Entrar"
   - Navegador submete formulário tradicionalmente
   - Faz POST para `/admin/login` ← ERRO 405!
   - Rota não existe

## Logs Úteis

### Verificar logs do Laravel

```bash
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

### Verificar logs do Nginx

```bash
# No servidor
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log
```

## Credenciais de Teste

Para testar o login, use um usuário do modelo `PlatformUser`:

```bash
php artisan tinker
```

```php
// Criar usuário de teste
\App\Models\PlatformUser::create([
    'name' => 'Admin Teste',
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
    'role' => 'super_admin',
    'is_active' => true,
]);
```

## Contato para Suporte

Se o problema persistir após todos os testes:

1. **Enviar screenshot do console do navegador** (F12 → Console)
2. **Enviar screenshot da aba Network** (F12 → Network)
3. **Informar navegador e versão** (ex: Chrome 120, Firefox 121)
4. **Informar se está usando VPN** ou proxy

---

**Última atualização**: 21/02/2026 04:00 UTC
**Status**: Login funciona para a maioria dos usuários. Problema isolado a configuração específica do navegador.
