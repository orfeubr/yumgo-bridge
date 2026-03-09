<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }} - Cadastro Não Aprovado</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
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
            background: #FEE2E2;
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
            color: #EF4444;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .reason-box {
            background: #FEF2F2;
            border-left: 4px solid #EF4444;
            border-radius: 8px;
            padding: 25px;
            text-align: left;
            margin: 30px 0;
        }

        .reason-box h3 {
            font-size: 16px;
            color: #991B1B;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .reason-box p {
            color: #7F1D1D;
            font-size: 14px;
            line-height: 1.8;
            white-space: pre-line;
        }

        .info-box {
            background: #F3F4F6;
            border-radius: 12px;
            padding: 25px;
            text-align: left;
            margin-top: 20px;
        }

        .info-box h3 {
            font-size: 16px;
            color: #374151;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .info-box p {
            color: #6B7280;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .action-button {
            display: inline-block;
            background: #3B82F6;
            color: white;
            padding: 14px 32px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 30px;
            transition: all 0.3s;
            font-size: 16px;
        }

        .action-button:hover {
            background: #2563EB;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
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
            opacity: 0.6;
            filter: grayscale(100%);
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            <div class="icon">❌</div>
        @endif

        <h1>{{ $tenant->name }}</h1>
        <div class="subtitle">Cadastro Não Aprovado</div>

        @if($reason)
            <div class="reason-box">
                <h3>📋 Motivo da Recusa</h3>
                <p>{{ $reason }}</p>
            </div>
        @else
            <div class="reason-box">
                <h3>📋 Motivo da Recusa</h3>
                <p>Infelizmente seu cadastro não foi aprovado. Entre em contato conosco para mais informações.</p>
            </div>
        @endif

        <div class="info-box">
            <h3>🔄 O que fazer agora?</h3>
            <p>
                Se você corrigiu os problemas mencionados ou acredita que houve um erro na análise,
                entre em contato conosco para solicitar uma nova revisão.
            </p>
            <p>
                Nossa equipe terá prazer em ajudá-lo a resolver qualquer questão e reativar seu cadastro.
            </p>
        </div>

        <a href="mailto:contato@yumgo.com.br?subject=Revisão de Cadastro - {{ $tenant->name }}" class="action-button">
            📧 Entrar em Contato
        </a>

        <div class="footer">
            <p><strong>Email:</strong> contato@yumgo.com.br</p>
            <p><strong>WhatsApp:</strong> (11) 99999-9999</p>
        </div>
    </div>
</body>
</html>
