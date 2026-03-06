#!/usr/bin/env node

// Script de teste para conexão Laravel Echo/Reverb
const Pusher = require('pusher-js');
const Echo = require('laravel-echo');

// Credenciais do usuário
const RESTAURANT_ID = 'a48efe45-872d-403e-a522-2cf445b1229b';
const TOKEN = '8|7Hf230oN8UyXEMFawW86UNsLX8wY13e8NdG31z5a66950d3d';

console.log('🧪 Teste de Conexão Laravel Echo/Reverb\n');
console.log(`Restaurant ID: ${RESTAURANT_ID}`);
console.log(`Token: ${TOKEN.substring(0, 20)}...\n`);

// Configurar Echo
const echo = new Echo({
    broadcaster: 'reverb',
    key: 'yumgo',
    wsHost: 'yumgo.com.br',
    wsPort: 8081,
    wssPort: 8081,
    forceTLS: false,
    encrypted: false,
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
echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ CONECTADO ao servidor Reverb!');
    console.log(`   Socket ID: ${echo.socketId()}`);
    console.log('   Status: SUCESSO\n');

    // Tentar inscrever no canal privado
    const channelName = `private-restaurant.${RESTAURANT_ID}`;
    console.log(`📡 Inscrevendo no canal: ${channelName}`);

    const channel = echo.private(channelName);

    channel.subscribed(() => {
        console.log('✅ INSCRITO no canal privado com sucesso!');
        console.log('   Aguardando eventos...\n');
    });

    channel.error((error) => {
        console.error('❌ ERRO ao inscrever no canal:', error);
    });

    // Escutar evento de teste
    channel.listen('.order.created', (data) => {
        console.log('🔔 EVENTO RECEBIDO: .order.created');
        console.log('   Dados:', JSON.stringify(data, null, 2));
    });
});

echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('❌ DESCONECTADO do servidor');
});

echo.connector.pusher.connection.bind('error', (error) => {
    console.error('❌ ERRO DE CONEXÃO:', error);
});

echo.connector.pusher.connection.bind('state_change', (states) => {
    console.log(`🔄 Estado: ${states.previous} → ${states.current}`);
});

// Manter script rodando
console.log('⏳ Iniciando conexão...\n');
setTimeout(() => {
    if (!echo.connector.pusher.connection.state === 'connected') {
        console.log('\n⏱️ Timeout - Conexão não estabelecida após 30s');
        process.exit(1);
    }
}, 30000);

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n\n👋 Encerrando...');
    echo.disconnect();
    process.exit(0);
});
