# 🤖 Workflow N8N - Auto-Fix de Erros em Tempo Real

Sistema automatizado que monitora erros via Flare e usa Claude AI para analisar e corrigir automaticamente.

---

## 🎯 FLUXO COMPLETO

```
Erro ocorre → Flare detecta → Webhook → n8n → Claude analisa →
Auto-fix (se possível) → Commit Git → Notificação Slack/Email
```

---

## 📋 PRÉ-REQUISITOS

### 1. **Flare Error Monitoring**
- ✅ Já configurado: `FLARE_KEY=C6P5fcFbV5mbLcUN6NKxRrrybw5E6uHl`
- Dashboard: https://flareapp.io

### 2. **n8n Automation**
- Install: `npm install -g n8n`
- Ou Docker: `docker run -p 5678:5678 n8nio/n8n`

### 3. **Claude API**
- Obter API key em: https://console.anthropic.com
- Modelo recomendado: `claude-sonnet-4-20250514`

---

## 🔧 CONFIGURAÇÃO

### **Passo 1: Configurar Webhook no Flare**

1. Acesse: https://flareapp.io/projects/yumgo/settings/webhooks
2. Adicione novo webhook:
   ```
   URL: https://seu-n8n.com/webhook/flare-webhook
   Events: All errors
   Method: POST
   ```

3. Teste o webhook:
   ```php
   // Acesse: https://yumgo.com.br/test-flare
   throw new \Exception('🔥 Teste Flare Webhook');
   ```

---

### **Passo 2: Importar Workflow no n8n**

1. Abra n8n: http://localhost:5678
2. Click em "Import from File"
3. Selecione: `n8n-workflows/auto-fix-errors.json`
4. Configure credenciais:
   - **Anthropic API**: Sua chave do Claude
   - **Slack** (opcional): Token do bot
   - **Email SMTP**: Configurações de email

---

### **Passo 3: Ativar Workflow**

1. No n8n, clique em "Activate" no workflow
2. Copie a URL do webhook gerado
3. Cole no Flare (Passo 1)

---

## 🤖 FUNCIONAMENTO DO WORKFLOW

### **Node 1: Webhook - Recebe Erro do Flare**
```json
{
  "exception": {
    "class": "ArgumentCountError",
    "message": "Too few arguments...",
    "file": "/var/www/restaurante/routes/web.php",
    "line": 31,
    "trace": [...]
  },
  "context": {
    "tenant_id": "abc123",
    "tenant_name": "Marmitaria da Gi",
    "env": "production"
  },
  "request": {
    "url": "https://yumgo.com.br/",
    "method": "GET"
  }
}
```

---

### **Node 2: Extract Error Data**
Extrai informações relevantes do erro:
- Tipo de exceção
- Mensagem e stack trace
- Arquivo e linha
- Contexto (tenant, URL, etc)
- Monta prompt para o Claude

---

### **Node 3: Claude - Analyze Error**
Envia para Claude AI:
```
Prompt:
ERRO DE PRODUÇÃO NO YUMGO:

Exceção: ArgumentCountError
Mensagem: Too few arguments to function...
Arquivo: routes/web.php:31

ANALISE:
1. Identifique a causa raiz
2. Sugira a correção necessária
3. Forneça o código corrigido
4. Indique se é crítico
```

**Resposta do Claude:**
```json
{
  "severity": "critical",
  "cause": "Controller::index() espera Request mas não está sendo passado",
  "solution": "Adicionar parâmetro $request na closure e passar para o controller",
  "code_fix": "Route::get('/', function (Request $request) {\n    return app(Controller::class)->index($request);\n});",
  "file_path": "routes/web.php",
  "can_auto_fix": true
}
```

---

### **Node 4: Parse Response**
Converte resposta do Claude para JSON estruturado

---

### **Node 5: Can Auto-Fix?**
Decide se pode aplicar correção automaticamente:

**Critérios:**
- ✅ `can_auto_fix === true` OU
- ✅ `severity === 'critical'`

**Se SIM:** → Apply Auto-Fix + Git Commit
**Se NÃO:** → Notifica para revisão manual

---

### **Node 6: Apply Auto-Fix** (SE PERMITIDO)
Aplica correção via API:
```bash
POST /api/auto-fix
{
  "file_path": "routes/web.php",
  "code_fix": "código corrigido",
  "cause": "descrição",
  "severity": "critical"
}
```

---

### **Node 7: Prepare Git Commit**
Cria commit automático:
```
Mensagem: fix: Controller espera Request mas não está sendo passado

Adiciona parâmetro $request na closure e passa para o controller

Auto-fixed by Claude AI via n8n workflow
Severity: critical

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
```

---

### **Node 8: Notify Slack**
Envia notificação no canal `#production-errors`:
```
🔴 ERRO DE PRODUÇÃO - YumGo

Severidade: CRITICAL
Tenant: Marmitaria da Gi
URL: https://yumgo.com.br/

Erro: ArgumentCountError: Too few arguments...

Análise do Claude:
Causa: Controller::index() espera Request
Solução: Adicionar parâmetro $request

Auto-fix aplicado: ✅ SIM
```

---

### **Node 9: Notify Email** (APENAS CRITICAL)
Envia email detalhado com:
- Stack trace completo
- Análise do Claude
- Código sugerido
- Status do auto-fix

---

## 🛡️ PROTEÇÕES DE SEGURANÇA

### **1. Validação de Severidade**
```javascript
// Só aplica auto-fix se:
can_auto_fix === true && severity === 'critical'
```

### **2. Whitelist de Arquivos**
```javascript
const ALLOWED_FILES = [
  'routes/web.php',
  'routes/api.php',
  'app/Http/Controllers/*',
  'app/Services/*'
];

// Bloqueia auto-fix em:
// - Migrations
// - .env
// - composer.json
```

### **3. Backup Antes de Aplicar**
```bash
# Auto-fix cria branch separada
git checkout -b auto-fix/1709337600
# Aplica correção
# Cria PR (se critical)
# Mantém histórico
```

### **4. Rate Limiting**
```
Máximo: 10 auto-fixes por hora
Se exceder: Só notifica, não aplica
```

---

## 📊 MÉTRICAS MONITORADAS

### **Dashboard n8n**
- Total de erros recebidos
- Erros corrigidos automaticamente
- Erros que precisaram intervenção manual
- Tempo médio de resposta
- Taxa de sucesso dos auto-fixes

### **Exemplo:**
```
Últimas 24h:
- 15 erros detectados
- 10 corrigidos automaticamente (66%)
- 3 aguardando revisão manual (20%)
- 2 ignorados (baixa severidade) (14%)

Tempo médio: 2.3 segundos
```

---

## 🧪 TESTAR O WORKFLOW

### **Teste 1: Erro Simples**
```bash
# Acesse no navegador:
https://yumgo.com.br/test-flare

# Deve:
1. Flare capturar erro
2. Enviar webhook para n8n
3. n8n processar com Claude
4. Notificação no Slack/Email
```

### **Teste 2: Erro Critical (Auto-Fix)**
```php
// Crie erro proposital em routes/web.php
Route::get('/test-autofix', function () {
    $controller = new NonExistentController();
});

// Deve:
1. Flare capturar
2. Claude analisar
3. Auto-fix aplicar
4. Commit criado
5. Notificação enviada
```

---

## 🎯 TIPOS DE ERROS QUE CLAUDE PODE CORRIGIR

### **✅ Auto-Fix Habilitado:**
1. **ArgumentCountError** - Parâmetros faltando
2. **ClassNotFoundException** - Use statement faltando
3. **MethodNotFoundException** - Typo em nome de método
4. **PropertyNotFoundException** - Propriedade não existe
5. **Syntax Errors** - Virgula faltando, parêntese etc
6. **Type Errors** - Tipo incorreto passado

### **⚠️ Requer Revisão Manual:**
1. **Database Errors** - Pode ter impacto nos dados
2. **Permission Errors** - Questões de segurança
3. **Business Logic Errors** - Regra de negócio errada
4. **Multi-Tenant Errors** - Vazamento entre tenants

---

## 🔗 INTEGRAÇÃO COM GITHUB

### **Auto-Create Pull Request (Critical Errors)**
```yaml
# .github/workflows/auto-fix-pr.yml
name: Auto-Fix PR Review

on:
  pull_request:
    branches: [ auto-fix/* ]

jobs:
  review:
    runs-on: ubuntu-latest
    steps:
      - name: Check Auto-Fix
        run: |
          # Valida que o fix está correto
          # Roda testes
          # Se passar, auto-merge
          # Se falhar, solicita review
```

---

## 📝 LOGS E AUDITORIA

### **Todos os auto-fixes são registrados:**
```
storage/logs/auto-fixes/
├── 2026-03-02-critical-route-error.log
├── 2026-03-02-class-not-found.log
└── ...
```

### **Formato do log:**
```json
{
  "timestamp": "2026-03-02T14:30:00Z",
  "error": {
    "exception": "ArgumentCountError",
    "file": "routes/web.php",
    "line": 31
  },
  "claude_analysis": {
    "cause": "...",
    "solution": "..."
  },
  "action_taken": "auto_fix_applied",
  "commit": "0d3cd3f",
  "verified": true
}
```

---

## 💰 CUSTOS ESTIMADOS

### **Claude API:**
- Modelo: Sonnet 4.5
- ~1000 tokens por análise
- Custo: ~$0.003 por erro
- 100 erros/mês: ~$0.30/mês

### **n8n:**
- Self-hosted: Grátis
- Cloud: $20/mês (plano básico)

### **Flare:**
- €19/mês (já contratado)

**Total adicional:** ~$20.30/mês

---

## 🚀 PRÓXIMOS PASSOS

1. ✅ Importar workflow no n8n
2. ✅ Configurar webhook no Flare
3. ✅ Testar com erro proposital
4. ⏳ Ajustar regras de auto-fix
5. ⏳ Configurar Slack/Email
6. ⏳ Monitorar por 1 semana
7. ⏳ Aumentar autonomia gradualmente

---

## ⚙️ CONFIGURAÇÃO DO FLARE

### **Adicionar Context Automático:**
```php
// app/Providers/AppServiceProvider.php

use Spatie\FlareClient\Flare;

public function boot()
{
    if (class_exists(Flare::class)) {
        Flare::context('environment', config('app.env'));

        if (tenancy()->initialized) {
            $tenant = tenant();
            Flare::context('tenant_id', $tenant->id);
            Flare::context('tenant_name', $tenant->name);
            Flare::context('tenant_slug', $tenant->slug);
        }
    }
}
```

---

**🤖 Sistema totalmente automatizado de detecção e correção de erros!**

**Status:** ✅ Pronto para uso
**Arquivo:** `n8n-workflows/auto-fix-errors.json`
