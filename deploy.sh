#!/bin/bash

set -e  # Para na primeira falha

echo "🚀 DEPLOY YUMGO - Iniciando..."
echo "=========================================="
echo ""

# Cores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 1. Git Pull
echo "📥 1/8 - Puxando últimas mudanças do Git..."
git pull origin master
echo -e "${GREEN}✓ Git pull concluído${NC}"
echo ""

# 2. Composer Install
echo "📦 2/8 - Instalando dependências PHP..."
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✓ Composer instalado${NC}"
echo ""

# 3. NPM Install (se necessário)
if [ -f "package.json" ]; then
    echo "📦 3/8 - Instalando dependências Node..."
    npm install --production
    echo -e "${GREEN}✓ NPM instalado${NC}"
else
    echo -e "${YELLOW}⊘ 3/8 - Sem package.json, pulando NPM${NC}"
fi
echo ""

# 4. Migrations
echo "🗄️ 4/8 - Rodando migrations..."
php artisan migrate --force
echo -e "${GREEN}✓ Migrations executadas${NC}"
echo ""

# 5. Limpar caches
echo "🧹 5/8 - Limpando caches..."
php artisan optimize:clear
echo -e "${GREEN}✓ Caches limpos${NC}"
echo ""

# 6. Otimizar para produção
echo "⚡ 6/8 - Otimizando para produção..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo -e "${GREEN}✓ Otimizações aplicadas${NC}"
echo ""

# 7. Permissões
echo "🔐 7/8 - Ajustando permissões..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}✓ Permissões ajustadas${NC}"
echo ""

# 8. Restart services (opcional)
echo "🔄 8/8 - Reiniciando serviços..."
if command -v supervisorctl &> /dev/null; then
    sudo supervisorctl restart all
    echo -e "${GREEN}✓ Supervisor reiniciado${NC}"
else
    echo -e "${YELLOW}⊘ Supervisor não encontrado${NC}"
fi

# PHP-FPM
if systemctl is-active --quiet php8.3-fpm; then
    sudo systemctl reload php8.3-fpm
    echo -e "${GREEN}✓ PHP-FPM recarregado${NC}"
elif systemctl is-active --quiet php8.2-fpm; then
    sudo systemctl reload php8.2-fpm
    echo -e "${GREEN}✓ PHP-FPM recarregado${NC}"
else
    echo -e "${YELLOW}⊘ PHP-FPM não encontrado${NC}"
fi

# Nginx
if systemctl is-active --quiet nginx; then
    sudo systemctl reload nginx
    echo -e "${GREEN}✓ Nginx recarregado${NC}"
else
    echo -e "${YELLOW}⊘ Nginx não encontrado${NC}"
fi
echo ""

echo "=========================================="
echo -e "${GREEN}✅ DEPLOY CONCLUÍDO COM SUCESSO!${NC}"
echo ""
echo "📊 Resumo:"
echo "  - Git pull: ✓"
echo "  - Composer: ✓"
echo "  - Migrations: ✓"
echo "  - Caches: ✓"
echo "  - Otimizações: ✓"
echo "  - Permissões: ✓"
echo "  - Serviços: ✓"
echo ""
echo "🌐 Acesse: https://yumgo.com.br"
echo ""
