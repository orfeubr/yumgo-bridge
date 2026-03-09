<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }} - Aguardando Aprovação</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 60px 40px;
            text-align: center;
        }

        .icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: #FEF3C7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
        }

        h1 {
            font-size: 32px;
            color: #1F2937;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .subtitle {
            font-size: 18px;
            color: #6B7280;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .message {
            background: #F3F4F6;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }

        .message h3 {
            font-size: 16px;
            color: #374151;
            margin-bottom: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .message ul {
            list-style: none;
            color: #6B7280;
            font-size: 14px;
            line-height: 1.8;
        }

        .message ul li {
            padding-left: 24px;
            position: relative;
            margin-bottom: 8px;
        }

        .message ul li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #10B981;
            font-weight: bold;
        }

        .info-box {
            background: #EFF6FF;
            border-left: 4px solid #3B82F6;
            border-radius: 8px;
            padding: 20px;
            text-align: left;
            margin-top: 20px;
        }

        .info-box p {
            color: #1E40AF;
            font-size: 14px;
            line-height: 1.6;
        }

        .footer {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #E5E7EB;
            color: #9CA3AF;
            font-size: 14px;
        }

        .logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            background: #F3F4F6;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .logo-placeholder {
            font-size: 40px;
            color: #9CA3AF;
        }

        @media (max-width: 640px) {
            .container {
                padding: 40px 24px;
            }

            h1 {
                font-size: 26px;
            }

            .subtitle {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @if($tenant->logo)
            <div class="logo">
                <img src="{{ asset('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}">
            </div>
        @else
            <div class="icon">⏳</div>
        @endif

        <h1>{{ $tenant->name }}</h1>
        <div class="subtitle">Aguardando Aprovação</div>

        <div class="message">
            <h3>
                <span style="color: #F59E0B;">⚠️</span>
                Seu cadastro está em análise
            </h3>
            <ul>
                <li>Nossa equipe está revisando suas informações</li>
                <li>O processo geralmente leva até 24 horas</li>
                <li>Você receberá um email assim que for aprovado</li>
                <li>Após aprovação, seu restaurante ficará visível no marketplace</li>
            </ul>
        </div>

        <div class="info-box">
            <p>
                <strong>💡 Dica:</strong> Enquanto aguarda, você já pode acessar o
                <a href="/painel" style="color: #2563EB; text-decoration: none; font-weight: 600;">painel administrativo</a>
                e começar a cadastrar seus produtos e configurar o cardápio!
            </p>
        </div>

        <div class="footer">
            <p>Em caso de dúvidas, entre em contato conosco:</p>
            <p><strong>contato@yumgo.com.br</strong></p>
        </div>
    </div>
</body>
</html>
