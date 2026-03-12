#!/bin/bash

# ========== SCRIPT DE CONFIGURAÇÃO AUTOMÁTICA - CLOUDFLARE ==========
# Configura headers de segurança via API do Cloudflare
# Data: 09/03/2026
# =====================================================================

echo "🔐 CONFIGURANDO HEADERS DE SEGURANÇA NO CLOUDFLARE"
echo "=================================================="
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ========== CONFIGURAÇÕES ==========

# Obter credenciais
if [ -z "$CLOUDFLARE_API_TOKEN" ]; then
    echo "📝 Você precisa fornecer suas credenciais do Cloudflare"
    echo ""
    echo "Como obter o API Token:"
    echo "1. Acesse: https://dash.cloudflare.com/profile/api-tokens"
    echo "2. Clique em 'Create Token'"
    echo "3. Use template 'Edit zone DNS' ou crie custom com:"
    echo "   - Zone → Transform Rules → Edit"
    echo "   - Zone → Zone → Read"
    echo "4. Copie o token gerado"
    echo ""
    read -p "Cole seu API Token aqui: " CLOUDFLARE_API_TOKEN

    if [ -z "$CLOUDFLARE_API_TOKEN" ]; then
        echo -e "${RED}❌ API Token é obrigatório!${NC}"
        exit 1
    fi
fi

if [ -z "$CLOUDFLARE_ZONE_ID" ]; then
    echo ""
    echo "Como obter o Zone ID:"
    echo "1. Acesse: https://dash.cloudflare.com"
    echo "2. Selecione o domínio: yumgo.com.br"
    echo "3. No menu lateral direito (Overview), role até 'API'"
    echo "4. Copie o 'Zone ID'"
    echo ""
    read -p "Cole seu Zone ID aqui: " CLOUDFLARE_ZONE_ID

    if [ -z "$CLOUDFLARE_ZONE_ID" ]; then
        echo -e "${RED}❌ Zone ID é obrigatório!${NC}"
        exit 1
    fi
fi

echo ""
echo -e "${GREEN}✅ Credenciais fornecidas!${NC}"
echo ""

# API Base URL
API_BASE="https://api.cloudflare.com/client/v4"

# ========== TESTAR CREDENCIAIS ==========

echo "🔍 Testando credenciais..."

VERIFY_RESPONSE=$(curl -s -X GET "$API_BASE/zones/$CLOUDFLARE_ZONE_ID" \
  -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN" \
  -H "Content-Type: application/json")

if echo "$VERIFY_RESPONSE" | grep -q '"success":true'; then
    ZONE_NAME=$(echo "$VERIFY_RESPONSE" | grep -o '"name":"[^"]*"' | head -1 | cut -d'"' -f4)
    echo -e "${GREEN}✅ Credenciais válidas!${NC}"
    echo "📍 Zona: $ZONE_NAME"
    echo ""
else
    echo -e "${RED}❌ Credenciais inválidas!${NC}"
    echo ""
    echo "Resposta da API:"
    echo "$VERIFY_RESPONSE" | grep -o '"message":"[^"]*"' | cut -d'"' -f4
    exit 1
fi

# ========== VERIFICAR RULESETS EXISTENTES ==========

echo "🔍 Verificando rulesets existentes..."

# Listar rulesets da fase http_response_headers_transform
RULESETS_RESPONSE=$(curl -s -X GET "$API_BASE/zones/$CLOUDFLARE_ZONE_ID/rulesets/phases/http_response_headers_transform/entrypoint" \
  -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN" \
  -H "Content-Type: application/json")

# Verificar se já existe ruleset
if echo "$RULESETS_RESPONSE" | grep -q '"id"'; then
    echo -e "${YELLOW}⚠️  Já existe um ruleset de headers!${NC}"
    RULESET_ID=$(echo "$RULESETS_RESPONSE" | grep -o '"id":"[^"]*"' | head -1 | cut -d'"' -f4)
    echo "Ruleset ID: $RULESET_ID"
    echo ""
    read -p "Deseja substituir? (s/N): " REPLACE

    if [ "$REPLACE" != "s" ] && [ "$REPLACE" != "S" ]; then
        echo "❌ Operação cancelada."
        exit 0
    fi

    # Deletar ruleset existente
    echo "🗑️  Deletando ruleset antigo..."
    DELETE_RESPONSE=$(curl -s -X DELETE "$API_BASE/zones/$CLOUDFLARE_ZONE_ID/rulesets/$RULESET_ID" \
      -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN")

    if echo "$DELETE_RESPONSE" | grep -q '"success":true'; then
        echo -e "${GREEN}✅ Ruleset antigo removido!${NC}"
    else
        echo -e "${RED}❌ Erro ao remover ruleset antigo${NC}"
        exit 1
    fi
fi

echo ""
echo "🔧 Criando regras de segurança..."
echo ""

# ========== CRIAR RULESET COM HEADERS DE SEGURANÇA ==========

# JSON do ruleset completo
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
            "value": "default-src '\''self'\''; script-src '\''self'\'' '\''unsafe-inline'\'' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://assets.pagar.me https://unpkg.com https://static.cloudflareinsights.com; style-src '\''self'\'' '\''unsafe-inline'\'' https://fonts.googleapis.com https://cdn.tailwindcss.com; font-src '\''self'\'' https://fonts.gstatic.com; img-src '\''self'\'' data: https:; connect-src '\''self'\'' https://api.pagar.me https://cloudflareinsights.com"
          }
        }
      },
      "expression": "(http.host eq \"yumgo.com.br\" or http.host contains \".yumgo.com.br\")",
      "description": "Security Headers - Proteção XSS, Clickjacking, MITM",
      "enabled": true
    }
  ]
}'

# Criar o ruleset
CREATE_RESPONSE=$(curl -s -X PUT "$API_BASE/zones/$CLOUDFLARE_ZONE_ID/rulesets/phases/http_response_headers_transform/entrypoint" \
  -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d "$RULESET_JSON")

# Verificar resultado
if echo "$CREATE_RESPONSE" | grep -q '"success":true'; then
    echo -e "${GREEN}✅ Headers de segurança configurados com sucesso!${NC}"
    echo ""

    # Extrair ID da regra criada
    NEW_RULESET_ID=$(echo "$CREATE_RESPONSE" | grep -o '"id":"[^"]*"' | head -1 | cut -d'"' -f4)
    echo "📋 Ruleset ID: $NEW_RULESET_ID"
    echo ""

    echo "✅ Headers configurados:"
    echo "  • X-Frame-Options: SAMEORIGIN"
    echo "  • X-Content-Type-Options: nosniff"
    echo "  • X-XSS-Protection: 1; mode=block"
    echo "  • Content-Security-Policy: (configurado)"
    echo "  • Referrer-Policy: strict-origin-when-cross-origin"
    echo "  • Permissions-Policy: (configurado)"
    echo "  • Strict-Transport-Security: max-age=31536000"
    echo ""

    echo "=================================================="
    echo -e "${GREEN}🎉 CONFIGURAÇÃO CONCLUÍDA!${NC}"
    echo "=================================================="
    echo ""
    echo "⏱️  Aguarde 30 segundos para propagação..."
    sleep 5
    echo "5..."
    sleep 5
    echo "10..."
    sleep 5
    echo "15..."
    sleep 5
    echo "20..."
    sleep 5
    echo "25..."
    sleep 5
    echo "30... ✅"
    echo ""

    echo "🧪 TESTANDO HEADERS..."
    echo ""

    # Testar headers
    TEST_HEADERS=$(curl -s -I https://yumgo.com.br)

    if echo "$TEST_HEADERS" | grep -q "x-frame-options"; then
        echo -e "${GREEN}✅ X-Frame-Options: Presente${NC}"
    else
        echo -e "${YELLOW}⚠️  X-Frame-Options: Não encontrado (pode demorar mais)${NC}"
    fi

    if echo "$TEST_HEADERS" | grep -q "x-content-type-options"; then
        echo -e "${GREEN}✅ X-Content-Type-Options: Presente${NC}"
    else
        echo -e "${YELLOW}⚠️  X-Content-Type-Options: Não encontrado${NC}"
    fi

    if echo "$TEST_HEADERS" | grep -q "strict-transport-security"; then
        echo -e "${GREEN}✅ Strict-Transport-Security: Presente${NC}"
    else
        echo -e "${YELLOW}⚠️  HSTS: Não encontrado${NC}"
    fi

    echo ""
    echo "📊 VERIFICAÇÃO FINAL:"
    echo "🔗 https://securityheaders.com/?q=https://yumgo.com.br"
    echo ""
    echo "⏱️  Nota: Pode levar até 2-3 minutos para a nota A+ aparecer"
    echo ""

else
    echo -e "${RED}❌ Erro ao configurar headers!${NC}"
    echo ""
    echo "Resposta da API:"
    echo "$CREATE_RESPONSE" | jq '.' 2>/dev/null || echo "$CREATE_RESPONSE"
    exit 1
fi
