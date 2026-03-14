const escpos = require('escpos');
const USB = require('escpos-usb');
const Network = require('escpos-network');
const log = require('electron-log');
const fs = require('fs');
const { exec } = require('child_process'); // Impressão nativa via comandos do SO
const os = require('os');
const path = require('path');

class ThermalPrinter {
    constructor() {
        this.printers = {};
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
     * Imprimir em impressora do sistema (Windows/macOS/Linux) - v2.1.0
     * Usa comandos nativos do SO (sem dependências node-gyp)
     */
    async printSystemPrinter(orderData, location, copies) {
        const printerObj = this.printers[location];
        const { config } = printerObj;
        const printerName = config.printerName;

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
                    // Windows: usar PowerShell Out-Printer (mais confiável que PRINT)
                    printCommand = `powershell.exe -Command "Out-Printer -Name '${printerName}' -InputObject (Get-Content -Path '${tempFile}' -Raw)"`;
                } else if (process.platform === 'darwin') {
                    // macOS: usar lp
                    printCommand = `lp -d "${printerName}" "${tempFile}"`;
                } else {
                    // Linux: usar lp ou lpr
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
        const charsPerLine = paperWidth === 58 ? 32 : 48;

        let text = '';

        // Cabeçalho
        text += this.centerText('NOVO PEDIDO', charsPerLine) + '\n';
        text += '\n';

        // Tipo de impressão
        const title = this.getLocationTitle(location).replace(/=/g, '');
        text += this.centerText(title, charsPerLine) + '\n';
        text += this.line(charsPerLine, '-') + '\n';
        text += '\n';

        // Número do pedido
        text += `PEDIDO #${order.order_number}\n`;
        text += '\n';

        // Data/Hora
        const date = new Date(order.created_at);
        text += `Data: ${date.toLocaleDateString('pt-BR')} ${date.toLocaleTimeString('pt-BR')}\n`;

        // Tipo de entrega
        const deliveryType = order.delivery.method === 'delivery' ? 'DELIVERY' : 'RETIRADA';
        text += `Tipo: ${deliveryType}\n`;
        text += this.line(charsPerLine, '-') + '\n';

        // Cliente
        text += 'CLIENTE:\n';
        text += `${order.customer.name}\n`;
        if (order.customer.phone) {
            text += `Tel: ${order.customer.phone}\n`;
        }

        // Endereço (se delivery)
        if (order.delivery.method === 'delivery' && order.delivery.address) {
            text += '\n';
            text += 'ENDERECO:\n';
            text += `${order.delivery.address}\n`;
            if (order.delivery.neighborhood) {
                text += `Bairro: ${order.delivery.neighborhood}\n`;
            }
            if (order.delivery.reference) {
                text += `Ref: ${order.delivery.reference}\n`;
            }
        }

        text += this.line(charsPerLine, '-') + '\n';

        // Itens do pedido
        text += 'ITENS:\n';
        text += '\n';

        order.items.forEach(item => {
            // Filtrar por localização se necessário
            if (location !== 'counter') {
                const itemLocation = item.print_location || 'kitchen';
                if (itemLocation !== location && itemLocation !== 'both') {
                    return;
                }
            }

            // Nome do produto
            text += `${item.quantity}x ${item.name.toUpperCase()}\n`;

            // Variações
            if (item.variations && Object.keys(item.variations).length > 0) {
                Object.entries(item.variations).forEach(([key, value]) => {
                    text += `  - ${key}: ${value}\n`;
                });
            }

            // Adicionais
            if (item.addons && item.addons.length > 0) {
                item.addons.forEach(addon => {
                    const addonName = typeof addon === 'object' ? addon.name : addon;
                    text += `  + ${addonName}\n`;
                });
            }

            // Observações
            if (item.notes) {
                text += `  OBS: ${item.notes}\n`;
            }

            text += '\n';
        });

        text += this.line(charsPerLine, '-') + '\n';

        // Observações gerais
        if (order.notes) {
            text += 'OBSERVACOES GERAIS:\n';
            text += `${order.notes}\n`;
            text += this.line(charsPerLine, '-') + '\n';
        }

        // Totais (apenas balcão)
        if (location === 'counter') {
            text += this.formatLine('Subtotal:', `R$ ${order.totals.subtotal.toFixed(2)}`, charsPerLine) + '\n';

            if (order.totals.delivery_fee > 0) {
                text += this.formatLine('Taxa Entrega:', `R$ ${order.totals.delivery_fee.toFixed(2)}`, charsPerLine) + '\n';
            }

            if (order.totals.discount > 0) {
                text += this.formatLine('Desconto:', `- R$ ${order.totals.discount.toFixed(2)}`, charsPerLine) + '\n';
            }

            text += this.line(charsPerLine, '-') + '\n';
            text += this.formatLine('TOTAL:', `R$ ${order.totals.total.toFixed(2)}`, charsPerLine) + '\n';
            text += '\n';

            // Forma de pagamento
            const paymentMethod = this.getPaymentMethodName(order.payment.method);
            text += this.formatLine('Pagamento:', paymentMethod, charsPerLine) + '\n';

            if (order.payment.status === 'paid') {
                text += 'Status: PAGO\n';
            }
        }

        // Rodapé
        text += '\n';
        text += this.centerText(this.line(charsPerLine, '='), charsPerLine) + '\n';
        text += this.centerText(new Date().toLocaleString('pt-BR'), charsPerLine) + '\n';
        text += this.centerText('Impresso via YumGo Bridge', charsPerLine) + '\n';
        text += this.centerText(this.line(charsPerLine, '='), charsPerLine) + '\n';
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
