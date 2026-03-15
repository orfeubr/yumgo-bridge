#!/usr/bin/env node

/**
 * Cliente WebSocket de Teste para YumGo Bridge
 *
 * Simula exatamente o que o Bridge faz para testar se eventos chegam
 */

import Pusher from 'pusher-js';

console.log('🔵 Iniciando teste de WebSocket...\n');

// Configuração (mesma que o Bridge usa)
const config = {
    key: 't9pg2dslmpl5y1cp6rrf',
    wsHost: 'localhost',
    wsPort: 8081,
    cluster: 'mt1', // Reverb usa mt1 como cluster padrão
    forceTLS: false,
    enabledTransports: ['ws'],
    disableStats: true
};

console.log('📡 Configuração:');
console.log(`   - Key: ${config.key}`);
console.log(`   - Host: ${config.wsHost}`);
console.log(`   - Port: ${config.wsPort}`);
console.log(`   - TLS: ${config.forceTLS}`);
console.log('');

// Criar conexão Pusher
const pusher = new Pusher(config.key, config);

// Eventos de conexão
pusher.connection.bind('state_change', (states) => {
    console.log(`📊 [STATE] ${states.previous} → ${states.current}`);
});

pusher.connection.bind('connected', () => {
    console.log('✅ CONECTADO ao Reverb!');
    console.log(`   Socket ID: ${pusher.connection.socket_id}`);
    console.log('');
});

pusher.connection.bind('disconnected', () => {
    console.log('❌ DESCONECTADO do Reverb');
});

pusher.connection.bind('error', (error) => {
    console.error('❌ ERRO:', error);
});

// Inscrever no canal
const channelName = 'restaurant.marmitariadagi';
console.log(`🔵 Inscrevendo no canal: ${channelName}`);

const channel = pusher.subscribe(channelName);

channel.bind('pusher:subscription_succeeded', () => {
    console.log(`✅ ✅ ✅ INSCRITO NO CANAL: ${channelName}`);
    console.log('🎧 Aguardando eventos .order.created...\n');
    console.log('-------------------------------------------');
    console.log('📌 Agora dispare um pedido no Laravel!');
    console.log('📌 Pressione Ctrl+C para sair');
    console.log('-------------------------------------------\n');
});

channel.bind('pusher:subscription_error', (error) => {
    console.error(`❌ ERRO AO INSCREVER NO CANAL:`, error);
});

// Escutar evento .order.created
channel.bind('.order.created', (data) => {
    console.log('\n🔔 🔔 🔔 EVENTO RECEBIDO!');
    console.log('📦 Dados:', JSON.stringify(data, null, 2));
});

// Manter processo vivo
process.on('SIGINT', () => {
    console.log('\n\n👋 Desconectando...');
    pusher.disconnect();
    process.exit(0);
});
