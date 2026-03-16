import WebSocket from 'ws';

console.log('🔵 Testando WebSocket em wss://ws.yumgo.com.br/app/t9pg2dslmpl5y1cp6rrf...\n');

const ws = new WebSocket('wss://ws.yumgo.com.br/app/t9pg2dslmpl5y1cp6rrf?protocol=7&client=js&version=8.4.0');

ws.on('open', function() {
    console.log('✅ CONEXÃO WEBSOCKET ABERTA!');
    console.log('🎉 ws.yumgo.com.br ESTÁ FUNCIONANDO!\n');
    process.exit(0);
});

ws.on('error', function(error) {
    console.error('❌ ERRO:', error.message);
    process.exit(1);
});

ws.on('close', function() {
    console.log('❌ Conexão fechada');
    process.exit(1);
});

setTimeout(() => {
    console.log('⏱️ Timeout 10s');
    process.exit(1);
}, 10000);
