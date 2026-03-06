const { ipcRenderer } = require('electron');

// ===== ELEMENTOS DO DOM =====
const statusIndicator = document.getElementById('statusIndicator');
const configCard = document.getElementById('configCard');
const printersCard = document.getElementById('printersCard');
const ordersCard = document.getElementById('ordersCard');
const ordersList = document.getElementById('ordersList');
const connectBtn = document.getElementById('connectBtn');
const disconnectBtn = document.getElementById('disconnectBtn');
const restaurantIdInput = document.getElementById('restaurantId');
const tokenInput = document.getElementById('token');
const notificationSound = document.getElementById('notificationSound');

// ===== CONEXÃO =====

async function pasteRestaurantId() {
    try {
        const text = await navigator.clipboard.readText();
        restaurantIdInput.value = text.trim();
        alert('ID do restaurante colado com sucesso!');
    } catch (err) {
        alert('Erro ao colar: ' + err.message + '\n\nTente usar Ctrl+V manualmente.');
    }
}

async function pasteToken() {
    try {
        const text = await navigator.clipboard.readText();
        tokenInput.value = text.trim();
        alert('Token colado com sucesso!');
    } catch (err) {
        alert('Erro ao colar: ' + err.message + '\n\nTente usar Ctrl+V manualmente.');
    }
}

function toggleTokenVisibility() {
    const type = tokenInput.getAttribute('type');
    if (type === 'password') {
        tokenInput.setAttribute('type', 'text');
    } else {
        tokenInput.setAttribute('type', 'password');
    }
}

function connect() {
    console.log('🔵 Função connect() chamada');

    const restaurantId = restaurantIdInput.value.trim();
    const token = tokenInput.value.trim();

    console.log('Restaurant ID:', restaurantId ? 'OK' : 'VAZIO');
    console.log('Token:', token ? 'OK (length: ' + token.length + ')' : 'VAZIO');

    if (!restaurantId || !token) {
        console.log('❌ Campos vazios - mostrando alerta');
        alert('Por favor, preencha todos os campos');
        return;
    }

    console.log('✅ Enviando credenciais para main process...');
    ipcRenderer.send('connect', { restaurantId, token });

    connectBtn.disabled = true;
    connectBtn.textContent = 'Conectando...';
    console.log('✅ Botão atualizado para "Conectando..."');
}

function disconnect() {
    if (confirm('Deseja realmente desconectar?')) {
        ipcRenderer.send('disconnect');
    }
}

// Restaurar configuração salva
ipcRenderer.on('restore-config', (event, config) => {
    restaurantIdInput.value = config.restaurantId || '';
    tokenInput.value = config.token || '';
});

// Status de conexão
ipcRenderer.on('status', (event, status) => {
    const dot = statusIndicator.querySelector('.status-dot');
    const text = statusIndicator.querySelector('span:last-child');

    dot.className = 'status-dot';
    connectBtn.disabled = false;

    switch(status) {
        case 'connected':
            dot.classList.add('connected');
            text.textContent = 'Conectado ✅';
            connectBtn.classList.add('hidden');
            disconnectBtn.classList.remove('hidden');
            printersCard.classList.remove('hidden');
            ordersCard.classList.remove('hidden');
            break;

        case 'disconnected':
            dot.classList.add('disconnected');
            text.textContent = 'Desconectado';
            connectBtn.classList.remove('hidden');
            connectBtn.textContent = 'Conectar';
            disconnectBtn.classList.add('hidden');
            break;

        case 'reconnecting':
            dot.classList.add('reconnecting');
            text.textContent = 'Reconectando...';
            break;

        case 'error':
            dot.classList.add('disconnected');
            text.textContent = 'Erro de conexão - Verifique as credenciais';
            connectBtn.textContent = 'Tentar novamente';
            // Removido alert infinito - mensagem está no status
            break;
    }
});

// ===== IMPRESSORAS =====

function updatePrinterFields(location) {
    const type = document.getElementById(`${location}Type`).value;
    const fieldsDiv = document.getElementById(`${location}Fields`);

    if (!type) {
        fieldsDiv.innerHTML = '';
        return;
    }

    if (type === 'usb') {
        fieldsDiv.innerHTML = `
            <div class="form-group">
                <label>Impressora USB</label>
                <select id="${location}PrinterSelect" onchange="selectPrinter('${location}')" style="width: 100%; padding: 8px; margin-bottom: 10px;">
                    <option value="">Clique em "Buscar" abaixo</option>
                </select>
            </div>
            <button class="btn btn-secondary" onclick="findUSBPrinters('${location}')">
                🔍 Buscar Impressoras USB
            </button>

            <!-- Campos técnicos escondidos (preenchidos automaticamente) -->
            <input type="hidden" id="${location}VendorId">
            <input type="hidden" id="${location}ProductId">

            <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 4px; font-size: 12px; color: #666;">
                💡 <strong>Dica:</strong> Conecte sua impressora USB e clique em "Buscar" para detectar automaticamente.
            </div>
        `;
    } else if (type === 'network') {
        fieldsDiv.innerHTML = `
            <div class="form-group">
                <label>Endereço IP</label>
                <input type="text" id="${location}Ip" placeholder="Ex: 192.168.1.100">
            </div>
            <div class="form-group">
                <label>Porta</label>
                <input type="number" id="${location}Port" value="9100" placeholder="9100">
            </div>
        `;
    }
}

// Armazena lista de impressoras encontradas por localização
const foundPrinters = {};

async function findUSBPrinters(location) {
    try {
        const printers = await ipcRenderer.invoke('find-usb-printers');
        const select = document.getElementById(`${location}PrinterSelect`);

        if (printers.length === 0) {
            alert('❌ Nenhuma impressora USB encontrada.\n\n' +
                  'Certifique-se de que:\n' +
                  '• A impressora está conectada via USB\n' +
                  '• A impressora está ligada\n' +
                  '• Os drivers estão instalados');
            return;
        }

        // Armazena lista para uso posterior
        foundPrinters[location] = printers;

        // Limpa e preenche o select
        select.innerHTML = '<option value="">Selecione uma impressora</option>';

        printers.forEach((printer, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = `📄 ${printer.displayName}`;
            select.appendChild(option);
        });

        // Mensagem de sucesso
        alert(`✅ ${printers.length} impressora(s) encontrada(s)!\n\n` +
              'Selecione uma impressora na lista acima.');

    } catch (error) {
        alert('Erro ao buscar impressoras: ' + error.message);
    }
}

// Nova função para quando o usuário seleciona uma impressora
function selectPrinter(location) {
    const select = document.getElementById(`${location}PrinterSelect`);
    const selectedIndex = select.value;

    if (!selectedIndex || selectedIndex === '') {
        return;
    }

    const printer = foundPrinters[location][parseInt(selectedIndex)];

    if (!printer) {
        return;
    }

    // Preenche os campos escondidos automaticamente
    document.getElementById(`${location}VendorId`).value = `0x${printer.vendorId.toString(16).padStart(4, '0')}`;
    document.getElementById(`${location}ProductId`).value = `0x${printer.productId.toString(16).padStart(4, '0')}`;

    console.log(`Impressora selecionada: ${printer.displayName}`);
    console.log(`Vendor ID: 0x${printer.vendorId.toString(16).padStart(4, '0')}`);
    console.log(`Product ID: 0x${printer.productId.toString(16).padStart(4, '0')}`);
}

function configurePrinter(location) {
    const type = document.getElementById(`${location}Type`).value;

    if (!type) {
        alert('Selecione o tipo de impressora');
        return;
    }

    let config = {
        location: location,
        type: type
    };

    if (type === 'usb') {
        const vendorId = document.getElementById(`${location}VendorId`).value;
        const productId = document.getElementById(`${location}ProductId`).value;

        if (!vendorId || !productId) {
            alert('Preencha Vendor ID e Product ID');
            return;
        }

        config.vendorId = parseInt(vendorId);
        config.productId = parseInt(productId);

    } else if (type === 'network') {
        const ip = document.getElementById(`${location}Ip`).value;
        const port = document.getElementById(`${location}Port`).value;

        if (!ip) {
            alert('Preencha o endereço IP');
            return;
        }

        config.ip = ip;
        config.port = parseInt(port) || 9100;
    }

    ipcRenderer.send('configure-printer', config);
}

ipcRenderer.on('printer-configured', (event, result) => {
    if (result.success) {
        const statusEl = document.getElementById(`${result.location}Status`);
        statusEl.textContent = 'Configurada ✓';
        statusEl.className = 'printer-status configured';
        alert(`Impressora ${result.location} configurada com sucesso!`);
    } else {
        alert(`Erro ao configurar impressora: ${result.error}`);
    }
});

function testPrint(location) {
    ipcRenderer.send('test-print', { location });
}

ipcRenderer.on('test-print-result', (event, result) => {
    if (result.success) {
        alert(result.message);
    } else {
        alert(`Erro no teste: ${result.error}`);
    }
});

// ===== PEDIDOS =====

ipcRenderer.on('new-order', (event, order) => {
    // Adicionar pedido na lista
    const orderEl = document.createElement('div');
    orderEl.className = 'order-item';
    orderEl.innerHTML = `
        <strong>Pedido #${order.order_number}</strong><br>
        Cliente: ${order.customer.name}<br>
        Total: R$ ${order.totals.total.toFixed(2)}<br>
        <small>${new Date(order.created_at).toLocaleString('pt-BR')}</small>
    `;

    // Remover mensagem "Nenhum pedido"
    if (ordersList.children.length === 1 && ordersList.children[0].tagName === 'P') {
        ordersList.innerHTML = '';
    }

    ordersList.insertBefore(orderEl, ordersList.firstChild);

    // Limitar a 10 pedidos
    if (ordersList.children.length > 10) {
        ordersList.removeChild(ordersList.lastChild);
    }
});

ipcRenderer.on('print-error', (event, data) => {
    alert(`Erro ao imprimir pedido #${data.order_id}: ${data.error}`);
});

// Tocar som de notificação
ipcRenderer.on('play-sound', () => {
    // Som desabilitado - notificações do sistema já têm som
    // notificationSound.play().catch(err => {
    //     console.error('Erro ao tocar som:', err);
    // });
});

// ===== INICIALIZAÇÃO =====

document.addEventListener('DOMContentLoaded', () => {
    console.log('YumGo Bridge iniciado');
    console.log('Verificando funções globais:');
    console.log('- connect:', typeof connect);
    console.log('- disconnect:', typeof disconnect);
    console.log('Elementos DOM:');
    console.log('- connectBtn:', connectBtn ? 'OK' : 'NULL');
    console.log('- restaurantIdInput:', restaurantIdInput ? 'OK' : 'NULL');
    console.log('- tokenInput:', tokenInput ? 'OK' : 'NULL');
});
