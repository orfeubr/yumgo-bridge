#!/bin/bash

echo "🔍 Monitorando WebSocket em tempo real..."
echo "Canal: restaurant.marmitariadagi"
echo "Eventos: order.created"
echo ""
echo "Pressione Ctrl+C para parar"
echo "================================"
echo ""

# Monitorar logs do Laravel (Reverb)
tail -f storage/logs/laravel.log | grep --line-buffered -i "order\|broadcast\|reverb\|websocket" | while read line; do
    echo "[$(date +%H:%M:%S)] $line"
done
