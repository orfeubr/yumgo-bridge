#!/bin/bash

# 🤖 Script de Inicialização do N8N
# YumGo - Sistema de Auto-Fix de Erros

set -e

echo "🚀 Iniciando N8N para YumGo..."
echo ""

# Cores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar se Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker não está rodando!${NC}"
    echo "Inicie o Docker e tente novamente."
    exit 1
fi

echo -e "${GREEN}✅ Docker está rodando${NC}"

# Parar containers atuais
echo ""
echo "🛑 Parando containers existentes..."
docker-compose down

# Subir containers com n8n
echo ""
echo "🔨 Construindo e iniciando containers..."
docker-compose up -d --build

# Aguardar n8n iniciar
echo ""
echo "⏳ Aguardando n8n inicializar (30 segundos)..."
sleep 30

# Verificar se n8n está rodando
if docker-compose ps | grep -q "yumgo-n8n.*Up"; then
    echo -e "${GREEN}✅ N8N está rodando!${NC}"
else
    echo -e "${RED}❌ N8N não iniciou corretamente${NC}"
    echo ""
    echo "Logs do n8n:"
    docker-compose logs --tail=50 n8n
    exit 1
fi

# Exibir informações
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}🎉 N8N INSTALADO COM SUCESSO!${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo -e "${YELLOW}📍 Acesse o painel:${NC}"
echo "   http://localhost:5678"
echo ""
echo -e "${YELLOW}🔐 Credenciais padrão:${NC}"
echo "   User: admin"
echo "   Pass: yumgo_n8n_2026"
echo ""
echo -e "${RED}⚠️  ATENÇÃO: Mude a senha em produção!${NC}"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${YELLOW}📋 Próximos passos:${NC}"
echo ""
echo "1. Acesse http://localhost:5678"
echo "2. Importe o workflow:"
echo "   Menu ⋮ → Import from File"
echo "   Arquivo: n8n-workflows/auto-fix-errors.json"
echo ""
echo "3. Configure credenciais:"
echo "   • Anthropic API (Claude)"
echo "   • Slack (opcional)"
echo "   • Email SMTP (opcional)"
echo ""
echo "4. Ative o workflow (botão 'Active')"
echo ""
echo "5. Configure webhook no Flare:"
echo "   URL do webhook será mostrada no node 'Webhook'"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo -e "${GREEN}📚 Documentação completa:${NC}"
echo "   • N8N-SETUP.md (instalação)"
echo "   • WORKFLOW-N8N-AUTO-FIX.md (funcionamento)"
echo "   • WORKFLOW-SECURITY-UPGRADE.md (segurança)"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Oferecer abrir no navegador
if command -v xdg-open > /dev/null; then
    read -p "Abrir n8n no navegador agora? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        xdg-open http://localhost:5678
    fi
elif command -v open > /dev/null; then
    read -p "Abrir n8n no navegador agora? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        open http://localhost:5678
    fi
fi

echo ""
echo -e "${GREEN}🚀 Pronto! N8N está rodando.${NC}"
echo ""

# Mostrar logs em tempo real
read -p "Ver logs em tempo real? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    docker-compose logs -f n8n
fi
