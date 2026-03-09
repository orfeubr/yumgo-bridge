# 🔐 GUIA: Aplicar Headers de Segurança no Nginx

## 📋 Opção 1: Script Automático (RECOMENDADO)

### Passo a Passo

1. **Executar script com sudo:**
```bash
cd /var/www/restaurante
sudo bash aplicar-security-headers.sh
```

2. **O script vai:**
   - ✅ Verificar se Nginx está instalado
   - ✅ Encontrar arquivo de configuração
   - ✅ Criar backup automático
   - ✅ Inserir headers de segurança
   - ✅ Testar configuração
   - ✅ Recarregar Nginx

3. **Se algo der errado:**
   - ✅ Backup é restaurado automaticamente
   - ✅ Zero risco de quebrar o site

---

## 📋 Opção 2: Manual (Se Preferir Controle Total)

### Passo 1: Backup

```bash
sudo cp /etc/nginx/sites-available/yumgo.com.br /etc/nginx/sites-available/yumgo.com.br.backup
```

### Passo 2: Editar Configuração

```bash
sudo nano /etc/nginx/sites-available/yumgo.com.br
```

### Passo 3: Adicionar Headers

Localize o bloco `server {}` e logo após a linha `server_name`, adicione:

```nginx
    # ========== HEADERS DE SEGURANÇA ==========
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://assets.pagar.me https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://api.pagar.me https://api.pagar.me/core/v5; frame-ancestors 'self'; base-uri 'self'; form-action 'self';" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(self), microphone=(), camera=(), payment=(self), usb=()" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    server_tokens off;
    # ========== FIM HEADERS ==========
```

**Salvar:** `Ctrl + O` → `Enter` → `Ctrl + X`

### Passo 4: Testar

```bash
sudo nginx -t
```

**Esperado:** `syntax is ok` e `test is successful`

### Passo 5: Recarregar

```bash
sudo systemctl reload nginx
```

---

## 🧪 Verificar Se Funcionou

### Método 1: Online (Mais Fácil)

Abra no navegador:
```
https://securityheaders.com/?q=https://yumgo.com.br
```

**Nota esperada: A+** 🏆

### Método 2: cURL (Terminal)

```bash
curl -I https://yumgo.com.br
```

**Deve aparecer:**
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

## ⚠️ SE ALGO DER ERRADO

### Site parou de funcionar?

**1. Restaurar backup:**
```bash
sudo cp /etc/nginx/sites-available/yumgo.com.br.backup /etc/nginx/sites-available/yumgo.com.br
sudo systemctl reload nginx
```

**2. Problema comum: CSP muito restritivo**

Se o site carregar mas algumas funcionalidades não funcionarem:
- Abra DevTools (F12) → Console
- Procure erros tipo: `Refused to load...`
- Adicione o domínio bloqueado no CSP

**Exemplo:** Se bloquear `https://exemplo.com`:
```nginx
# Adicionar https://exemplo.com na diretiva correta:
script-src 'self' 'unsafe-inline' https://exemplo.com;
```

**3. HSTS causando problema?**

Se você ativar HSTS e o HTTPS parar de funcionar, os navegadores não conseguirão acessar o site por 1 ano!

**Solução:** Comente a linha do HSTS:
```nginx
# add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
```

---

## 📊 Checklist Final

- [ ] Backup criado
- [ ] Headers adicionados
- [ ] `nginx -t` passou
- [ ] Nginx recarregado
- [ ] Site continua funcionando
- [ ] SecurityHeaders.com mostra A+
- [ ] Todas as funcionalidades testadas

---

## 🎉 PRONTO!

Após aplicar, sua plataforma terá:
- ✅ **Security Score: A+** (100%)
- ✅ Proteção contra XSS
- ✅ Proteção contra clickjacking
- ✅ HTTPS obrigatório (HSTS)
- ✅ Headers de privacidade

**Tempo total: 5-10 minutos** ⏱️

---

**Arquivos criados:**
- `nginx-security-headers.conf` - Headers prontos para copiar
- `aplicar-security-headers.sh` - Script automático
- `APLICAR-HEADERS-SEGURANCA.md` - Este guia

**Data:** 09/03/2026
