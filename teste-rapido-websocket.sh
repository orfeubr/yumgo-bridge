#!/bin/bash

echo "🚀 TESTE RÁPIDO DE WEBSOCKET"
echo "============================"
echo ""

# 1. Verificar Reverb
echo "1️⃣  Verificando Reverb..."
REVERB_PID=$(ps aux | grep "[r]everb:start" | awk '{print $2}' | head -1)
if [ -n "$REVERB_PID" ]; then
    echo "   ✅ Reverb rodando (PID: $REVERB_PID)"
else
    echo "   ❌ Reverb NÃO está rodando!"
    exit 1
fi
echo ""

# 2. Verificar Queue
echo "2️⃣  Verificando Queue Workers..."
QUEUE_COUNT=$(ps aux | grep "[q]ueue:work" | wc -l)
if [ $QUEUE_COUNT -gt 0 ]; then
    echo "   ✅ $QUEUE_COUNT queue workers rodando"
else
    echo "   ❌ Nenhum queue worker!"
fi
echo ""

# 3. Verificar Broadcasting
echo "3️⃣  Verificando Broadcasting config..."
BROADCAST=$(grep "^BROADCAST_CONNECTION" .env | cut -d'=' -f2)
echo "   BROADCAST_CONNECTION=$BROADCAST"
if [ "$BROADCAST" == "reverb" ]; then
    echo "   ✅ Configurado para Reverb"
else
    echo "   ⚠️  NÃO está usando Reverb!"
fi
echo ""

# 4. Disparar evento de teste
echo "4️⃣  Disparando evento de teste..."
php artisan test:print 10
echo ""

# 5. Verificar fila
echo "5️⃣  Verificando fila..."
QUEUE_SIZE=$(redis-cli -n 0 LLEN queues:default 2>/dev/null || echo "N/A")
echo "   Jobs na fila: $QUEUE_SIZE"
echo ""

echo "✅ TESTE CONCLUÍDO!"
echo ""
echo "🔔 Agora verifique se o bridge recebeu o pedido."
echo "   Se NÃO recebeu, veja: DEBUG-WEBSOCKET-BRIDGE.md"
