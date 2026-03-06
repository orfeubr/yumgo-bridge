// Teste de conexão WebSocket com Reverb
const Pusher = require('pusher-js');

console.log('🔵 Testando conexão WebSocket...\n');

const pusher = new Pusher('t9pg2dslmpl5y1cp6rrf', {
    wsHost: 'ws.yumgo.com.br',
    wsPort: 443,
    wssPort: 443,
    forceTLS: true,
    encrypted: true,
    disableStats: true,
    enabledTransports: ['wss']
});

pusher.connection.bind('state_change', (states) => {
    console.log(`📊 Estado: ${states.previous} → ${states.current}`);
});

pusher.connection.bind('connected', () => {
    console.log('✅ CONECTADO com sucesso!');
    console.log('Socket ID:', pusher.connection.socket_id);
    process.exit(0);
});

pusher.connection.bind('disconnected', () => {
    console.log('❌ Desconectado');
});

pusher.connection.bind('error', (error) => {
    console.log('❌ ERRO:', JSON.stringify(error, null, 2));
    process.exit(1);
});

pusher.connection.bind('failed', () => {
    console.log('❌ FALHOU - Conexão impossível');
    process.exit(1);
});

// Timeout de 10 segundos
setTimeout(() => {
    console.log('⏱️  Timeout - Conexão demorou muito');
    process.exit(1);
}, 10000);

console.log('Aguardando conexão...\n');
