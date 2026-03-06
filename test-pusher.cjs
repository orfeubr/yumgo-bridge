#!/usr/bin/env node

// Teste simplificado com Pusher direto
const Pusher = require('pusher-js/node');

const RESTAURANT_ID = 'a48efe45-872d-403e-a522-2cf445b1229b';
const TOKEN = '9|G3rqtMDNitAkxtl6dkzK9LBMZfktNlUVG1JpC3Nh329741b4';

console.log('🧪 Teste de Conexão Pusher/Reverb\n');
console.log(`Restaurant ID: ${RESTAURANT_ID}`);
console.log(`Token: ${TOKEN.substring(0, 20)}...\n`);

// Configurar Pusher (via Nginx proxy)
const pusher = new Pusher('t9pg2dslmpl5y1cp6rrf', {  // REVERB_APP_KEY correto
    cluster: '',  // Vazio para Reverb
    wsHost: 'ws.yumgo.com.br',
    wsPort: 443,
    wssPort: 443,
    wsPath: '',  // Empty - Pusher adds /app/{key} automatically
    forceTLS: true,
    encrypted: true,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: 'https://yumgo.com.br/api/broadcasting/auth',
    auth: {
        headers: {
            'Authorization': `Bearer ${TOKEN}`,
            'Accept': 'application/json',
            'X-Restaurant-ID': RESTAURANT_ID
        }
    }
});

// Eventos de conexão
pusher.connection.bind('connected', () => {
    console.log('✅ CONECTADO ao Reverb!');
    console.log(`   Socket ID: ${pusher.connection.socket_id}`);

    // Tentar inscrever no canal privado
    console.log(`\n📡 Inscrevendo no canal privado: restaurant.${RESTAURANT_ID}`);

    const channel = pusher.subscribe(`private-restaurant.${RESTAURANT_ID}`);

    channel.bind('pusher:subscription_succeeded', () => {
        console.log('✅ INSCRITO no canal com sucesso!');
        console.log('   Aguardando eventos de pedido...\n');
    });

    channel.bind('pusher:subscription_error', (error) => {
        console.error('❌ ERRO na inscrição:', error);
    });

    channel.bind('order.created', (data) => {
        console.log('🔔 EVENTO RECEBIDO: order.created');
        console.log(JSON.stringify(data, null, 2));
    });
});

pusher.connection.bind('disconnected', () => {
    console.log('❌ Desconectado');
});

pusher.connection.bind('error', (error) => {
    console.error('❌ ERRO:', error);
});

pusher.connection.bind('state_change', (states) => {
    console.log(`🔄 Estado: ${states.previous} → ${states.current}`);
});

console.log('⏳ Conectando...\n');

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n\n👋 Encerrando...');
    pusher.disconnect();
    process.exit(0);
});

// Timeout
setTimeout(() => {
    if (pusher.connection.state !== 'connected') {
        console.log('\n⏱️ Timeout - Não conectou em 30s');
        process.exit(1);
    }
}, 30000);
