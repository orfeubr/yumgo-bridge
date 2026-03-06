const { app, BrowserWindow, ipcMain, Menu, Tray, nativeImage } = require('electron');
const path = require('path');
const Store = require('electron-store');
const log = require('electron-log');
const io = require('socket.io-client');
const ThermalPrinter = require('./printer');

// Configuração
const store = new Store();
const isDev = process.argv.includes('--dev');

// Estado global
let mainWindow;
let tray;
let socket;
let printerManager;
let isConnected = false;

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

// ===== WEBSOCKET =====

function connectWebSocket(restaurantId, token) {
    if (socket && socket.connected) {
        socket.disconnect();
    }

    log.info(`Conectando ao servidor... Restaurant ID: ${restaurantId}`);

    const serverUrl = isDev
        ? 'http://localhost:8000'
        : 'https://yumgo.com.br';

    socket = io(serverUrl, {
        transports: ['websocket', 'polling'],
        auth: {
            token: token,
            restaurant_id: restaurantId,
            type: 'bridge-app'
        },
        reconnection: true,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 5000,
        reconnectionAttempts: Infinity
    });

    // Eventos de conexão
    socket.on('connect', () => {
        log.info('✅ Conectado ao servidor YumGo');
        isConnected = true;

        // Inscrever no canal do restaurante
        socket.emit('subscribe', `restaurant.${restaurantId}.orders`);

        // Atualizar UI
        mainWindow.webContents.send('status', 'connected');
        updateTrayStatus(true);

        // Notificação
        showNotification('Conectado', 'YumGo Bridge conectado com sucesso!');
    });

    socket.on('disconnect', (reason) => {
        log.warn(`❌ Desconectado do servidor. Motivo: ${reason}`);
        isConnected = false;

        mainWindow.webContents.send('status', 'disconnected');
        updateTrayStatus(false);
    });

    socket.on('connect_error', (error) => {
        log.error('Erro de conexão:', error.message);
        mainWindow.webContents.send('status', 'error');
    });

    socket.on('reconnecting', (attemptNumber) => {
        log.info(`Tentando reconectar... Tentativa ${attemptNumber}`);
        mainWindow.webContents.send('status', 'reconnecting');
    });

    // Evento de novo pedido
    socket.on('new-order', async (data) => {
        log.info(`🔔 Novo pedido recebido: #${data.order_number}`);

        try {
            // Tocar som de notificação
            mainWindow.webContents.send('play-sound');

            // Mostrar notificação
            showNotification(
                `Novo Pedido #${data.order_number}`,
                `Cliente: ${data.customer.name}\nTotal: R$ ${data.totals.total.toFixed(2)}`
            );

            // Imprimir em todas as impressoras configuradas
            if (printerManager) {
                for (const location of data.print_locations) {
                    await printerManager.printOrder(data, location);
                }
            }

            // Atualizar lista de pedidos na UI
            mainWindow.webContents.send('new-order', data);

        } catch (error) {
            log.error('Erro ao processar pedido:', error);
            mainWindow.webContents.send('print-error', {
                order_id: data.order_id,
                error: error.message
            });
        }
    });
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
    if (socket) {
        socket.disconnect();
    }
    isConnected = false;
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
    if (socket) {
        socket.disconnect();
    }
});

// Log não tratados
process.on('uncaughtException', (error) => {
    log.error('Erro não tratado:', error);
});

process.on('unhandledRejection', (error) => {
    log.error('Promise rejeitada:', error);
});
