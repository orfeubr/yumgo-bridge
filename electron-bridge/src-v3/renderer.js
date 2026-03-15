// YumGo Bridge v3.0 - Renderer Process (Interface)

const { ipcRenderer } = require('electron');

// ===== STATE =====
let currentTab = 'home';
let logs = [];
let orderHistory = [];
let printerConfigs = {
    kitchen: null,
    bar: null,
    counter: null
};

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initPrinterConfigs();
    initEventListeners();
    loadAppVersion();
    loadSystemInfo();

    addLog('info', 'YumGo Bridge v3.0 iniciado');
});

// ===== TABS NAVIGATION =====
function initTabs() {
    const tabItems = document.querySelectorAll('.tab-item');

    tabItems.forEach(item => {
        item.addEventListener('click', () => {
            const tabName = item.getAttribute('data-tab');
            switchTab(tabName);
        });
    });
}

function switchTab(tabName) {
    // Atualizar sidebar
    document.querySelectorAll('.tab-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

    // Atualizar conteúdo
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(`tab-${tabName}`).classList.add('active');

    currentTab = tabName;
    addLog('info', `Navegou para aba: ${tabName}`);
}

// ===== PRINTER CONFIGURATION =====
function initPrinterConfigs() {
    createPrinterConfig('kitchen', 'Cozinha');
    createPrinterConfig('bar', 'Bar');
    createPrinterConfig('counter', 'Balcão');
}

function createPrinterConfig(location, label) {
    const container = document.getElementById('printers-container');

    const html = `
        <div class="card printer-card" id="${location}-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">🖨️ ${label}</h2>
                <span id="${location}StatusBadge" class="status-badge" style="display: none;">✅ Configurada</span>
            </div>

            <!-- Tipo de impressora -->
            <div class="form-group">
                <label>Tipo de Impressora</label>
                <select id="${location}Type" onchange="updatePrinterFields('${location}')">
                    <option value="">Selecione</option>
                    <option value="system">🖨️ Sistema (Recomendado)</option>
                    <option value="usb">🔌 USB Direta</option>
                    <option value="network">🌐 Rede (IP)</option>
                </select>
            </div>

            <!-- Campos dinâmicos -->
            <div id="${location}Fields"></div>

            <!-- Configurações Avançadas -->
            <div class="advanced-config" id="${location}Advanced" style="display:none;">
                <h3>⚙️ Configurações Avançadas</h3>

                <!-- Caracteres por linha -->
                <div class="form-group">
                    <label>📏 Caracteres por linha</label>
                    <input
                        type="range"
                        id="${location}CharsPerLine"
                        min="32"
                        max="48"
                        value="48"
                        oninput="updateCharsLabel('${location}')"
                        style="width: 100%;"
                    >
                    <div style="text-align: center; margin-top: 8px;">
                        <strong id="${location}CharsLabel" style="font-size: 24px; color: var(--primary);">48</strong>
                        <span style="color: var(--text-secondary);"> caracteres</span>
                    </div>
                    <small>
                        💡 <strong>32-38:</strong> Papel 58mm (compacto)<br>
                        💡 <strong>42-48:</strong> Papel 80mm (padrão)
                    </small>
                </div>

                <!-- Espaçamento -->
                <div class="form-group">
                    <label>📐 Espaçamento</label>
                    <select id="${location}Spacing">
                        <option value="compact">Compacto (mais conteúdo)</option>
                        <option value="normal" selected>Normal (recomendado)</option>
                        <option value="spacious">Espaçado (melhor leitura)</option>
                    </select>
                </div>

                <!-- Cópias -->
                <div class="form-group">
                    <label>📋 Número de cópias</label>
                    <select id="${location}Copies">
                        <option value="1">1 via</option>
                        <option value="2" selected>2 vias</option>
                        <option value="3">3 vias</option>
                        <option value="4">4 vias</option>
                    </select>
                </div>

                <!-- Tamanho da fonte -->
                <div class="form-group">
                    <label>🔤 Tamanho da fonte</label>
                    <select id="${location}FontSize">
                        <option value="small">Pequeno</option>
                        <option value="normal" selected>Normal</option>
                        <option value="large">Grande</option>
                    </select>
                </div>

                <!-- Remover acentos -->
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="${location}RemoveAccents">
                        <span>✂️ Remover acentos (impressoras antigas)</span>
                    </label>
                </div>

                <!-- Logo -->
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="${location}PrintLogo">
                        <span>🖼️ Imprimir logo do restaurante</span>
                    </label>
                </div>
            </div>

            <!-- Botões -->
            <button class="btn btn-primary" onclick="savePrinter('${location}')">
                Salvar Configuração
            </button>
            <button class="btn btn-secondary" onclick="testPrinter('${location}')">
                Testar Impressão
            </button>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
}

function updateCharsLabel(location) {
    const slider = document.getElementById(`${location}CharsPerLine`);
    const label = document.getElementById(`${location}CharsLabel`);
    if (slider && label) {
        label.textContent = slider.value;
    }
}

function updatePrinterFields(location) {
    const type = document.getElementById(`${location}Type`).value;
    const fields = document.getElementById(`${location}Fields`);
    const advanced = document.getElementById(`${location}Advanced`);

    if (!type) {
        fields.innerHTML = '';
        advanced.style.display = 'none';
        return;
    }

    // Mostrar config avançada
    advanced.style.display = 'block';

    // Campos específicos por tipo
    if (type === 'system') {
        fields.innerHTML = `
            <div class="form-group">
                <label>Impressora do Sistema</label>
                <button class="btn btn-primary" onclick="findSystemPrinters('${location}')">
                    🖨️ Detectar Impressoras Instaladas
                </button>
                <select id="${location}PrinterName" style="margin-top: 8px;">
                    <option value="">Aguardando detecção...</option>
                </select>
                <small>Funciona com qualquer impressora instalada no Windows</small>
            </div>
        `;
    } else if (type === 'usb') {
        fields.innerHTML = `
            <div class="form-group">
                <label>Impressora USB</label>
                <button class="btn btn-primary" onclick="findUSBPrinters('${location}')">
                    🔌 Buscar Impressoras USB
                </button>
                <select id="${location}USBSelect" style="margin-top: 8px;">
                    <option value="">Aguardando detecção...</option>
                </select>
                <input type="hidden" id="${location}VendorId">
                <input type="hidden" id="${location}ProductId">
                <small>Apenas para impressoras térmicas conectadas via USB</small>
            </div>
        `;
    } else if (type === 'network') {
        fields.innerHTML = `
            <div class="form-group">
                <label>Endereço IP</label>
                <input type="text" id="${location}Ip" placeholder="192.168.1.100">
                <small>IP da impressora na rede local</small>
            </div>
            <div class="form-group">
                <label>Porta</label>
                <input type="number" id="${location}Port" value="9100">
                <small>Porta padrão: 9100</small>
            </div>
        `;
    }
}

async function findSystemPrinters(location) {
    addLog('info', `Buscando impressoras do sistema para ${location}...`);

    try {
        const printers = await ipcRenderer.invoke('find-system-printers');
        const select = document.getElementById(`${location}PrinterName`);

        if (!printers || printers.length === 0) {
            select.innerHTML = '<option value="">Nenhuma impressora encontrada</option>';
            addLog('warn', 'Nenhuma impressora do sistema encontrada');
            return;
        }

        select.innerHTML = '<option value="">Selecione uma impressora</option>';
        printers.forEach(printer => {
            const option = document.createElement('option');
            option.value = printer.name;
            option.textContent = printer.label || printer.name;
            select.appendChild(option);
        });

        addLog('info', `${printers.length} impressora(s) encontrada(s)`);
    } catch (error) {
        addLog('error', `Erro ao buscar impressoras: ${error.message}`);
    }
}

async function findUSBPrinters(location) {
    addLog('info', `Buscando impressoras USB para ${location}...`);

    try {
        const printers = await ipcRenderer.invoke('find-usb-printers');
        const select = document.getElementById(`${location}USBSelect`);

        if (!printers || printers.length === 0) {
            select.innerHTML = '<option value="">Nenhuma impressora USB encontrada</option>';
            addLog('warn', 'Nenhuma impressora USB encontrada');
            return;
        }

        select.innerHTML = '<option value="">Selecione uma impressora</option>';
        printers.forEach(printer => {
            const option = document.createElement('option');
            option.value = JSON.stringify({ vendorId: printer.vendorId, productId: printer.productId });
            option.textContent = printer.displayName;
            select.appendChild(option);

            // Listener para preencher campos ocultos
            select.addEventListener('change', (e) => {
                if (e.target.value) {
                    const { vendorId, productId } = JSON.parse(e.target.value);
                    document.getElementById(`${location}VendorId`).value = vendorId;
                    document.getElementById(`${location}ProductId`).value = productId;
                }
            });
        });

        addLog('info', `${printers.length} impressora(s) USB encontrada(s)`);
    } catch (error) {
        addLog('error', `Erro ao buscar impressoras USB: ${error.message}`);
    }
}

async function savePrinter(location) {
    const typeEl = document.getElementById(`${location}Type`);
    if (!typeEl || !typeEl.value) {
        alert('Selecione o tipo de impressora primeiro');
        return;
    }

    const config = {
        type: typeEl.value,
        charsPerLine: parseInt(document.getElementById(`${location}CharsPerLine`).value),
        spacing: document.getElementById(`${location}Spacing`).value,
        copies: parseInt(document.getElementById(`${location}Copies`).value),
        fontSize: document.getElementById(`${location}FontSize`).value,
        removeAccents: document.getElementById(`${location}RemoveAccents`).checked,
        printLogo: document.getElementById(`${location}PrintLogo`).checked,
    };

    // Campos específicos
    if (config.type === 'system') {
        const nameEl = document.getElementById(`${location}PrinterName`);
        if (!nameEl || !nameEl.value) {
            alert('Selecione uma impressora do sistema');
            return;
        }
        config.printerName = nameEl.value;
    } else if (config.type === 'usb') {
        config.vendorId = document.getElementById(`${location}VendorId`).value;
        config.productId = document.getElementById(`${location}ProductId`).value;
        if (!config.vendorId || !config.productId) {
            alert('Selecione uma impressora USB');
            return;
        }
    } else if (config.type === 'network') {
        config.ip = document.getElementById(`${location}Ip`).value;
        config.port = parseInt(document.getElementById(`${location}Port`).value);
        if (!config.ip) {
            alert('Preencha o endereço IP');
            return;
        }
    }

    try {
        await ipcRenderer.invoke('save-printer', location, config);
        printerConfigs[location] = config;
        addLog('info', `✅ Impressora ${location} salva: ${config.charsPerLine} chars/linha, ${config.spacing} spacing`);
        alert(`✅ Impressora ${location} configurada com sucesso!`);
        updatePrinterStatusBadge(location);  // Atualiza badge
        updateHomeStatus();
    } catch (error) {
        addLog('error', `Erro ao salvar ${location}: ${error.message}`);
        alert(`❌ Erro ao salvar: ${error.message}`);
    }
}

// Atualiza badge de status da impressora
function updatePrinterStatusBadge(location) {
    const badge = document.getElementById(`${location}StatusBadge`);
    if (badge) {
        if (printerConfigs[location] !== null) {
            badge.style.display = 'inline-block';  // Mostra "✅ Configurada"
        } else {
            badge.style.display = 'none';  // Esconde
        }
    }
}

async function testPrinter(location) {
    addLog('info', `Testando impressão em ${location}...`);

    try {
        await ipcRenderer.invoke('test-print', location);
        addLog('info', `✅ Teste de impressão enviado para ${location}`);
        alert(`✅ Teste de impressão enviado!\nVerifique se imprimiu na impressora ${location}.`);
    } catch (error) {
        addLog('error', `Erro ao testar ${location}: ${error.message}`);
        alert(`❌ Erro: ${error.message}`);
    }
}

// ===== CONNECTION =====
function initEventListeners() {
    // Conexão
    document.getElementById('connectBtn')?.addEventListener('click', connect);
    document.getElementById('disconnectBtn')?.addEventListener('click', disconnect);
    document.getElementById('toggleToken')?.addEventListener('click', toggleTokenVisibility);

    // Autostart
    document.getElementById('autostartCheckbox')?.addEventListener('change', toggleAutostart);

    // Preferências
    document.getElementById('soundEnabledCheckbox')?.addEventListener('change', savePreferences);
    document.getElementById('notificationsEnabledCheckbox')?.addEventListener('change', savePreferences);

    // Testes
    document.getElementById('testWebSocketBtn')?.addEventListener('click', testWebSocket);
    document.getElementById('testPrintBtn')?.addEventListener('click', testPrintAll);

    // Histórico
    document.getElementById('clearHistoryBtn')?.addEventListener('click', clearHistory);

    // Logs
    document.getElementById('clearLogsBtn')?.addEventListener('click', clearLogs);
    document.getElementById('exportLogsBtn')?.addEventListener('click', exportLogs);

    // Filtros de log
    document.querySelectorAll('.btn-filter').forEach(btn => {
        btn.addEventListener('click', (e) => {
            document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
            filterLogs(e.target.getAttribute('data-level'));
        });
    });

    // Botões da window
    document.getElementById('minimizeBtn')?.addEventListener('click', () => {
        ipcRenderer.send('minimize-window');
    });

    document.getElementById('closeBtn')?.addEventListener('click', () => {
        ipcRenderer.send('close-window');
    });
}

async function connect() {
    const restaurantId = document.getElementById('restaurantId').value.trim();
    const token = document.getElementById('token').value.trim();

    if (!restaurantId || !token) {
        alert('Preencha o ID do restaurante e o token');
        return;
    }

    addLog('info', `Conectando ao restaurante ${restaurantId}...`);

    try {
        await ipcRenderer.invoke('connect', { restaurantId, token });
        addLog('info', 'Conexão iniciada');
    } catch (error) {
        addLog('error', `Erro ao conectar: ${error.message}`);
    }
}

async function disconnect() {
    if (confirm('Desconectar do servidor?')) {
        await ipcRenderer.invoke('disconnect');
        addLog('info', 'Desconectado');
    }
}

function toggleTokenVisibility() {
    const input = document.getElementById('token');
    const btn = document.getElementById('toggleToken');
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
}

async function toggleAutostart() {
    const checkbox = document.getElementById('autostartCheckbox');
    const enabled = checkbox.checked;

    try {
        await ipcRenderer.invoke('set-autostart', enabled);
        addLog('info', `Autostart ${enabled ? 'habilitado' : 'desabilitado'}`);
        updateHomeStatus();
    } catch (error) {
        addLog('error', `Erro ao alterar autostart: ${error.message}`);
        checkbox.checked = !enabled;
    }
}

function savePreferences() {
    const prefs = {
        soundEnabled: document.getElementById('soundEnabledCheckbox').checked,
        notificationsEnabled: document.getElementById('notificationsEnabledCheckbox').checked
    };

    ipcRenderer.send('save-preferences', prefs);
    addLog('info', 'Preferências salvas');
}

// ===== TESTS =====
async function testWebSocket() {
    const resultDiv = document.getElementById('websocketTestResult');
    resultDiv.classList.remove('hidden');
    resultDiv.className = 'test-result';
    resultDiv.textContent = '⏳ Testando conexão...';

    try {
        const result = await ipcRenderer.invoke('test-websocket');
        resultDiv.className = 'test-result success';
        resultDiv.textContent = `✅ ${result.message}`;
        addLog('info', 'Teste de WebSocket: OK');
    } catch (error) {
        resultDiv.className = 'test-result error';
        resultDiv.textContent = `❌ ${error.message}`;
        addLog('error', `Teste de WebSocket falhou: ${error.message}`);
    }
}

async function testPrintAll() {
    const resultDiv = document.getElementById('printTestResult');
    resultDiv.classList.remove('hidden');
    resultDiv.className = 'test-result';
    resultDiv.textContent = '⏳ Enviando teste para todas as impressoras...';

    try {
        await ipcRenderer.invoke('test-print-all');
        resultDiv.className = 'test-result success';
        resultDiv.textContent = '✅ Teste enviado para todas as impressoras configuradas!';
        addLog('info', 'Teste de impressão enviado para todas');
    } catch (error) {
        resultDiv.className = 'test-result error';
        resultDiv.textContent = `❌ ${error.message}`;
        addLog('error', `Teste de impressão falhou: ${error.message}`);
    }
}

// ===== LOGS =====
function addLog(level, message) {
    const timestamp = new Date().toLocaleTimeString('pt-BR');
    const log = { level, message, timestamp };
    logs.push(log);

    // Limitar a 1000 logs
    if (logs.length > 1000) {
        logs.shift();
    }

    // Atualizar UI
    const container = document.getElementById('logsContainer');
    const entry = document.createElement('div');
    entry.className = `log-entry ${level}`;
    entry.innerHTML = `
        <span class="log-time">${timestamp}</span>
        <span class="log-level">${level.toUpperCase()}</span>
        <span class="log-message">${message}</span>
    `;

    container.insertBefore(entry, container.firstChild);

    // Limitar logs visíveis a 100
    while (container.children.length > 100) {
        container.removeChild(container.lastChild);
    }

    console.log(`[${level.toUpperCase()}] ${message}`);
}

function clearLogs() {
    if (confirm('Limpar todos os logs?')) {
        logs = [];
        document.getElementById('logsContainer').innerHTML = '';
        addLog('info', 'Logs limpos');
    }
}

function exportLogs() {
    const text = logs.map(log => `[${log.timestamp}] [${log.level.toUpperCase()}] ${log.message}`).join('\n');
    const blob = new Blob([text], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `yumgo-bridge-logs-${Date.now()}.txt`;
    a.click();
    addLog('info', 'Logs exportados');
}

function filterLogs(level) {
    const entries = document.querySelectorAll('.log-entry');
    entries.forEach(entry => {
        if (level === 'all' || entry.classList.contains(level)) {
            entry.style.display = 'flex';
        } else {
            entry.style.display = 'none';
        }
    });
}

// ===== HISTORY =====
function addToHistory(order) {
    orderHistory.unshift({
        id: order.id,
        number: order.order_number,
        customer: order.customer.name,
        total: order.totals.total,
        timestamp: new Date()
    });

    // Limitar a 50
    if (orderHistory.length > 50) {
        orderHistory.pop();
    }

    updateHistory();
    updateHomeRecent();
}

function updateHistory() {
    const list = document.getElementById('historyList');

    if (orderHistory.length === 0) {
        list.innerHTML = '<p class="empty-state">Nenhum pedido impresso ainda</p>';
        return;
    }

    list.innerHTML = orderHistory.map(order => `
        <div class="order-card">
            <div class="order-header">
                <div class="order-number">#${order.number}</div>
                <div class="order-time">${order.timestamp.toLocaleTimeString('pt-BR')}</div>
            </div>
            <div class="order-customer">${order.customer}</div>
            <div class="order-total">R$ ${order.total.toFixed(2)}</div>
        </div>
    `).join('');
}

function updateHomeRecent() {
    const container = document.getElementById('recentOrders');
    const recent = orderHistory.slice(0, 5);

    if (recent.length === 0) {
        container.innerHTML = '<p class="empty-state">Nenhum pedido recebido ainda</p>';
        return;
    }

    container.innerHTML = recent.map(order => `
        <div class="order-card">
            <div class="order-header">
                <div class="order-number">#${order.number}</div>
                <div class="order-time">${order.timestamp.toLocaleTimeString('pt-BR')}</div>
            </div>
            <div class="order-customer">${order.customer}</div>
            <div class="order-total">R$ ${order.total.toFixed(2)}</div>
        </div>
    `).join('');
}

function clearHistory() {
    if (confirm('Limpar todo o histórico?')) {
        orderHistory = [];
        updateHistory();
        updateHomeRecent();
        addLog('info', 'Histórico limpo');
    }
}

// ===== HOME STATUS =====
function updateHomeStatus() {
    // Atualizar contadores
    const printersConfigured = Object.values(printerConfigs).filter(c => c !== null).length;
    document.getElementById('homePrintersStatus').textContent = `${printersConfigured} configuradas`;

    const ordersToday = orderHistory.filter(o => {
        const today = new Date().toDateString();
        return o.timestamp.toDateString() === today;
    }).length;
    document.getElementById('homeOrdersCount').textContent = ordersToday;
}

// ===== IPC LISTENERS =====

// Status de conexão
ipcRenderer.on('connection-status', (event, status) => {
    const dot = document.querySelector('.status-dot');
    const text = document.querySelector('.connection-status span');
    const homeStatus = document.getElementById('homeConnectionStatus');

    dot.className = 'status-dot';

    if (status === 'connected') {
        dot.classList.add('connected');
        text.textContent = 'Conectado';
        homeStatus.textContent = 'Conectado ✅';
        homeStatus.style.color = 'var(--success)';
        addLog('info', '✅ Conectado ao servidor YumGo');
    } else if (status === 'disconnected') {
        dot.classList.add('disconnected');
        text.textContent = 'Desconectado';
        homeStatus.textContent = 'Desconectado';
        homeStatus.style.color = 'var(--error)';
        addLog('warn', 'Desconectado do servidor');
    } else if (status === 'reconnecting') {
        dot.classList.add('reconnecting');
        text.textContent = 'Reconectando...';
        homeStatus.textContent = 'Reconectando...';
        homeStatus.style.color = 'var(--warning)';
        addLog('warn', 'Tentando reconectar...');
    }
});

// Novo pedido
ipcRenderer.on('new-order', (event, order) => {
    addLog('info', `🔔 Novo pedido #${order.order_number} - ${order.customer.name}`);
    addToHistory(order);
    updateHomeStatus();
});

// Restaurar configurações
ipcRenderer.on('restore-config', (event, config) => {
    document.getElementById('restaurantId').value = config.restaurantId || '';
    document.getElementById('token').value = config.token || '';
});

ipcRenderer.on('restore-printers', (event, configs) => {
    printerConfigs = configs;

    // Atualizar badges de status de cada impressora
    Object.keys(printerConfigs).forEach(location => {
        updatePrinterStatusBadge(location);
    });

    updateHomeStatus();
});

ipcRenderer.on('restore-preferences', (event, prefs) => {
    document.getElementById('soundEnabledCheckbox').checked = prefs.soundEnabled !== false;
    document.getElementById('notificationsEnabledCheckbox').checked = prefs.notificationsEnabled !== false;
});

ipcRenderer.on('autostart-status', (event, enabled) => {
    document.getElementById('autostartCheckbox').checked = enabled;
    document.getElementById('homeAutostartStatus').textContent = enabled ? 'Habilitado ✅' : 'Desabilitado';
});

// ===== SYSTEM INFO =====
async function loadAppVersion() {
    const version = await ipcRenderer.invoke('get-app-version');
    document.getElementById('appVersion').textContent = version;
    document.getElementById('systemVersion').textContent = version;
}

async function loadSystemInfo() {
    const os = await ipcRenderer.invoke('get-system-os');
    const electron = await ipcRenderer.invoke('get-electron-version');
    const node = await ipcRenderer.invoke('get-node-version');

    document.getElementById('systemOs').textContent = os;
    document.getElementById('systemElectron').textContent = electron;
    document.getElementById('systemNode').textContent = node;
}

// Expor funções globais para onclick
window.updatePrinterFields = updatePrinterFields;
window.updateCharsLabel = updateCharsLabel;
window.findSystemPrinters = findSystemPrinters;
window.findUSBPrinters = findUSBPrinters;
window.savePrinter = savePrinter;
window.testPrinter = testPrinter;
