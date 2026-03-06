const WebSocket = require('ws');

const url = 'wss://ws.yumgo.com.br/app/t9pg2dslmpl5y1cp6rrf';

console.log('🔵 Testando WebSocket do servidor...');
console.log(`URL: ${url}\n`);

const ws = new WebSocket(url, {
    headers: {
        'Origin': 'https://yumgo.com.br'
    },
    rejectUnauthorized: false  // Ignora SSL por enquanto
});

ws.on('open', () => {
    console.log('✅ CONECTADO AO REVERB!');
    setTimeout(() => ws.close(), 1000);
});

ws.on('message', (data) => {
    console.log('📩:', data.toString());
});

ws.on('error', (err) => {
    console.log('❌ ERRO:', err.message);
    console.log('Stack:', err.stack);
});

ws.on('close', () => {
    console.log('🔴 Conexão fechada');
});

setTimeout(() => {
    console.log('⏱️  Timeout - sem resposta');
    process.exit(1);
}, 5000);
