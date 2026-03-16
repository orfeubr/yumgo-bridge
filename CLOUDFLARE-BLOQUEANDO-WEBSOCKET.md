# 🚨 CLOUDFLARE BLOQUEANDO WEBSOCKET - DIAGNÓSTICO COMPLETO

**Data:** 16/03/2026
**Problema:** Cloudflare está removendo URLs `wss://` do Content Security Policy

---

## 🔍 DIAGNÓSTICO

### ✅ Laravel está CORRETO
```bash
curl -I https://yumgo.com.br/test-csp
```

**Headers enviados pelo Laravel:**
```
x-csp-has-websocket: YES      ← ✅ Laravel ESTÁ enviando WebSocket URLs
x-csp-length: 496             ← ✅ Tamanho completo (com wss://)
```

**Log do Laravel:**
```json
{
  "url": "https://yumgo.com.br/test-csp",
  "csp_length": 496,
  "has_wss": true  ← ✅ Confirmado no backend
}
```

### ❌ Navegador recebe INCOMPLETO
**CSP que chega no navegador:**
```
connect-src 'self' https://api.pagar.me https://cloudflareinsights.com
```

**❌ FALTANDO:**
- `wss://ws.yumgo.com.br`
- `wss://yumgo.com.br`
- `ws://localhost:8081`

---

## 🎯 CAUSA RAIZ

**Cloudflare está modificando/removendo as URLs WebSocket do header CSP antes de entregar ao navegador.**

**Possíveis causas:**
1. ⚙️ **Auto Minify** - Removendo URLs durante minificação HTML
2. 📋 **Transform Rules** - Regras modificando headers
3. 📄 **Page Rules** - Rules afetando headers
4. 🔧 **Workers** - Script modificando response
5. 🛡️ **Security Level** - Proteção bloqueando WebSocket

---

## 🔧 SOLUÇÕES (EM ORDEM DE PREFERÊNCIA)

### ✅ SOLUÇÃO 1: DNS-Only para Subdomínios (RECOMENDADO)

**Por quê?**
- Mais simples e rápido
- Não afeta domínio principal (yumgo.com.br)
- Apenas subdomínios tenant ficam DNS-only

**Como fazer:**
1. Acessar [Cloudflare Dashboard](https://dash.cloudflare.com)
2. Selecionar domínio `yumgo.com.br`
3. Ir em **DNS** → **Records**
4. Encontrar registro wildcard: `*.yumgo.com.br` (tipo A ou CNAME)
5. Clicar no ícone da **nuvem laranja** 🟠
6. Mudar para **cinza** ☁️ (DNS-only)
7. Salvar

**Resultado:**
- ✅ WebSocket funciona imediatamente
- ✅ Domínio principal continua com proteção Cloudflare
- ✅ CDN desabilitado para tenants (mas não é problema - poucos assets)
- ❌ Perde proteção DDoS nos subdomínios

---

### ✅ SOLUÇÃO 2: Desabilitar Auto Minify

**Como fazer:**
1. Cloudflare Dashboard → **Speed** → **Optimization**
2. Procurar seção **Auto Minify**
3. **Desabilitar** todas as opções:
   - [ ] JavaScript
   - [ ] CSS
   - [ ] HTML ← **ESTE é o provável culpado!**
4. Salvar

**Resultado:**
- ✅ WebSocket deve funcionar
- ❌ Perde compressão automática HTML (mas temos Gzip no Nginx)

---

### ✅ SOLUÇÃO 3: Bypass Cache para Subdomínios

**Como fazer:**
1. Cloudflare → **Rules** → **Page Rules**
2. Clicar **Create Page Rule**
3. URL pattern: `*.yumgo.com.br/*`
4. Configurações:
   - **Cache Level:** Bypass
   - **Disable Performance:** On
   - **Browser Cache TTL:** Respect Existing Headers
5. Save and Deploy

**Resultado:**
- ✅ WebSocket funciona
- ❌ Cache desabilitado (pode aumentar carga no servidor)

---

### ✅ SOLUÇÃO 4: Transform Rules (Avançado)

**Como fazer:**
1. Cloudflare → **Rules** → **Transform Rules**
2. **HTTP Response Header Modification**
3. Criar regra:
   ```
   Hostname matches regex: .*\.yumgo\.com\.br

   Then:
   Set static → Content-Security-Policy
   Value: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://assets.pagar.me https://unpkg.com https://static.cloudflareinsights.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://api.pagar.me https://cloudflareinsights.com wss://ws.yumgo.com.br wss://yumgo.com.br ws://localhost:8081
   ```
4. Save

**Resultado:**
- ✅ WebSocket funciona
- ✅ Mantém proteção Cloudflare
- ⚠️ Requer plano Pro ou superior

---

## 🧪 COMO TESTAR

### Teste 1: Verificar Headers

```bash
curl -I https://marmitariadagi.yumgo.com.br/teste-echo-final.html | grep content-security-policy
```

**Deve conter:**
```
wss://ws.yumgo.com.br wss://yumgo.com.br
```

### Teste 2: Console do Navegador

1. Abrir https://marmitariadagi.yumgo.com.br/teste-echo-final.html
2. Abrir DevTools (F12) → Console
3. Clicar em "CONECTAR"

**Antes (erro):**
```
Content-Security-Policy: As configurações da página bloquearam...
Estado: connecting → unavailable
```

**Depois (sucesso):**
```
Estado: connecting → connected
✅ Conectado ao servidor YumGo via Reverb/Pusher
```

### Teste 3: Electron Bridge

1. Abrir YumGo Bridge no Windows
2. Clicar "Conectar"

**Sucesso:**
```
✅ Conectado ao servidor YumGo
📊 Estado: connected
```

---

## 📊 RESUMO DO PROBLEMA

| Componente | Status | CSP Correto? |
|------------|--------|--------------|
| **Laravel Backend** | ✅ Enviando | ✅ SIM (496 chars, wss:// incluído) |
| **Nginx** | ✅ Passou | ✅ SIM (não modifica) |
| **Cloudflare** | ❌ BLOQUEANDO | ❌ REMOVE wss:// URLs |
| **Navegador** | ❌ Recebe incompleto | ❌ NÃO (sem wss://) |

---

## 🎯 RECOMENDAÇÃO FINAL

**Para produção:**
- ✅ SOLUÇÃO 1 (DNS-only) - Mais simples
- Se precisar proteção DDoS em tenants: SOLUÇÃO 3 (Page Rules)
- Se tiver plano Pro+: SOLUÇÃO 4 (Transform Rules)

**Para desenvolvimento:**
- SOLUÇÃO 2 (Desabilitar Auto Minify) - Rápido para testar

---

## 📞 SUPORTE

Se precisar de ajuda:
1. Screenshots do console do navegador (F12)
2. Configuração atual do Cloudflare (DNS, Page Rules, etc)
3. Teste com `curl -I` para confirmar headers

---

**Problema identificado em:** 16/03/2026 03:30 UTC
**Causa:** Auditoria de segurança 09/03/2026 não considerou WebSocket
**Status:** Aguardando configuração Cloudflare
