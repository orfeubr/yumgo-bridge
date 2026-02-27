# 🔐 CONFIGURAÇÃO GOOGLE OAUTH - PASSO A PASSO

## ✅ STATUS ATUAL

- ✅ Código Laravel configurado
- ✅ Rotas criadas
- ✅ Botões na interface
- ⏳ **FALTA:** Adicionar Client ID e Secret no .env

---

## 🚀 PASSOS RESTANTES

### **VOCÊ ESTÁ AQUI:** Precisa copiar o Client ID e Secret do Google

1. **Acesse:** https://console.cloud.google.com/apis/credentials
2. **Selecione o projeto:** YumGo Delivery
3. **Clique em:** "YumGo Web Client" (na lista OAuth 2.0 Client IDs)
4. **Copie:**
   - Client ID: `xxxxx.apps.googleusercontent.com`
   - Client Secret: `GOCSPX-xxxxx`

---

## 📝 EDITAR O ARQUIVO .env

Abra o arquivo `/var/www/restaurante/.env` e procure por estas linhas (no final):

```bash
# OAuth Social Login
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

**COLE OS VALORES COPIADOS:**

```bash
# OAuth Social Login
GOOGLE_CLIENT_ID=1234567890-abcdefghijk.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-abc123def456ghi789
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

---

## 🔧 COMANDO PARA EDITAR .env VIA SSH

Se estiver no servidor via SSH, rode:

```bash
nano /var/www/restaurante/.env
```

**Ou use este comando para adicionar automaticamente:**

```bash
# Substitua pelos seus valores reais:
sed -i 's/GOOGLE_CLIENT_ID=$/GOOGLE_CLIENT_ID=SEU_CLIENT_ID_AQUI/' /var/www/restaurante/.env
sed -i 's/GOOGLE_CLIENT_SECRET=$/GOOGLE_CLIENT_SECRET=SEU_SECRET_AQUI/' /var/www/restaurante/.env
```

---

## ⚡ APÓS ADICIONAR, LIMPE O CACHE:

```bash
cd /var/www/restaurante
php artisan config:clear
php artisan cache:clear
```

---

## 🧪 TESTAR O LOGIN GOOGLE

1. Acesse: https://marmitariadagi.yumgo.com.br/
2. Modal de login aparece automaticamente (após 0,5s)
3. Clique em **"Continuar com Google"**
4. Escolha sua conta Google
5. Autorize o acesso
6. ✅ Você será redirecionado e logado automaticamente!

---

## 🐛 TROUBLESHOOTING

### **Erro: "redirect_uri_mismatch"**

**Causa:** A URL de callback não está autorizada no Google Cloud.

**Solução:**
1. Volte em: https://console.cloud.google.com/apis/credentials
2. Clique em "YumGo Web Client"
3. Em **Authorized redirect URIs**, adicione:
   ```
   https://marmitariadagi.yumgo.com.br/auth/google/callback
   https://marmitaria-gi.yumgo.com.br/auth/google/callback
   ```
4. Clique **"SAVE"**
5. Aguarde 5 minutos (propagação)
6. Teste novamente

---

### **Erro: "This app isn't verified"**

**Causa:** App em modo de teste.

**Solução:** É normal! Clique em **"Advanced"** → **"Go to YumGo Delivery (unsafe)"**

Para produção:
1. Google Cloud Console → OAuth consent screen
2. Clique **"PUBLISH APP"**
3. Envie para revisão do Google (leva 1-3 dias)

---

### **Erro: "Access blocked: This app's request is invalid"**

**Causa:** Client ID ou Secret incorreto.

**Solução:**
1. Verifique se copiou corretamente
2. Não pode ter espaços antes/depois
3. Rode `php artisan config:clear`

---

## 📋 CHECKLIST COMPLETO

- [ ] Criar projeto no Google Cloud Console
- [ ] Configurar OAuth consent screen
- [ ] Criar credenciais OAuth 2.0
- [ ] Adicionar Authorized redirect URIs
- [ ] Copiar Client ID
- [ ] Copiar Client Secret
- [ ] Adicionar no .env
- [ ] Limpar cache Laravel
- [ ] Testar login Google
- [ ] Funciona? ✅

---

## 🔗 LINKS ÚTEIS

- **Google Cloud Console:** https://console.cloud.google.com/
- **Credentials:** https://console.cloud.google.com/apis/credentials
- **OAuth Consent Screen:** https://console.cloud.google.com/apis/credentials/consent

---

## 💬 AINDA COM DÚVIDA?

Me mande prints de tela de:
1. A página de credenciais do Google Cloud
2. O erro que aparece ao clicar em "Continuar com Google"
3. O console do navegador (F12 → Console)

Vou te ajudar a resolver! 🚀
