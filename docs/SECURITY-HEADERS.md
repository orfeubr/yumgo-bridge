# Headers de Segurança - Nginx

## 📋 Visão Geral

Headers HTTP de segurança protegem contra ataques comuns (XSS, clickjacking, MIME sniffing, etc.)

Este documento descreve os headers recomendados para o projeto DeliveryPro.

---

## 🔒 Headers Recomendados

### 1. X-Frame-Options

**Proteção:** Clickjacking (site malicioso carrega seu site em iframe)

```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
```

**Valores:**
- `DENY`: Nunca permitir iframe
- `SAMEORIGIN`: Permitir apenas do mesmo domínio
- `ALLOW-FROM https://exemplo.com`: Permitir de domínio específico (deprecated)

**Recomendado:** `SAMEORIGIN`

---

### 2. X-Content-Type-Options

**Proteção:** MIME sniffing (navegador tenta adivinhar tipo de arquivo)

```nginx
add_header X-Content-Type-Options "nosniff" always;
```

**Efeito:** Força navegador a respeitar Content-Type declarado

**Recomendado:** Sempre ativo

---

### 3. X-XSS-Protection

**Proteção:** XSS (Cross-Site Scripting) - legacy

```nginx
add_header X-XSS-Protection "1; mode=block" always;
```

**Valores:**
- `0`: Desativa proteção
- `1`: Ativa proteção (sanitiza página)
- `1; mode=block`: Ativa e bloqueia página inteira

**Nota:** Deprecated em navegadores modernos (use CSP)

---

### 4. Content-Security-Policy (CSP)

**Proteção:** XSS, injection de scripts maliciosos

```nginx
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self';" always;
```

**Diretivas:**
- `default-src 'self'`: Padrão: apenas do mesmo domínio
- `script-src`: De onde carregar scripts
- `style-src`: De onde carregar CSS
- `font-src`: De onde carregar fontes
- `img-src`: De onde carregar imagens
- `connect-src`: Para onde fazer fetch/XHR

**Nosso caso (DeliveryPro):**
- Tailwind CDN
- Alpine.js CDN
- Google Fonts
- Pagar.me SDK

**Recomendado:** Começar com `report-only` e ajustar

---

### 5. Referrer-Policy

**Proteção:** Vazamento de URLs sensíveis em Referer header

```nginx
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

**Valores:**
- `no-referrer`: Nunca envia
- `same-origin`: Envia apenas para mesmo domínio
- `strict-origin-when-cross-origin`: Envia origem em cross-origin, URL completa em same-origin
- `unsafe-url`: Sempre envia URL completa (não recomendado)

**Recomendado:** `strict-origin-when-cross-origin`

---

### 6. Permissions-Policy (Feature-Policy)

**Proteção:** Controla acesso a APIs do navegador

```nginx
add_header Permissions-Policy "geolocation=(self), microphone=(), camera=()" always;
```

**Features comuns:**
- `geolocation`: GPS
- `microphone`: Microfone
- `camera`: Câmera
- `payment`: Payment Request API
- `usb`: USB devices

**Nosso caso:** Permitir geolocalização, bloquear câmera/mic

---

### 7. Strict-Transport-Security (HSTS)

**Proteção:** Downgrade para HTTP (força HTTPS)

```nginx
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
```

**Parâmetros:**
- `max-age=31536000`: 1 ano em segundos
- `includeSubDomains`: Aplica a subdomínios
- `preload`: Inclui em lista HSTS preload (browsers)

**⚠️ ATENÇÃO:** Só ative se HTTPS estiver 100% funcional

---

## 📝 Configuração Nginx Completa

Adicionar no bloco `server {}` do Nginx:

```nginx
server {
    listen 443 ssl http2;
    server_name yumgo.com.br *.yumgo.com.br;

    # SSL configs...

    # ========== HEADERS DE SEGURANÇA ==========

    # Prevenir clickjacking
    add_header X-Frame-Options "SAMEORIGIN" always;

    # Prevenir MIME sniffing
    add_header X-Content-Type-Options "nosniff" always;

    # XSS Protection (legacy)
    add_header X-XSS-Protection "1; mode=block" always;

    # Content Security Policy
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://assets.pagar.me; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://api.pagar.me; frame-ancestors 'self';" always;

    # Referrer Policy
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Permissions Policy
    add_header Permissions-Policy "geolocation=(self), microphone=(), camera=()" always;

    # HSTS (apenas se HTTPS estiver 100% funcional)
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

    # Remove header Server (oculta versão Nginx)
    server_tokens off;

    # ========== FIM HEADERS ==========

    # Resto da configuração...
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

---

## 🧪 Testar Headers

### Online (rápido)
- https://securityheaders.com
- https://observatory.mozilla.org

### cURL (manual)
```bash
curl -I https://yumgo.com.br
```

### Esperado (Nota A+)
```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Content-Security-Policy: default-src 'self'; ...
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(self), microphone=(), camera=()
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

---

## ⚠️ Avisos Importantes

### CSP: Começar Permissivo
```nginx
# DESENVOLVIMENTO: Permitir tudo
Content-Security-Policy-Report-Only: default-src 'self' 'unsafe-inline' 'unsafe-eval' https:; report-uri /csp-report;

# PRODUÇÃO: Restringir gradualmente
Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.tailwindcss.com; ...
```

### HSTS: Só Ativar em Produção
- **Desenvolvimento:** NÃO ativar (localhost não tem HTTPS)
- **Staging:** Testar com `max-age=300` (5 minutos)
- **Produção:** Ativar com `max-age=31536000` (1 ano)

### SubResource Integrity (SRI)
CDNs externos devem usar SRI para prevenir CDN hijacking:

```html
<script src="https://cdn.tailwindcss.com"
        integrity="sha384-abc123..."
        crossorigin="anonymous"></script>
```

---

## 📊 Checklist de Implementação

- [ ] Adicionar headers no Nginx
- [ ] Reiniciar Nginx: `sudo systemctl reload nginx`
- [ ] Testar com SecurityHeaders.com
- [ ] Verificar se site ainda funciona (CSP pode quebrar)
- [ ] Ajustar CSP conforme necessário
- [ ] Ativar HSTS (último passo!)
- [ ] Adicionar site ao HSTS preload: https://hstspreload.org

---

## 🔗 Referências

- [OWASP Secure Headers Project](https://owasp.org/www-project-secure-headers/)
- [Mozilla Observatory](https://observatory.mozilla.org/)
- [CSP Reference](https://content-security-policy.com/)
- [HSTS Preload](https://hstspreload.org/)

---

**Criado em:** 09/03/2026
**Última atualização:** 09/03/2026
**Responsável:** Equipe DevOps
