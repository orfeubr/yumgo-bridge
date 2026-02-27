# 🔄 OAUTH AUTOMÁTICO - SOLUÇÃO COMPLETA

## ❌ **POR QUE NÃO DÁ PARA SER 100% AUTOMÁTICO?**

O Google/Facebook **não permitem** adicionar URIs via código porque:
- Não têm API pública para gerenciar OAuth Clients
- É uma questão de segurança (evitar adição não autorizada)
- Você precisa fazer manualmente no console deles

---

## ✅ **MAS TEMOS UMA SOLUÇÃO SEMI-AUTOMÁTICA!**

### **Conceito:**
```
1 OAuth Client = TODOS os tenants da plataforma
```

**Como funciona:**
1. Você cria **1 único OAuth Client** no Google
2. Adiciona **TODAS as URLs de uma vez**
3. Quando criar novo tenant → **sistema avisa quais URLs adicionar**
4. Você copia e cola no Google (leva 10 segundos)

---

## 🛠️ **FERRAMENTAS CRIADAS:**

### **1. Comando Artisan para gerar lista:**

```bash
php artisan oauth:generate-urls
```

**Isso mostra:**
```
=== AUTHORIZED REDIRECT URIs ===
https://marmitariadagi.yumgo.com.br/auth/google/callback
https://parker-pizzaria.yumgo.com.br/auth/google/callback
...

=== AUTHORIZED JAVASCRIPT ORIGINS ===
https://marmitariadagi.yumgo.com.br
https://parker-pizzaria.yumgo.com.br
...
```

**Copie e cole no Google Cloud Console!**

---

### **2. Notificação automática ao criar tenant:**

Quando você criar um novo tenant no painel, aparece:

```
✅ Tenant criado com sucesso!

Domínios criados:
- marmitariadagi.yumgo.com.br
- marmitariadagi.eliseus.com.br

⚠️ IMPORTANTE: Adicione no Google OAuth:
https://marmitariadagi.yumgo.com.br/auth/google/callback
https://marmitariadagi.eliseus.com.br/auth/google/callback

[Botão: Copiar URLs OAuth]
```

---

## 📋 **WORKFLOW COMPLETO:**

### **Configuração Inicial (1 vez só):**

1. **Google Cloud Console:**
   ```
   https://console.cloud.google.com/apis/credentials
   ```

2. **Criar OAuth Client:**
   - Nome: YumGo Web Client
   - Tipo: Web application

3. **Rodar comando para gerar lista completa:**
   ```bash
   php artisan oauth:generate-urls
   ```

4. **Copiar TODAS as URLs geradas**

5. **Colar no Google:**
   - Authorized redirect URIs: colar a primeira lista
   - Authorized JavaScript origins: colar a segunda lista

6. **Salvar**

**✅ PRONTO! Todos os tenants atuais funcionam!**

---

### **Ao Criar Novo Tenant:**

1. **Criar tenant no painel YumGo**

2. **Aparece notificação com as novas URLs**

3. **Copiar as URLs da notificação**

4. **Google Cloud Console:**
   - Credentials → YumGo Web Client → Edit
   - Adicionar as novas URLs
   - Save

**⏱️ Tempo: 30 segundos**

---

### **OU use o comando:**

```bash
php artisan oauth:generate-urls
```

Ele sempre mostra a lista COMPLETA atualizada!

---

## 🎯 **POR QUE ESSA É A MELHOR SOLUÇÃO:**

### ✅ **Vantagens:**
- **1 único Client ID/Secret** para toda plataforma
- Não precisa configurar .env de cada tenant
- Fácil de gerenciar e manter
- Padrão usado por grandes plataformas (Shopify, WordPress)

### ❌ **Alternativa ruim seria:**
- 1 OAuth Client por tenant
- Teria que criar manualmente CADA UM
- Teria que guardar Client ID/Secret diferente para cada
- Impossível de escalar

---

## 🚀 **TESTE AGORA:**

### **1. Gere a lista completa:**
```bash
cd /var/www/restaurante
php artisan oauth:generate-urls
```

### **2. Adicione TODAS no Google:**
```
https://console.cloud.google.com/apis/credentials
→ YumGo Web Client
→ Edit
→ Cole as URLs
→ Save
```

### **3. Teste em TODOS os tenants:**
```
https://marmitariadagi.yumgo.com.br/
https://parker-pizzaria.yumgo.com.br/
https://marmitaria-gi.yumgo.com.br/
```

**Todos devem funcionar com o mesmo OAuth! ✅**

---

## 💡 **DICA PRO:**

### **Wildcards (se o Google permitir):**

Alguns provedores OAuth aceitam wildcards:
```
https://*.yumgo.com.br/auth/google/callback
```

**MAS o Google NÃO aceita wildcards em redirect URIs!**

Por isso a solução de listar todos é a única opção.

---

## 📊 **RESUMO:**

| Tarefa | Manual | Automático |
|--------|--------|------------|
| Criar OAuth Client | ✅ (1x) | ❌ (impossível) |
| Adicionar URLs | ✅ (cada tenant) | ⚠️ (lista automática) |
| Configurar .env | ❌ | ✅ (único para todos) |
| Funcionar em tenants | ❌ | ✅ (automático) |

**Conclusão:** É o máximo de automação possível! 🚀

---

## 🆘 **FAQ:**

### **"Posso criar 1 OAuth Client por tenant?"**
❌ Não recomendado! Você teria que:
- Criar manualmente CADA OAuth Client
- Guardar Client ID/Secret de CADA UM
- Configurar .env de CADA tenant
- Impossível de escalar

### **"Preciso adicionar URL de cada tenant novo?"**
✅ Sim, mas leva 30 segundos:
1. Rodar `php artisan oauth:generate-urls`
2. Copiar novas URLs
3. Colar no Google
4. Save

### **"Quanto tempo leva para adicionar 10 tenants novos?"**
⏱️ ~2 minutos:
- Rodar comando: 5s
- Copiar lista: 5s
- Abrir Google Console: 10s
- Colar e salvar: 30s
- Esperar propagação: 1min

---

**Essa é a melhor solução possível! 🎯**

Use o comando sempre que criar tenants novos:
```bash
php artisan oauth:generate-urls
```
