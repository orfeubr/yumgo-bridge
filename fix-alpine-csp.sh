#!/bin/bash

# ========== FIX: Adicionar unsafe-eval para Alpine.js ==========
# Atualiza CSP no Cloudflare para permitir Alpine.js funcionar
# Data: 09/03/2026
# ================================================================

echo "🔧 CORRIGINDO CSP PARA ALPINE.JS"
echo "================================="
echo ""

# Credenciais
CLOUDFLARE_API_TOKEN="${CLOUDFLARE_API_TOKEN:-sk_live_...seu_token...}"
CLOUDFLARE_ZONE_ID="28d9b024c97896f65910c9c205d77a66"
API_BASE="https://api.cloudflare.com/client/v4"

# Verificar se token foi fornecido
if [[ "$CLOUDFLARE_API_TOKEN" == "sk_live_...seu_token..." ]]; then
    echo "❌ ERRO: Configure o token antes de executar!"
    echo ""
    echo "export CLOUDFLARE_API_TOKEN='seu_token_aqui'"
    echo "bash fix-alpine-csp.sh"
    exit 1
fi

echo "✅ Token configurado"
echo ""

# Buscar ruleset existente
echo "🔍 Buscando ruleset existente..."
RULESETS_RESPONSE=$(curl -s -X GET "$API_BASE/zones/$CLOUDFLARE_ZONE_ID/rulesets/phases/http_response_headers_transform/entrypoint" \
  -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN" \
  -H "Content-Type: application/json")

RULESET_ID=$(echo "$RULESETS_RESPONSE" | grep -o '"id":"[^"]*"' | head -1 | cut -d'"' -f4)

if [ -z "$RULESET_ID" ]; then
    echo "❌ Ruleset não encontrado!"
    exit 1
fi

echo "✅ Ruleset encontrado: $RULESET_ID"
echo ""

# Deletar ruleset antigo
echo "🗑️  Removendo ruleset antigo..."
curl -s -X DELETE "$API_BASE/zones/$CLOUDFLARE_ZONE_ID/rulesets/$RULESET_ID" \
  -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN" > /dev/null

echo "✅ Removido"
echo ""

# Criar novo ruleset com unsafe-eval
echo "🔧 Criando ruleset atualizado (com unsafe-eval)..."

RULESET_JSON='{
  "rules": [
    {
      "action": "rewrite",
      "action_parameters": {
        "headers": {
          "X-Frame-Options": {
            "operation": "set",
            "value": "SAMEORIGIN"
          },
          "X-Content-Type-Options": {
            "operation": "set",
            "value": "nosniff"
          },
          "X-XSS-Protection": {
            "operation": "set",
            "value": "1; mode=block"
          },
          "Referrer-Policy": {
            "operation": "set",
            "value": "strict-origin-when-cross-origin"
          },
          "Permissions-Policy": {
            "operation": "set",
            "value": "geolocation=(self), microphone=(), camera=(), payment=(self)"
          },
          "Strict-Transport-Security": {
            "operation": "set",
            "value": "max-age=31536000; includeSubDomains; preload"
          },
          "Content-Security-Policy": {
            "operation": "set",
            "value": "default-src '\''self'\''; script-src '\''self'\'' '\''unsafe-inline'\'' '\''unsafe-eval'\'' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://assets.pagar.me https://unpkg.com; style-src '\''self'\'' '\''unsafe-inline'\'' https://fonts.googleapis.com https://cdn.tailwindcss.com; font-src '\''self'\'' https://fonts.gstatic.com; img-src '\''self'\'' data: https:; connect-src '\''self'\'' https://api.pagar.me"
          }
        }
      },
      "expression": "(http.host eq \"yumgo.com.br\" or http.host contains \".yumgo.com.br\")",
      "description": "Security Headers - Com unsafe-eval para Alpine.js",
      "enabled": true
    }
  ]
}'

CREATE_RESPONSE=$(curl -s -X PUT "$API_BASE/zones/$CLOUDFLARE_ZONE_ID/rulesets/phases/http_response_headers_transform/entrypoint" \
  -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d "$RULESET_JSON")

if echo "$CREATE_RESPONSE" | grep -q '"success":true'; then
    echo "✅ CSP atualizado com sucesso!"
    echo ""
    echo "📝 Mudança aplicada:"
    echo "   script-src ... 'unsafe-inline' 'unsafe-eval' ..."
    echo "                                  ^^^^^^^^^^^^^^"
    echo "                                  ADICIONADO!"
    echo ""
    echo "⏱️  Aguardando propagação (30 segundos)..."
    sleep 30
    echo ""
    echo "✅ PRONTO!"
    echo ""
    echo "🧪 Teste agora: https://marmitariadagi.yumgo.com.br"
    echo ""
else
    echo "❌ Erro ao atualizar CSP!"
    echo "$CREATE_RESPONSE"
    exit 1
fi
