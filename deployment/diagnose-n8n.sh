#!/bin/bash

echo "🔍 DIAGNÓSTICO N8N - bot.yumgo.com.br"
echo "=========================================="
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# 1. Docker instalado?
echo "1️⃣ Verificando Docker..."
if command -v docker &> /dev/null; then
    echo -e "${GREEN}✓ Docker instalado${NC}"
    docker --version
else
    echo -e "${RED}✗ Docker NÃO instalado${NC}"
fi
echo ""

# 2. Docker Compose instalado?
echo "2️⃣ Verificando Docker Compose..."
if docker compose version &> /dev/null; then
    echo -e "${GREEN}✓ Docker Compose instalado${NC}"
    docker compose version
else
    echo -e "${RED}✗ Docker Compose NÃO instalado${NC}"
fi
echo ""

# 3. Containers rodando?
echo "3️⃣ Verificando containers..."
if docker compose ps &> /dev/null; then
    docker compose ps
else
    echo -e "${RED}✗ Erro ao listar containers${NC}"
fi
echo ""

# 4. N8N rodando?
echo "4️⃣ Verificando N8N especificamente..."
if docker compose ps | grep -q "yumgo-n8n"; then
    if docker compose ps | grep "yumgo-n8n" | grep -q "Up"; then
        echo -e "${GREEN}✓ N8N está rodando${NC}"
    else
        echo -e "${RED}✗ N8N existe mas NÃO está UP${NC}"
    fi
else
    echo -e "${RED}✗ Container N8N não encontrado${NC}"
    echo "Execute: docker compose up -d n8n"
fi
echo ""

# 5. N8N responde em localhost?
echo "5️⃣ Testando N8N em localhost:5678..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost:5678/healthz | grep -q "200"; then
    echo -e "${GREEN}✓ N8N responde em localhost:5678${NC}"
    curl -s http://localhost:5678/healthz
else
    echo -e "${RED}✗ N8N NÃO responde em localhost:5678${NC}"
    echo "Verifique logs: docker compose logs n8n"
fi
echo ""

# 6. Nginx instalado?
echo "6️⃣ Verificando Nginx..."
if command -v nginx &> /dev/null; then
    echo -e "${GREEN}✓ Nginx instalado${NC}"
    nginx -v 2>&1
else
    echo -e "${RED}✗ Nginx NÃO instalado${NC}"
fi
echo ""

# 7. Nginx rodando?
echo "7️⃣ Verificando status Nginx..."
if systemctl is-active --quiet nginx; then
    echo -e "${GREEN}✓ Nginx está rodando${NC}"
else
    echo -e "${RED}✗ Nginx NÃO está rodando${NC}"
    echo "Execute: sudo systemctl start nginx"
fi
echo ""

# 8. Configuração bot.yumgo.com.br existe?
echo "8️⃣ Verificando configuração bot.yumgo.com.br..."
if [ -f "/etc/nginx/sites-available/bot.yumgo.com.br.conf" ]; then
    echo -e "${GREEN}✓ Arquivo existe em sites-available${NC}"
else
    echo -e "${RED}✗ Arquivo NÃO existe em sites-available${NC}"
    echo "Execute: sudo cp deployment/nginx/bot.yumgo.com.br.conf /etc/nginx/sites-available/"
fi

if [ -L "/etc/nginx/sites-enabled/bot.yumgo.com.br.conf" ]; then
    echo -e "${GREEN}✓ Link simbólico existe em sites-enabled${NC}"
else
    echo -e "${RED}✗ Link simbólico NÃO existe em sites-enabled${NC}"
    echo "Execute: sudo ln -s /etc/nginx/sites-available/bot.yumgo.com.br.conf /etc/nginx/sites-enabled/"
fi
echo ""

# 9. Configuração Nginx válida?
echo "9️⃣ Testando configuração Nginx..."
if sudo nginx -t &> /dev/null; then
    echo -e "${GREEN}✓ Configuração Nginx válida${NC}"
else
    echo -e "${RED}✗ Erro na configuração Nginx${NC}"
    sudo nginx -t
fi
echo ""

# 10. DNS resolvendo?
echo "🔟 Testando DNS bot.yumgo.com.br..."
if host bot.yumgo.com.br &> /dev/null; then
    echo -e "${GREEN}✓ DNS resolve${NC}"
    host bot.yumgo.com.br
else
    echo -e "${YELLOW}⚠ DNS não resolve ainda${NC}"
    echo "Pode estar propagando. Aguarde alguns minutos."
fi
echo ""

# 11. Portas abertas?
echo "1️⃣1️⃣ Verificando portas..."
echo "Porta 5678 (N8N):"
if ss -tlnp | grep -q ":5678"; then
    echo -e "${GREEN}✓ Porta 5678 aberta${NC}"
    ss -tlnp | grep ":5678"
else
    echo -e "${RED}✗ Porta 5678 NÃO está aberta${NC}"
fi

echo "Porta 80 (HTTP):"
if ss -tlnp | grep -q ":80"; then
    echo -e "${GREEN}✓ Porta 80 aberta${NC}"
else
    echo -e "${RED}✗ Porta 80 NÃO está aberta${NC}"
fi
echo ""

# 12. Logs recentes
echo "1️⃣2️⃣ Últimas linhas do log N8N:"
echo "-----------------------------------"
docker compose logs --tail=20 n8n 2>/dev/null || echo "Não foi possível acessar logs do N8N"
echo ""

echo "1️⃣3️⃣ Últimas linhas do log Nginx (erro):"
echo "-----------------------------------"
sudo tail -10 /var/log/nginx/error.log 2>/dev/null || echo "Não foi possível acessar logs do Nginx"
echo ""

# Resumo
echo "=========================================="
echo "📋 RESUMO DO DIAGNÓSTICO"
echo "=========================================="
echo ""
echo "Cole o resultado completo acima para análise!"
echo ""
echo "🔧 COMANDOS ÚTEIS:"
echo "- Iniciar N8N: docker compose up -d n8n"
echo "- Ver logs N8N: docker compose logs -f n8n"
echo "- Recarregar Nginx: sudo systemctl reload nginx"
echo "- Testar localhost: curl http://localhost:5678"
echo ""
