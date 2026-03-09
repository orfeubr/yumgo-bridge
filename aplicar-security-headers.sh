#!/bin/bash

# ========== SCRIPT DE APLICAÇÃO DE HEADERS DE SEGURANÇA ==========
# Data: 09/03/2026
# Descrição: Aplica headers de segurança no Nginx
# ================================================================

echo "🔐 APLICANDO HEADERS DE SEGURANÇA NO NGINX"
echo "=========================================="
echo ""

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then
    echo "⚠️  Este script precisa ser executado como root (sudo)"
    echo "Execute: sudo bash aplicar-security-headers.sh"
    exit 1
fi

# Verificar se Nginx está instalado
if ! command -v nginx &> /dev/null; then
    echo "❌ Nginx não está instalado!"
    exit 1
fi

echo "✅ Nginx encontrado: $(nginx -v 2>&1)"
echo ""

# Encontrar arquivo de configuração
CONFIG_FILE="/etc/nginx/sites-available/yumgo.com.br"

if [ ! -f "$CONFIG_FILE" ]; then
    echo "⚠️  Arquivo $CONFIG_FILE não encontrado!"
    echo ""
    echo "Arquivos disponíveis em /etc/nginx/sites-available/:"
    ls -1 /etc/nginx/sites-available/
    echo ""
    read -p "Digite o nome do arquivo de configuração correto: " CONFIG_FILE
    CONFIG_FILE="/etc/nginx/sites-available/$CONFIG_FILE"

    if [ ! -f "$CONFIG_FILE" ]; then
        echo "❌ Arquivo $CONFIG_FILE não existe!"
        exit 1
    fi
fi

echo "✅ Configuração encontrada: $CONFIG_FILE"
echo ""

# Fazer backup
BACKUP_FILE="${CONFIG_FILE}.backup-$(date +%Y%m%d-%H%M%S)"
echo "📦 Criando backup: $BACKUP_FILE"
cp "$CONFIG_FILE" "$BACKUP_FILE"
echo "✅ Backup criado!"
echo ""

# Verificar se headers já existem
if grep -q "X-Frame-Options" "$CONFIG_FILE"; then
    echo "⚠️  Headers de segurança já parecem estar configurados!"
    echo ""
    read -p "Deseja sobrescrever? (s/N): " CONFIRM
    if [ "$CONFIRM" != "s" ] && [ "$CONFIRM" != "S" ]; then
        echo "❌ Operação cancelada."
        exit 0
    fi
fi

# Encontrar linha do server_name
SERVER_NAME_LINE=$(grep -n "server_name" "$CONFIG_FILE" | head -1 | cut -d: -f1)

if [ -z "$SERVER_NAME_LINE" ]; then
    echo "❌ Não foi possível encontrar 'server_name' no arquivo!"
    echo "Por favor, adicione os headers manualmente."
    exit 1
fi

echo "📝 Encontrado server_name na linha $SERVER_NAME_LINE"
echo ""

# Calcular linha de inserção (logo após server_name)
INSERT_LINE=$((SERVER_NAME_LINE + 1))

# Criar arquivo temporário com headers
TMP_FILE=$(mktemp)
cat > "$TMP_FILE" << 'EOF'

    # ========== HEADERS DE SEGURANÇA ==========
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://assets.pagar.me https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://api.pagar.me https://api.pagar.me/core/v5; frame-ancestors 'self'; base-uri 'self'; form-action 'self';" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(self), microphone=(), camera=(), payment=(self), usb=()" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    server_tokens off;
    # ========== FIM HEADERS ==========

EOF

# Inserir headers no arquivo
echo "🔧 Inserindo headers de segurança..."
head -n $SERVER_NAME_LINE "$CONFIG_FILE" > "${CONFIG_FILE}.new"
cat "$TMP_FILE" >> "${CONFIG_FILE}.new"
tail -n +$((INSERT_LINE)) "$CONFIG_FILE" >> "${CONFIG_FILE}.new"

# Substituir arquivo original
mv "${CONFIG_FILE}.new" "$CONFIG_FILE"
rm "$TMP_FILE"

echo "✅ Headers inseridos com sucesso!"
echo ""

# Testar configuração
echo "🧪 Testando configuração do Nginx..."
if nginx -t; then
    echo ""
    echo "✅ Configuração válida!"
    echo ""

    read -p "Recarregar Nginx agora? (S/n): " RELOAD
    if [ "$RELOAD" != "n" ] && [ "$RELOAD" != "N" ]; then
        echo "🔄 Recarregando Nginx..."
        systemctl reload nginx

        if [ $? -eq 0 ]; then
            echo "✅ Nginx recarregado com sucesso!"
            echo ""
            echo "=========================================="
            echo "🎉 HEADERS DE SEGURANÇA APLICADOS!"
            echo "=========================================="
            echo ""
            echo "📊 Próximos passos:"
            echo "1. Testar site: https://yumgo.com.br"
            echo "2. Verificar score: https://securityheaders.com/?q=https://yumgo.com.br"
            echo "3. Nota esperada: A+ 🏆"
            echo ""
            echo "📦 Backup salvo em: $BACKUP_FILE"
            echo ""
        else
            echo "❌ Erro ao recarregar Nginx!"
            echo "Restaurando backup..."
            cp "$BACKUP_FILE" "$CONFIG_FILE"
            exit 1
        fi
    fi
else
    echo ""
    echo "❌ Configuração inválida!"
    echo "Restaurando backup..."
    cp "$BACKUP_FILE" "$CONFIG_FILE"
    echo "✅ Backup restaurado."
    echo ""
    echo "Por favor, corrija os erros e tente novamente."
    exit 1
fi
