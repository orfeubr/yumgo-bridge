# 🐛 Debug: WebSocket não chega no Bridge

## ✅ O QUE JÁ VERIFICAMOS

1. **Laravel Broadcasting**: ✅ Configurado e funcionando
   - `BROADCAST_CONNECTION=reverb`
   - Credenciais corretas no .env
   - Cache limpo

2. **Reverb Server**: ✅ Rodando
   - Processo ativo (PID 1213960)
   - Porta 8081 escutando
   - Host: 0.0.0.0

3. **Evento sendo disparado**: ✅ Sim
   ```bash
   php artisan test:print 10
   # Saída: ✅ Evento disparado!
   ```

4. **Queue Workers**: ✅ Rodando
   - 2 workers default
   - 1 worker nfce
   - Fila vazia (jobs processados)

---

## ❓ O QUE FALTA VERIFICAR

### 1. Bridge está conectado ao Reverb?

**Como verificar:**
```bash
# No bridge (Electron app):
# - Verifique se mostra "✅ Conectado" no status
# - Verifique console do DevTools (Ctrl+Shift+I)
```

**Onde ver logs do bridge:**
- Windows: `%APPDATA%/yumgo-bridge/logs/main.log`
- Linux: `~/.config/yumgo-bridge/logs/main.log`
- macOS: `~/Library/Logs/yumgo-bridge/main.log`

### 2. Reverb está broadcasting o evento?

**Monitorar em tempo real:**
```bash
cd /var/www/restaurante
./monitor-websocket.sh
```

Depois, em outro terminal:
```bash
php artisan test:print 10
```

Você deve ver logs mostrando o broadcast.

### 3. Bridge está escutando o canal correto?

**Canal esperado:**
```
restaurant.marmitariadagi
```

**Evento esperado:**
```
.order.created
```

**No código do bridge (main.js linha 413):**
```javascript
const channelName = `restaurant.${restaurantId}`;
channel.listen('.order.created', async (data) => { ... });
```

---

## 🔍 TESTE PASSO A PASSO

### 1. Verificar se bridge conectou

**No bridge:**
1. Abrir DevTools (View > Toggle Developer Tools)
2. Ir para Console
3. Procurar mensagens:
   - ✅ `Conectado ao servidor YumGo via Reverb/Pusher`
   - ❌ `Erro ao conectar` ou timeout

### 2. Verificar Restaurant ID no bridge

**No painel do restaurante:**
```
https://marmitariadagi.yumgo.com.br/painel/print-monitor
```

- Ver qual Restaurant ID está configurado no bridge
- **DEVE SER:** `marmitariadagi`
- Se estiver diferente, reconectar!

### 3. Testar envio de evento

```bash
# Terminal 1: Monitorar WebSocket
./monitor-websocket.sh

# Terminal 2: Disparar evento
php artisan test:print 10
```

Se você VER nos logs do monitor mas NÃO VER no bridge:
→ **Problema está no bridge (conexão ou escuta de canal)**

Se você NÃO VER nos logs do monitor:
→ **Problema está no Reverb (broadcasting)**

---

## 🛠️ SOLUÇÕES COMUNS

### Problema: Bridge não conecta

**Verificar:**
1. Token correto?
2. Restaurant ID correto?
3. Firewall bloqueando porta 8081?

**Solução:**
```javascript
// No bridge, verificar console:
// Deve mostrar: "restaurant.marmitariadagi"
// Se mostrar outro ID, reconectar!
```

### Problema: Conecta mas não recebe

**Verificar:**
1. Canal correto?
2. Nome do evento correto?

**Logs do bridge devem mostrar:**
```
📡 Conectando ao canal público: restaurant.marmitariadagi
```

Se mostrar ID diferente, está conectando no canal errado!

### Problema: Reverb não broadcast

**Verificar logs:**
```bash
tail -100 storage/logs/laravel.log | grep -i broadcast
```

Se aparecer erros como:
- `No query results for model [App\Models\Order]`
- `Undefined property: customer`

→ Evento está falhando antes de enviar!

---

## 🧪 COMANDO DE TESTE COMPLETO

```bash
# 1. Listar tenants
php list-tenants.php

# 2. Testar WebSocket
php test-websocket-order.php marmitariadagi

# 3. Testar impressão (comando existente)
php artisan test:print 10

# 4. Monitorar (deixar rodando)
./monitor-websocket.sh
```

---

## 📊 CHECKLIST DE DEBUG

- [ ] Reverb rodando? `ps aux | grep reverb`
- [ ] Queue rodando? `ps aux | grep queue:work`
- [ ] Broadcasting = reverb? `grep BROADCAST .env`
- [ ] Cache limpo? `php artisan config:clear`
- [ ] Bridge conectado? (Ver status no app)
- [ ] Restaurant ID correto no bridge?
- [ ] Canal correto? `restaurant.marmitariadagi`
- [ ] Evento dispara sem erro? `php artisan test:print 10`
- [ ] Logs do bridge? (Ver arquivo de log)

---

## 🎯 PRÓXIMO PASSO

**Abra o bridge e verifique:**

1. **Status da conexão**
   - ✅ Conectado: Ir para passo 2
   - ❌ Desconectado: Reconectar com credenciais corretas

2. **Console do DevTools (Ctrl+Shift+I)**
   - Ver se há erros
   - Ver se mostra: `Inscrevendo no canal PÚBLICO: restaurant.marmitariadagi`

3. **Disparar teste**
   ```bash
   php artisan test:print 10
   ```

4. **Ver se recebe no console do bridge:**
   - ✅ `🔔 Novo pedido recebido: #AUTO-4273`
   - ❌ Nada aparece: Problema no canal ou auth

---

**Se ainda não funcionar, me envie:**
1. Logs do bridge (arquivo main.log)
2. Saída do console do bridge (DevTools)
3. Saída de `./monitor-websocket.sh` + `php artisan test:print 10`
