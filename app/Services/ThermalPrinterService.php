<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PrinterConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Serviço de Impressão Térmica
 *
 * Gera comandos ESC/POS para impressoras térmicas 80mm
 * Suporta impressão via:
 * - Local (USB/Rede via ponte)
 * - Cloud (PrintNode, ePrint, etc.)
 * - Webhook (para apps mobile)
 */
class ThermalPrinterService
{
    // Caracteres especiais ESC/POS
    const ESC = "\x1B";
    const GS = "\x1D";
    const LF = "\x0A";
    const CUT = "\x1B\x69"; // Cortar papel

    // Largura do papel 80mm (42-48 caracteres dependendo da fonte)
    const WIDTH = 48;

    /**
     * Imprimir pedido em impressora térmica
     */
    public function printOrder(Order $order, string $printerType = 'kitchen'): bool
    {
        try {
            // Buscar configuração da impressora
            $printerConfig = PrinterConfig::where('type', $printerType)
                ->where('is_active', true)
                ->first();

            if (!$printerConfig) {
                Log::warning("Impressora {$printerType} não configurada ou inativa");
                return false;
            }

            // Gerar conteúdo ESC/POS
            $content = $this->generateOrderReceipt($order, $printerType);

            // Enviar para impressora
            return $this->sendToPrinter($content, $printerConfig);

        } catch (\Exception $e) {
            Log::error("Erro ao imprimir pedido #{$order->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gerar recibo do pedido (formato ESC/POS)
     */
    private function generateOrderReceipt(Order $order, string $printerType): string
    {
        $tenant = tenant();
        $receipt = '';

        // ===== CABEÇALHO =====
        $receipt .= $this->setAlign('center');
        $receipt .= $this->setBold(true);
        $receipt .= $this->setSize(2, 2);
        $receipt .= strtoupper($tenant->name) . self::LF;
        $receipt .= $this->setSize(1, 1);
        $receipt .= $this->setBold(false);
        $receipt .= self::LF;

        // Tipo de impressão
        $title = match($printerType) {
            'kitchen' => '=== COZINHA ===',
            'bar' => '=== BAR ===',
            'counter' => '=== BALCÃO ===',
            default => '=== PEDIDO ===',
        };
        $receipt .= $this->setBold(true);
        $receipt .= $title . self::LF;
        $receipt .= $this->setBold(false);
        $receipt .= self::LF;

        // ===== DADOS DO PEDIDO =====
        $receipt .= $this->setAlign('left');
        $receipt .= $this->line();

        $receipt .= $this->setBold(true);
        $receipt .= $this->setSize(2, 2);
        $receipt .= "PEDIDO #" . str_pad($order->id, 4, '0', STR_PAD_LEFT) . self::LF;
        $receipt .= $this->setSize(1, 1);
        $receipt .= $this->setBold(false);

        $receipt .= $this->formatLine('Data/Hora:', $order->created_at->format('d/m/Y H:i'));

        if ($order->delivery_method === 'delivery') {
            $receipt .= $this->formatLine('Tipo:', 'DELIVERY 🏍️');
        } else {
            $receipt .= $this->formatLine('Tipo:', 'RETIRADA 🏪');
        }

        $receipt .= $this->line();

        // ===== CLIENTE =====
        $receipt .= $this->setBold(true);
        $receipt .= "CLIENTE:" . self::LF;
        $receipt .= $this->setBold(false);
        $receipt .= $order->customer_name . self::LF;

        if ($order->customer_phone) {
            $receipt .= 'Tel: ' . $order->customer_phone . self::LF;
        }

        if ($order->delivery_method === 'delivery' && $order->delivery_address) {
            $receipt .= self::LF;
            $receipt .= $this->setBold(true);
            $receipt .= "ENDERECO:" . self::LF;
            $receipt .= $this->setBold(false);
            $receipt .= $this->wrapText($order->delivery_address, self::WIDTH);

            if ($order->delivery_neighborhood) {
                $receipt .= 'Bairro: ' . $order->delivery_neighborhood . self::LF;
            }

            if ($order->delivery_reference) {
                $receipt .= 'Ref: ' . $this->wrapText($order->delivery_reference, self::WIDTH);
            }
        }

        $receipt .= $this->line();

        // ===== ITENS DO PEDIDO =====
        $receipt .= $this->setBold(true);
        $receipt .= $this->setSize(1, 2);
        $receipt .= "ITENS:" . self::LF;
        $receipt .= $this->setSize(1, 1);
        $receipt .= $this->setBold(false);
        $receipt .= self::LF;

        foreach ($order->items as $item) {
            // Filtrar itens por impressora se configurado
            if ($printerType === 'kitchen' && $item->product->print_location === 'bar') {
                continue; // Item do bar, não imprime na cozinha
            }
            if ($printerType === 'bar' && $item->product->print_location === 'kitchen') {
                continue; // Item da cozinha, não imprime no bar
            }

            $receipt .= $this->setBold(true);
            $receipt .= $this->setSize(1, 2);
            $receipt .= "{$item->quantity}x ";
            $receipt .= $this->setSize(1, 1);
            $receipt .= strtoupper($item->product_name) . self::LF;
            $receipt .= $this->setBold(false);

            // Variações (tamanho, sabor, etc)
            if (!empty($item->variations)) {
                $variations = is_string($item->variations) ? json_decode($item->variations, true) : $item->variations;
                if (is_array($variations)) {
                    foreach ($variations as $key => $value) {
                        $receipt .= "  - {$key}: {$value}" . self::LF;
                    }
                }
            }

            // Adicionais
            if (!empty($item->addons)) {
                $addons = is_string($item->addons) ? json_decode($item->addons, true) : $item->addons;
                if (is_array($addons)) {
                    foreach ($addons as $addon) {
                        $addonName = is_array($addon) ? $addon['name'] : $addon;
                        $receipt .= "  + {$addonName}" . self::LF;
                    }
                }
            }

            // Observações
            if (!empty($item->notes)) {
                $receipt .= "  OBS: " . $this->wrapText($item->notes, self::WIDTH - 7);
            }

            $receipt .= self::LF;
        }

        $receipt .= $this->line();

        // ===== OBSERVAÇÕES GERAIS =====
        if (!empty($order->notes)) {
            $receipt .= $this->setBold(true);
            $receipt .= "OBSERVACOES GERAIS:" . self::LF;
            $receipt .= $this->setBold(false);
            $receipt .= $this->wrapText($order->notes, self::WIDTH);
            $receipt .= $this->line();
        }

        // ===== RESUMO FINANCEIRO (apenas balcão) =====
        if ($printerType === 'counter') {
            $receipt .= $this->formatLine('Subtotal:', 'R$ ' . number_format($order->subtotal, 2, ',', '.'));

            if ($order->delivery_fee > 0) {
                $receipt .= $this->formatLine('Taxa Entrega:', 'R$ ' . number_format($order->delivery_fee, 2, ',', '.'));
            }

            if ($order->discount > 0) {
                $receipt .= $this->formatLine('Desconto:', '- R$ ' . number_format($order->discount, 2, ',', '.'));
            }

            $receipt .= $this->line();
            $receipt .= $this->setBold(true);
            $receipt .= $this->setSize(2, 2);
            $receipt .= $this->formatLine('TOTAL:', 'R$ ' . number_format($order->total, 2, ',', '.'));
            $receipt .= $this->setSize(1, 1);
            $receipt .= $this->setBold(false);
            $receipt .= self::LF;

            // Forma de pagamento
            $paymentMethod = match($order->payment_method) {
                'pix' => 'PIX',
                'credit_card' => 'Cartão Crédito',
                'debit_card' => 'Cartão Débito',
                'money' => 'Dinheiro',
                default => ucfirst($order->payment_method),
            };
            $receipt .= $this->formatLine('Pagamento:', $paymentMethod);

            if ($order->payment_status === 'paid') {
                $receipt .= $this->setBold(true);
                $receipt .= 'Status: PAGO ✓' . self::LF;
                $receipt .= $this->setBold(false);
            }
        }

        // ===== RODAPÉ =====
        $receipt .= self::LF;
        $receipt .= $this->setAlign('center');
        $receipt .= $this->line('=');
        $receipt .= date('d/m/Y H:i:s') . self::LF;
        $receipt .= 'Impresso via YumGo' . self::LF;
        $receipt .= $this->line('=');
        $receipt .= self::LF . self::LF . self::LF;

        // Cortar papel
        $receipt .= self::CUT;

        return $receipt;
    }

    /**
     * Enviar para impressora
     */
    private function sendToPrinter(string $content, PrinterConfig $config): bool
    {
        switch ($config->connection_type) {
            case 'network':
                return $this->sendToNetworkPrinter($content, $config->ip_address, $config->port ?? 9100);

            case 'usb':
                return $this->sendToUSBPrinter($content, $config->device_path);

            case 'cloud':
                return $this->sendToCloudPrinter($content, $config);

            case 'webhook':
                return $this->sendViaWebhook($content, $config->webhook_url);

            default:
                Log::warning("Tipo de conexão não suportado: {$config->connection_type}");
                return false;
        }
    }

    /**
     * Enviar para impressora de rede (TCP/IP)
     */
    private function sendToNetworkPrinter(string $content, string $ip, int $port): bool
    {
        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

            if (!$socket) {
                throw new \Exception("Falha ao criar socket");
            }

            socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 5, 'usec' => 0]);

            if (!socket_connect($socket, $ip, $port)) {
                throw new \Exception("Falha ao conectar em {$ip}:{$port}");
            }

            socket_write($socket, $content, strlen($content));
            socket_close($socket);

            Log::info("Impressão enviada para {$ip}:{$port}");
            return true;

        } catch (\Exception $e) {
            Log::error("Erro ao enviar para impressora de rede: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar para impressora USB (via device)
     */
    private function sendToUSBPrinter(string $content, string $devicePath): bool
    {
        try {
            if (!file_exists($devicePath)) {
                throw new \Exception("Device não encontrado: {$devicePath}");
            }

            $handle = fopen($devicePath, 'w');

            if (!$handle) {
                throw new \Exception("Falha ao abrir device");
            }

            fwrite($handle, $content);
            fclose($handle);

            Log::info("Impressão enviada para {$devicePath}");
            return true;

        } catch (\Exception $e) {
            Log::error("Erro ao enviar para impressora USB: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar para serviço de nuvem (PrintNode, ePrint, etc.)
     */
    private function sendToCloudPrinter(string $content, PrinterConfig $config): bool
    {
        try {
            // Exemplo: PrintNode API
            $response = Http::withBasicAuth($config->cloud_api_key, '')
                ->post('https://api.printnode.com/printjobs', [
                    'printerId' => $config->cloud_printer_id,
                    'title' => 'Pedido YumGo',
                    'contentType' => 'raw_base64',
                    'content' => base64_encode($content),
                    'source' => 'YumGo',
                ]);

            if ($response->successful()) {
                Log::info("Impressão enviada para PrintNode");
                return true;
            }

            throw new \Exception("PrintNode retornou erro: " . $response->body());

        } catch (\Exception $e) {
            Log::error("Erro ao enviar para nuvem: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar via webhook (para apps mobile)
     */
    private function sendViaWebhook(string $content, string $webhookUrl): bool
    {
        try {
            $response = Http::timeout(10)->post($webhookUrl, [
                'content' => base64_encode($content),
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($response->successful()) {
                Log::info("Impressão enviada via webhook");
                return true;
            }

            throw new \Exception("Webhook retornou erro: " . $response->status());

        } catch (\Exception $e) {
            Log::error("Erro ao enviar via webhook: " . $e->getMessage());
            return false;
        }
    }

    // ===== FUNÇÕES AUXILIARES ESC/POS =====

    private function setAlign(string $align): string
    {
        return match($align) {
            'left' => self::ESC . 'a' . chr(0),
            'center' => self::ESC . 'a' . chr(1),
            'right' => self::ESC . 'a' . chr(2),
            default => '',
        };
    }

    private function setBold(bool $bold): string
    {
        return self::ESC . 'E' . ($bold ? chr(1) : chr(0));
    }

    private function setSize(int $width, int $height): string
    {
        $size = (($width - 1) << 4) | ($height - 1);
        return self::GS . '!' . chr($size);
    }

    private function line(string $char = '-'): string
    {
        return str_repeat($char, self::WIDTH) . self::LF;
    }

    private function formatLine(string $label, string $value): string
    {
        $labelLen = mb_strlen($label);
        $valueLen = mb_strlen($value);
        $spaces = self::WIDTH - $labelLen - $valueLen;

        if ($spaces < 1) {
            return $label . self::LF . $value . self::LF;
        }

        return $label . str_repeat(' ', $spaces) . $value . self::LF;
    }

    private function wrapText(string $text, int $width): string
    {
        $lines = explode("\n", wordwrap($text, $width, "\n", true));
        return implode(self::LF, $lines) . self::LF;
    }
}
