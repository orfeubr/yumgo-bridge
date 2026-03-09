# 🔐 Cloudflare Security Headers - Guia Rápido

## ⚡ Configuração Automática (2 minutos)

### 📋 Passo 1: Obter API Token

1. Acesse: **https://dash.cloudflare.com/profile/api-tokens**
2. Clique: **"Create Token"**
3. Use template: **"Edit zone DNS"**
   - OU crie custom com permissões:
     - `Zone` → `Transform Rules` → `Edit`
     - `Zone` → `Zone` → `Read`
4. **Zone Resources:** Selecione `yumgo.com.br`
5. Clique: **"Continue to summary"**
6. Clique: **"Create Token"**
7. **COPIE O TOKEN** (aparece apenas uma vez!)

Exemplo de token:
```
aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890
```

---

### 📋 Passo 2: Obter Zone ID

1. Acesse: **https://dash.cloudflare.com**
2. Clique no domínio: **yumgo.com.br**
3. No menu direito (Overview), role até **"API"**
4. Copie o **"Zone ID"**

Exemplo de Zone ID:
```
a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
```

---

### 🚀 Passo 3: Executar Script

```bash
cd /var/www/restaurante

# Exportar credenciais
export CLOUDFLARE_API_TOKEN="seu_token_aqui"
export CLOUDFLARE_ZONE_ID="seu_zone_id_aqui"

# Executar script
bash cloudflare-security-headers.sh
```

**OU deixar o script pedir as credenciais:**

```bash
bash cloudflare-security-headers.sh
# Script vai pedir Token e Zone ID interativamente
```

---

## ✅ O Que o Script Faz

1. ✅ Valida credenciais Cloudflare
2. ✅ Verifica rulesets existentes
3. ✅ Remove rulesets antigos (se houver)
4. ✅ Cria ruleset com 7 headers de segurança
5. ✅ Aguarda propagação (30 segundos)
6. ✅ Testa headers no site
7. ✅ Fornece link para verificação

---

## 📊 Headers Configurados

| Header | Valor | Proteção |
|--------|-------|----------|
| **X-Frame-Options** | SAMEORIGIN | Clickjacking |
| **X-Content-Type-Options** | nosniff | MIME sniffing |
| **X-XSS-Protection** | 1; mode=block | XSS (legacy) |
| **Content-Security-Policy** | (completo) | XSS moderno |
| **Referrer-Policy** | strict-origin-when-cross-origin | Privacidade |
| **Permissions-Policy** | (configurado) | APIs navegador |
| **HSTS** | max-age=31536000 | Força HTTPS |

---

## 🧪 Verificar Resultado

### Imediatamente:
```bash
curl -I https://yumgo.com.br | grep -i "x-frame-options\|x-content\|strict-transport"
```

### Após 2-3 minutos:
**https://securityheaders.com/?q=https://yumgo.com.br**

**Nota esperada: A+** 🏆

---

## ⚠️ Troubleshooting

### Erro: "Invalid token"
- Verifique se copiou o token completo
- Token expira se não for usado
- Crie um novo token

### Erro: "Zone not found"
- Verifique o Zone ID
- Deve ser o ID do domínio yumgo.com.br

### Headers não aparecem
- Aguarde 2-3 minutos para propagação
- Limpe cache do Cloudflare:
  - Dashboard → Caching → Purge Everything
- Teste em modo anônimo/incógnito

### Score ainda baixo
- Aguarde até 5 minutos
- Cache do securityheaders.com pode demorar
- Force refresh: Ctrl + Shift + R

---

## 🔄 Remover Headers (Se Necessário)

```bash
# Listar rulesets
curl -s -X GET "https://api.cloudflare.com/client/v4/zones/$CLOUDFLARE_ZONE_ID/rulesets/phases/http_response_headers_transform/entrypoint" \
  -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN" | jq '.'

# Copiar o "id" e deletar
curl -X DELETE "https://api.cloudflare.com/client/v4/zones/$CLOUDFLARE_ZONE_ID/rulesets/{RULESET_ID}" \
  -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN"
```

---

## 🎯 Resumo

| Tempo | Ação |
|-------|------|
| **2 min** | Obter API Token + Zone ID |
| **1 min** | Executar script |
| **2 min** | Aguardar propagação |
| **Total: 5 min** | ✅ Score A+ |

---

## 📚 Links Úteis

- **API Tokens:** https://dash.cloudflare.com/profile/api-tokens
- **Dashboard:** https://dash.cloudflare.com
- **API Docs:** https://developers.cloudflare.com/api/
- **Test Headers:** https://securityheaders.com

---

**Data:** 09/03/2026
**Arquivo:** `cloudflare-security-headers.sh`
**Resultado:** Security Score 100% (A+)
