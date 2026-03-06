const escpos = require('escpos');
const USB = require('escpos-usb');
const Network = require('escpos-network');
const log = require('electron-log');
const fs = require('fs');

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

            if (config.type === 'usb') {
                device = new USB(config.vendorId, config.productId);
            } else if (config.type === 'network') {
                device = new Network(config.ip, config.port || 9100);
            } else {
                throw new Error(`Tipo de impressora não suportado: ${config.type}`);
            }

            this.printers[location] = {
                device: device,
                printer: new escpos.Printer(device),
                config: config
            };

            log.info(`Impressora ${location} configurada: ${config.type}`);

        } catch (error) {
            log.error(`Erro ao configurar impressora ${location}:`, error);
            throw error;
        }
    }

    /**
     * Imprimir pedido (v1.7.0 - suporte a múltiplas cópias)
     */
    async printOrder(orderData, location) {
        const printerObj = this.printers[location];

        if (!printerObj) {
            throw new Error(`Impressora ${location} não configurada`);
        }

        const { config } = printerObj;
        const copies = config.copies || 1;

        log.info(`Imprimindo ${copies} cópia(s) do pedido #${orderData.order_number} em ${location}`);

        return new Promise((resolve, reject) => {
            const { device, printer } = printerObj;

            device.open((error) => {
                if (error) {
                    log.error(`Erro ao abrir impressora ${location}:`, error);
                    reject(error);
                    return;
                }

                try {
                    // Imprimir múltiplas cópias (v1.7.0)
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
     * Gerar recibo formatado (v1.7.0 - configurações avançadas)
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
