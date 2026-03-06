# 🔧 TODO: Sistema de Tokens para Bridge App

## ⚠️ O Que Falta Implementar

### 1. Adicionar HasApiTokens no Model User

**Arquivo:** `app/Models/User.php`

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // ← Adicionar HasApiTokens
```

### 2. Criar Resource no Filament para Gerar Tokens

**Arquivo:** `app/Filament/Restaurant/Resources/BridgeTokenResource.php`

```php
class BridgeTokenResource extends Resource
{
    // Tela para gerar e copiar token
    // Botão: "Gerar Novo Token"
    // Exibe token UMA VEZ (depois não mostra mais)
    // Lista tokens ativos com "Revogar"
}
```

### 3. Ou Adicionar na Página de Configurações

**Arquivo:** `app/Filament/Restaurant/Pages/Settings.php`

Adicionar seção:
```
🖨️ Impressão Automática
━━━━━━━━━━━━━━━━━━━━━━
ID do Restaurante: 144c5973... [Copiar]

Token de Acesso:
[ ] Nenhum token ativo

[Gerar Token de Acesso]
```

### 4. Migration para Sanctum (se não existir)

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 5. Rota de Autenticação do Bridge

**Arquivo:** `routes/tenant.php`

```php
// Verificar se token é válido
Route::middleware('auth:sanctum')->get('/api/bridge/verify', function () {
    return response()->json(['status' => 'authenticated']);
});
```

---

## 🎯 Fluxo Correto:

### No Painel (Futuro):

1. Restaurante acessa: Configurações → Impressão Automática
2. Clica: "Gerar Token de Acesso"
3. Sistema gera: `1|xxxxxxxxxxxxxxxxxxxxxxxxxxx`
4. Restaurante copia
5. Cola no app Bridge

### No App Bridge:

1. Cola ID do Restaurante
2. Cola Token
3. Clica "Conectar"
4. WebSocket autentica via Sanctum
5. Status: 🟢 Conectado

---

## 📝 Implementação Rápida (5 minutos):

**Opção 1: Via Tinker (Provisório)**

```php
php artisan tinker

$tenant = Tenant::first();
tenancy()->initialize($tenant);
$user = User::first();
$token = $user->createToken('bridge-app')->plainTextToken;

// Copiar este token para o app
```

**Opção 2: Criar Página Filament (Definitivo)**

Criar resource completo com:
- Gerar token
- Listar tokens
- Revogar tokens
- Copiar para clipboard

---

## ⏰ Prioridade

- [ ] **Alta:** Adicionar HasApiTokens no User
- [ ] **Alta:** Criar página de gerar token
- [ ] **Média:** Rota de verificação
- [ ] **Baixa:** UI bonita com clipboard

**Sem isso, o app não consegue conectar!** ⚠️
