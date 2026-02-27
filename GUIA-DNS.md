# 🌐 Guia Completo: DNS e Cadastro de Restaurantes

## ✅ Domínio Atualizado

**Tenant:** Pizzaria Bella
**Domínio:** `pizzaria-bella.eliseus.com.br`
**Painel:** https://pizzaria-bella.eliseus.com.br/painel

---

## 🎯 Configuração DNS na Cloudflare

### **Opção 1: Wildcard (RECOMENDADO)** ⭐

Configure **UMA VEZ** e nunca mais mexa:

**Na Cloudflare:**
```
Tipo: A
Nome: *
Conteúdo: SEU_IP_SERVIDOR
TTL: Auto
Proxy: ✅ (laranja - Proxied)
```

**Vantagens:**
- ✅ Cadastra restaurante e JÁ funciona
- ✅ Não precisa mexer no DNS nunca mais
- ✅ Suporta ilimitados restaurantes
- ✅ Automático e instantâneo

---

### **Opção 2: DNS Individual**

**Fluxo:**

1. **Painel Admin** → Cria restaurante → Define slug: `churrascaria-gaucha`
2. **Sistema gera:** `churrascaria-gaucha.eliseus.com.br`
3. **Cloudflare** → Adiciona DNS:
   ```
   Tipo: A
   Nome: churrascaria-gaucha
   Conteúdo: SEU_IP_SERVIDOR
   ```
4. Aguarda propagação (1-5 minutos)
5. Acessa: https://churrascaria-gaucha.eliseus.com.br/painel

---

## 🔐 Credenciais Atuais

**Painel da Plataforma (Admin)**
```
URL: https://food.eliseus.com.br/admin
Email: admin@deliverypro.com
Senha: AdminDeliveryPro@2024
```

**Painel do Restaurante (Pizzaria Bella)**
```
URL: https://pizzaria-bella.eliseus.com.br/painel
Email: admin@pizzariabella.com.br
Senha: senha123
Perfil: Super Admin (todas permissões)
```

---

## 📋 Recursos Disponíveis

**Painel Admin:** `/admin`
- ✅ Gerenciar Restaurantes (CRUD completo)
- ✅ Criar novos tenants
- ✅ Ver domínios de cada restaurante
- ✅ Alterar planos e status

**Painel Restaurante:** `/painel`
- ✅ Dashboard
- ✅ Produtos (CRUD)
- ✅ Usuários (com permissões granulares)
- ✅ Perfis (Roles do Shield)
- ✅ Sistema de permissões visual
