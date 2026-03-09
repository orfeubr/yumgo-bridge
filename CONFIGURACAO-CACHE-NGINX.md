# 🚀 Configuração de Cache para Imagens - Nginx

## 📋 Instruções

### Opção 1: Adicionar no arquivo de configuração do site

**Localização típica:**
- `/etc/nginx/sites-enabled/yumgo.conf`
- `/etc/nginx/conf.d/default.conf`
- `/etc/nginx/nginx.conf`

**Adicionar dentro do bloco `server {}`:**

```nginx
# ========================================
# 🚀 CACHE DE IMAGENS
# ========================================

# Cache de imagens servidas via Laravel (rota /storage/)
location ~ ^/storage/.*\.(jpg|jpeg|png|gif|webp|svg|png)$ {
    try_files $uri /index.php?$query_string;

    # Cache agressivo - 1 ano
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
    add_header X-Content-Type-Options "nosniff";
}

# Cache de assets estáticos
location ~* \.(css|js|woff|woff2|ttf|otf|eot)$ {
    expires 1M;
    add_header Cache-Control "public, max-age=2592000";
    access_log off;
}

# Compressão Gzip (se ainda não estiver ativo)
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript
           application/json application/javascript
           application/xml+rss image/svg+xml;
```

### Aplicar as mudanças:

```bash
# Testar configuração
sudo nginx -t

# Se OK, recarregar Nginx
sudo systemctl reload nginx

# Ou restart
sudo systemctl restart nginx
```

---

## 🌐 Opção 2: Cloudflare Page Rules (RECOMENDADO)

Se o site usa Cloudflare, configure Page Rules:

### 1. Acessar Cloudflare Dashboard
- https://dash.cloudflare.com
- Selecione o domínio `yumgo.com.br`

### 2. Ir em "Rules" → "Page Rules"

### 3. Criar nova regra para imagens:

**URL Pattern:**
```
*yumgo.com.br/storage/*
```

**Settings:**
- ✅ **Cache Level:** Cache Everything
- ✅ **Edge Cache TTL:** 1 year
- ✅ **Browser Cache TTL:** 1 year

### 4. Criar regra para assets:

**URL Pattern:**
```
*yumgo.com.br/*.{css,js,woff,woff2}
```

**Settings:**
- ✅ **Cache Level:** Cache Everything
- ✅ **Edge Cache TTL:** 1 month

### 5. Salvar e Limpar Cache

Após criar as regras:
- Cloudflare → "Caching" → "Purge Everything"

---

## 🧪 Testar Cache

### Testar headers de cache:

```bash
curl -I https://marmitariadagi.yumgo.com.br/storage/products/thumbs/01KJ90KR3W14N2RAGG11JW2HBP.png
```

**Deve retornar:**
```
Cache-Control: public, max-age=31536000, immutable
cf-cache-status: HIT  (← Cloudflare está cacheando)
```

### Verificar se Cloudflare está cacheando:

1. Primeira requisição: `cf-cache-status: MISS`
2. Segunda requisição: `cf-cache-status: HIT` ✅

---

## 📊 Resultado Esperado

**Antes:**
- Cada imagem: 0.5-1.5s (passando pelo PHP)
- 10 imagens: 5-15s para carregar tudo

**Depois (com cache):**
- Primeira visita: 0.5-1.5s (popula cache)
- Visitas seguintes: **<100ms** (cache do Cloudflare/Browser) ⚡

---

## ⚠️ Importante

1. **Cloudflare está na frente?**
   - Verifique se o DNS aponta para Cloudflare (proxy laranja ativo)
   - Page Rules do Cloudflare são mais efetivos que Nginx

2. **Após mudar imagem:**
   - Imagem com mesmo nome é cacheada
   - Para forçar atualização: renomear arquivo ou adicionar `?v=2`

3. **Headers atuais:**
   - A rota Laravel já envia `Cache-Control: public, max-age=31536000`
   - Nginx/Cloudflare devem respeitar esses headers

---

**Prioridade:** Configure **Cloudflare Page Rules** primeiro (mais fácil e efetivo)!
