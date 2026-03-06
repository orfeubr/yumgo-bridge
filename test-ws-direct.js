#!/usr/bin/env node
// Teste de conexão WebSocket DIRETA
const WebSocket = require('ws');

const url = 'wss://ws.yumgo.com.br/app/t9pg2dslmpl5y1cp6rrf';

console.log('🔵 Testando conexão WebSocket direta...');
console.log(`URL: ${url}\n`);

const ws = new WebSocket(url, {
    headers: {
        'Origin': 'https://yumgo.com.br'
    }
});

ws.on('open', function open() {
    console.log('✅ CONECTADO!');
    console.log('Enviando subscribe...');

    ws.send(JSON.stringify({
        event: 'pusher:subscribe',
        data: {
            channel: 'presence-test'
        }
    }));
});

ws.on('message', function message(data) {
    console.log('📩 Mensagem recebida:', data.toString());
});

ws.on('error', function error(err) {
    console.log('❌ ERRO:', err.message);
    process.exit(1);
});

ws.on('close', function close() {
    console.log('🔴 Conexão fechada');
    process.exit(0);
});

setTimeout(() => {
    console.log('⏱️  Timeout');
    process.exit(1);
}, 10000);
