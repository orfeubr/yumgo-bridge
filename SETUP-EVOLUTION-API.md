# 🚀 Setup Rápido: Evolution API (WhatsApp)

**API de WhatsApp Gratuita e Open Source**

---

## 🎯 Por Que Evolution API?

✅ **GRATUITO** (100% open source)
✅ **Fácil** (Docker em 5 minutos)
✅ **Confiável** (comunidade ativa brasileira)
✅ **Completo** (enviar/receber mensagens, webhook, multi-instância)
✅ **Sem Limites** (diferente do Twilio que cobra por mensagem)

**Custo Total:** R$ 0 (se já tem servidor) ou R$ 20-40/mês (VPS)

---

## 📋 OPÇÃO 1: Servidor Próprio (VPS)

### Requisitos Mínimos:
- **CPU:** 1 vCore
- **RAM:** 1 GB
- **Disco:** 10 GB
- **Sistema:** Ubuntu 22.04 / Debian 11

### Provedores Recomendados:
- **Contabo:** R$ 19,90/mês (4GB RAM)
- **DigitalOcean:** $6/mês (~R$ 30)
- **Hetzner:** €4/mês (~R$ 22)
- **AWS Lightsail:** $5/mês (~R$ 25)

---

## 🔧 INSTALAÇÃO (Docker - 5 minutos)

### 1. Conectar no Servidor
```bash
ssh root@seu-servidor.com
```

### 2. Instalar Docker
```bash
# Atualizar sistema
apt update && apt upgrade -y

# Instalar Docker
curl -fsSL https://get.docker.com | sh

# Adicionar usuário ao grupo docker
usermod -aG docker $USER

# Instalar Docker Compose
apt install docker-compose -y
```

### 3. Clonar Evolution API
```bash
# Ir para diretório de apps
cd /opt

# Clonar repositório
git clone https://github.com/EvolutionAPI/evolution-api.git
cd evolution-api
```

### 4. Configurar Environment
```bash
# Copiar exemplo
cp .env.example .env

# Editar configurações
nano .env
```

**Configurações Importantes (.env):**
```env
# API
SERVER_URL=https://api-whatsapp.seu-dominio.com.br
PORT=8080

# API Key (gerar uma senha forte)
AUTHENTICATION_API_KEY=SuaSenhaForte123!@#

# Database (opcional - usar SQLite para começar)
DATABASE_ENABLED=false

# Webhook (opcional)
WEBHOOK_GLOBAL_ENABLED=false

# Logs
LOG_LEVEL=ERROR
LOG_COLOR=true
```

### 5. Iniciar Containers
```bash
docker-compose up -d
```

### 6. Verificar Status
```bash
# Ver logs
docker-compose logs -f

# Verificar se está rodando
curl http://localhost:8080
```

---

## 🌐 CONFIGURAR DOMÍNIO (Nginx + SSL)

### 1. Instalar Nginx
```bash
apt install nginx certbot python3-certbot-nginx -y
```

### 2. Criar Config
```bash
nano /etc/nginx/sites-available/evolution-api
```

**Conteúdo:**
```nginx
server {
    listen 80;
    server_name api-whatsapp.seu-dominio.com.br;

    location / {
        proxy_pass http://localhost:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

### 3. Ativar Site
```bash
ln -s /etc/nginx/sites-available/evolution-api /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

### 4. Configurar SSL (Let's Encrypt)
```bash
certbot --nginx -d api-whatsapp.seu-dominio.com.br
```

### 5. Testar
```bash
curl https://api-whatsapp.seu-dominio.com.br
```

---

## 📱 CRIAR INSTÂNCIA WHATSAPP

### 1. Criar Instância via API
```bash
curl -X POST https://api-whatsapp.seu-dominio.com.br/instance/create \
  -H "apikey: SuaSenhaForte123!@#" \
  -H "Content-Type: application/json" \
  -d '{
    "instanceName": "yumgo_whatsapp",
    "qrcode": true,
    "integration": "WHATSAPP-BAILEYS"
  }'
```

**Response:**
```json
{
  "instance": {
    "instanceName": "yumgo_whatsapp",
    "status": "created"
  },
  "qrcode": {
    "code": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
    "base64": "..."
  }
}
```

### 2. Escanear QR Code

**Opção A: Via Browser**
1. Abrir: `https://api-whatsapp.seu-dominio.com.br/instance/connect/yumgo_whatsapp`
2. Escanear QR Code com WhatsApp

**Opção B: Salvar QR Code**
```bash
# Copiar base64 do response e decodificar
echo "SEU_BASE64_AQUI" | base64 -d > qrcode.png
```

### 3. Verificar Status
```bash
curl -X GET https://api-whatsapp.seu-dominio.com.br/instance/connectionState/yumgo_whatsapp \
  -H "apikey: SuaSenhaForte123!@#"
```

**Response (conectado):**
```json
{
  "instance": {
    "instanceName": "yumgo_whatsapp",
    "state": "open"
  }
}
```

---

## 🔌 INTEGRAR COM YUMGO

### 1. Adicionar ao .env
```env
# Evolution API
EVOLUTION_API_URL=https://api-whatsapp.seu-dominio.com.br
EVOLUTION_API_KEY=SuaSenhaForte123!@#
EVOLUTION_INSTANCE_NAME=yumgo_whatsapp
```

### 2. Implementar no Controller

Editar: `/app/Http/Controllers/Api/SocialAuthController.php`

**Substituir linha ~79 (TODO) por:**

```php
public function requestWhatsAppCode(Request $request)
{
    $request->validate([
        'phone' => 'required|string|min:10|max:15',
    ]);

    $customer = auth('sanctum')->user();

    if (!$customer) {
        return response()->json([
            'success' => false,
            'message' => 'Não autenticado'
        ], 401);
    }

    // Gerar código de 6 dígitos
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Salvar no customer com expiração de 10 minutos
    $customer->update([
        'phone' => $request->phone,
        'verification_code' => $code,
        'verification_code_expires_at' => now()->addMinutes(10),
    ]);

    // 🚀 EVOLUTION API - ENVIAR CÓDIGO VIA WHATSAPP
    try {
        $response = \Http::withHeaders([
            'apikey' => config('services.evolution.api_key')
        ])->post(config('services.evolution.url') . '/message/sendText/' . config('services.evolution.instance_name'), [
            'number' => '55' . preg_replace('/\D/', '', $request->phone),
            'text' => "🔐 *Código de Verificação YumGo*\n\n" .
                      "Seu código é: *{$code}*\n\n" .
                      "Válido por 10 minutos.\n\n" .
                      "Se você não solicitou, ignore esta mensagem."
        ]);

        if ($response->successful()) {
            \Log::info("Código enviado para {$request->phone}: {$code}");

            return response()->json([
                'success' => true,
                'message' => 'Código enviado via WhatsApp',
                // DEBUG: Remover em produção
                'debug_code' => config('app.debug') ? $code : null,
            ]);
        } else {
            \Log::error("Erro ao enviar WhatsApp: " . $response->body());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar código. Tente novamente.',
                // DEBUG: Mostrar código em dev
                'debug_code' => config('app.debug') ? $code : null,
            ], 500);
        }
    } catch (\Exception $e) {
        \Log::error("Exceção ao enviar WhatsApp: " . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Erro ao enviar código. Tente novamente.',
            // DEBUG: Mostrar código em dev
            'debug_code' => config('app.debug') ? $code : null,
        ], 500);
    }
}
```

### 3. Atualizar config/services.php
```php
// Adicionar no array de serviços:

'evolution' => [
    'url' => env('EVOLUTION_API_URL'),
    'api_key' => env('EVOLUTION_API_KEY'),
    'instance_name' => env('EVOLUTION_INSTANCE_NAME'),
],
```

---

## 🧪 TESTAR ENVIO

### 1. Via Postman/Curl
```bash
curl -X POST https://marmitaria-gi.yumgo.com.br/api/v1/auth/whatsapp/request-code \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{"phone":"11999998888"}'
```

### 2. Via Frontend
```javascript
// No welcome.blade.php já está implementado
// Basta testar no navegador:

const response = await fetch('/api/v1/auth/whatsapp/request-code', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        phone: '11999998888'
    })
});

const data = await response.json();
console.log(data); // {success: true, message: "Código enviado"}
```

---

## 📊 MONITORAMENTO

### Ver Logs da API
```bash
docker-compose logs -f
```

### Ver Instâncias Ativas
```bash
curl -X GET https://api-whatsapp.seu-dominio.com.br/instance/fetchInstances \
  -H "apikey: SuaSenhaForte123!@#"
```

### Verificar Conexão WhatsApp
```bash
curl -X GET https://api-whatsapp.seu-dominio.com.br/instance/connectionState/yumgo_whatsapp \
  -H "apikey: SuaSenhaForte123!@#"
```

---

## 🔄 WEBHOOK (Opcional - Receber Mensagens)

### 1. Configurar Webhook
```bash
curl -X POST https://api-whatsapp.seu-dominio.com.br/webhook/set/yumgo_whatsapp \
  -H "apikey: SuaSenhaForte123!@#" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://marmitaria-gi.yumgo.com.br/api/v1/webhooks/whatsapp",
    "webhook_by_events": true,
    "events": [
      "QRCODE_UPDATED",
      "MESSAGES_UPSERT",
      "CONNECTION_UPDATE"
    ]
  }'
```

### 2. Criar Rota no Laravel
```php
// routes/tenant.php
Route::post('/webhooks/whatsapp', [WebhookController::class, 'whatsapp']);
```

### 3. Implementar Controller
```php
public function whatsapp(Request $request)
{
    $event = $request->input('event');
    $data = $request->input('data');

    \Log::info('Webhook WhatsApp recebido', [
        'event' => $event,
        'data' => $data
    ]);

    // Processar eventos
    if ($event === 'MESSAGES_UPSERT') {
        $from = $data['key']['remoteJid'];
        $message = $data['message']['conversation'] ?? '';

        // Responder automaticamente
        // ...
    }

    return response()->json(['success' => true]);
}
```

---

## ⚠️ IMPORTANTE

### Segurança:
- ✅ **NUNCA** compartilhe sua API Key
- ✅ Use HTTPS (SSL) **sempre**
- ✅ Configure firewall (UFW)
  ```bash
  ufw allow 22/tcp    # SSH
  ufw allow 80/tcp    # HTTP
  ufw allow 443/tcp   # HTTPS
  ufw enable
  ```

### Manutenção:
- ✅ Backup do banco de dados (se usar)
- ✅ Monitorar logs diariamente
- ✅ Atualizar Evolution API regularmente
  ```bash
  cd /opt/evolution-api
  git pull
  docker-compose up -d --build
  ```

### Limites WhatsApp:
- ⚠️ **NÃO ENVIAR SPAM** (risco de ban)
- ⚠️ Máximo ~1000 mensagens/dia por número
- ⚠️ Intervalo de 1 segundo entre mensagens
- ⚠️ Não enviar para números não salvos (sem opt-in)

---

## 🆘 TROUBLESHOOTING

### Problema: QR Code não aparece
```bash
# Ver logs
docker-compose logs -f

# Recriar instância
curl -X DELETE https://api-whatsapp.seu-dominio.com.br/instance/delete/yumgo_whatsapp \
  -H "apikey: SuaSenhaForte123!@#"

# Criar novamente
curl -X POST https://api-whatsapp.seu-dominio.com.br/instance/create \
  -H "apikey: SuaSenhaForte123!@#" \
  -d '{"instanceName":"yumgo_whatsapp","qrcode":true}'
```

### Problema: WhatsApp desconectou
```bash
# Reconectar
curl -X GET https://api-whatsapp.seu-dominio.com.br/instance/connect/yumgo_whatsapp \
  -H "apikey: SuaSenhaForte123!@#"
```

### Problema: Mensagem não enviada
```bash
# Verificar status da instância
curl -X GET https://api-whatsapp.seu-dominio.com.br/instance/connectionState/yumgo_whatsapp \
  -H "apikey: SuaSenhaForte123!@#"

# Se state != "open", reconectar
```

---

## 📚 DOCUMENTAÇÃO OFICIAL

- **Evolution API:** https://doc.evolution-api.com/
- **GitHub:** https://github.com/EvolutionAPI/evolution-api
- **Comunidade:** https://t.me/evolutionapi

---

## 🎉 RESUMO

### Custo Total:
- **VPS:** R$ 20-40/mês (ou grátis se já tem servidor)
- **Evolution API:** R$ 0 (open source)
- **Total:** R$ 20-40/mês (vs R$ 30-100/mês do Twilio)

### Tempo de Setup:
- ⏱️ **15 minutos** total
  - 5 min: Instalar Docker
  - 5 min: Configurar Evolution API
  - 5 min: Criar instância e escanear QR

### Resultado:
✅ Sistema de verificação WhatsApp 100% funcional
✅ Envio ilimitado de códigos
✅ Custo fixo (não por mensagem)
✅ Total controle da infraestrutura

---

**Pronto para produção! 🚀**

Se precisar de ajuda, acesse a comunidade Telegram: https://t.me/evolutionapi
