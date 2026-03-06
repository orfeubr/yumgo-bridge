const { app, BrowserWindow, ipcMain, Menu, Tray, nativeImage } = require('electron');
const path = require('path');
const Store = require('electron-store');
const log = require('electron-log');
const Pusher = require('pusher-js');
const Echo = require('laravel-echo');
const axios = require('axios');
const ThermalPrinter = require('./printer');

// Configuração
const store = new Store();
const isDev = process.argv.includes('--dev');

// Estado global
let mainWindow;
let tray;
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

    const contextMenu = Menu.buildFromTemplate([
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

    tray.setContextMenu(contextMenu);
    tray.setToolTip('YumGo Bridge - Impressão Local');

    tray.on('click', () => {
        mainWindow.show();
    });
}

function updateTrayStatus(connected) {
    if (!tray) return;

    const contextMenu = tray.getContextMenu();
    const statusItem = contextMenu.getMenuItemById('status');

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

    log.info(`Conectando ao servidor... Restaurant ID: ${restaurantId}`);

    // Configurar URLs baseado no ambiente
    const baseUrl = isDev ? 'http://localhost:8000' : 'https://yumgo.com.br';
    const wsHost = isDev ? 'localhost' : 'ws.yumgo.com.br';  // Subdomínio WebSocket via Nginx SSL
    const wsPort = isDev ? 8081 : 443;  // HTTPS/443 em produção
    const wsPath = '';  // Empty - Pusher adds /app/{key} automatically

    try {
        // Configurar Laravel Echo com Pusher/Reverb
        echo = new Echo({
            broadcaster: 'reverb',
            key: 't9pg2dslmpl5y1cp6rrf',  // REVERB_APP_KEY from .env
            wsHost: wsHost,
            wsPort: wsPort,
            wssPort: wsPort,
            wsPath: wsPath,
            forceTLS: !isDev,  // TLS em produção via Nginx HTTPS
            encrypted: !isDev,
            disableStats: true,
            enabledTransports: isDev ? ['ws'] : ['wss'],  // WSS em produção
            authEndpoint: `${baseUrl}/api/broadcasting/auth`,
            auth: {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'X-Restaurant-ID': restaurantId
                }
            }
        });

        // Eventos de conexão do Pusher
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

        echo.connector.pusher.connection.bind('error', (error) => {
            log.error('Erro de conexão:', error);
            mainWindow.webContents.send('status', 'error');
        });

        echo.connector.pusher.connection.bind('state_change', (states) => {
            log.info(`Estado da conexão: ${states.previous} → ${states.current}`);

            if (states.current === 'connecting' || states.current === 'unavailable') {
                mainWindow.webContents.send('status', 'reconnecting');
            }
        });

        // Inscrever no canal privado do restaurante
        const channelName = `private-restaurant.${restaurantId}`;
        log.info(`Inscrevendo no canal: ${channelName}`);

        echo.private(channelName)
            .listen('.order.created', async (data) => {
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
            })
            .error((error) => {
                log.error('Erro ao inscrever no canal:', error);
                mainWindow.webContents.send('status', 'error');
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
