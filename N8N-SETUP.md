# 🤖 N8N - Instalação e Configuração

**Data:** 02/03/2026
**Status:** ✅ Pronto para iniciar

---

## 📋 O QUE É O N8N?

N8N é uma ferramenta de automação de workflows (como Zapier, mas self-hosted e open-source).

**No YumGo, usamos para:**
- 🔍 Monitorar erros via Flare
- 🤖 Analisar com Claude AI
- 🔧 Aplicar correções automaticamente
- 📧 Notificar equipe (Slack/Email)

---

## 🚀 INSTALAÇÃO

### **1. Subir o Container**

```bash
cd /var/www/restaurante

# Parar containers atuais (se estiverem rodando)
docker-compose down

# Reconstruir com n8n
docker-compose up -d --build

# Verificar se n8n está rodando
docker-compose ps
```

**Você deve ver:**
```
NAME            STATUS          PORTS
yumgo-n8n       Up 30 seconds   0.0.0.0:5678->5678/tcp
```

---

### **2. Acessar o Painel**

Abra no navegador:
```
http://localhost:5678
```

ou (se configurou domínio):
```
https://n8n.yumgo.com.br
```

**Credenciais padrão:**
- **User:** admin
- **Pass:** yumgo_n8n_2026

⚠️ **MUDE A SENHA em produção!**

---

### **3. Importar Workflow de Auto-Fix**

1. No painel n8n, click em **"Workflows"** (menu lateral)
2. Click no botão **"+"** (New Workflow)
3. Click no menu **⋮** → **"Import from File"**
4. Selecione: `/var/www/restaurante/n8n-workflows/auto-fix-errors.json`
5. Click em **"Import"**

✅ O workflow completo será importado com todos os 10 nodes!

---

## 🔐 CONFIGURAR CREDENCIAIS

### **1. Anthropic API (Claude AI)**

No painel n8n:

1. Menu lateral → **"Credentials"**
2. Click em **"+ New Credential"**
3. Busque: **"Anthropic"**
4. Preencha:
   - **Name:** Claude API
   - **API Key:** Obter em https://console.anthropic.com
5. **Save**

**Onde obter a API Key:**
- Acesse: https://console.anthropic.com
- Click em **"API Keys"**
- **"Create Key"**
- Copie e cole no n8n

---

### **2. Slack (Notificações)**

1. Menu lateral → **"Credentials"**
2. **"+ New Credential"** → **"Slack"**
3. Preencha:
   - **Name:** Slack YumGo
   - **Access Token:** Bot User OAuth Token

**Onde obter o token:**

1. Acesse: https://api.slack.com/apps
2. **"Create New App"** → **"From scratch"**
3. Nome: **YumGo Alerts**
4. Workspace: Seu workspace
5. Vá em **"OAuth & Permissions"**
6. **Scopes** → Adicione:
   - `chat:write`
   - `chat:write.public`
7. **Install to Workspace**
8. Copie o **"Bot User OAuth Token"** (começa com `xoxb-`)

**Criar canal #production-errors:**
```
1. No Slack, crie canal: #production-errors
2. Adicione o bot YumGo Alerts ao canal
3. No workflow n8n, verifique que canal está correto
```

---

### **3. Email SMTP (Notificações)**

1. Menu lateral → **"Credentials"**
2. **"+ New Credential"** → **"SMTP"**
3. Preencha:
   - **Name:** Email YumGo
   - **User:** seu-email@gmail.com
   - **Password:** senha de app (Gmail)
   - **Host:** smtp.gmail.com
   - **Port:** 587
   - **Secure:** TLS

**Gmail - Senha de App:**
1. Acesse: https://myaccount.google.com/security
2. Ative verificação em 2 etapas
3. Gere "Senha de app"
4. Use essa senha no n8n (não a senha normal)

---

## ⚙️ ATIVAR WORKFLOW

1. Abra o workflow importado
2. Verifique que todas credenciais estão configuradas:
   - ✅ Node "Claude - Analyze Error" → Anthropic API
   - ✅ Node "Notify Slack" → Slack API
   - ✅ Node "Notify Email" → SMTP
3. Click no botão **"Active"** (canto superior direito)

✅ Workflow ativo!

---

## 🔗 CONFIGURAR WEBHOOK NO FLARE

### **1. Obter URL do Webhook**

1. No workflow ativo, click no node **"Webhook - Flare Error"**
2. Copie a **"Production URL"**

**Formato:**
```
http://localhost:5678/webhook/flare-webhook
```

ou se tiver domínio público:
```
https://n8n.yumgo.com.br/webhook/flare-webhook
```

⚠️ **Se localhost:** Flare não conseguirá chamar (precisa de URL pública)

**Soluções para desenvolvimento:**
- **ngrok:** `ngrok http 5678` (túnel temporário)
- **Cloudflare Tunnel:** Túnel permanente
- **Deploy n8n em servidor público**

---

### **2. Adicionar Webhook no Flare**

1. Acesse: https://flareapp.io/projects/yumgo/settings/webhooks
2. Click em **"Add webhook"**
3. Preencha:
   - **URL:** `https://n8n.yumgo.com.br/webhook/flare-webhook`
   - **Events:** All errors
   - **Method:** POST
   - **Active:** ✅
4. **Save**

---

## 🧪 TESTAR O WORKFLOW

### **Teste 1: Erro Proposital**

```bash
# Acesse no navegador:
https://yumgo.com.br/test-flare
```

**O que deve acontecer:**
1. ✅ Página gera erro ArgumentCountError
2. ✅ Flare captura o erro
3. ✅ Flare envia webhook para n8n
4. ✅ n8n recebe no node "Webhook - Flare Error"
5. ✅ Claude analisa erro (2-3 segundos)
6. ✅ Validação de segurança aprova/bloqueia
7. ✅ Se aprovado: aplica auto-fix
8. ✅ Notificação no Slack/Email

**Monitorar execução:**
- No n8n: Menu **"Executions"**
- Veja logs de cada node
- Verifique se passou por todos os 10 nodes

---

### **Teste 2: Verificar Notificações**

**Slack:**
- Vá no canal `#production-errors`
- Deve aparecer mensagem com:
  - Severidade do erro
  - Análise do Claude
  - Código corrigido
  - Status (aplicado ou revisão manual)

**Email:**
- Verifique caixa de entrada
- Email detalhado com stack trace completo

---

## 📊 MONITORAMENTO

### **Dashboard n8n**

Acesse: **Menu → Executions**

Métricas disponíveis:
- Total de execuções (últimas 24h, 7d, 30d)
- Taxa de sucesso
- Tempo médio de execução
- Erros no workflow

**Filtros:**
- Status: Success / Error / Waiting
- Workflow: Auto-Fix Production Errors
- Período: Custom date range

---

### **Logs Detalhados**

```bash
# Ver logs do container n8n
docker-compose logs -f n8n

# Últimas 100 linhas
docker-compose logs --tail=100 n8n
```

---

## 🔧 CONFIGURAÇÕES AVANÇADAS

### **1. Domínio Público para N8N**

Se quiser acessar de fora:

**Nginx Reverse Proxy:**
```nginx
# /etc/nginx/sites-available/n8n.yumgo.com.br

server {
    listen 80;
    server_name n8n.yumgo.com.br;

    location / {
        proxy_pass http://localhost:5678;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

**Cloudflare SSL:**
```bash
# Instalar certbot
sudo certbot --nginx -d n8n.yumgo.com.br
```

---

### **2. Aumentar Segurança**

**Whitelist de IPs (apenas Flare):**
```nginx
# nginx.conf - Permitir apenas IP do Flare
location /webhook/ {
    allow 185.199.108.0/22;  # GitHub/Flare IPs
    deny all;
    proxy_pass http://localhost:5678;
}
```

**Webhook Secret (validação):**
- Configure secret no Flare
- Valide no node "Webhook" via header

---

### **3. Backup do N8N**

```bash
# Backup manual
docker exec yumgo-n8n n8n export:workflow --all --output=/home/node/backups/

# Backup automático (cron)
0 2 * * * docker exec yumgo-n8n n8n export:workflow --all --output=/home/node/backups/backup-$(date +\%Y\%m\%d).json
```

---

## 🛠️ TROUBLESHOOTING

### **Problema: n8n não inicia**

```bash
# Ver logs
docker-compose logs n8n

# Recriar container
docker-compose down
docker-compose up -d n8n
```

### **Problema: Webhook não recebe chamadas**

1. Verifique se workflow está **Active**
2. Teste manualmente:
```bash
curl -X POST http://localhost:5678/webhook/flare-webhook \
  -H "Content-Type: application/json" \
  -d '{"test": true}'
```
3. Verifique URL no Flare está correta
4. Se localhost: use ngrok

### **Problema: Claude retorna erro**

1. Verifique API Key válida
2. Verifique saldo da conta Anthropic
3. Logs: Menu Executions → Click na execução → Ver node "Claude"

### **Problema: Auto-fix não aplica**

1. Verifique validação de segurança
2. Node "Validate Security" → Ver output
3. Campo `block_reason` mostra o motivo

---

## 💰 CUSTOS

### **n8n (Self-Hosted):**
- ✅ **Grátis** (open-source)
- ✅ Rodando no mesmo servidor
- ✅ Sem limite de execuções

### **Claude API (Anthropic):**
- Modelo: Sonnet 4.5
- ~1.000 tokens por análise
- Custo: ~$0.003 por erro
- **100 erros/mês:** ~$0.30
- **1.000 erros/mês:** ~$3.00

### **Flare (Error Monitoring):**
- €19/mês (já contratado)
- 10.000 erros/mês

### **Total adicional:**
- N8N: R$ 0
- Claude API: R$ 1,50 - R$ 15/mês
- **SUPER BARATO!** 🚀

---

## 📝 CHECKLIST DE INSTALAÇÃO

- [ ] `docker-compose up -d` executado
- [ ] n8n acessível em http://localhost:5678
- [ ] Senha padrão alterada
- [ ] Workflow `auto-fix-errors.json` importado
- [ ] Credencial Anthropic API configurada
- [ ] Credencial Slack configurada (opcional)
- [ ] Credencial SMTP configurada (opcional)
- [ ] Workflow ativado (botão "Active")
- [ ] URL do webhook copiada
- [ ] Webhook configurado no Flare
- [ ] Teste com `/test-flare` executado com sucesso
- [ ] Notificação recebida no Slack/Email
- [ ] Monitoramento em "Executions" verificado

---

## 🎉 PRÓXIMOS PASSOS

1. ✅ **Monitorar por 1 semana** - Ver taxa de sucesso
2. ✅ **Ajustar regras** - Adicionar novos padrões perigosos se necessário
3. ✅ **Expandir workflows** - Criar outros para deploy, backups, etc
4. ✅ **Treinar Claude** - Adicionar mais contexto sobre erros comuns
5. ✅ **Integrar CI/CD** - Auto-deploy após auto-fix aprovado

---

**🤖 N8N está pronto para automatizar o YumGo!**

**Documentação completa:**
- `WORKFLOW-N8N-AUTO-FIX.md` - Funcionamento detalhado
- `WORKFLOW-SECURITY-UPGRADE.md` - Melhorias de segurança
- `N8N-SETUP.md` - Este arquivo (instalação)

**Dúvidas?** Consulte: https://docs.n8n.io

---

**Última atualização:** 02/03/2026
