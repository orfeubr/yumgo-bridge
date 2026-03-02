#!/bin/bash

set -e

echo "🚀 Configurando bot.yumgo.com.br para N8N"
echo "================================================"
echo ""

# Cores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 1. Verificar se N8N está rodando
echo "📦 1/7 - Verificando se N8N está rodando..."
if docker compose ps | grep -q "yumgo-n8n.*Up"; then
    echo -e "${GREEN}✓ N8N está rodando${NC}"
else
    echo -e "${YELLOW}⚠ N8N não está rodando. Iniciando...${NC}"
    docker compose up -d n8n
    echo "⏳ Aguardando 15 segundos para N8N inicializar..."
    sleep 15
fi
echo ""

# 2. Testar se N8N responde
echo "🧪 2/7 - Testando se N8N responde..."
if curl -s http://localhost:5678/healthz | grep -q "ok"; then
    echo -e "${GREEN}✓ N8N está respondendo${NC}"
else
    echo -e "${RED}✗ N8N não está respondendo${NC}"
    echo "Execute: docker compose logs n8n"
    exit 1
fi
echo ""

# 3. Copiar configuração Nginx
echo "📝 3/7 - Copiando configuração Nginx..."
sudo cp /var/www/restaurante/deployment/nginx/bot.yumgo.com.br.conf /etc/nginx/sites-available/
echo -e "${GREEN}✓ Configuração copiada${NC}"
echo ""

# 4. Ativar site
echo "🔗 4/7 - Ativando site..."
sudo ln -sf /etc/nginx/sites-available/bot.yumgo.com.br.conf /etc/nginx/sites-enabled/
echo -e "${GREEN}✓ Site ativado${NC}"
echo ""

# 5. Testar configuração Nginx
echo "🧪 5/7 - Testando configuração Nginx..."
if sudo nginx -t; then
    echo -e "${GREEN}✓ Configuração válida${NC}"
else
    echo -e "${RED}✗ Erro na configuração${NC}"
    exit 1
fi
echo ""

# 6. Recarregar Nginx
echo "🔄 6/7 - Recarregando Nginx..."
sudo systemctl reload nginx
echo -e "${GREEN}✓ Nginx recarregado${NC}"
echo ""

# 7. Instalar SSL (Certbot)
echo "🔐 7/7 - Instalando SSL com Certbot..."
if command -v certbot &> /dev/null; then
    echo "Certbot encontrado. Deseja instalar SSL agora? (s/n)"
    read -r response
    if [[ "$response" =~ ^[Ss]$ ]]; then
        sudo certbot --nginx -d bot.yumgo.com.br --non-interactive --agree-tos --email contato@yumgo.com.br || true
        echo -e "${GREEN}✓ SSL configurado${NC}"
    else
        echo -e "${YELLOW}⚠ SSL não instalado. Execute depois: sudo certbot --nginx -d bot.yumgo.com.br${NC}"
    fi
else
    echo -e "${YELLOW}⚠ Certbot não instalado${NC}"
    echo "Instale com: sudo apt install certbot python3-certbot-nginx"
fi
echo ""

echo "================================================"
echo -e "${GREEN}✅ CONFIGURAÇÃO COMPLETA!${NC}"
echo ""
echo "🔗 Acesse: http://bot.yumgo.com.br"
echo "🔐 Credenciais:"
echo "   User: admin"
echo "   Pass: yumgo_n8n_2026"
echo ""
echo "📋 Próximos passos:"
echo "   1. Acessar bot.yumgo.com.br"
echo "   2. Fazer login"
echo "   3. Mudar senha padrão"
echo "   4. Importar workflow: n8n-workflows/auto-fix-errors.json"
echo "   5. Configurar credenciais (Claude API, Slack, SMTP)"
echo "   6. Ativar workflow"
echo ""
echo "🆘 Se não funcionar:"
echo "   - Ver logs N8N: docker compose logs n8n"
echo "   - Ver logs Nginx: sudo tail -f /var/log/nginx/bot.yumgo.com.br.error.log"
echo "   - Testar localhost: curl http://localhost:5678/healthz"
echo ""
