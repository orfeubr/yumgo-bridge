const { app, BrowserWindow, ipcMain, Menu, Tray, nativeImage, dialog } = require('electron');
const path = require('path');
const Store = require('electron-store');
const log = require('electron-log');
const { autoUpdater } = require('electron-updater');
const AutoLaunch = require('auto-launch');

// v3.2.0: Pusher.js direto (sem Laravel Echo)
global.Pusher = require('pusher-js');

const axios = require('axios');
const ThermalPrinter = require('./printer');

// Configuração
const store = new Store();
const isDev = process.argv.includes('--dev');

// ⭐ Fix para notificações do Windows mostrarem corretamente
if (process.platform === 'win32') {
    app.setAppUserModelId('com.yumgo.bridge');
}

// Auto-launch (Iniciar com Windows)
const autoLauncher = new AutoLaunch({
    name: 'YumGo Bridge',
    path: app.getPath('exe'),
});

// Configurar electron-updater
autoUpdater.logger = log;
autoUpdater.logger.transports.file.level = 'info';
autoUpdater.autoDownload = false; // Perguntar antes de baixar

// Estado global
let mainWindow;
let tray;
let trayMenu;  // FIX: Armazenar referência ao menu
let echo;
let printerManager;
let isConnected = false;
let currentToken = null;
let currentRestaurantId = null;

// Proteção contra impressão duplicada (v1.8.0)
const printedOrders = new Map(); // orderId -> timestamp
const PRINT_COOLDOWN = 5 * 60 * 1000; // 5 minutos

// ===== AUTO-UPDATER (v1.7.0) =====

// Evento: Atualização disponível
autoUpdater.on('update-available', (info) => {
    log.info('🔄 Atualização disponível:', info.version);

    dialog.showMessageBox({
        type: 'info',
        title: 'Atualização Disponível',
        message: `Nova versão ${info.version} disponível!`,
        detail: `Você está usando a versão ${app.getVersion()}.\n\nDeseja baixar a atualização agora?`,
        buttons: ['Sim, Baixar', 'Mais Tarde'],
        defaultId: 0,
        cancelId: 1
    }).then(result => {
        if (result.response === 0) {
            log.info('Usuário escolheu baixar atualização');
            autoUpdater.downloadUpdate();
        } else {
            log.info('Usuário adiou atualização');
        }
    });
});

// Evento: Nenhuma atualização disponível
autoUpdater.on('update-not-available', (info) => {
    log.info('✅ App está atualizado:', info.version);
});

// Evento: Erro ao verificar atualização
autoUpdater.on('error', (err) => {
    log.error('❌ Erro ao verificar atualização:', err);
});

// Evento: Download em progresso
autoUpdater.on('download-progress', (progressObj) => {
    const percent = Math.round(progressObj.percent);
    log.info(`📥 Baixando atualização: ${percent}%`);

    if (mainWindow) {
        mainWindow.webContents.send('download-progress', {
            percent: percent,
            transferred: progressObj.transferred,
            total: progressObj.total
        });
    }
});

// Evento: Download concluído
autoUpdater.on('update-downloaded', (info) => {
    log.info('✅ Atualização baixada:', info.version);

    dialog.showMessageBox({
        type: 'info',
        title: 'Atualização Pronta',
        message: 'Atualização baixada com sucesso!',
        detail: `A versão ${info.version} está pronta para instalar.\n\nO app será reiniciado para aplicar a atualização.`,
        buttons: ['Instalar e Reiniciar', 'Instalar Depois'],
        defaultId: 0,
        cancelId: 1
    }).then(result => {
        if (result.response === 0) {
            log.info('Usuário escolheu instalar agora');
            autoUpdater.quitAndInstall();
        } else {
            log.info('Usuário adiou instalação');
        }
    });
});

// Função para verificar atualizações manualmente
function checkForUpdates() {
    if (isDev) {
        log.info('Modo dev: Verificação de atualizações desabilitada');
        dialog.showMessageBox({
            type: 'info',
            title: 'Modo Desenvolvimento',
            message: 'Auto-update desabilitado em modo dev',
            buttons: ['OK']
        });
        return;
    }

    log.info('Verificando atualizações...');
    autoUpdater.checkForUpdates();
}

// ===== INICIALIZAÇÃO =====

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 500,
        height: 700,
        minWidth: 400,
        minHeight: 600,
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false,
            enableRemoteModule: true
        },
        icon: path.join(__dirname, '../assets/icon.png'),
        title: 'YumGo Bridge - Impressão Local',
        show: false,
        backgroundColor: '#ffffff'
    });

    mainWindow.loadFile(path.join(__dirname, 'index.html'));

    // Mostrar quando pronto
    mainWindow.once('ready-to-show', () => {
        mainWindow.show();

        if (isDev) {
            mainWindow.webContents.openDevTools();
        }
    });

    // Minimizar para tray ao fechar
    mainWindow.on('close', (event) => {
        if (!app.isQuitting) {
            event.preventDefault();
            mainWindow.hide();
        }
        return false;
    });

    // Criar tray icon
    createTray();

    // Restaurar configuração salva
    const config = store.get('config');
    if (config && config.restaurantId && config.token) {
        mainWindow.webContents.send('restore-config', config);
        setTimeout(() => {
            connectWebSocket(config.restaurantId, config.token);
        }, 1000);
    }

    // Restaurar configurações de impressora
    const printers = store.get('printers', {});
    mainWindow.webContents.send('restore-printers', printers);

    // Restaurar status de autostart
    autoLauncher.isEnabled().then((isEnabled) => {
        mainWindow.webContents.send('autostart-status', isEnabled);
    }).catch((err) => {
        log.error('Erro ao verificar autostart:', err);
    });
}

function createTray() {
    const icon = nativeImage.createFromPath(path.join(__dirname, '../assets/icon.png'));
    tray = new Tray(icon.resize({ width: 16, height: 16 }));

    // FIX: Armazenar menu para poder atualizá-lo depois
    trayMenu = Menu.buildFromTemplate([
        {
            label: 'Abrir YumGo Bridge',
            click: () => {
                mainWindow.show();
            }
        },
        {
            label: 'Status: Desconectado',
            enabled: false,
            id: 'status'
        },
        { type: 'separator' },
        {
            label: '🔄 Verificar Atualizações',
            click: () => {
                checkForUpdates();
            }
        },
        {
            label: 'Sair',
            click: () => {
                app.isQuitting = true;
                app.quit();
            }
        }
    ]);

    tray.setContextMenu(trayMenu);
    tray.setToolTip('YumGo Bridge - Impressão Local');

    tray.on('click', () => {
        mainWindow.show();
    });
}

function updateTrayStatus(connected) {
    if (!tray) return;

    // FIX v2.2.1: Recriar menu inteiro ao invés de só mudar label
    const newMenu = Menu.buildFromTemplate([
        {
            label: 'Abrir YumGo Bridge',
            click: () => {
                mainWindow.show();
            }
        },
        {
            label: connected ? 'Status: ✅ Conectado' : 'Status: ⚠️ Desconectado',
            enabled: false
        },
        { type: 'separator' },
        {
            label: '🔄 Verificar Atualizações',
            click: () => {
                checkForUpdates();
            }
        },
        {
            label: 'Sair',
            click: () => {
                app.isQuitting = true;
                app.quit();
            }
        }
    ]);

    tray.setContextMenu(newMenu);
    trayMenu = newMenu; // Atualizar referência

    log.info(`Tray status atualizado: ${connected ? 'Conectado' : 'Desconectado'}`);
}

// ===== WEBSOCKET COM LARAVEL ECHO =====

// ===== HEARTBEAT (Monitor de Impressão) =====
let heartbeatInterval = null;

function startHeartbeat(baseUrl, token) {
    // Limpar intervalo anterior (se existir)
    if (heartbeatInterval) {
        clearInterval(heartbeatInterval);
    }

    // Função para enviar heartbeat
    const sendHeartbeat = async () => {
        try {
            const printers = printerManager ? printerManager.getAllPrinters() : [];

            await axios.post(`${baseUrl}/api/v1/bridge/heartbeat`, {
                version: app.getVersion(),
                printers: printers,
                timestamp: new Date().toISOString(),
            }, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                }
            });

            log.info('💓 Heartbeat enviado ao servidor');
        } catch (error) {
            log.error('❌ Erro ao enviar heartbeat:', error.message);
        }
    };

    // Enviar heartbeat imediatamente
    sendHeartbeat();

    // Enviar heartbeat a cada 30 segundos
    heartbeatInterval = setInterval(sendHeartbeat, 30000);
    log.info('⏰ Heartbeat agendado (a cada 30s)');
}

function stopHeartbeat() {
    if (heartbeatInterval) {
        clearInterval(heartbeatInterval);
        heartbeatInterval = null;
        log.info('⏹️ Heartbeat parado');
    }
}

function connectWebSocket(restaurantId, token) {
    // Desconectar se já houver conexão
    if (echo && echo.pusher) {
        echo.pusher.disconnect();
        echo = null;
    }

    // Salvar credenciais
    currentToken = token;
    currentRestaurantId = restaurantId;

    log.info(`🔵 Conectando ao servidor... Restaurant ID: ${restaurantId}`);
    log.info(`Token: ${token.substring(0, 20)}...`);

    // Configurar URLs baseado no ambiente
    const baseUrl = 'https://yumgo.com.br';  // Sempre servidor (não localhost)
    // ⭐ Conectar via Nginx proxy (wss://ws.yumgo.com.br porta 443)
    const wsHost = 'ws.yumgo.com.br';
    const wsPort = 443;
    const wsPath = '';  // Empty - Pusher adds /app/{key} automatically

    // ⭐ v3.2.7: Removido Pusher.Runtime.createXHR - deixar Pusher usar XMLHttpRequest nativo do Chromium
    // O Electron já tem XMLHttpRequest disponível via Chromium, não precisamos configurar manualmente

    log.info(`📡 Configuração WebSocket:`);
    log.info(`   - baseUrl: ${baseUrl}`);
    log.info(`   - wsHost: ${wsHost}`);
    log.info(`   - wsPort: ${wsPort}`);
    log.info(`   - wsPath: "${wsPath}"`);
    log.info(`   - isDev: ${isDev}`);

    try {
        // v3.2.0: PUSHER.JS DIRETO (solução para incompatibilidade Laravel Echo)
        log.info('🔵 Criando conexão Pusher.js direta...');

        const pusherConfig = {
            key: 't9pg2dslmpl5y1cp6rrf',
            wsHost: wsHost,
            wsPort: wsPort,
            cluster: 'mt1',  // ⭐ Obrigatório pelo SDK (Reverb ignora)
            forceTLS: true,  // ⭐ SSL habilitado (wss:// via Nginx proxy)
            enabledTransports: ['ws', 'wss'],  // ⭐ Aceita ws:// e wss://
            disableStats: true,
            // Electron-specific options
            activityTimeout: 120000,
            pongTimeout: 30000,
            unavailableTimeout: 10000
        };

        // Log config
        log.info(`📡 Pusher config:`, JSON.stringify(pusherConfig, null, 2));

        log.info('🔵 Criando instância do Pusher...');
        const pusher = new global.Pusher(pusherConfig.key, pusherConfig);
        log.info('✅ Pusher criado com sucesso');

        // Guardar referência global para uso em disconnect
        echo = { pusher }; // Mantém compatibilidade com código existente

        log.info('📡 Pusher connection state:', pusher.connection.state);

        // 🔍 DEBUG: Capturar TODOS os eventos Pusher
        pusher.connection.bind_global((eventName, data) => {
            log.info(`🔔 [PUSHER EVENT] ${eventName}`, data ? JSON.stringify(data).substring(0, 200) : '');
        });

        // Eventos de conexão do Pusher
        log.info('🔵 Registrando event listeners do Pusher...');

        // Log de TODOS os eventos de state para debug
        pusher.connection.bind('state_change', (states) => {
            log.info(`📊 [STATE CHANGE] ${states.previous} → ${states.current}`);

            // Capturar informações adicionais de cada estado
            if (states.current === 'connecting') {
                log.info('   → Tentando conectar...');
            } else if (states.current === 'connected') {
                log.info('   → Conectado!');
            } else if (states.current === 'unavailable') {
                log.error('   → Servidor indisponível');
            } else if (states.current === 'failed') {
                log.error('   → Falha na conexão');
            } else if (states.current === 'disconnected') {
                log.warn('   → Desconectado');
            }
        });

        // Capturar erro no nível de transporte
        pusher.connection.bind('error', (error) => {
            log.error('❌ [CONNECTION ERROR] DETALHADO:');
            log.error('   → Full error object:', JSON.stringify(error, Object.getOwnPropertyNames(error), 2));

            if (error.error) {
                log.error('   → error.error:', JSON.stringify(error.error, Object.getOwnPropertyNames(error.error), 2));
            }
            if (error.type) {
                log.error('   → error.type:', error.type);
            }
            if (error.data) {
                log.error('   → error.data:', JSON.stringify(error.data, null, 2));
            }
            if (error.message) {
                log.error('   → error.message:', error.message);
            }
            if (error.code) {
                log.error('   → error.code:', error.code);
            }

            // Mostrar erro na UI
            if (mainWindow) {
                mainWindow.webContents.send('connection-status', 'error');
            }
        });

        pusher.connection.bind('connected', () => {
            log.info('✅ Conectado ao servidor YumGo via Reverb/Pusher');
            log.info('📡 Socket ID:', pusher.connection.socket_id);
            isConnected = true;

            // Atualizar UI
            mainWindow.webContents.send('connection-status', 'connected');
            updateTrayStatus(true);

            // Notificação
            showNotification('Conectado', 'YumGo Bridge conectado com sucesso!');

            // ⭐ Heartbeat habilitado (API de monitoramento implementada)
            startHeartbeat(baseUrl, token);
        });

        pusher.connection.bind('disconnected', () => {
            log.warn('❌ Desconectado do servidor');
            isConnected = false;

            mainWindow.webContents.send('connection-status', 'disconnected');
            updateTrayStatus(false);

            // Parar heartbeat
            stopHeartbeat();
        });

        pusher.connection.bind('failed', () => {
            log.error('💥 FALHA PERMANENTE NA CONEXÃO!');
            log.error('   → Pusher não conseguiu conectar após todas as tentativas');
            log.error('   → Verifique: firewall, antivírus, proxy, certificado SSL');
            isConnected = false;

            mainWindow.webContents.send('connection-status', 'error');
            updateTrayStatus(false);
        });

        // Inscrever no canal PÚBLICO do restaurante
        const channelName = `restaurant.${restaurantId}`;
        log.info(`🔵 Inscrevendo no canal PÚBLICO: ${channelName}`);

        const channel = pusher.subscribe(channelName);

        log.info(`📡 Aguardando inscrição no canal...`);

        // Callback de sucesso na inscrição
        channel.bind('pusher:subscription_succeeded', () => {
            log.info(`✅ ✅ ✅ INSCRITO NO CANAL: ${channelName}`);
            log.info(`🎧 Aguardando eventos .order.created...`);
        });

        // Callback de erro na inscrição
        channel.bind('pusher:subscription_error', (error) => {
            log.error('❌ Erro ao inscrever no canal:', JSON.stringify(error, null, 2));
            mainWindow.webContents.send('connection-status', 'error');
        });

        // Escutar evento .order.created
        channel.bind('.order.created', async (data) => {
            log.info(`🔔 🔔 🔔 PEDIDO RECEBIDO VIA WEBSOCKET!`);
            log.info(`📦 Dados: #${data.order.order_number}`);

            log.info(`🔔 Novo pedido recebido: #${data.order.order_number}`);

            try {
                const order = data.order;
                const orderId = order.id;
                const now = Date.now();
                const forceReprint = order.force_reprint === true; // ⭐ Flag de reimpressão forçada

                // ===== PROTEÇÃO CONTRA IMPRESSÃO DUPLICADA (v1.8.0) =====
                if (printedOrders.has(orderId) && !forceReprint) {  // ⭐ Ignora proteção se force_reprint = true
                    const lastPrinted = printedOrders.get(orderId);
                    const timeSinceLastPrint = now - lastPrinted;

                    if (timeSinceLastPrint < PRINT_COOLDOWN) {
                        const minutesLeft = Math.ceil((PRINT_COOLDOWN - timeSinceLastPrint) / 60000);
                        log.warn(`⚠️ Pedido #${order.order_number} (ID: ${orderId}) já foi impresso há ${Math.floor(timeSinceLastPrint / 1000)}s. Ignorando impressão duplicada.`);

                        mainWindow.webContents.send('print-skipped', {
                            order_id: orderId,
                            order_number: order.order_number,
                            reason: `Já impresso há ${Math.floor(timeSinceLastPrint / 1000)}s (cooldown: ${minutesLeft} min)`
                        });

                        return; // Não imprimir novamente
                    } else {
                        // Cooldown expirado, pode reimprimir
                        log.info(`✅ Cooldown expirado para pedido #${order.order_number}. Permitindo impressão.`);
                    }
                }

                // ⭐ Log de reimpressão forçada
                if (forceReprint) {
                    log.info(`🔄 REIMPRESSÃO FORÇADA do pedido #${order.order_number} (ignora cooldown)`);
                }

                // Registrar impressão
                printedOrders.set(orderId, now);

                // Limpar registros antigos (mais de 10 minutos)
                for (const [id, timestamp] of printedOrders.entries()) {
                    if (now - timestamp > PRINT_COOLDOWN * 2) {
                        printedOrders.delete(id);
                    }
                }

                log.info(`📝 Pedido #${order.order_number} registrado no histórico de impressão`);
                // ===== FIM DA PROTEÇÃO =====

                // Tocar som de notificação
                mainWindow.webContents.send('play-sound');

                // Mostrar notificação
                showNotification(
                    `Novo Pedido #${order.order_number}`,
                    `Cliente: ${order.customer.name}\nTotal: R$ ${order.totals.total.toFixed(2)}`
                );

                // Imprimir em todas as impressoras configuradas
                if (printerManager && order.print_locations) {
                    for (const location of order.print_locations) {
                        const printer = printerManager.printers[location];

                        // ⭐ Validação rigorosa: verificar se impressora existe E está configurada
                        if (!printer) {
                            log.warn(`⚠️ Impressora "${location}" não configurada. Pulando impressão.`);
                            continue;
                        }

                        // ⭐ Validação extra: verificar se tipo é válido e tem nome (para "system")
                        const config = printer.config;
                        if (!config || config.type === 'none') {
                            log.warn(`⚠️ Impressora "${location}" configurada como "none". Pulando impressão.`);
                            continue;
                        }

                        // ⭐ Para impressoras "system", validações rigorosas
                        if (config.type === 'system') {
                            const printerName = config.printerName || '';

                            // 1. Verificar se tem nome
                            if (!printerName || printerName === '' || printerName === 'none') {
                                log.warn(`⚠️ Impressora "${location}" sem nome definido. Pulando impressão.`);
                                continue;
                            }

                            // 2. ⭐ IGNORAR impressoras virtuais (PDF, OneNote, Fax, etc)
                            const invalidPrinters = [
                                'pdf', 'print to pdf', 'microsoft print to pdf',
                                'onenote', 'one note',
                                'fax', 'faxworks',
                                'xps', 'microsoft xps',
                                'pdfcreator', 'pdf architect', 'pdf creator',
                                'foxit', 'adobe pdf',
                                'cutepdf', 'primopdf', 'novapdf'
                            ];

                            const printerNameLower = printerName.toLowerCase();
                            const isInvalidPrinter = invalidPrinters.some(invalid => printerNameLower.includes(invalid));

                            if (isInvalidPrinter) {
                                log.warn(`⚠️ Impressora "${location}" é virtual/PDF (${printerName}). Pulando impressão.`);
                                continue;
                            }
                        }

                        // ✅ Impressora válida, pode imprimir
                        let printSuccess = false;
                        let lastError = null;
                        const maxRetries = 3;

                        for (let attempt = 1; attempt <= maxRetries; attempt++) {
                            try {
                                log.info(`🖨️ Tentativa ${attempt}/${maxRetries} de impressão em: ${location}`);
                                await printerManager.printOrder(order, location);
                                log.info(`✅ Impresso com sucesso em: ${location} (${config.printerName})`);
                                printSuccess = true;

                                // ⭐ Reportar sucesso para o servidor
                                try {
                                    await axios.post(`${baseUrl}/api/v1/bridge/print-success`, {
                                        order_id: orderId,
                                        location: location,
                                        timestamp: new Date().toISOString(),
                                    }, {
                                        headers: {
                                            'Authorization': `Bearer ${currentToken}`,
                                            'Content-Type': 'application/json',
                                        }
                                    });
                                    log.info(`📊 Sucesso de impressão reportado ao servidor`);
                                } catch (reportError) {
                                    log.error(`⚠️ Erro ao reportar sucesso (não crítico):`, reportError.message);
                                }

                                break; // Sucesso, sair do loop
                            } catch (error) {
                                lastError = error;
                                log.error(`❌ Erro na tentativa ${attempt}/${maxRetries} em ${location}:`, error.message);

                                if (attempt < maxRetries) {
                                    // Aguardar antes de tentar novamente (1min, 2min, 5min)
                                    const delayMs = attempt === 1 ? 60000 : (attempt === 2 ? 120000 : 300000);
                                    log.info(`⏳ Aguardando ${delayMs/1000}s antes da próxima tentativa...`);
                                    await new Promise(resolve => setTimeout(resolve, delayMs));
                                }
                            }
                        }

                        // Se todas as tentativas falharam, reportar falha
                        if (!printSuccess && lastError) {
                            log.error(`💥 Todas as ${maxRetries} tentativas falharam para ${location}`);

                            // ⭐ Reportar falha para o servidor
                            try {
                                await axios.post(`${baseUrl}/api/v1/bridge/print-failed`, {
                                    order_id: orderId,
                                    location: location,
                                    error: lastError.message,
                                    attempts: maxRetries,
                                    timestamp: new Date().toISOString(),
                                }, {
                                    headers: {
                                        'Authorization': `Bearer ${currentToken}`,
                                        'Content-Type': 'application/json',
                                    }
                                });
                                log.info(`📊 Falha de impressão reportada ao servidor`);
                            } catch (reportError) {
                                log.error(`⚠️ Erro ao reportar falha (não crítico):`, reportError.message);
                            }

                            // Notificar usuário
                            showNotification(
                                `⚠️ Falha na Impressão`,
                                `Pedido #${order.order_number}\nImpressora: ${location}\nErro: ${lastError.message}`
                            );
                        }
                    }
                }

                // Atualizar lista de pedidos na UI
                mainWindow.webContents.send('new-order', order);

            } catch (error) {
                log.error('Erro ao processar pedido:', error);
                mainWindow.webContents.send('print-error', {
                    order_id: data.order.id,
                    error: error.message
                });
            }
        });

    } catch (error) {
        log.error('Erro ao inicializar Pusher.js:', error);
        mainWindow.webContents.send('connection-status', 'error');
    }
}

function showNotification(title, body) {
    const { Notification } = require('electron');

    if (Notification.isSupported()) {
        new Notification({
            title: title,
            body: body,
            icon: path.join(__dirname, '../assets/icon.png'),
            sound: true
        }).show();
    }
}

// ===== IPC HANDLERS =====

// Conectar ao servidor
ipcMain.on('connect', (event, { restaurantId, token }) => {
    // Salvar configuração
    store.set('config', { restaurantId, token });

    // Conectar
    connectWebSocket(restaurantId, token);
});

// Desconectar
ipcMain.on('disconnect', () => {
    if (echo && echo.pusher) {
        echo.pusher.disconnect();
        echo = null;
    }
    isConnected = false;
    currentToken = null;
    currentRestaurantId = null;
    mainWindow.webContents.send('connection-status', 'disconnected');
    updateTrayStatus(false);
});

// Configurar impressora (v3.0.1: mudado para handle)
ipcMain.handle('configure-printer', async (event, config) => {
    try {
        if (!printerManager) {
            printerManager = new ThermalPrinter();
        }

        await printerManager.configurePrinter(config.location, config);

        // Salvar configuração
        const printers = store.get('printers', {});
        printers[config.location] = config;
        store.set('printers', printers);

        log.info(`Impressora ${config.location} configurada com sucesso`);

        return {
            location: config.location,
            success: true
        };

    } catch (error) {
        log.error('Erro ao configurar impressora:', error);
        throw error;
    }
});

// Teste de impressão (v3.0.1: mudado para handle)
ipcMain.handle('test-print', async (event, location) => {
    try {
        if (!printerManager) {
            throw new Error('Nenhuma impressora configurada');
        }

        const testOrder = {
            order_number: '0001',
            customer: {
                name: 'Teste de Impressão',
                phone: '(11) 99999-9999'
            },
            delivery: {
                method: 'delivery',
                address: 'Rua Teste, 123',
                neighborhood: 'Bairro Teste'
            },
            items: [
                {
                    quantity: 1,
                    name: 'Produto Teste',
                    price: 25.00,
                    notes: 'Observação de teste'
                }
            ],
            totals: {
                subtotal: 25.00,
                delivery_fee: 5.00,
                total: 30.00
            },
            payment: {
                method: 'pix',
                status: 'paid'
            },
            notes: 'Este é um pedido de teste',
            created_at: new Date().toISOString()
        };

        await printerManager.printOrder(testOrder, location);

        log.info(`Teste de impressão enviado para ${location}`);

        return {
            success: true,
            message: 'Impressão de teste enviada com sucesso!'
        };

    } catch (error) {
        log.error('Erro no teste de impressão:', error);
        throw error;
    }
});

// Dicionário de fabricantes conhecidos (Vendor IDs)
const KNOWN_VENDORS = {
    0x04b8: 'Epson',
    0x0519: 'Bematech',
    0x0483: 'Elgin',
    0x1504: 'Daruma',
    0x154f: 'Diebold',
    0x0fe6: 'IVI',
    0x0dd4: 'Zebra',
    0x2730: 'Sewoo',
    0x0519: 'Custom Engineering',
    0x0924: 'Star Micronics'
};

// Dicionário de modelos conhecidos (Product IDs)
const KNOWN_MODELS = {
    0x0e15: 'TM-T20',
    0x0e03: 'TM-T88',
    0x0e01: 'TM-T81',
    0x2008: 'MP-4200 TH',
    0x7070: 'i9',
    0x0202: 'DR700'
};

// Buscar impressoras USB disponíveis
// Detectar impressoras USB (método antigo - ainda funciona)
ipcMain.handle('find-usb-printers', async () => {
    try {
        const escpos = require('escpos');
        const USB = require('escpos-usb');

        const devices = USB.findPrinter();

        return devices.map(device => {
            const vendorId = device.deviceDescriptor.idVendor;
            const productId = device.deviceDescriptor.idProduct;

            // Nome amigável do fabricante
            const vendorName = KNOWN_VENDORS[vendorId] || 'Desconhecido';

            // Nome amigável do modelo
            const modelName = KNOWN_MODELS[productId] || `Modelo ${productId.toString(16).toUpperCase()}`;

            // Nome completo: Fabricante + Modelo
            const displayName = `${vendorName} ${modelName}`;

            return {
                vendorId: vendorId,
                productId: productId,
                vendorName: vendorName,
                modelName: modelName,
                displayName: displayName,
                manufacturer: device.deviceDescriptor.iManufacturer,
                product: device.deviceDescriptor.iProduct
            };
        });

    } catch (error) {
        log.error('Erro ao buscar impressoras USB:', error);
        return [];
    }
});

// NOVO: Detectar TODAS impressoras instaladas no sistema (v1.9.3+)
// Inclui: USB, Rede, Virtuais (Print to PDF), etc
ipcMain.handle('find-system-printers', async (event) => {
    try {
        log.info('🔍 Buscando impressoras instaladas no sistema...');
        console.log('DEBUG: find-system-printers called');

        // SOLUÇÃO: Usar PowerShell no Windows (mais confiável que getPrinters)
        if (process.platform === 'win32') {
            const { execSync } = require('child_process');
            const stdout = execSync('powershell -Command "Get-Printer | Select-Object Name, DriverName, PortName | ConvertTo-Json"', {
                encoding: 'utf8',
                windowsHide: true
            });

            const windowsPrinters = JSON.parse(stdout);
            console.log('DEBUG: PowerShell returned printers:', windowsPrinters);

            // Converter para formato compatível
            const printers = (Array.isArray(windowsPrinters) ? windowsPrinters : [windowsPrinters]).map(p => {
                let emoji = '🖨️';
                const nameLower = (p.Name || '').toLowerCase();

                if (nameLower.includes('pdf')) emoji = '📄';
                else if (nameLower.includes('xps')) emoji = '📄';
                else if (nameLower.includes('fax')) emoji = '📠';

                return {
                    name: p.Name,
                    displayName: p.Name,
                    status: 0,
                    isDefault: false,
                    emoji: emoji,
                    label: `${emoji} ${p.Name}`
                };
            });

            log.info(`✅ Encontradas ${printers.length} impressora(s) no Windows`);
            return printers;
        }

        // Fallback: Usar getPrinters() para macOS/Linux
        const printers = event.sender.getPrinters();
        console.log('DEBUG: getPrinters() returned:', printers);

        log.info(`✅ Encontradas ${printers.length} impressora(s) no sistema`);

        return printers.map(printer => {
            // Adiciona emoji baseado no tipo/nome
            let emoji = '🖨️';
            const nameLower = printer.name.toLowerCase();

            if (nameLower.includes('pdf')) emoji = '📄';
            else if (nameLower.includes('thermal') || nameLower.includes('térmica')) emoji = '🎫';
            else if (nameLower.includes('epson') || nameLower.includes('bematech')) emoji = '🎫';
            else if (nameLower.includes('network') || nameLower.includes('rede')) emoji = '🌐';
            else if (nameLower.includes('usb')) emoji = '🔌';

            // Status da impressora
            let statusText = 'Disponível';
            if (printer.status === 3) statusText = 'Offline';
            else if (printer.status === 4) statusText = 'Erro';
            else if (printer.isDefault) statusText = 'Padrão';

            return {
                name: printer.name,
                displayName: printer.displayName || printer.name,
                description: printer.description || '',
                status: printer.status,
                statusText: statusText,
                isDefault: printer.isDefault,
                emoji: emoji,
                // Nome formatado para exibição
                label: `${emoji} ${printer.displayName || printer.name}${printer.isDefault ? ' (Padrão)' : ''}`
            };
        });

    } catch (error) {
        log.error('❌ Erro ao buscar impressoras do sistema:', error);
        return [];
    }
});

// Obter versão do app (v1.9.3+)
ipcMain.handle('get-app-version', () => {
    return app.getVersion();
});

// Selecionar arquivo de logo (v1.7.0)
ipcMain.handle('select-logo-file', async () => {
    const { dialog } = require('electron');

    const result = await dialog.showOpenDialog({
        title: 'Selecionar Logo do Restaurante',
        filters: [
            { name: 'Imagens', extensions: ['png', 'jpg', 'jpeg', 'bmp'] }
        ],
        properties: ['openFile']
    });

    if (result.canceled || result.filePaths.length === 0) {
        return { canceled: true };
    }

    return {
        canceled: false,
        filePath: result.filePaths[0]
    };
});

// Limpar configuração
ipcMain.on('clear-config', () => {
    store.clear();
    app.relaunch();
    app.exit();
});

// ===== AUTOSTART (Iniciar com Windows) =====

// Get autostart status
ipcMain.handle('get-autostart', async () => {
    try {
        const isEnabled = await autoLauncher.isEnabled();
        log.info(`Autostart status: ${isEnabled}`);
        return isEnabled;
    } catch (error) {
        log.error('Erro ao verificar autostart:', error);
        return false;
    }
});

// Set autostart
ipcMain.handle('set-autostart', async (event, enable) => {
    try {
        if (enable) {
            await autoLauncher.enable();
            log.info('Autostart habilitado');
        } else {
            await autoLauncher.disable();
            log.info('Autostart desabilitado');
        }
        return true;
    } catch (error) {
        log.error('Erro ao configurar autostart:', error);
        throw error;
    }
});

// ===== HANDLERS ADICIONAIS (v3.0.0) =====

// save-printer (alias de configure-printer para compatibilidade v3)
ipcMain.handle('save-printer', async (event, location, config) => {
    try {
        if (!printerManager) {
            printerManager = new ThermalPrinter();
        }

        const fullConfig = { ...config, location };
        await printerManager.configurePrinter(location, fullConfig);

        // Salvar configuração
        const printers = store.get('printers', {});
        printers[location] = fullConfig;
        store.set('printers', printers);

        log.info(`Impressora ${location} configurada com sucesso (v3)`);
        return { success: true };

    } catch (error) {
        log.error('Erro ao configurar impressora:', error);
        throw error;
    }
});

// ⭐ remove-printer: Remover/limpar configuração de impressora
ipcMain.handle('remove-printer', async (event, location) => {
    try {
        const printers = store.get('printers', {});

        if (printers[location]) {
            delete printers[location];
            store.set('printers', printers);

            // Remover do printerManager também
            if (printerManager && printerManager.printers[location]) {
                delete printerManager.printers[location];
            }

            log.info(`✅ Impressora ${location} removida com sucesso`);
            return { success: true, message: 'Impressora removida' };
        } else {
            log.warn(`⚠️ Impressora ${location} não estava configurada`);
            return { success: true, message: 'Impressora não estava configurada' };
        }
    } catch (error) {
        log.error('Erro ao remover impressora:', error);
        throw error;
    }
});

// connect como handle (para uso com invoke)
ipcMain.handle('connect', async (event, { restaurantId, token }) => {
    try {
        // Salvar configuração
        store.set('config', { restaurantId, token });

        // Feedback imediato: conectando
        if (mainWindow) {
            mainWindow.webContents.send('connection-status', 'reconnecting');
        }

        // Conectar
        connectWebSocket(restaurantId, token);

        return { success: true };
    } catch (error) {
        log.error('Erro ao conectar:', error);
        if (mainWindow) {
            mainWindow.webContents.send('connection-status', 'error');
        }
        throw error;
    }
});

// disconnect como handle
ipcMain.handle('disconnect', async () => {
    try {
        if (echo && echo.pusher) {
            echo.pusher.disconnect();
            echo = null;
        }
        isConnected = false;
        currentToken = null;
        currentRestaurantId = null;

        if (mainWindow) {
            mainWindow.webContents.send('connection-status', 'disconnected');
        }
        updateTrayStatus(false);

        return { success: true };
    } catch (error) {
        log.error('Erro ao desconectar:', error);
        throw error;
    }
});

// test-websocket
ipcMain.handle('test-websocket', async () => {
    try {
        if (!isConnected) {
            throw new Error('Bridge não está conectado');
        }

        if (!echo || !echo.pusher) {
            throw new Error('WebSocket não inicializado');
        }

        return {
            success: true,
            connected: isConnected,
            restaurantId: currentRestaurantId,
            socketId: echo.pusher.connection.socket_id || null
        };

    } catch (error) {
        log.error('Erro no teste WebSocket:', error);
        throw error;
    }
});

// test-print-all
ipcMain.handle('test-print-all', async () => {
    try {
        if (!printerManager) {
            throw new Error('Nenhuma impressora configurada');
        }

        const printers = store.get('printers', {});
        const locations = Object.keys(printers);

        if (locations.length === 0) {
            throw new Error('Nenhuma impressora configurada');
        }

        const testOrder = {
            order_number: 'TESTE-001',
            customer: {
                name: 'Teste de Impressão',
                phone: '(11) 99999-9999'
            },
            delivery: {
                method: 'delivery',
                address: 'Rua Teste, 123',
                neighborhood: 'Bairro Teste'
            },
            items: [
                {
                    quantity: 2,
                    name: 'Produto Teste',
                    price: 25.00,
                    notes: 'Observação de teste'
                }
            ],
            totals: {
                subtotal: 50.00,
                delivery_fee: 5.00,
                total: 55.00
            },
            payment: {
                method: 'pix',
                status: 'paid'
            },
            notes: 'Este é um pedido de teste automático',
            created_at: new Date().toISOString()
        };

        const results = [];
        for (const location of locations) {
            // ⭐ Verificar se tem impressora configurada
            if (!printerManager.printers[location]) {
                results.push({ location, success: false, error: 'Impressora não configurada' });
                log.warn(`⚠️ Impressora "${location}" não configurada. Pulando teste.`);
                continue;
            }

            try {
                await printerManager.printOrder(testOrder, location);
                results.push({ location, success: true });
                log.info(`Teste de impressão enviado para ${location}`);
            } catch (error) {
                results.push({ location, success: false, error: error.message });
                log.error(`Erro ao imprimir em ${location}:`, error);
            }
        }

        return {
            success: true,
            results: results
        };

    } catch (error) {
        log.error('Erro no teste de impressão:', error);
        throw error;
    }
});

// get-system-os
ipcMain.handle('get-system-os', () => {
    return process.platform;
});

// get-electron-version
ipcMain.handle('get-electron-version', () => {
    return process.versions.electron;
});

// get-node-version
ipcMain.handle('get-node-version', () => {
    return process.versions.node;
});

// minimize-window
ipcMain.on('minimize-window', () => {
    if (mainWindow) {
        mainWindow.minimize();
    }
});

// close-window
ipcMain.on('close-window', () => {
    if (mainWindow) {
        mainWindow.hide();
    }
});

// save-preferences
ipcMain.on('save-preferences', (event, prefs) => {
    store.set('preferences', prefs);
    log.info('Preferências salvas:', prefs);
});

// ===== APP LIFECYCLE =====

app.whenReady().then(() => {
    createWindow();

    // Verificar atualizações ao iniciar (v1.7.0)
    if (!isDev) {
        // Aguarda 3 segundos antes de verificar (app já inicializou)
        setTimeout(() => {
            log.info('Verificando atualizações automaticamente...');
            autoUpdater.checkForUpdates();
        }, 3000);
    }

    // Restaurar impressoras configuradas
    const printers = store.get('printers');
    if (printers) {
        printerManager = new ThermalPrinter();
        Object.entries(printers).forEach(async ([location, config]) => {
            try {
                // ⭐ Validar se configuração é válida antes de restaurar
                if (!config || config.type === 'none') {
                    log.warn(`⚠️ Impressora ${location} configurada como "none". Ignorando.`);
                    return;
                }

                // ⭐ Para "system", validações rigorosas
                if (config.type === 'system') {
                    const printerName = config.printerName || '';

                    // 1. Verificar se tem nome
                    if (!printerName || printerName === '' || printerName === 'none') {
                        log.warn(`⚠️ Impressora ${location} sem nome definido. Ignorando.`);
                        return;
                    }

                    // 2. ⭐ IGNORAR impressoras virtuais (PDF, OneNote, Fax, etc)
                    const invalidPrinters = [
                        'pdf', 'print to pdf', 'microsoft print to pdf',
                        'onenote', 'one note',
                        'fax', 'faxworks',
                        'xps', 'microsoft xps',
                        'pdfcreator', 'pdf architect', 'pdf creator',
                        'foxit', 'adobe pdf',
                        'cutepdf', 'primopdf', 'novapdf'
                    ];

                    const printerNameLower = printerName.toLowerCase();
                    const isInvalidPrinter = invalidPrinters.some(invalid => printerNameLower.includes(invalid));

                    if (isInvalidPrinter) {
                        log.warn(`⚠️ Impressora ${location} é virtual/PDF (${printerName}). Ignorando.`);
                        return;
                    }
                }

                await printerManager.configurePrinter(location, config);
                log.info(`✅ Impressora ${location} restaurada: ${config.printerName || config.type}`);
            } catch (error) {
                log.error(`❌ Erro ao restaurar impressora ${location}:`, error);
            }
        });
    }
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});

app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
        createWindow();
    } else {
        mainWindow.show();
    }
});

app.on('before-quit', () => {
    app.isQuitting = true;
    if (echo && echo.pusher) {
        echo.pusher.disconnect();
    }
});

// Log não tratados
process.on('uncaughtException', (error) => {
    log.error('Erro não tratado:', error);
});

process.on('unhandledRejection', (error) => {
    log.error('Promise rejeitada:', error);
});
