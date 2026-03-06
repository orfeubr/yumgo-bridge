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
                <label>Impressora</label>
                <select id="${location}PrinterSelect" onchange="selectPrinter('${location}')" style="width: 100%; padding: 8px; margin-bottom: 10px;">
                    <option value="">Selecione uma impressora abaixo</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button class="btn btn-primary" onclick="findSystemPrinters('${location}')" style="flex: 1; min-width: 200px;">
                    🖨️ Detectar Impressoras do Sistema
                </button>
                <button class="btn btn-secondary" onclick="findUSBPrinters('${location}')" style="flex: 1; min-width: 200px;">
                    🔌 Buscar USB (Avançado)
                </button>
            </div>

            <!-- Campos técnicos escondidos (preenchidos automaticamente) -->
            <input type="hidden" id="${location}VendorId">
            <input type="hidden" id="${location}ProductId">
            <input type="hidden" id="${location}PrinterName">

            <div style="margin-top: 15px; padding: 10px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 4px; font-size: 12px; color: #1565c0;">
                <strong>⭐ RECOMENDADO:</strong> Use "Detectar Impressoras do Sistema" para ver TODAS as impressoras instaladas
                (USB, Rede, PDF, etc). Funciona mesmo se a impressora não estiver conectada no momento.
            </div>

            <div style="margin-top: 10px; padding: 10px; background: #fff3e0; border-left: 4px solid #ff9800; border-radius: 4px; font-size: 11px; color: #e65100;">
                <strong>🔧 Modo Avançado:</strong> "Buscar USB" detecta apenas impressoras USB conectadas AGORA.
                Use somente se "Detectar Sistema" não encontrar sua impressora térmica.
            </div>

            <!-- ==================== CONFIGURAÇÕES AVANÇADAS v1.7.0 ==================== -->
            <div style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 6px;">
                <h4 style="margin-top: 0; margin-bottom: 15px; color: #333; font-size: 14px;">⚙️ Configurações Avançadas</h4>

                <!-- Número de cópias -->
                <div class="form-group">
                    <label>📋 Número de cópias</label>
                    <select id="${location}Copies" style="width: 100%; padding: 8px;">
                        <option value="1">1 via</option>
                        <option value="2" selected>2 vias</option>
                        <option value="3">3 vias</option>
                        <option value="4">4 vias</option>
                    </select>
                    <small style="color: #666;">Ex: 1 para cozinha, 1 para entregador</small>
                </div>

                <!-- Largura do papel -->
                <div class="form-group">
                    <label>📏 Largura do papel</label>
                    <select id="${location}PaperWidth" style="width: 100%; padding: 8px;">
                        <option value="58">58mm (compacto)</option>
                        <option value="80" selected>80mm (padrão)</option>
                    </select>
                    <small style="color: #666;">Verifique a largura da bobina de papel</small>
                </div>

                <!-- Tamanho da fonte -->
                <div class="form-group">
                    <label>🔤 Tamanho da fonte</label>
                    <select id="${location}FontSize" style="width: 100%; padding: 8px;">
                        <option value="small">Pequeno (mais conteúdo)</option>
                        <option value="normal" selected>Normal (recomendado)</option>
                        <option value="large">Grande (melhor legibilidade)</option>
                    </select>
                </div>

                <!-- Imprimir logo -->
                <div class="form-group">
                    <label>🖼️ Imprimir logo do restaurante</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="${location}PrintLogo" style="width: auto; margin: 0;">
                        <label for="${location}PrintLogo" style="margin: 0; font-weight: normal;">Sim, imprimir logo no topo do cupom</label>
                    </div>
                    <div id="${location}LogoPathDiv" style="margin-top: 10px; display: none;">
                        <input type="text" id="${location}LogoPath" placeholder="Caminho da imagem (PNG/JPG)" style="width: 100%; padding: 8px;" readonly>
                        <button class="btn btn-secondary" onclick="selectLogo('${location}')" style="margin-top: 5px; font-size: 12px;">
                            📁 Selecionar Logo
                        </button>
                    </div>
                </div>

                <!-- Remover acentos -->
                <div class="form-group">
                    <label>✂️ Remover acentos</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="${location}RemoveAccents" style="width: auto; margin: 0;">
                        <label for="${location}RemoveAccents" style="margin: 0; font-weight: normal;">Sim (para impressoras antigas sem suporte UTF-8)</label>
                    </div>
                    <small style="color: #666;">Ex: "São Paulo" vira "Sao Paulo"</small>
                </div>
            </div>
        `;

        // Event listener para mostrar/ocultar seleção de logo
        setTimeout(() => {
            const printLogoCheckbox = document.getElementById(`${location}PrintLogo`);
            const logoPathDiv = document.getElementById(`${location}LogoPathDiv`);

            if (printLogoCheckbox && logoPathDiv) {
                printLogoCheckbox.addEventListener('change', function() {
                    logoPathDiv.style.display = this.checked ? 'block' : 'none';
                });
            }
        }, 100);

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

            <!-- ==================== CONFIGURAÇÕES AVANÇADAS v1.7.0 ==================== -->
            <div style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 6px;">
                <h4 style="margin-top: 0; margin-bottom: 15px; color: #333; font-size: 14px;">⚙️ Configurações Avançadas</h4>

                <!-- Número de cópias -->
                <div class="form-group">
                    <label>📋 Número de cópias</label>
                    <select id="${location}Copies" style="width: 100%; padding: 8px;">
                        <option value="1">1 via</option>
                        <option value="2" selected>2 vias</option>
                        <option value="3">3 vias</option>
                        <option value="4">4 vias</option>
                    </select>
                    <small style="color: #666;">Ex: 1 para cozinha, 1 para entregador</small>
                </div>

                <!-- Largura do papel -->
                <div class="form-group">
                    <label>📏 Largura do papel</label>
                    <select id="${location}PaperWidth" style="width: 100%; padding: 8px;">
                        <option value="58">58mm (compacto)</option>
                        <option value="80" selected>80mm (padrão)</option>
                    </select>
                    <small style="color: #666;">Verifique a largura da bobina de papel</small>
                </div>

                <!-- Tamanho da fonte -->
                <div class="form-group">
                    <label>🔤 Tamanho da fonte</label>
                    <select id="${location}FontSize" style="width: 100%; padding: 8px;">
                        <option value="small">Pequeno (mais conteúdo)</option>
                        <option value="normal" selected>Normal (recomendado)</option>
                        <option value="large">Grande (melhor legibilidade)</option>
                    </select>
                </div>

                <!-- Imprimir logo -->
                <div class="form-group">
                    <label>🖼️ Imprimir logo do restaurante</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="${location}PrintLogo" style="width: auto; margin: 0;">
                        <label for="${location}PrintLogo" style="margin: 0; font-weight: normal;">Sim, imprimir logo no topo do cupom</label>
                    </div>
                    <div id="${location}LogoPathDiv" style="margin-top: 10px; display: none;">
                        <input type="text" id="${location}LogoPath" placeholder="Caminho da imagem (PNG/JPG)" style="width: 100%; padding: 8px;" readonly>
                        <button class="btn btn-secondary" onclick="selectLogo('${location}')" style="margin-top: 5px; font-size: 12px;">
                            📁 Selecionar Logo
                        </button>
                    </div>
                </div>

                <!-- Remover acentos -->
                <div class="form-group">
                    <label>✂️ Remover acentos</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="${location}RemoveAccents" style="width: auto; margin: 0;">
                        <label for="${location}RemoveAccents" style="margin: 0; font-weight: normal;">Sim (para impressoras antigas sem suporte UTF-8)</label>
                    </div>
                    <small style="color: #666;">Ex: "São Paulo" vira "Sao Paulo"</small>
                </div>
            </div>
        `;

        // Event listener para mostrar/ocultar seleção de logo
        setTimeout(() => {
            const printLogoCheckbox = document.getElementById(`${location}PrintLogo`);
            const logoPathDiv = document.getElementById(`${location}LogoPathDiv`);

            if (printLogoCheckbox && logoPathDiv) {
                printLogoCheckbox.addEventListener('change', function() {
                    logoPathDiv.style.display = this.checked ? 'block' : 'none';
                });
            }
        }, 100);
    }
}

// Armazena lista de impressoras encontradas por localização
const foundPrinters = {};

// NOVO: Detectar TODAS impressoras instaladas no sistema (v1.9.3+)
// Inclui USB, Rede, Virtuais (Print to PDF), etc
async function findSystemPrinters(location) {
    try {
        console.log('🔍 Buscando impressoras do sistema...');
        const printers = await ipcRenderer.invoke('find-system-printers');
        console.log('✅ Impressoras retornadas:', printers);

        const select = document.getElementById(`${location}PrinterSelect`);

        if (printers.length === 0) {
            console.error('❌ Nenhuma impressora encontrada!');
            alert('❌ Nenhuma impressora encontrada no sistema.\n\n' +
                  'Verifique se há impressoras instaladas:\n' +
                  '• Windows: Configurações → Impressoras\n' +
                  '• macOS: Preferências → Impressoras\n' +
                  '• Linux: Settings → Printers');
            return;
        }

        // Armazena lista para uso posterior
        foundPrinters[location] = printers.map(p => ({
            ...p,
            // Marca como impressora do sistema (não USB)
            isSystemPrinter: true
        }));

        // Limpa e preenche o select
        select.innerHTML = '<option value="">Selecione uma impressora</option>';

        printers.forEach((printer, index) => {
            const option = document.createElement('option');
            option.value = index;
            // Usa o label formatado com emoji
            option.textContent = printer.label;
            select.appendChild(option);
        });

        // Mensagem de sucesso
        const defaultPrinter = printers.find(p => p.isDefault);
        let successMsg = `✅ ${printers.length} impressora(s) encontrada(s)!\n\n`;

        if (defaultPrinter) {
            successMsg += `⭐ Impressora padrão: ${defaultPrinter.displayName}\n\n`;
        }

        successMsg += 'Selecione uma impressora na lista acima.';
        alert(successMsg);

    } catch (error) {
        console.error('Erro ao buscar impressoras do sistema:', error);
        alert('Erro ao buscar impressoras: ' + error.message);
    }
}

// Detectar impressoras USB (método original - ainda funciona)
async function findUSBPrinters(location) {
    try {
        const printers = await ipcRenderer.invoke('find-usb-printers');
        const select = document.getElementById(`${location}PrinterSelect`);

        if (printers.length === 0) {
            alert('❌ Nenhuma impressora USB encontrada.\n\n' +
                  'Certifique-se de que:\n' +
                  '• A impressora está conectada via USB\n' +
                  '• A impressora está ligada\n' +
                  '• Os drivers estão instalados\n\n' +
                  '💡 Dica: Experimente o botão "Detectar Impressoras do Sistema"');
            return;
        }

        // Armazena lista para uso posterior
        foundPrinters[location] = printers.map(p => ({
            ...p,
            isSystemPrinter: false // Marca como USB
        }));

        // Limpa e preenche o select
        select.innerHTML = '<option value="">Selecione uma impressora</option>';

        printers.forEach((printer, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = `📄 ${printer.displayName}`;
            select.appendChild(option);
        });

        // Mensagem de sucesso
        alert(`✅ ${printers.length} impressora(s) USB encontrada(s)!\n\n` +
              'Selecione uma impressora na lista acima.');

    } catch (error) {
        alert('Erro ao buscar impressoras: ' + error.message);
    }
}

// Função chamada quando o usuário seleciona uma impressora
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

    // Verifica se é impressora do sistema ou USB
    if (printer.isSystemPrinter) {
        // Impressora do sistema: usa nome
        document.getElementById(`${location}PrinterName`).value = printer.name;
        document.getElementById(`${location}VendorId`).value = '';
        document.getElementById(`${location}ProductId`).value = '';

        console.log(`✅ Impressora do sistema selecionada: ${printer.displayName}`);
        console.log(`   Nome: ${printer.name}`);
        console.log(`   Status: ${printer.statusText}`);

    } else {
        // Impressora USB: usa vendor/product ID
        document.getElementById(`${location}VendorId`).value = `0x${printer.vendorId.toString(16).padStart(4, '0')}`;
        document.getElementById(`${location}ProductId`).value = `0x${printer.productId.toString(16).padStart(4, '0')}`;
        document.getElementById(`${location}PrinterName`).value = '';

        console.log(`✅ Impressora USB selecionada: ${printer.displayName}`);
        console.log(`   Vendor ID: 0x${printer.vendorId.toString(16).padStart(4, '0')}`);
        console.log(`   Product ID: 0x${printer.productId.toString(16).padStart(4, '0')}`);
    }
}

// Função para selecionar logo (v1.7.0)
async function selectLogo(location) {
    try {
        const result = await ipcRenderer.invoke('select-logo-file');

        if (result.filePath) {
            document.getElementById(`${location}LogoPath`).value = result.filePath;
            console.log(`Logo selecionado: ${result.filePath}`);
        }

    } catch (error) {
        alert('Erro ao selecionar logo: ' + error.message);
    }
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

    // CORREÇÃO: Detecta se é impressora do sistema (tem nome mas não tem vendor/product ID)
    const printerName = document.getElementById(`${location}PrinterName`).value;
    const isSystemPrinter = printerName && printerName.trim() !== '';

    if (isSystemPrinter) {
        // Impressora do sistema (detectada via PowerShell/getPrinters)
        config.type = 'system';
        config.printerName = printerName;
        config.vendorId = null;
        config.productId = null;

        // Configurações avançadas para impressoras do sistema
        const copiesEl = document.getElementById(`${location}Copies`);
        config.copies = copiesEl ? parseInt(copiesEl.value) : 1;

        const paperWidthEl = document.getElementById(`${location}PaperWidth`);
        config.paperWidth = paperWidthEl ? parseInt(paperWidthEl.value) : 80;

        const fontSizeEl = document.getElementById(`${location}FontSize`);
        config.fontSize = fontSizeEl ? fontSizeEl.value : 'normal';

        const printLogoEl = document.getElementById(`${location}PrintLogo`);
        config.printLogo = printLogoEl ? printLogoEl.checked : false;

        if (config.printLogo) {
            const logoPathEl = document.getElementById(`${location}LogoPath`);
            config.logoPath = logoPathEl ? logoPathEl.value : '';
        }

        const removeAccentsEl = document.getElementById(`${location}RemoveAccents`);
        config.removeAccents = removeAccentsEl ? removeAccentsEl.checked : false;

        console.log(`✅ Configurando impressora do sistema: ${printerName}`);
        console.log(`   Cópias: ${config.copies}, Largura: ${config.paperWidth}mm`);

    } else if (type === 'usb') {
        const vendorId = document.getElementById(`${location}VendorId`).value;
        const productId = document.getElementById(`${location}ProductId`).value;

        if (!vendorId || !productId) {
            alert('Busque e selecione uma impressora USB primeiro');
            return;
        }

        config.vendorId = parseInt(vendorId);
        config.productId = parseInt(productId);

        // ==================== NOVAS CONFIGURAÇÕES v1.7.0 ====================
        // Número de cópias
        const copiesEl = document.getElementById(`${location}Copies`);
        config.copies = copiesEl ? parseInt(copiesEl.value) : 1;

        // Largura do papel
        const paperWidthEl = document.getElementById(`${location}PaperWidth`);
        config.paperWidth = paperWidthEl ? parseInt(paperWidthEl.value) : 80;

        // Tamanho da fonte
        const fontSizeEl = document.getElementById(`${location}FontSize`);
        config.fontSize = fontSizeEl ? fontSizeEl.value : 'normal';

        // Imprimir logo
        const printLogoEl = document.getElementById(`${location}PrintLogo`);
        config.printLogo = printLogoEl ? printLogoEl.checked : false;

        if (config.printLogo) {
            const logoPathEl = document.getElementById(`${location}LogoPath`);
            config.logoPath = logoPathEl ? logoPathEl.value : '';

            if (!config.logoPath) {
                alert('Selecione um arquivo de logo para imprimir');
                return;
            }
        }

        // Remover acentos
        const removeAccentsEl = document.getElementById(`${location}RemoveAccents`);
        config.removeAccents = removeAccentsEl ? removeAccentsEl.checked : false;

        console.log(`Configurações avançadas:`, {
            copies: config.copies,
            paperWidth: config.paperWidth,
            fontSize: config.fontSize,
            printLogo: config.printLogo,
            logoPath: config.logoPath,
            removeAccents: config.removeAccents
        });

    } else if (type === 'network') {
        const ip = document.getElementById(`${location}Ip`).value;
        const port = document.getElementById(`${location}Port`).value;

        if (!ip) {
            alert('Preencha o endereço IP');
            return;
        }

        config.ip = ip;
        config.port = parseInt(port) || 9100;

        // Configurações avançadas também para rede (v1.7.0)
        const copiesEl = document.getElementById(`${location}Copies`);
        config.copies = copiesEl ? parseInt(copiesEl.value) : 1;

        const paperWidthEl = document.getElementById(`${location}PaperWidth`);
        config.paperWidth = paperWidthEl ? parseInt(paperWidthEl.value) : 80;

        const fontSizeEl = document.getElementById(`${location}FontSize`);
        config.fontSize = fontSizeEl ? fontSizeEl.value : 'normal';

        const printLogoEl = document.getElementById(`${location}PrintLogo`);
        config.printLogo = printLogoEl ? printLogoEl.checked : false;

        if (config.printLogo) {
            const logoPathEl = document.getElementById(`${location}LogoPath`);
            config.logoPath = logoPathEl ? logoPathEl.value : '';
        }

        const removeAccentsEl = document.getElementById(`${location}RemoveAccents`);
        config.removeAccents = removeAccentsEl ? removeAccentsEl.checked : false;
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

document.addEventListener('DOMContentLoaded', async () => {
    console.log('YumGo Bridge iniciado');

    // Busca e exibe a versão do app (v1.9.3+)
    try {
        const version = await ipcRenderer.invoke('get-app-version');
        const versionElement = document.getElementById('appVersion');
        if (versionElement) {
            versionElement.textContent = version;
            console.log(`📦 Versão: ${version}`);
        }
    } catch (error) {
        console.error('Erro ao obter versão:', error);
    }

    // MODO TESTE: Mostra seção de impressoras mesmo sem conexão (v1.9.9+)
    console.log('🧪 Modo de teste: Habilitando seção de impressoras...');
    if (printersCard) {
        printersCard.classList.remove('hidden');
        console.log('✅ Seção de impressoras habilitada (modo offline)');
    }

    console.log('Verificando funções globais:');
    console.log('- connect:', typeof connect);
    console.log('- disconnect:', typeof disconnect);
    console.log('Elementos DOM:');
    console.log('- connectBtn:', connectBtn ? 'OK' : 'NULL');
    console.log('- restaurantIdInput:', restaurantIdInput ? 'OK' : 'NULL');
    console.log('- tokenInput:', tokenInput ? 'OK' : 'NULL');
});
