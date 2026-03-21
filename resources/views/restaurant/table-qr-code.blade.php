<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - Mesa {{ $table->number }}</title>

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
            border: 3px solid #EA1D2C;
            padding: 30px;
            border-radius: 15px;
            margin: 20px 0;
        }

        .table-number {
            font-size: 48px;
            font-weight: bold;
            color: #EA1D2C;
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
            background: #EA1D2C;
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
            background: #c51825;
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
            <h1 style="color: #EA1D2C; margin: 0;">QR Code para Mesa</h1>
            <p style="color: #666;">Imprima e cole na mesa para os clientes fazerem pedidos</p>
        </div>

        <!-- QR Code -->
        <div class="qr-container">
            <div style="text-align: center; margin-bottom: 20px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#EA1D2C" stroke-width="2" style="display: inline-block;">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>

            {!! $qr !!}

            <div class="table-number">
                Mesa {{ $table->number }}
            </div>

            <div class="instruction">
                📱 Aponte a câmera do celular<br>
                para escanear e fazer seu pedido
            </div>

            @if($table->seats > 0)
                <div style="color: #6b7280; font-size: 14px; margin-top: 10px;">
                    Capacidade: {{ $table->seats }} pessoa{{ $table->seats > 1 ? 's' : '' }}
                </div>
            @endif
        </div>

        <!-- URL (apenas na tela) -->
        <div class="url-display no-print">
            <strong>URL:</strong> {{ $table->qr_url }}
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
