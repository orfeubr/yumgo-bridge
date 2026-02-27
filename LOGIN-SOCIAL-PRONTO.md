# ✅ Login Social + Celular - PRONTO!

## 🎉 O Que Foi Implementado

### 1. **Botões de Login Social** ✅
- ✅ **Google** - Botão branco com logo colorida
- ✅ **Facebook** - Botão azul oficial
- ✅ Posicionados **ANTES** dos formulários
- ✅ Divisor "ou" entre social e tradicional

### 2. **Formulário Prioriza Celular** ✅
- ✅ **Login**: "Celular ou Email" (prioridade celular)
- ✅ **Cadastro**:
  - Celular obrigatório ⭐
  - Email opcional
  - Hint: "Usaremos para enviar atualizações"

### 3. **Backend Atualizado** ✅
- ✅ AuthController aceita celular ou email
- ✅ Validação ajustada
- ✅ Email agora é opcional no cadastro

---

## 📱 Como Funciona

### Login Social:
1. Cliente clica em "Continuar com Google" ou "Continuar com Facebook"
2. Redireciona para OAuth
3. Retorna com dados do perfil
4. Cria/atualiza customer
5. **Se não tiver celular**: pede verificação WhatsApp
6. Loga e redireciona

### Login Tradicional:
1. Cliente digita **celular** (preferencial) ou email
2. Digite senha
3. Faz login

### Cadastro:
1. Nome completo
2. **Celular** (obrigatório) ⭐
3. Email (opcional, mas recomendado)
4. Senha

---

## 🔗 Rotas Configuradas

```
✅ GET  /auth/google/redirect
✅ GET  /auth/google/callback
✅ GET  /auth/facebook/redirect
✅ GET  /auth/facebook/callback
✅ POST /api/v1/login (aceita celular ou email)
✅ POST /api/v1/register (celular obrigatório, email opcional)
```

---

## 🧪 Testar Agora

### 1. Acessar Login:
```
https://marmitaria-gi.yumgo.com.br/login
```

### 2. Ver Mudanças:
- ✅ Botões do Google e Facebook no topo
- ✅ Campos dizem "Celular" em vez de "Telefone"
- ✅ Email é opcional no cadastro

### 3. Testar Login Social:
**IMPORTANTE:** Antes de funcionar, precisa configurar:

#### Google OAuth:
1. Acesse: https://console.cloud.google.com/
2. Crie projeto
3. Ative Google+ API
4. Crie credenciais OAuth 2.0
5. Redirect URI: `https://marmitaria-gi.yumgo.com.br/auth/google/callback`
6. Adicione no `.env`:
```env
GOOGLE_CLIENT_ID=seu-client-id
GOOGLE_CLIENT_SECRET=seu-secret
```

#### Facebook OAuth:
1. Acesse: https://developers.facebook.com/
2. Crie app
3. Adicione "Login do Facebook"
4. Redirect URI: `https://marmitaria-gi.yumgo.com.br/auth/facebook/callback`
5. Adicione no `.env`:
```env
FACEBOOK_CLIENT_ID=seu-app-id
FACEBOOK_CLIENT_SECRET=seu-secret
```

---

## 📋 Arquivos Modificados

```
✅ /resources/views/tenant/auth/login.blade.php
   - Botões de login social adicionados
   - Labels mudados para "Celular"
   - Email opcional no cadastro

✅ /app/Http/Controllers/Api/AuthController.php
   - Aceita 'identifier' em vez de 'email' no login
   - Email opcional no register
   - Mensagens ajustadas para "celular"
```

---

## ⚠️ PRÓXIMO PASSO IMPORTANTE

### Configurar OAuth (Google/Facebook)

**Sem isso, os botões não funcionarão!**

Quer que eu crie um guia passo-a-passo de como configurar?
Ou prefere que eu configure com credenciais de teste primeiro?

---

**Status:** ✅ Frontend pronto, aguardando credenciais OAuth
