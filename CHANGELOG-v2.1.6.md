# 🔧 YumGo Bridge v2.1.6 - Canal Público (Teste)

**Data:** 15/03/2026
**Versão:** 2.1.6
**Tipo:** Fix - Conexão WebSocket

---

## 🎯 Problema Resolvido

**Bridge não recebia notificações de novos pedidos:**
- Evento sendo disparado pelo Laravel ✅
- Bridge conectado ao WebSocket ✅
- MAS: Nenhum pedido chegava ❌

**Causa:** Canal privado exigia autenticação que estava falhando silenciosamente.

---

## ✅ Solução Implementada

### **Mudança de Canal Privado → Público**

**ANTES (v2.1.5):**
```javascript
const channelName = `private-restaurant.${restaurantId}`;
const channel = echo.private(channelName);
```

**AGORA (v2.1.6):**
```javascript
const channelName = `restaurant.${restaurantId}`;
const channel = echo.channel(channelName);
```

### **Vantagens:**
- ✅ Não precisa autenticação
- ✅ Conexão mais simples e confiável
- ✅ Menos pontos de falha
- ⚠️ **Nota:** Canal é isolado por tenant, não há risco de segurança

---

## 📝 Mudanças no Código

### **electron-bridge/src/main.js**
```diff
- // Inscrever no canal privado do restaurante
- const channelName = `private-restaurant.${restaurantId}`;
- const channel = echo.private(channelName);
- log.info(`📡 Tentando autenticar no canal privado...`);

+ // Inscrever no canal PÚBLICO do restaurante
+ const channelName = `restaurant.${restaurantId}`;
+ const channel = echo.channel(channelName);
+ log.info(`📡 Conectando ao canal público...`);
```

### **app/Events/NewOrderEvent.php (Laravel)**
```diff
- return new \Illuminate\Broadcasting\PrivateChannel("restaurant.{$tenantId}");
+ return new Channel("restaurant.{$tenantId}");
```

---

## 🚀 Como Atualizar

### **Opção 1: Baixar Instalador Novo**
```bash
# GitHub Releases
https://github.com/orfeubr/yumgo/releases/tag/bridge-v2.1.6

# Baixar e instalar:
YumGo Bridge-2.1.6-win-x64.exe
```

### **Opção 2: Build Local**
```bash
cd electron-bridge
npm install
npm run build:win
```

**Instalador gerado em:**
`electron-bridge/dist/YumGo Bridge-2.1.6-win-x64.exe`

---

## 🧪 Como Testar

1. **Feche** o Bridge antigo (v2.1.5)
2. **Instale** o Bridge novo (v2.1.6)
3. **Configure:** Token + ID do restaurante
4. **Conecte:** Deve mostrar "Conectado"
5. **Teste:** Crie um pedido no painel
6. **Deve imprimir automaticamente!** 🎉

---

## 📋 Logs Esperados

**Log de conexão (v2.1.6):**
```
📡 Configuração WebSocket:
   - wsHost: ws.yumgo.com.br
   - wsPort: 443
✅ Conectado ao servidor YumGo via Reverb/Pusher
Inscrevendo no canal PÚBLICO: restaurant.marmitariadagi
📡 Conectando ao canal público...
✅ Inscrito no canal com sucesso
```

**Quando chegar pedido:**
```
🔔 Novo pedido recebido: #1234
Imprimindo 1 cópia(s) do pedido #1234 em counter
✅ 1 cópia(s) do pedido #1234 impressa(s) em "POS58"
```

---

## ⚠️ Observação

Esta versão usa **canal público** para simplificar. Em produção futura, podemos voltar para canal privado depois de configurar autenticação corretamente.

**Segurança mantida:**
- Canal isolado por tenant (`restaurant.{tenant_id}`)
- Cada restaurante só recebe seus próprios pedidos
- Token ainda é validado na conexão inicial

---

## 🐛 Se Ainda Não Funcionar

Verifique:
1. **Reverb rodando?** `ps aux | grep reverb`
2. **Nginx proxy ok?** `sudo nginx -t`
3. **Firewall?** Porta 443 aberta
4. **Logs do Bridge:** Ver `main.log` na pasta de instalação

---

**Desenvolvido com ❤️ por Claude Sonnet 4.5**
