# 🌐 Configuração DNS para WebSocket - YumGo Bridge

## ❌ Problema Atual

O domínio `ws.yumgo.com.br` está atrás do **Cloudflare Proxy**, que bloqueia ou limita conexões WebSocket.

```
Cliente → Cloudflare (172.67.149.129) ❌ → Servidor (34.221.34.95)
                     ↑
              WebSocket bloqueado
```

## ✅ Solução: DNS Direto

O WebSocket precisa ir **direto para o servidor**, sem passar pelo proxy do Cloudflare.

```
Cliente → DNS Direto → Servidor (34.221.34.95) ✅
                        ↑
                 WebSocket funciona!
```

---

## 🔧 Passo a Passo (Cloudflare)

### 1. Acessar Dashboard Cloudflare

```
https://dash.cloudflare.com
→ Selecione o domínio: yumgo.com.br
→ Vá em: DNS > Records
```

### 2. Encontrar o Registro WS

Procure pelo registro:
```
Tipo: A ou CNAME
Nome: ws
Valor: (qualquer)
Proxy status: 🟠 Proxied (LARANJA)  ← ESTE É O PROBLEMA!
```

### 3. Editar o Registro

Clique no registro `ws` e:

**Opção A: Já existe registro A**
```
Tipo: A
Nome: ws
IPv4: 34.221.34.95
Proxy status: 🔘 DNS only (CINZA)  ← CLICAR AQUI!
TTL: Auto ou 300
```

**Opção B: Existe CNAME**
- Deletar o CNAME
- Criar novo registro A:
```
Tipo: A
Nome: ws
IPv4: 34.221.34.95
Proxy status: 🔘 DNS only (CINZA)
TTL: 300
```

### 4. Salvar

Clique em **Save** e aguarde 2-5 minutos para propagação.

---

## 🧪 Testar Configuração

### 1. Verificar DNS (local)

```bash
# Deve retornar o IP do servidor (34.221.34.95)
nslookup ws.yumgo.com.br

# ✅ Correto:
Name:   ws.yumgo.com.br
Address: 34.221.34.95

# ❌ Errado (ainda aponta para Cloudflare):
Name:   ws.yumgo.com.br
Address: 172.67.149.129
```

**Nota:** Se ainda aparecer IP do Cloudflare, aguarde mais alguns minutos ou limpe cache DNS:
```bash
# Windows
ipconfig /flushdns

# macOS/Linux
sudo dscacheutil -flushcache
```

### 2. Testar HTTPS

```bash
curl -I https://ws.yumgo.com.br/

# Deve aparecer:
# server: nginx  ← CORRETO!

# Se aparecer:
# server: cloudflare  ← AINDA ERRADO (aguarde propagação)
```

### 3. Testar WebSocket (no app)

1. Abra o YumGo Bridge
2. Conecte com ID do restaurante + Token
3. Verifique logs:

**Sucesso esperado:**
```
✅ [info] Inscrevendo no canal: private-restaurant.xxx
✅ [info] Estado da conexão: connecting → connected
✅ [info] ✅ Conectado ao servidor YumGo via Reverb/Pusher
```

**Se ainda falhar:**
```
❌ [info] Estado da conexão: failed → disconnected
```
→ DNS ainda não propagou, aguarde mais 5-10 minutos

---

## 🚀 Solução Alternativa (Temporária)

Se você não puder alterar o DNS agora, pode testar localmente usando IP direto:

### 1. Editar `/etc/hosts` (Windows)

**Localização:** `C:\Windows\System32\drivers\etc\hosts`

Adicione a linha:
```
34.221.34.95    ws.yumgo.com.br
```

**Como editar:**
1. Abra Bloco de Notas **como Administrador**
2. Arquivo > Abrir > `C:\Windows\System32\drivers\etc\hosts`
3. Adicione a linha
4. Salvar

### 2. Testar

- Abra YumGo Bridge
- Conecte
- Deve funcionar!

**Nota:** Esta solução é **temporária** e só funciona no PC que você editou o arquivo.

---

## 🔐 Certificado SSL

O certificado SSL está correto e já configurado:
```
✅ /etc/letsencrypt/live/ws.yumgo.com.br/
✅ Válido até: (verificar com: sudo certbot certificates)
```

**Renovar (se necessário):**
```bash
sudo certbot renew
sudo systemctl reload nginx
```

---

## 📋 Checklist Pós-Configuração

- [ ] DNS `ws.yumgo.com.br` aponta para `34.221.34.95`
- [ ] Proxy Cloudflare **DESABILITADO** (cinza, não laranja)
- [ ] `nslookup ws.yumgo.com.br` retorna IP do servidor
- [ ] `curl -I https://ws.yumgo.com.br/` retorna `server: nginx`
- [ ] YumGo Bridge conecta com sucesso
- [ ] Status: "Conectado ✅"

---

## ⚠️ Importante: Segurança

**Ao desabilitar o proxy Cloudflare:**
- ✅ WebSocket funciona
- ⚠️ Perde proteção DDoS do Cloudflare
- ⚠️ IP do servidor fica exposto

**Mitigação:**
- Configure firewall para aceitar apenas portas necessárias (80, 443, 22)
- Use fail2ban para proteção contra brute force
- Mantenha sistema atualizado

---

## 📞 Suporte

**Problemas ou dúvidas?**
- Email: suporte@yumgo.com.br
- Este documento: `/var/www/restaurante/electron-bridge/WEBSOCKET-DNS-CONFIG.md`

---

**Criado em:** 06/03/2026
**Servidor:** AWS EC2 (34.221.34.95)
**Reverb:** Porta 8081 (HTTP local) → 443 (HTTPS via Nginx)
