const escpos = require('escpos');
const USB = require('escpos-usb');
const Network = require('escpos-network');
const log = require('electron-log');

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
     * Imprimir pedido
     */
    async printOrder(orderData, location) {
        const printerObj = this.printers[location];

        if (!printerObj) {
            throw new Error(`Impressora ${location} não configurada`);
        }

        return new Promise((resolve, reject) => {
            const { device, printer } = printerObj;

            device.open((error) => {
                if (error) {
                    log.error(`Erro ao abrir impressora ${location}:`, error);
                    reject(error);
                    return;
                }

                try {
                    this.printReceipt(printer, orderData, location);

                    device.close(() => {
                        log.info(`Pedido #${orderData.order_number} impresso em ${location}`);
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
     * Gerar recibo formatado
     */
    printReceipt(printer, order, location) {
        // Cabeçalho
        printer
            .align('ct')
            .style('b')
            .size(2, 2)
            .text('NOVO PEDIDO')
            .size(1, 1)
            .style('normal')
            .text('');

        // Tipo de impressão
        const title = this.getLocationTitle(location);
        printer
            .style('b')
            .text(title)
            .style('normal')
            .text(this.line())
            .text('');

        // Número do pedido
        printer
            .align('lt')
            .style('b')
            .size(2, 2)
            .text(`PEDIDO #${order.order_number}`)
            .size(1, 1)
            .style('normal')
            .text('');

        // Data/Hora
        const date = new Date(order.created_at);
        printer.text(`Data: ${date.toLocaleDateString('pt-BR')} ${date.toLocaleTimeString('pt-BR')}`);

        // Tipo de entrega
        const deliveryType = order.delivery.method === 'delivery' ? 'DELIVERY' : 'RETIRADA';
        printer.text(`Tipo: ${deliveryType}`);
        printer.text(this.line());

        // Cliente
        printer
            .style('b')
            .text('CLIENTE:')
            .style('normal')
            .text(order.customer.name);

        if (order.customer.phone) {
            printer.text(`Tel: ${order.customer.phone}`);
        }

        // Endereço (se delivery)
        if (order.delivery.method === 'delivery' && order.delivery.address) {
            printer
                .text('')
                .style('b')
                .text('ENDERECO:')
                .style('normal')
                .text(this.wrapText(order.delivery.address, 48));

            if (order.delivery.neighborhood) {
                printer.text(`Bairro: ${order.delivery.neighborhood}`);
            }

            if (order.delivery.reference) {
                printer.text(`Ref: ${this.wrapText(order.delivery.reference, 43)}`);
            }
        }

        printer.text(this.line());

        // Itens do pedido
        printer
            .style('b')
            .size(1, 2)
            .text('ITENS:')
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
                .text(`${item.quantity}x ${item.name.toUpperCase()}`)
                .size(1, 1)
                .style('normal');

            // Variações
            if (item.variations && Object.keys(item.variations).length > 0) {
                Object.entries(item.variations).forEach(([key, value]) => {
                    printer.text(`  - ${key}: ${value}`);
                });
            }

            // Adicionais
            if (item.addons && item.addons.length > 0) {
                item.addons.forEach(addon => {
                    const addonName = typeof addon === 'object' ? addon.name : addon;
                    printer.text(`  + ${addonName}`);
                });
            }

            // Observações
            if (item.notes) {
                printer.text(`  OBS: ${this.wrapText(item.notes, 42)}`);
            }

            printer.text('');
        });

        printer.text(this.line());

        // Observações gerais
        if (order.notes) {
            printer
                .style('b')
                .text('OBSERVACOES GERAIS:')
                .style('normal')
                .text(this.wrapText(order.notes, 48))
                .text(this.line());
        }

        // Totais (apenas balcão)
        if (location === 'counter') {
            printer
                .text(this.formatLine('Subtotal:', `R$ ${order.totals.subtotal.toFixed(2)}`));

            if (order.totals.delivery_fee > 0) {
                printer.text(this.formatLine('Taxa Entrega:', `R$ ${order.totals.delivery_fee.toFixed(2)}`));
            }

            if (order.totals.discount > 0) {
                printer.text(this.formatLine('Desconto:', `- R$ ${order.totals.discount.toFixed(2)}`));
            }

            printer
                .text(this.line())
                .style('b')
                .size(2, 2)
                .text(this.formatLine('TOTAL:', `R$ ${order.totals.total.toFixed(2)}`))
                .size(1, 1)
                .style('normal')
                .text('');

            // Forma de pagamento
            const paymentMethod = this.getPaymentMethodName(order.payment.method);
            printer.text(this.formatLine('Pagamento:', paymentMethod));

            if (order.payment.status === 'paid') {
                printer
                    .style('b')
                    .text('Status: PAGO')
                    .style('normal');
            }
        }

        // Rodapé
        printer
            .text('')
            .align('ct')
            .text(this.line('='))
            .text(new Date().toLocaleString('pt-BR'))
            .text('Impresso via YumGo Bridge')
            .text(this.line('='))
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

    line(char = '-') {
        return char.repeat(48);
    }

    formatLine(label, value) {
        const spaces = 48 - label.length - value.length;
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
