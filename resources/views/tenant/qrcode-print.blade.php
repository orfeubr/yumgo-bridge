<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>QR Code - {{ $tenant->name }}</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #f3f4f6;
        }

        .print-container {
            background: white;
            padding: 60px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 600px;
        }

        .logo {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #EA1D2C, #DC2626);
            border-radius: 20px;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
        }

        h1 {
            font-size: 32px;
            color: #1a1a1a;
            margin: 0 0 10px 0;
            font-weight: 900;
        }

        .subtitle {
            font-size: 18px;
            color: #666;
            margin: 0 0 40px 0;
        }

        .qr-code {
            display: inline-block;
            padding: 30px;
            background: white;
            border: 4px solid #EA1D2C;
            border-radius: 20px;
            margin: 20px 0;
        }

        .instructions {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px dashed #e5e5e5;
        }

        .instructions h2 {
            font-size: 24px;
            color: #EA1D2C;
            margin: 0 0 20px 0;
        }

        .steps {
            text-align: left;
            display: inline-block;
            font-size: 16px;
            line-height: 1.8;
        }

        .steps li {
            margin: 10px 0;
        }

        .url {
            font-size: 14px;
            color: #999;
            margin-top: 20px;
            word-break: break-all;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="logo">📱</div>

        <h1>{{ $tenant->name }}</h1>
        <p class="subtitle">Cardápio Digital</p>

        <div class="qr-code">
            {!! QrCode::size(350)->margin(2)->errorCorrection('H')->generate($url) !!}
        </div>

        <p class="url">{{ $url }}</p>

        <div class="instructions">
            <h2>📲 Como Fazer seu Pedido</h2>
            <ol class="steps">
                <li>Aponte a câmera do seu celular para o QR Code</li>
                <li>Toque no link que aparecer na tela</li>
                <li>Navegue pelo cardápio e escolha seus produtos</li>
                <li>Finalize o pedido e escolha a forma de pagamento</li>
            </ol>
        </div>

        <div class="no-print" style="margin-top: 40px;">
            <button onclick="window.print()" style="padding: 15px 30px; background: #EA1D2C; color: white; border: none; border-radius: 10px; font-size: 18px; font-weight: bold; cursor: pointer;">
                🖨️ Imprimir
            </button>
            <button onclick="window.close()" style="padding: 15px 30px; background: #666; color: white; border: none; border-radius: 10px; font-size: 18px; font-weight: bold; cursor: pointer; margin-left: 10px;">
                Fechar
            </button>
        </div>
    </div>

    <script>
        // Auto-print quando abrir
        // window.addEventListener('load', () => {
        //     setTimeout(() => window.print(), 500);
        // });
    </script>
</body>
</html>
