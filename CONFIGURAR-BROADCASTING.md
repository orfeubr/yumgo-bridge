# 🔧 Configurar Broadcasting para YumGo Bridge

## ⚠️ IMPORTANTE: O App Não Funciona Sem Isso!

O YumGo Bridge precisa de WebSocket para receber pedidos em tempo real.

---

## 1️⃣ Instalar Laravel Reverb

```bash
composer require laravel/reverb
php artisan reverb:install
```

---

## 2️⃣ Configurar .env

Adicione/edite estas linhas no `.env`:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=yumgo
REVERB_APP_KEY=abcdefghijklmnopqrstuvwxyz123456
REVERB_APP_SECRET=1234567890abcdefghijklmnopqrstuvwxyz
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http

# Para produção (com SSL):
# REVERB_SCHEME=https
# REVERB_HOST=yumgo.com.br
```

---

## 3️⃣ Rodar Servidor Reverb

### Desenvolvimento:

```bash
php artisan reverb:start
```

### Produção (Supervisor):

Crie: `/etc/supervisor/conf.d/laravel-reverb.conf`

```ini
[program:laravel-reverb]
process_name=%(program_name)s
command=php /var/www/restaurante/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/restaurante/storage/logs/reverb.log
```

Ative:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-reverb
```

---

## 4️⃣ Testar Conexão

```bash
# Terminal 1: Rodar Reverb
php artisan reverb:start

# Terminal 2: Verificar
curl http://localhost:8080/health
# Deve retornar: {"status":"ok"}
```

---

## 5️⃣ Atualizar App Electron

Edite `electron-bridge/src/main.js`:

Mude a URL do servidor:

```javascript
// ANTES (não funciona):
const serverUrl = 'wss://marmitariadagi.yumgo.com.br';

// DEPOIS (local):
const serverUrl = 'http://localhost:8080';

// PRODUÇÃO (com domínio):
const serverUrl = 'wss://yumgo.com.br:8080';
```

---

## 6️⃣ Nginx (Produção)

Adicione proxy para WebSocket:

```nginx
# /etc/nginx/sites-available/yumgo

upstream reverb {
    server 127.0.0.1:8080;
}

server {
    listen 443 ssl;
    server_name yumgo.com.br;

    # ... resto da config ...

    # Proxy WebSocket
    location /reverb {
        proxy_pass http://reverb;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 86400;
    }
}
```

---

## 7️⃣ Firewall (Produção)

Libere porta 8080:

```bash
sudo ufw allow 8080/tcp
```

---

## ✅ Verificação Final

1. [ ] Reverb instalado
2. [ ] .env configurado
3. [ ] Reverb rodando (`php artisan reverb:start`)
4. [ ] Saúde OK (`curl localhost:8080/health`)
5. [ ] App Electron conecta
6. [ ] Status: 🟢 Conectado

---

## 🐛 Troubleshooting

### "Connection refused"
- Reverb não está rodando
- Firewall bloqueando porta 8080

### "Unauthorized"
- Token inválido
- REVERB_APP_KEY não confere com .env

### "Timeout"
- Nginx não está proxy reverso
- SSL/TLS mal configurado

---

**Depois de configurar, rebuilde o app e teste novamente!** 🚀
