const escpos = require('escpos');
const USB = require('escpos-usb');
const Network = require('escpos-network');
const log = require('electron-log');
const fs = require('fs');
const { exec } = require('child_process'); // Impressão nativa via comandos do SO
const os = require('os');
const path = require('path');

/**
 * Device virtual que captura comandos ESC/POS em Buffer
 * (para gerar comandos binários sem device físico)
 */
class BufferDevice {
    constructor() {
        this.buffer = Buffer.alloc(0);
    }

    open(callback) {
        callback(null, this);
    }

    write(data, callback) {
        this.buffer = Buffer.concat([this.buffer, data]);
        if (callback) callback(null);
    }

    close(callback) {
        if (callback) callback(null);
    }

    getBuffer() {
        return this.buffer;
    }
}

class ThermalPrinter {
    constructor() {
        this.printers = {};
    }

    /**
     * Obter todas as impressoras configuradas (para heartbeat)
     */
    getAllPrinters() {
        return Object.keys(this.printers).map(location => ({
            location: location,
            type: this.printers[location].config?.type,
            name: this.printers[location].config?.printerName || 'N/A'
        }));
    }

    /**
     * Configurar impressora
     */
    async configurePrinter(location, config) {
        try {
            let device;
            let printer;

            if (config.type === 'usb') {
                device = new USB(config.vendorId, config.productId);
                printer = new escpos.Printer(device);
            } else if (config.type === 'network') {
                device = new Network(config.ip, config.port || 9100);
                printer = new escpos.Printer(device);
            } else if (config.type === 'system') {
                // Impressora do sistema (Windows/macOS/Linux)
                // Usa nome da impressora ao invés de USB/Network
                device = null; // Não usa device físico, usa nome do sistema
                printer = null; // Será criado no momento da impressão
                log.info(`Impressora do sistema configurada: ${config.printerName}`);
            } else {
                throw new Error(`Tipo de impressora não suportado: ${config.type}`);
            }

            this.printers[location] = {
                device: device,
                printer: printer,
                config: config
            };

            log.info(`Impressora ${location} configurada: ${config.type}`);

        } catch (error) {
            log.error(`Erro ao configurar impressora ${location}:`, error);
            throw error;
        }
    }

    /**
     * Imprimir pedido (v2.1.0 - suporte a impressoras do sistema)
     */
    async printOrder(orderData, location) {
        const printerObj = this.printers[location];

        if (!printerObj) {
            throw new Error(`Impressora ${location} não configurada`);
        }

        const { config } = printerObj;
        const copies = config.copies || 1;

        log.info(`Imprimindo ${copies} cópia(s) do pedido #${orderData.order_number} em ${location}`);

        // v2.1.0: Suporte a impressoras do sistema (Windows/macOS/Linux)
        if (config.type === 'system') {
            return this.printSystemPrinter(orderData, location, copies);
        }

        // Impressoras térmicas USB/Network (comportamento original)
        return new Promise((resolve, reject) => {
            const { device, printer } = printerObj;

            device.open((error) => {
                if (error) {
                    log.error(`Erro ao abrir impressora ${location}:`, error);
                    reject(error);
                    return;
                }

                try {
                    // Imprimir múltiplas cópias
                    for (let i = 0; i < copies; i++) {
                        this.printReceipt(printer, orderData, location, i + 1, copies);
                    }

                    device.close(() => {
                        log.info(`${copies} cópia(s) do pedido #${orderData.order_number} impressa(s) em ${location}`);
                        resolve();
                    });

                } catch (printError) {
                    log.error(`Erro ao imprimir em ${location}:`, printError);
                    device.close();
                    reject(printError);
                }
            });
        });
    }

    /**
     * Detectar se impressora é térmica (POS, RP, TM, etc)
     */
    isThermalPrinter(printerName) {
        const thermalPrefixes = ['pos', 'rp', 'tm', 'xp', 'mp', 'ep', 'tp'];
        const nameLower = printerName.toLowerCase();
        return thermalPrefixes.some(prefix => nameLower.startsWith(prefix));
    }

    /**
     * Tentar encontrar impressora térmica USB automaticamente
     */
    async findUSBThermalPrinter() {
        try {
            const devices = USB.findPrinter();
            if (devices && devices.length > 0) {
                log.info(`🔍 ${devices.length} impressora(s) USB térmica(s) detectada(s)`);
                return devices[0]; // Retorna primeira encontrada
            }
            return null;
        } catch (error) {
            log.warn(`Erro ao buscar impressoras USB: ${error.message}`);
            return null;
        }
    }

    /**
     * Imprimir usando ESC/POS nativo (PROFISSIONAL) - v3.5.0
     */
    async printESCPOS(orderData, location, copies, device) {
        return new Promise((resolve, reject) => {
            device.open((error) => {
                if (error) {
                    log.error(`Erro ao abrir device USB: ${error.message}`);
                    reject(error);
                    return;
                }

                try {
                    const printer = new escpos.Printer(device);
                    const { config } = this.printers[location];

                    // Determinar largura do papel
                    const paperWidth = config.paperWidth || 58;
                    let charsPerLine;
                    if (paperWidth <= 58) {
                        charsPerLine = 32;
                    } else if (paperWidth <= 80) {
                        charsPerLine = 48;
                    } else {
                        charsPerLine = 42;
                    }

                    // Função auxiliar para remover acentos
                    const removeAccents = (text) => {
                        if (!text) return '';
                        return text
                            .normalize('NFD')
                            .replace(/[\u0300-\u036f]/g, '')
                            .replace(/[^\x00-\x7F]/g, '');
                    };

                    // Função para centralizar texto
                    const center = (text) => {
                        const clean = removeAccents(text);
                        const padding = Math.max(0, Math.floor((charsPerLine - clean.length) / 2));
                        return ' '.repeat(padding) + clean;
                    };

                    // Função para linha de separação
                    const separator = () => '='.repeat(charsPerLine);

                    // Imprimir múltiplas cópias
                    for (let copy = 0; copy < copies; copy++) {
                        // CABEÇALHO
                        printer
                            .align('ct')
                            .style('bu')
                            .size(1, 1)
                            .text(center('YUMGO - PEDIDO'))
                            .style('normal')
                            .text(separator())
                            .text('')
                            .align('lt');

                        // NÚMERO DO PEDIDO
                        printer
                            .style('b')
                            .size(1, 1)
                            .text(removeAccents(`Pedido: #${orderData.order_number}`))
                            .style('normal')
                            .text('');

                        // LOCAL DE IMPRESSÃO
                        const locationNames = {
                            counter: 'BALCAO',
                            kitchen: 'COZINHA',
                            bar: 'BAR'
                        };
                        printer.text(removeAccents(`Local: ${locationNames[location] || location.toUpperCase()}`));
                        printer.text('');

                        // CLIENTE
                        printer.text(separator());
                        printer.text(removeAccents(`Cliente: ${orderData.customer?.name || 'N/A'}`));
                        if (orderData.customer?.phone) {
                            printer.text(removeAccents(`Tel: ${orderData.customer.phone}`));
                        }
                        printer.text('');

                        // TIPO DE ENTREGA
                        const deliveryType = orderData.delivery?.type === 'delivery' ? 'ENTREGA' : 'RETIRADA';
                        printer.text(removeAccents(`Tipo: ${deliveryType}`));

                        if (orderData.delivery?.type === 'delivery' && orderData.delivery?.address) {
                            printer.text(removeAccents(`End: ${orderData.delivery.address}`));
                            if (orderData.delivery.neighborhood) {
                                printer.text(removeAccents(`Bairro: ${orderData.delivery.neighborhood}`));
                            }
                        }
                        printer.text('');

                        // ITENS DO PEDIDO
                        printer.text(separator());
                        printer.style('b').text('ITENS:').style('normal');
                        printer.text('');

                        orderData.items?.forEach((item) => {
                            // Nome e quantidade
                            printer.text(removeAccents(`${item.quantity}x ${item.name}`));

                            // Variações (ex: tamanho, sabor)
                            if (item.variations && typeof item.variations === 'object') {
                                Object.entries(item.variations).forEach(([key, value]) => {
                                    printer.text(removeAccents(`  - ${key}: ${value}`));
                                });
                            }

                            // Adicionais
                            if (item.addons && Array.isArray(item.addons)) {
                                item.addons.forEach((addon) => {
                                    printer.text(removeAccents(`  + ${addon.name || addon}`));
                                });
                            }

                            // Observações do item
                            if (item.notes) {
                                printer.text(removeAccents(`  Obs: ${item.notes}`));
                            }

                            printer.text('');
                        });

                        // OBSERVAÇÕES GERAIS
                        if (orderData.notes) {
                            printer.text(separator());
                            printer.style('b').text('OBSERVACOES GERAIS:').style('normal');
                            printer.text(removeAccents(orderData.notes));
                            printer.text('');
                        }

                        // TOTAIS
                        printer.text(separator());
                        printer.text(removeAccents(`Subtotal: R$ ${orderData.totals?.subtotal?.toFixed(2) || '0.00'}`));
                        if (orderData.totals?.delivery_fee > 0) {
                            printer.text(removeAccents(`Taxa Entrega: R$ ${orderData.totals.delivery_fee.toFixed(2)}`));
                        }
                        if (orderData.totals?.discount > 0) {
                            printer.text(removeAccents(`Desconto: -R$ ${orderData.totals.discount.toFixed(2)}`));
                        }
                        printer.style('b').size(1, 1).text(removeAccents(`TOTAL: R$ ${orderData.totals?.total?.toFixed(2) || '0.00'}`)).style('normal').size(0, 0);
                        printer.text('');

                        // PAGAMENTO
                        printer.text(separator());
                        const paymentMethod = {
                            credit_card: 'CARTAO CREDITO',
                            debit_card: 'CARTAO DEBITO',
                            pix: 'PIX',
                            money: 'DINHEIRO'
                        }[orderData.payment?.method] || orderData.payment?.method?.toUpperCase() || 'N/A';

                        printer.text(removeAccents(`Pagamento: ${paymentMethod}`));
                        printer.text('');

                        // DATA/HORA
                        printer.text(separator());
                        const createdAt = new Date(orderData.created_at);
                        printer.text(removeAccents(`Data: ${createdAt.toLocaleDateString('pt-BR')}`));
                        printer.text(removeAccents(`Hora: ${createdAt.toLocaleTimeString('pt-BR')}`));
                        printer.text('');

                        // RODAPÉ
                        printer.text(separator());
                        printer.align('ct').text(center('Obrigado pela preferencia!'));
                        printer.text('').text('');

                        // Cortar papel
                        printer.cut();
                    }

                    // Fechar conexão
                    printer.close();
                    device.close();

                    log.info(`✅ ${copies} cópia(s) impressa(s) via ESC/POS em ${location}`);
                    resolve();

                } catch (printError) {
                    log.error(`Erro ao imprimir ESC/POS: ${printError.message}`);
                    device.close();
                    reject(printError);
                }
            });
        });
    }

    /**
     * Gerar comandos ESC/POS MINIMALISTAS (máxima compatibilidade) - v3.10.0
     */
    async generateESCPOSBuffer(orderData, location) {
        return new Promise((resolve, reject) => {
            try {
                // Buffer manual - comandos ESC/POS crus (máxima compatibilidade)
                const ESC = 0x1B;
                const GS = 0x1D;
                const LF = 0x0A;

                let buffer = [];

                // Inicializar impressora (ESC @)
                buffer.push(ESC, 0x40);

                // Função limpar texto
                const clean = (text) => {
                    if (!text) return '';
                    return text
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^\x00-\x7F]/g, '');
                };

                // Função adicionar texto
                const addText = (text) => {
                    const cleaned = clean(text);
                    for (let i = 0; i < cleaned.length; i++) {
                        buffer.push(cleaned.charCodeAt(i));
                    }
                    buffer.push(LF);
                };

                // Separador
                const addSep = () => {
                    addText('================================');
                };

                // CABEÇALHO
                addText('        YUMGO - PEDIDO');
                addSep();
                addText('');

                // PEDIDO
                addText(`Pedido: #${orderData.order_number}`);
                addText('');

                // CLIENTE
                addSep();
                addText(`Cliente: ${orderData.customer?.name || 'N/A'}`);
                if (orderData.customer?.phone) {
                    addText(`Tel: ${orderData.customer.phone}`);
                }
                addText('');

                // ENTREGA
                const deliveryType = orderData.delivery?.type === 'delivery' ? 'ENTREGA' : 'RETIRADA';
                addText(`Tipo: ${deliveryType}`);

                if (orderData.delivery?.type === 'delivery' && orderData.delivery?.address) {
                    addText(`End: ${orderData.delivery.address}`);
                    if (orderData.delivery.neighborhood) {
                        addText(`Bairro: ${orderData.delivery.neighborhood}`);
                    }
                }
                addText('');

                // ITENS
                addSep();
                addText('ITENS:');
                addText('');

                orderData.items?.forEach((item) => {
                    addText(`${item.quantity}x ${item.name}`);

                    if (item.variations && typeof item.variations === 'object') {
                        Object.entries(item.variations).forEach(([key, value]) => {
                            addText(`  - ${key}: ${value}`);
                        });
                    }

                    if (item.addons && Array.isArray(item.addons)) {
                        item.addons.forEach((addon) => {
                            addText(`  + ${addon.name || addon}`);
                        });
                    }

                    if (item.notes) {
                        addText(`  Obs: ${item.notes}`);
                    }

                    addText('');
                });

                // OBSERVAÇÕES
                if (orderData.notes) {
                    addSep();
                    addText('OBSERVACOES GERAIS:');
                    addText(orderData.notes);
                    addText('');
                }

                // TOTAIS
                addSep();
                addText(`Subtotal: R$ ${orderData.totals?.subtotal?.toFixed(2) || '0.00'}`);
                if (orderData.totals?.delivery_fee > 0) {
                    addText(`Taxa Entrega: R$ ${orderData.totals.delivery_fee.toFixed(2)}`);
                }
                if (orderData.totals?.discount > 0) {
                    addText(`Desconto: -R$ ${orderData.totals.discount.toFixed(2)}`);
                }
                addText(`TOTAL: R$ ${orderData.totals?.total?.toFixed(2) || '0.00'}`);
                addText('');

                // PAGAMENTO
                addSep();
                const paymentMethod = {
                    credit_card: 'CARTAO CREDITO',
                    debit_card: 'CARTAO DEBITO',
                    pix: 'PIX',
                    money: 'DINHEIRO'
                }[orderData.payment?.method] || orderData.payment?.method?.toUpperCase() || 'N/A';
                addText(`Pagamento: ${paymentMethod}`);
                addText('');

                // DATA/HORA
                addSep();
                const createdAt = new Date(orderData.created_at);
                addText(`Data: ${createdAt.toLocaleDateString('pt-BR')}`);
                addText(`Hora: ${createdAt.toLocaleTimeString('pt-BR')}`);
                addText('');

                // RODAPÉ
                addSep();
                addText('   Obrigado pela preferencia!');
                addText('');
                addText('');

                // Corte parcial (mais compatível que total)
                buffer.push(GS, 0x56, 0x01);

                resolve(Buffer.from(buffer));

            } catch (error) {
                reject(error);
            }
        });
    }

    /**
     * RAW printing via Winspool (API nativa Windows) - PROFISSIONAL v3.9.0
     * Usa OpenPrinter + WritePrinter via .NET Framework (PowerShell)
     */
    async printRawWinspool(orderData, location, copies, printerName) {
        return new Promise(async (resolve, reject) => {
            try {
                // Gerar buffer ESC/POS
                const buffer = await this.generateESCPOSBuffer(orderData, location);

                // Salvar em arquivo binário
                const tempDir = os.tmpdir();
                const tempFile = path.join(tempDir, `yumgo-${orderData.order_number}-${Date.now()}.bin`);

                fs.writeFileSync(tempFile, buffer);
                log.info(`🔥 ESC/POS RAW criado: ${tempFile} (${buffer.length} bytes)`);

                let printsCompleted = 0;

                for (let i = 0; i < copies; i++) {
                    // PowerShell: RAW printing via .NET Framework (Winspool API)
                    const psScript = `
                    Add-Type -TypeDefinition @"
                    using System;
                    using System.IO;
                    using System.Runtime.InteropServices;

                    public class RawPrinter {
                        [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Ansi)]
                        public class DOCINFOA {
                            [MarshalAs(UnmanagedType.LPStr)] public string pDocName;
                            [MarshalAs(UnmanagedType.LPStr)] public string pOutputFile;
                            [MarshalAs(UnmanagedType.LPStr)] public string pDataType;
                        }

                        [DllImport("winspool.Drv", EntryPoint = "OpenPrinterA", SetLastError = true, CharSet = CharSet.Ansi, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
                        public static extern bool OpenPrinter([MarshalAs(UnmanagedType.LPStr)] string szPrinter, out IntPtr hPrinter, IntPtr pd);

                        [DllImport("winspool.Drv", EntryPoint = "ClosePrinter", SetLastError = true, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
                        public static extern bool ClosePrinter(IntPtr hPrinter);

                        [DllImport("winspool.Drv", EntryPoint = "StartDocPrinterA", SetLastError = true, CharSet = CharSet.Ansi, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
                        public static extern bool StartDocPrinter(IntPtr hPrinter, int level, [In, MarshalAs(UnmanagedType.LPStruct)] DOCINFOA di);

                        [DllImport("winspool.Drv", EntryPoint = "EndDocPrinter", SetLastError = true, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
                        public static extern bool EndDocPrinter(IntPtr hPrinter);

                        [DllImport("winspool.Drv", EntryPoint = "StartPagePrinter", SetLastError = true, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
                        public static extern bool StartPagePrinter(IntPtr hPrinter);

                        [DllImport("winspool.Drv", EntryPoint = "EndPagePrinter", SetLastError = true, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
                        public static extern bool EndPagePrinter(IntPtr hPrinter);

                        [DllImport("winspool.Drv", EntryPoint = "WritePrinter", SetLastError = true, ExactSpelling = true, CallingConvention = CallingConvention.StdCall)]
                        public static extern bool WritePrinter(IntPtr hPrinter, IntPtr pBytes, int dwCount, out int dwWritten);

                        public static bool SendBytesToPrinter(string szPrinterName, byte[] pBytes) {
                            IntPtr pUnmanagedBytes = IntPtr.Zero;
                            int nLength = pBytes.Length;
                            DOCINFOA di = new DOCINFOA();
                            di.pDocName = "YumGo Receipt";
                            di.pDataType = "RAW";

                            IntPtr hPrinter = IntPtr.Zero;
                            if (!OpenPrinter(szPrinterName, out hPrinter, IntPtr.Zero)) {
                                return false;
                            }

                            if (!StartDocPrinter(hPrinter, 1, di)) {
                                ClosePrinter(hPrinter);
                                return false;
                            }

                            if (!StartPagePrinter(hPrinter)) {
                                EndDocPrinter(hPrinter);
                                ClosePrinter(hPrinter);
                                return false;
                            }

                            pUnmanagedBytes = Marshal.AllocCoTaskMem(nLength);
                            Marshal.Copy(pBytes, 0, pUnmanagedBytes, nLength);

                            int dwWritten = 0;
                            bool bSuccess = WritePrinter(hPrinter, pUnmanagedBytes, nLength, out dwWritten);

                            Marshal.FreeCoTaskMem(pUnmanagedBytes);

                            EndPagePrinter(hPrinter);
                            EndDocPrinter(hPrinter);
                            ClosePrinter(hPrinter);

                            return bSuccess && (dwWritten == nLength);
                        }
                    }
"@
                    $bytes = [System.IO.File]::ReadAllBytes('${tempFile}');
                    $result = [RawPrinter]::SendBytesToPrinter('${printerName}', $bytes);
                    if ($result) { exit 0 } else { exit 1 }
                    `;

                    exec(`powershell -Command "${psScript}"`, (error, stdout, stderr) => {
                        if (error) {
                            log.error(`Erro RAW Winspool: ${error.message}`);
                            if (stderr) log.error(`stderr: ${stderr}`);
                            reject(error);
                            return;
                        }

                        printsCompleted++;
                        log.info(`✅ Cópia ${printsCompleted}/${copies} via RAW Winspool`);

                        if (printsCompleted === copies) {
                            setTimeout(() => {
                                try {
                                    fs.unlinkSync(tempFile);
                                    log.info(`🗑️ Arquivo removido`);
                                } catch (e) {}
                            }, 2000);

                            log.info(`✅ ${copies} cópia(s) impressa(s) via RAW Winspool API!`);
                            resolve();
                        }
                    });
                }

            } catch (error) {
                log.error(`Erro RAW Winspool: ${error.message}`);
                reject(error);
            }
        });
    }

    /**
     * PowerShell Out-Printer (método original que funcionava) - v3.11.0
     */
    async printOutPrinter(orderData, location, copies, printerName) {
        return new Promise(async (resolve, reject) => {
            try {
                // Gerar texto formatado com largura CORRETA
                const receiptText = this.generateTextReceipt(orderData, location);

                // Salvar em arquivo
                const tempDir = os.tmpdir();
                const tempFile = path.join(tempDir, `yumgo-${orderData.order_number}-${Date.now()}.txt`);

                fs.writeFileSync(tempFile, receiptText, 'utf8');
                log.info(`📄 Arquivo texto criado: ${tempFile}`);

                let printsCompleted = 0;

                for (let i = 0; i < copies; i++) {
                    // Comando PRINT do Windows (mais básico, não quebra texto)
                    const printCmd = `PRINT /D:"${printerName}" "${tempFile}"`;

                    exec(printCmd, (error, stdout, stderr) => {
                        if (error) {
                            log.error(`Erro Out-Printer: ${error.message}`);
                            reject(error);
                            return;
                        }

                        printsCompleted++;
                        log.info(`✅ Cópia ${printsCompleted}/${copies} via Out-Printer`);

                        if (printsCompleted === copies) {
                            setTimeout(() => {
                                try {
                                    fs.unlinkSync(tempFile);
                                    log.info(`🗑️ Arquivo removido`);
                                } catch (e) {}
                            }, 2000);

                            log.info(`✅ ${copies} cópia(s) via PowerShell Out-Printer!`);
                            resolve();
                        }
                    });
                }

            } catch (error) {
                log.error(`Erro Out-Printer: ${error.message}`);
                reject(error);
            }
        });
    }

    /**
     * Gerar recibo em texto puro com largura CORRETA
     */
    generateTextReceipt(order, location) {
        const config = this.printers[location]?.config || {};
        const paperWidth = config.paperWidth || 58;

        // ⭐ CORRIGIDO: 32 colunas para 58mm (não 48!)
        const charsPerLine = paperWidth <= 58 ? 32 : 48;

        // Remover acentos
        const clean = (text) => {
            if (!text) return '';
            return text
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^\x00-\x7F]/g, '');
        };

        const center = (text) => {
            const t = clean(text);
            const padding = Math.max(0, Math.floor((charsPerLine - t.length) / 2));
            return ' '.repeat(padding) + t;
        };

        const sep = () => '='.repeat(charsPerLine);

        let receipt = [];

        // CABEÇALHO
        receipt.push(center('YUMGO - PEDIDO'));
        receipt.push(sep());
        receipt.push('');
        receipt.push(clean(`Pedido: #${order.order_number}`));
        receipt.push('');

        // CLIENTE
        receipt.push(sep());
        receipt.push(clean(`Cliente: ${order.customer?.name || 'N/A'}`));
        if (order.customer?.phone) {
            receipt.push(clean(`Tel: ${order.customer.phone}`));
        }
        receipt.push('');

        // ENTREGA
        const deliveryType = order.delivery?.type === 'delivery' ? 'ENTREGA' : 'RETIRADA';
        receipt.push(clean(`Tipo: ${deliveryType}`));

        if (order.delivery?.type === 'delivery' && order.delivery?.address) {
            receipt.push(clean(`End: ${order.delivery.address}`));
            if (order.delivery.neighborhood) {
                receipt.push(clean(`Bairro: ${order.delivery.neighborhood}`));
            }
        }
        receipt.push('');

        // ITENS
        receipt.push(sep());
        receipt.push('ITENS:');
        receipt.push('');

        order.items?.forEach((item) => {
            receipt.push(clean(`${item.quantity}x ${item.name}`));

            if (item.variations && typeof item.variations === 'object') {
                Object.entries(item.variations).forEach(([key, value]) => {
                    receipt.push(clean(`  - ${key}: ${value}`));
                });
            }

            if (item.addons && Array.isArray(item.addons)) {
                item.addons.forEach((addon) => {
                    receipt.push(clean(`  + ${addon.name || addon}`));
                });
            }

            if (item.notes) {
                receipt.push(clean(`  Obs: ${item.notes}`));
            }

            receipt.push('');
        });

        // OBSERVAÇÕES
        if (order.notes) {
            receipt.push(sep());
            receipt.push('OBSERVACOES GERAIS:');
            receipt.push(clean(order.notes));
            receipt.push('');
        }

        // TOTAIS
        receipt.push(sep());
        receipt.push(clean(`Subtotal: R$ ${order.totals?.subtotal?.toFixed(2) || '0.00'}`));
        if (order.totals?.delivery_fee > 0) {
            receipt.push(clean(`Taxa Entrega: R$ ${order.totals.delivery_fee.toFixed(2)}`));
        }
        if (order.totals?.discount > 0) {
            receipt.push(clean(`Desconto: -R$ ${order.totals.discount.toFixed(2)}`));
        }
        receipt.push(clean(`TOTAL: R$ ${order.totals?.total?.toFixed(2) || '0.00'}`));
        receipt.push('');

        // PAGAMENTO
        receipt.push(sep());
        const paymentMethod = {
            credit_card: 'CARTAO CREDITO',
            debit_card: 'CARTAO DEBITO',
            pix: 'PIX',
            money: 'DINHEIRO'
        }[order.payment?.method] || order.payment?.method?.toUpperCase() || 'N/A';
        receipt.push(clean(`Pagamento: ${paymentMethod}`));
        receipt.push('');

        // DATA/HORA
        receipt.push(sep());
        const createdAt = new Date(order.created_at);
        receipt.push(clean(`Data: ${createdAt.toLocaleDateString('pt-BR')}`));
        receipt.push(clean(`Hora: ${createdAt.toLocaleTimeString('pt-BR')}`));
        receipt.push('');

        // RODAPÉ
        receipt.push(sep());
        receipt.push(center('Obrigado pela preferencia!'));
        receipt.push('');
        receipt.push('');
        receipt.push('');

        return receipt.join('\n');
    }

    /**
     * Buscar porta da impressora no Windows (USB001, COM1, etc)
     */
    async getPrinterPort(printerName) {
        return new Promise((resolve) => {
            const cmd = `powershell -Command "Get-Printer | Where-Object {$_.Name -eq '${printerName}'} | Select-Object -ExpandProperty PortName"`;

            exec(cmd, (error, stdout) => {
                if (error || !stdout) {
                    resolve(null);
                    return;
                }
                const port = stdout.trim();
                resolve(port);
            });
        });
    }

    /**
     * Imprimir direto na porta (para térmicas Windows)
     */
    async printToPort(orderData, location, copies, printerName, port) {
        return new Promise((resolve, reject) => {
            try {
                const receiptText = this.generateTextReceipt(orderData, location);
                const tempDir = os.tmpdir();
                const tempFile = path.join(tempDir, `yumgo-${orderData.order_number}-${Date.now()}.txt`);

                fs.writeFileSync(tempFile, receiptText, 'utf8');
                log.info(`📄 Arquivo criado: ${tempFile}`);

                let printsCompleted = 0;

                for (let i = 0; i < copies; i++) {
                    // Copiar direto para porta (mais confiável para térmicas)
                    const copyCmd = `copy "${tempFile}" "\\\\.\\${port}"`;

                    exec(copyCmd, (error, stdout, stderr) => {
                        if (error) {
                            log.error(`Erro ao copiar para porta ${port}: ${error.message}`);
                            reject(error);
                            return;
                        }

                        printsCompleted++;
                        log.info(`✅ Cópia ${printsCompleted}/${copies} enviada para porta ${port}`);

                        if (printsCompleted === copies) {
                            setTimeout(() => {
                                try {
                                    fs.unlinkSync(tempFile);
                                    log.info(`🗑️ Arquivo removido`);
                                } catch (e) {}
                            }, 2000);

                            log.info(`✅ ${copies} cópia(s) impressa(s) via porta ${port}`);
                            resolve();
                        }
                    });
                }

            } catch (error) {
                log.error(`Erro ao imprimir na porta: ${error.message}`);
                reject(error);
            }
        });
    }

    /**
     * Imprimir em impressora do sistema (Windows/macOS/Linux) - v3.7.0
     * ⭐ PROFISSIONAL: 4 métodos de fallback para térmicas
     */
    async printSystemPrinter(orderData, location, copies) {
        const printerObj = this.printers[location];
        const { config } = printerObj;
        const printerName = config.printerName;

        // ⭐ SOLUÇÃO PROFISSIONAL: Detectar térmicas e usar ESC/POS
        if (this.isThermalPrinter(printerName)) {
            log.info(`🔥 "${printerName}" detectada como térmica. Tentando métodos profissionais...`);

            // MÉTODO 1: ESC/POS via USB direto (libusb)
            try {
                const device = await this.findUSBThermalPrinter();
                if (device) {
                    log.info(`✅ [Método 1] Device USB encontrado! Usando ESC/POS nativo.`);
                    return await this.printESCPOS(orderData, location, copies, device);
                }
            } catch (error) {
                log.warn(`⚠️ [Método 1] ESC/POS USB falhou: ${error.message}`);
            }

            // MÉTODO 2: PowerShell Out-Printer (FUNCIONAVA antes) ⭐
            if (process.platform === 'win32') {
                log.info(`🔧 [Método 2] Tentando PowerShell Out-Printer (método original)...`);
                try {
                    return await this.printOutPrinter(orderData, location, copies, printerName);
                } catch (outError) {
                    log.warn(`⚠️ [Método 2] Out-Printer falhou: ${outError.message}`);
                }
            }

            // MÉTODO 3: Copiar direto na porta (Windows)
            if (process.platform === 'win32') {
                log.info(`🔧 [Método 3] Tentando porta direta...`);
                try {
                    const port = await this.getPrinterPort(printerName);
                    if (port && (port.startsWith('USB') || port.startsWith('COM'))) {
                        log.info(`🎯 Porta encontrada: ${port}. Copiando...`);
                        return await this.printToPort(orderData, location, copies, printerName, port);
                    } else {
                        log.warn(`⚠️ Porta "${port}" não é USB/COM.`);
                    }
                } catch (portError) {
                    log.warn(`⚠️ [Método 3] Porta direta falhou: ${portError.message}`);
                }
            }

            log.info(`🔧 [Método 4] Fallback: comando do sistema...`);
        }

        // FALLBACK: Comando do sistema (para impressoras comuns)
        return new Promise((resolve, reject) => {
            try {
                // Gerar texto formatado do pedido
                const receiptText = this.generateTextReceipt(orderData, location);

                // Criar arquivo temporário com o texto
                const tempDir = os.tmpdir();
                const tempFile = path.join(tempDir, `yumgo-${orderData.order_number}-${Date.now()}.txt`);

                fs.writeFileSync(tempFile, receiptText, 'utf8');
                log.info(`Arquivo temporário criado: ${tempFile}`);

                // Comando de impressão baseado no SO
                let printCommand;

                if (process.platform === 'win32') {
                    printCommand = `PRINT /D:"${printerName}" "${tempFile}"`;
                } else if (process.platform === 'darwin') {
                    printCommand = `lp -d "${printerName}" "${tempFile}"`;
                } else {
                    printCommand = `lp -d "${printerName}" "${tempFile}"`;
                }

                // Imprimir múltiplas cópias
                let printsCompleted = 0;

                for (let i = 0; i < copies; i++) {
                    exec(printCommand, (error, stdout, stderr) => {
                        if (error) {
                            log.error(`Erro ao imprimir em "${printerName}":`, error);
                            log.error(`stderr: ${stderr}`);
                            reject(error);
                            return;
                        }

                        printsCompleted++;
                        log.info(`Cópia ${printsCompleted}/${copies} do pedido #${orderData.order_number} enviada para "${printerName}"`);

                        // Quando todas as cópias forem impressas
                        if (printsCompleted === copies) {
                            // Aguardar 2 segundos antes de deletar (dar tempo da impressora processar)
                            setTimeout(() => {
                                try {
                                    fs.unlinkSync(tempFile);
                                    log.info(`Arquivo temporário removido: ${tempFile}`);
                                } catch (unlinkError) {
                                    log.warn(`Não foi possível remover arquivo temporário: ${unlinkError.message}`);
                                }
                            }, 2000);

                            log.info(`✅ ${copies} cópia(s) do pedido #${orderData.order_number} impressa(s) em "${printerName}"`);
                            resolve();
                        }
                    });
                }

            } catch (error) {
                log.error(`Erro ao gerar impressão para "${printerName}":`, error);
                reject(error);
            }
        });
    }

    /**
     * Gerar recibo formatado em texto puro (para impressoras do sistema) - v2.1.0
     */
    generateTextReceipt(order, location) {
        const config = this.printers[location]?.config || {};
        const paperWidth = config.paperWidth || 80;

        // ⭐ Helper para remover acentos (evita problemas com PowerShell/Windows)
        const removeAccents = (text) => {
            if (!text) return '';
            return text
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '') // Remove diacríticos
                .replace(/[^\x00-\x7F]/g, ''); // Remove não-ASCII
        };

        // Cálculo dinâmico de caracteres por linha
        // 58mm = 32 chars | 80mm = 48 chars | Outros proporcionais
        let charsPerLine;
        if (paperWidth <= 58) {
            charsPerLine = 32; // ⭐ 58mm - ajustado para não quebrar (era 48)
        } else if (paperWidth <= 80) {
            charsPerLine = 48; // 80mm padrão
        } else {
            // Para tamanhos maiores (ex: 110mm), calcular proporcionalmente
            charsPerLine = Math.floor(paperWidth * 0.6); // ~60% da largura em chars
        }

        let text = '';

        // ═══ CABEÇALHO PROFISSIONAL ═══
        text += this.line(charsPerLine, '=') + '\n';

        const title = removeAccents(this.getLocationTitle(location).replace(/=/g, '').trim());
        text += this.centerText(`** NOVO PEDIDO - ${title} **`, charsPerLine) + '\n';

        text += this.line(charsPerLine, '=') + '\n';
        text += '\n';

        // Número do pedido + Data/Hora (mesma linha se couber)
        const date = new Date(order.created_at);
        const timeStr = date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        const dateStr = date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });

        text += this.formatLine(`PEDIDO #${order.order_number}`, `${timeStr} - ${dateStr}`, charsPerLine) + '\n';
        text += '\n';

        // Cliente + Telefone
        text += `Cliente: ${removeAccents(order.customer.name)}\n`;
        if (order.customer.phone) {
            text += `Tel: ${order.customer.phone}\n`;
        }

        // Tipo de entrega
        const deliveryType = order.delivery.method === 'delivery' ? 'DELIVERY' : 'RETIRADA';
        text += '\n';
        text += `${deliveryType}`;
        if (order.delivery.neighborhood) {
            text += ` - Bairro: ${removeAccents(order.delivery.neighborhood)}`;
        }
        text += '\n';

        // Endereço (se delivery)
        if (order.delivery.method === 'delivery' && order.delivery.address) {
            text += `End: ${removeAccents(order.delivery.address)}\n`;
            if (order.delivery.reference) {
                text += `Ref: ${removeAccents(order.delivery.reference)}\n`;
            }
        }

        text += '\n';
        text += this.line(charsPerLine, '-') + '\n';
        text += this.centerText('** ITENS **', charsPerLine) + '\n';
        text += this.line(charsPerLine, '-') + '\n';
        text += '\n';

        // Itens do pedido
        order.items.forEach(item => {
            // Filtrar por localização se necessário
            if (location !== 'counter') {
                const itemLocation = item.print_location || 'kitchen';
                if (itemLocation !== location && itemLocation !== 'both') {
                    return;
                }
            }

            // Nome do produto com quantidade
            const itemName = removeAccents(`${item.quantity}x ${item.name.toUpperCase()}`);

            // Preço do item (se houver)
            if (location === 'counter' && item.price) {
                const itemTotal = (item.quantity * item.price).toFixed(2);
                text += this.formatLine(itemName, `R$ ${itemTotal}`, charsPerLine) + '\n';
            } else {
                text += `${itemName}\n`;
            }

            // Variações
            if (item.variations && Object.keys(item.variations).length > 0) {
                Object.entries(item.variations).forEach(([key, value]) => {
                    text += `   - ${removeAccents(key)}: ${removeAccents(value)}\n`;
                });
            }

            // Adicionais
            if (item.addons && item.addons.length > 0) {
                item.addons.forEach(addon => {
                    const addonName = typeof addon === 'object' ? addon.name : addon;
                    text += `   + ${removeAccents(addonName)}\n`;
                });
            }

            // Observações (destacado)
            if (item.notes) {
                text += `   >> OBS: ${removeAccents(item.notes)}\n`;
            }

            text += '\n';
        });

        // Observações gerais (se houver)
        if (order.notes) {
            text += this.line(charsPerLine, '-') + '\n';
            text += '** OBSERVACOES GERAIS **\n';
            text += `${order.notes}\n`;
        }

        // Totais (apenas balcão)
        if (location === 'counter') {
            text += this.line(charsPerLine, '-') + '\n';
            text += this.formatLine('Subtotal:', `R$ ${order.totals.subtotal.toFixed(2)}`, charsPerLine) + '\n';

            if (order.totals.delivery_fee > 0) {
                text += this.formatLine('Taxa Entrega:', `R$ ${order.totals.delivery_fee.toFixed(2)}`, charsPerLine) + '\n';
            }

            if (order.totals.discount > 0) {
                text += this.formatLine('Desconto:', `- R$ ${order.totals.discount.toFixed(2)}`, charsPerLine) + '\n';
            }

            text += this.line(charsPerLine, '=') + '\n';
            text += this.formatLine('** TOTAL **', `R$ ${order.totals.total.toFixed(2)}`, charsPerLine) + '\n';
            text += this.line(charsPerLine, '=') + '\n';

            // Forma de pagamento
            const paymentMethod = this.getPaymentMethodName(order.payment.method);
            text += `PAGAMENTO: ${paymentMethod}`;

            if (order.payment.status === 'paid') {
                text += ' - PAGO ✓';
            }
            text += '\n';
        }

        // Rodapé profissional
        text += '\n';
        text += this.line(charsPerLine, '=') + '\n';
        text += this.centerText('Impresso via YumGo Bridge', charsPerLine) + '\n';
        text += this.line(charsPerLine, '=') + '\n';
        text += '\n\n\n';

        return text;
    }

    /**
     * Centralizar texto
     */
    centerText(text, width) {
        const spaces = Math.floor((width - text.length) / 2);
        return ' '.repeat(Math.max(0, spaces)) + text;
    }

    /**
     * Gerar recibo formatado ESC/POS (v1.7.0 - configurações avançadas)
     */
    printReceipt(printer, order, location, copyNumber = 1, totalCopies = 1) {
        const config = this.printers[location].config;

        // Configurações v1.7.0
        const paperWidth = config.paperWidth || 80;
        const fontSize = config.fontSize || 'normal';
        const removeAccents = config.removeAccents || false;
        const printLogo = config.printLogo || false;
        const logoPath = config.logoPath || '';

        // Calcular largura de caracteres baseado no papel
        const charsPerLine = paperWidth === 58 ? 32 : 48;

        // Helper para remover acentos se configurado
        const formatText = (text) => {
            if (!removeAccents) return text;
            return text
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '') // Remove diacríticos
                .replace(/[^\x00-\x7F]/g, ''); // Remove não-ASCII
        };

        // Helper para tamanho de fonte
        const applyFontSize = (printer) => {
            if (fontSize === 'small') {
                return printer.size(1, 1);
            } else if (fontSize === 'large') {
                return printer.size(1, 2);
            }
            return printer.size(1, 1); // normal
        };

        // === LOGO (v1.7.0) ===
        if (printLogo && logoPath && fs.existsSync(logoPath)) {
            try {
                const Image = escpos.Image;
                Image.load(logoPath, (image) => {
                    printer
                        .align('ct')
                        .image(image, 's24')
                        .text('');
                });
            } catch (logoError) {
                log.warn(`Erro ao carregar logo: ${logoError.message}`);
            }
        }

        // Cabeçalho
        printer
            .align('ct')
            .style('b')
            .size(2, 2)
            .text(formatText('NOVO PEDIDO'))
            .size(1, 1)
            .style('normal')
            .text('');

        // Indicador de cópia (v1.7.0)
        if (totalCopies > 1) {
            printer
                .align('ct')
                .text(formatText(`--- COPIA ${copyNumber}/${totalCopies} ---`))
                .text('');
        }

        // Tipo de impressão
        const title = this.getLocationTitle(location);
        printer
            .style('b')
            .text(formatText(title))
            .style('normal')
            .text(this.line(charsPerLine, '-'))
            .text('');

        // Número do pedido
        applyFontSize(printer);
        printer
            .align('lt')
            .style('b')
            .size(2, 2)
            .text(formatText(`PEDIDO #${order.order_number}`))
            .size(1, 1)
            .style('normal')
            .text('');

        // Data/Hora
        const date = new Date(order.created_at);
        printer.text(formatText(`Data: ${date.toLocaleDateString('pt-BR')} ${date.toLocaleTimeString('pt-BR')}`));

        // Tipo de entrega
        const deliveryType = order.delivery.method === 'delivery' ? 'DELIVERY' : 'RETIRADA';
        printer.text(formatText(`Tipo: ${deliveryType}`));
        printer.text(this.line(charsPerLine, '-'));

        // Cliente
        printer
            .style('b')
            .text(formatText('CLIENTE:'))
            .style('normal')
            .text(formatText(order.customer.name));

        if (order.customer.phone) {
            printer.text(formatText(`Tel: ${order.customer.phone}`));
        }

        // Endereço (se delivery)
        if (order.delivery.method === 'delivery' && order.delivery.address) {
            printer
                .text('')
                .style('b')
                .text(formatText('ENDERECO:'))
                .style('normal')
                .text(formatText(this.wrapText(order.delivery.address, charsPerLine)));

            if (order.delivery.neighborhood) {
                printer.text(formatText(`Bairro: ${order.delivery.neighborhood}`));
            }

            if (order.delivery.reference) {
                printer.text(formatText(`Ref: ${this.wrapText(order.delivery.reference, charsPerLine - 5)}`));
            }
        }

        printer.text(this.line(charsPerLine, '-'));

        // Itens do pedido
        printer
            .style('b')
            .size(1, 2)
            .text(formatText('ITENS:'))
            .size(1, 1)
            .style('normal')
            .text('');

        order.items.forEach(item => {
            // Filtrar por localização se necessário
            if (location !== 'counter') {
                const itemLocation = item.print_location || 'kitchen';
                if (itemLocation !== location && itemLocation !== 'both') {
                    return; // Não imprimir este item nesta impressora
                }
            }

            // Nome do produto
            printer
                .style('b')
                .size(1, 2)
                .text(formatText(`${item.quantity}x ${item.name.toUpperCase()}`))
                .size(1, 1)
                .style('normal');

            // Variações
            if (item.variations && Object.keys(item.variations).length > 0) {
                Object.entries(item.variations).forEach(([key, value]) => {
                    printer.text(formatText(`  - ${key}: ${value}`));
                });
            }

            // Adicionais
            if (item.addons && item.addons.length > 0) {
                item.addons.forEach(addon => {
                    const addonName = typeof addon === 'object' ? addon.name : addon;
                    printer.text(formatText(`  + ${addonName}`));
                });
            }

            // Observações
            if (item.notes) {
                printer.text(formatText(`  OBS: ${this.wrapText(item.notes, charsPerLine - 6)}`));
            }

            printer.text('');
        });

        printer.text(this.line(charsPerLine, '-'));

        // Observações gerais
        if (order.notes) {
            printer
                .style('b')
                .text(formatText('OBSERVACOES GERAIS:'))
                .style('normal')
                .text(formatText(this.wrapText(order.notes, charsPerLine)))
                .text(this.line(charsPerLine, '-'));
        }

        // Totais (apenas balcão)
        if (location === 'counter') {
            printer
                .text(formatText(this.formatLine('Subtotal:', `R$ ${order.totals.subtotal.toFixed(2)}`, charsPerLine)));

            if (order.totals.delivery_fee > 0) {
                printer.text(formatText(this.formatLine('Taxa Entrega:', `R$ ${order.totals.delivery_fee.toFixed(2)}`, charsPerLine)));
            }

            if (order.totals.discount > 0) {
                printer.text(formatText(this.formatLine('Desconto:', `- R$ ${order.totals.discount.toFixed(2)}`, charsPerLine)));
            }

            printer
                .text(this.line(charsPerLine, '-'))
                .style('b')
                .size(2, 2)
                .text(formatText(this.formatLine('TOTAL:', `R$ ${order.totals.total.toFixed(2)}`, charsPerLine)))
                .size(1, 1)
                .style('normal')
                .text('');

            // Forma de pagamento
            const paymentMethod = this.getPaymentMethodName(order.payment.method);
            printer.text(formatText(this.formatLine('Pagamento:', paymentMethod, charsPerLine)));

            if (order.payment.status === 'paid') {
                printer
                    .style('b')
                    .text(formatText('Status: PAGO'))
                    .style('normal');
            }
        }

        // Rodapé
        printer
            .text('')
            .align('ct')
            .text(this.line(charsPerLine, '='))
            .text(formatText(new Date().toLocaleString('pt-BR')))
            .text(formatText('Impresso via YumGo Bridge'))
            .text(this.line(charsPerLine, '='))
            .text('')
            .text('')
            .text('');

        // Cortar papel
        printer.cut();
    }

    /**
     * Funções auxiliares
     */

    getLocationTitle(location) {
        const titles = {
            'kitchen': '=== COZINHA ===',
            'bar': '=== BAR ===',
            'counter': '=== BALCAO ===',
            'generic': '=== PEDIDO ==='
        };
        return titles[location] || '=== PEDIDO ===';
    }

    getPaymentMethodName(method) {
        const names = {
            'pix': 'PIX',
            'credit_card': 'Cartão Crédito',
            'debit_card': 'Cartão Débito',
            'money': 'Dinheiro'
        };
        return names[method] || method.toUpperCase();
    }

    line(width = 48, char = '-') {
        return char.repeat(width);
    }

    formatLine(label, value, width = 48) {
        const spaces = width - label.length - value.length;
        return label + ' '.repeat(Math.max(spaces, 1)) + value;
    }

    wrapText(text, width) {
        if (!text) return '';

        const words = text.split(' ');
        let lines = [];
        let currentLine = '';

        words.forEach(word => {
            if ((currentLine + word).length > width) {
                if (currentLine) lines.push(currentLine.trim());
                currentLine = word + ' ';
            } else {
                currentLine += word + ' ';
            }
        });

        if (currentLine) lines.push(currentLine.trim());

        return lines.join('\n');
    }
}

module.exports = ThermalPrinter;
