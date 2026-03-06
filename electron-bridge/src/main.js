const { app, BrowserWindow, ipcMain, Menu, Tray, nativeImage } = require('electron');
const path = require('path');
const Store = require('electron-store');
const log = require('electron-log');

// FIX: Pusher precisa estar disponível globalmente para Laravel Echo
global.Pusher = require('pusher-js');

const Echo = require('laravel-echo').default;  // FIX: ES6 default export
const axios = require('axios');
const ThermalPrinter = require('./printer');

// Configuração
const store = new Store();
const isDev = process.argv.includes('--dev');

// Estado global
let mainWindow;
let tray;
let trayMenu;  // FIX: Armazenar referência ao menu
let echo;
let printerManager;
let isConnected = false;
let currentToken = null;
let currentRestaurantId = null;

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
    if (!tray || !trayMenu) return;

    // FIX: Usar trayMenu armazenado ao invés de getContextMenu()
    const statusItem = trayMenu.getMenuItemById('status');

    if (statusItem) {
        statusItem.label = connected
            ? 'Status: ✅ Conectado'
            : 'Status: ⚠️ Desconectado';
    }
}

// ===== WEBSOCKET COM LARAVEL ECHO =====

function connectWebSocket(restaurantId, token) {
    // Desconectar se já houver conexão
    if (echo) {
        echo.disconnect();
        echo = null;
    }

    // Salvar credenciais
    currentToken = token;
    currentRestaurantId = restaurantId;

    log.info(`🔵 Conectando ao servidor... Restaurant ID: ${restaurantId}`);
    log.info(`Token: ${token.substring(0, 20)}...`);

    // Configurar URLs baseado no ambiente
    const baseUrl = isDev ? 'http://localhost:8000' : 'https://yumgo.com.br';
    const wsHost = isDev ? 'localhost' : 'ws.yumgo.com.br';  // Subdomínio WebSocket via Nginx SSL
    const wsPort = isDev ? 8081 : 443;  // HTTPS/443 em produção
    const wsPath = '';  // Empty - Pusher adds /app/{key} automatically

    // FIX: Configurar Pusher para Node.js/Electron environment
    if (!global.Pusher.Runtime) {
        global.Pusher.Runtime = {
            createXHR: function() {
                const xhr = require('https').request ||  require('http').request;
                return xhr;
            }
        };
    }

    log.info(`📡 Configuração WebSocket:`);
    log.info(`   - baseUrl: ${baseUrl}`);
    log.info(`   - wsHost: ${wsHost}`);
    log.info(`   - wsPort: ${wsPort}`);
    log.info(`   - wsPath: "${wsPath}"`);
    log.info(`   - isDev: ${isDev}`);

    try {
        // Configurar Laravel Echo com Pusher/Reverb (servidor próprio)
        //
        // NOTA: cluster é obrigatório mesmo para servidor próprio
        // O Pusher-JS valida a presença do cluster, mas IGNORA o valor quando
        // wsHost é especificado. Isso é comportamento documentado:
        // https://pusher.com/docs/channels/using_channels/connection/#self-hosted
        //
        // O valor 'mt1' é um cluster Pusher válido, mas qualquer string funciona.
        // O importante é que o campo exista para passar na validação.

        log.info('🔵 Criando cliente Pusher...');

        let pusherClient;
        try {
            // FIX: Em Node.js/Electron, não especificar enabledTransports
            // Deixar o Pusher escolher automaticamente o melhor transporte
            pusherClient = new global.Pusher('t9pg2dslmpl5y1cp6rrf', {
                wsHost: wsHost,
                wsPort: wsPort,
                wssPort: wsPort,
                forceTLS: !isDev,
                encrypted: !isDev,
                disableStats: true,
                // REMOVIDO enabledTransports - Pusher-JS Node escolhe automaticamente
                cluster: 'mt1',  // Obrigatório para Pusher-JS, ignorado com wsHost
            });

            log.info('✅ Cliente Pusher criado');
            log.info('📡 Estado inicial:', pusherClient.connection.state);

            // Log de informações do transporte
            if (pusherClient.connection.options) {
                log.info('📡 Transporte habilitado:', pusherClient.connection.options.enabledTransports);
            }

        } catch (pusherError) {
            log.error('❌ ERRO ao criar cliente Pusher:', pusherError.message);
            log.error('Stack:', pusherError.stack);
            throw pusherError;
        }

        const echoConfig = {
            broadcaster: 'pusher',
            key: 't9pg2dslmpl5y1cp6rrf',
            client: pusherClient,  // FIX: Passar cliente criado manualmente
            authEndpoint: `${baseUrl}/api/broadcasting/auth`,
            auth: {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'X-Restaurant-ID': restaurantId
                }
            }
        };

        // Log config sem o cliente (evita circular reference)
        log.info(`📡 Echo config:`, JSON.stringify({
            broadcaster: echoConfig.broadcaster,
            key: echoConfig.key,
            authEndpoint: echoConfig.authEndpoint,
            hasClient: !!echoConfig.client
        }, null, 2));

        log.info('🔵 Criando instância do Echo...');
        echo = new Echo(echoConfig);
        log.info('✅ Echo criado com sucesso');

        log.info('🔵 Acessando Pusher connector...');
        const pusher = echo.connector.pusher;
        log.info('✅ Pusher connector acessado');

        log.info('📡 Pusher connection state:', pusher.connection.state);
        log.info('📡 Pusher socket_id:', pusher.connection.socket_id || 'null');

        // Eventos de conexão do Pusher
        log.info('🔵 Registrando event listeners do Pusher...');

        // Log de TODOS os eventos de state para debug
        echo.connector.pusher.connection.bind('state_change', (states) => {
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
        echo.connector.pusher.connection.bind('error', (error) => {
            log.error('❌ [CONNECTION ERROR]', JSON.stringify(error, null, 2));
            if (error.error) {
                log.error('   → Error object:', JSON.stringify(error.error, null, 2));
            }
            if (error.type) {
                log.error('   → Error type:', error.type);
            }
            if (error.data) {
                log.error('   → Error data:', JSON.stringify(error.data, null, 2));
            }
        });

        echo.connector.pusher.connection.bind('connected', () => {
            log.info('✅ Conectado ao servidor YumGo via Reverb/Pusher');
            isConnected = true;

            // Atualizar UI
            mainWindow.webContents.send('status', 'connected');
            updateTrayStatus(true);

            // Notificação
            showNotification('Conectado', 'YumGo Bridge conectado com sucesso!');
        });

        echo.connector.pusher.connection.bind('disconnected', () => {
            log.warn('❌ Desconectado do servidor');
            isConnected = false;

            mainWindow.webContents.send('status', 'disconnected');
            updateTrayStatus(false);
        });

        // Já temos error binding acima, não duplicar

        // Já temos state_change acima, remover duplicado

        // Inscrever no canal privado do restaurante
        const channelName = `private-restaurant.${restaurantId}`;
        log.info(`Inscrevendo no canal: ${channelName}`);

        const channel = echo.private(channelName);

        log.info(`📡 Tentando autenticar no canal privado...`);

        channel.error((error) => {
            log.error('❌ Erro ao inscrever no canal:', JSON.stringify(error, null, 2));
            mainWindow.webContents.send('status', 'error');
        });

        channel.listen('.order.created', async (data) => {
                log.info(`🔔 Novo pedido recebido: #${data.order.order_number}`);

                try {
                    const order = data.order;

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
                            await printerManager.printOrder(order, location);
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
        log.error('Erro ao inicializar Laravel Echo:', error);
        mainWindow.webContents.send('status', 'error');
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
    if (echo) {
        echo.disconnect();
        echo = null;
    }
    isConnected = false;
    currentToken = null;
    currentRestaurantId = null;
    mainWindow.webContents.send('status', 'disconnected');
    updateTrayStatus(false);
});

// Configurar impressora
ipcMain.on('configure-printer', async (event, config) => {
    try {
        if (!printerManager) {
            printerManager = new ThermalPrinter();
        }

        await printerManager.configurePrinter(config.location, config);

        // Salvar configuração
        const printers = store.get('printers', {});
        printers[config.location] = config;
        store.set('printers', printers);

        event.reply('printer-configured', {
            location: config.location,
            success: true
        });

        log.info(`Impressora ${config.location} configurada com sucesso`);

    } catch (error) {
        log.error('Erro ao configurar impressora:', error);
        event.reply('printer-configured', {
            location: config.location,
            success: false,
            error: error.message
        });
    }
});

// Teste de impressão
ipcMain.on('test-print', async (event, { location }) => {
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

        event.reply('test-print-result', {
            success: true,
            message: 'Impressão de teste enviada com sucesso!'
        });

    } catch (error) {
        log.error('Erro no teste de impressão:', error);
        event.reply('test-print-result', {
            success: false,
            error: error.message
        });
    }
});

// Buscar impressoras USB disponíveis
ipcMain.handle('find-usb-printers', async () => {
    try {
        const escpos = require('escpos');
        const USB = require('escpos-usb');

        const devices = USB.findPrinter();

        return devices.map(device => ({
            vendorId: device.deviceDescriptor.idVendor,
            productId: device.deviceDescriptor.idProduct,
            manufacturer: device.deviceDescriptor.iManufacturer,
            product: device.deviceDescriptor.iProduct
        }));

    } catch (error) {
        log.error('Erro ao buscar impressoras USB:', error);
        return [];
    }
});

// Limpar configuração
ipcMain.on('clear-config', () => {
    store.clear();
    app.relaunch();
    app.exit();
});

// ===== APP LIFECYCLE =====

app.whenReady().then(() => {
    createWindow();

    // Restaurar impressoras configuradas
    const printers = store.get('printers');
    if (printers) {
        printerManager = new ThermalPrinter();
        Object.entries(printers).forEach(async ([location, config]) => {
            try {
                await printerManager.configurePrinter(location, config);
                log.info(`Impressora ${location} restaurada`);
            } catch (error) {
                log.error(`Erro ao restaurar impressora ${location}:`, error);
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
    if (echo) {
        echo.disconnect();
    }
});

// Log não tratados
process.on('uncaughtException', (error) => {
    log.error('Erro não tratado:', error);
});

process.on('unhandledRejection', (error) => {
    log.error('Promise rejeitada:', error);
});
