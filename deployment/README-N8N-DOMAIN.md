# 🌐 Configurar Domínio bot.yumgo.com.br para N8N

## 🎯 O que este script faz?

1. ✅ Verifica se N8N está rodando (inicia se não estiver)
2. ✅ Testa se N8N responde em localhost:5678
3. ✅ Copia configuração Nginx
4. ✅ Ativa site no Nginx
5. ✅ Testa configuração
6. ✅ Recarrega Nginx
7. ✅ Instala SSL (Certbot) - opcional

## 🚀 Como Usar

### **No servidor AWS (via SSH):**

```bash
cd /var/www/restaurante
./deployment/setup-n8n-domain.sh
```

**Ou manualmente:**
```bash
cd /var/www/restaurante
bash deployment/setup-n8n-domain.sh
```

## 📋 Pré-requisitos

Antes de executar, certifique-se que:

- [ ] DNS configurado (bot.yumgo.com.br → IP elástico)
- [ ] Security Group permite HTTP (80) e HTTPS (443)
- [ ] Docker rodando
- [ ] Nginx instalado
- [ ] SSH ativo no servidor

## 🧪 Testar Manualmente

Se preferir fazer passo a passo:

### 1. Iniciar N8N
```bash
cd /var/www/restaurante
docker compose up -d n8n
```

### 2. Verificar se está rodando
```bash
docker compose ps
curl http://localhost:5678/healthz
```

### 3. Copiar configuração Nginx
```bash
sudo cp deployment/nginx/bot.yumgo.com.br.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/bot.yumgo.com.br.conf /etc/nginx/sites-enabled/
```

### 4. Testar e recarregar Nginx
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 5. Instalar SSL
```bash
sudo certbot --nginx -d bot.yumgo.com.br
```

## 🔗 Acessar

Depois de configurado:

- **HTTP:** http://bot.yumgo.com.br
- **HTTPS:** https://bot.yumgo.com.br (após Certbot)

**Credenciais padrão:**
- User: `admin`
- Pass: `yumgo_n8n_2026`

⚠️ **Mude a senha após primeiro acesso!**

## 🆘 Troubleshooting

### Erro 404 (Nginx)
```bash
# Verificar se site está ativo
ls -la /etc/nginx/sites-enabled/ | grep bot

# Ver logs
sudo tail -f /var/log/nginx/bot.yumgo.com.br.error.log
```

### Erro 502 (Bad Gateway)
```bash
# N8N não está rodando ou não responde
docker compose ps
docker compose logs n8n
curl http://localhost:5678/healthz
```

### DNS não resolve
```bash
# Testar DNS
nslookup bot.yumgo.com.br
ping bot.yumgo.com.br

# Se não resolver, aguardar propagação (5-60 min)
```

### SSL não instala
```bash
# Instalar Certbot
sudo apt update
sudo apt install certbot python3-certbot-nginx

# Tentar novamente
sudo certbot --nginx -d bot.yumgo.com.br
```

## 📦 Arquivos Criados

```
/var/www/restaurante/
├── deployment/
│   ├── nginx/
│   │   └── bot.yumgo.com.br.conf  ✅ Config Nginx
│   ├── setup-n8n-domain.sh        ✅ Script automático
│   └── README-N8N-DOMAIN.md       ✅ Este arquivo
```

## 🔐 Webhook URL (para Flare)

Após configurar, use esta URL no Flare:

```
https://bot.yumgo.com.br/webhook/flare-webhook
```

O endpoint `/webhook/` **não tem autenticação** para permitir chamadas do Flare.

## 📊 Monitoramento

```bash
# Ver status N8N
docker compose ps n8n

# Ver logs N8N
docker compose logs -f n8n

# Ver logs Nginx
sudo tail -f /var/log/nginx/bot.yumgo.com.br.access.log
sudo tail -f /var/log/nginx/bot.yumgo.com.br.error.log

# Health check
curl http://localhost:5678/healthz
curl https://bot.yumgo.com.br/healthz
```

## ✅ Checklist Final

Após executar o script:

- [ ] bot.yumgo.com.br abre a interface do N8N
- [ ] Login funciona (admin / yumgo_n8n_2026)
- [ ] SSL ativo (HTTPS com cadeado verde)
- [ ] Webhook acessível: https://bot.yumgo.com.br/webhook/
- [ ] Health check OK: https://bot.yumgo.com.br/healthz

---

**Tempo estimado:** 5-10 minutos
**Dificuldade:** Fácil (script automático)
