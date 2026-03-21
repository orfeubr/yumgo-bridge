<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - Balcão</title>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 20mm;
            }

            .qr-container {
                page-break-inside: avoid;
            }
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .qr-container {
            text-align: center;
            border: 3px solid #10b981;
            padding: 30px;
            border-radius: 15px;
            margin: 20px 0;
        }

        .title {
            font-size: 48px;
            font-weight: bold;
            color: #10b981;
            margin: 20px 0 10px;
        }

        .instruction {
            font-size: 18px;
            color: #666;
            margin: 15px 0;
            line-height: 1.6;
        }

        .buttons {
            text-align: center;
            margin-top: 30px;
        }

        button {
            background: #10b981;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            margin: 5px;
        }

        button:hover {
            background: #059669;
        }

        button.secondary {
            background: #6b7280;
        }

        button.secondary:hover {
            background: #4b5563;
        }

        .url-display {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            color: #6b7280;
            word-break: break-all;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="container">

        <!-- Cabeçalho -->
        <div style="text-align: center; margin-bottom: 30px;" class="no-print">
            <h1 style="color: #10b981; margin: 0;">QR Code para Balcão</h1>
            <p style="color: #666;">Imprima e cole no balcão para os clientes fazerem pedidos</p>
        </div>

        <!-- QR Code -->
        <div class="qr-container">
            <div style="text-align: center; margin-bottom: 20px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" style="display: inline-block;">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>

            {!! $qr !!}

            <div class="title">
                🧍 Balcão
            </div>

            <div class="instruction">
                📱 Aponte a câmera do celular<br>
                para escanear e fazer seu pedido<br>
                <strong style="color: #10b981;">direto no balcão</strong>
            </div>

            <div style="background: #ecfdf5; border: 2px dashed #10b981; padding: 15px; border-radius: 10px; margin-top: 20px;">
                <p style="margin: 0; color: #065f46; font-weight: bold;">✓ Pedido sem garçom</p>
                <p style="margin: 5px 0 0; color: #047857; font-size: 14px;">Retire direto no balcão</p>
            </div>
        </div>

        <!-- URL (apenas na tela) -->
        <div class="url-display no-print">
            <strong>URL:</strong> {{ $url }}
        </div>

        <!-- Botões (apenas na tela) -->
        <div class="buttons no-print">
            <button onclick="window.print()">
                🖨️ Imprimir
            </button>
            <button class="secondary" onclick="window.close()">
                ✕ Fechar
            </button>
        </div>

        <!-- Rodapé de impressão -->
        <div style="text-align: center; margin-top: 40px; color: #9ca3af; font-size: 12px; display: none;" class="print-only">
            <p>{{ tenant()->name }} | {{ now()->format('d/m/Y H:i') }}</p>
        </div>

    </div>

    <style>
        @media print {
            .print-only {
                display: block !important;
            }
        }
    </style>

</body>
</html>
