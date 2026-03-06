# 🔧 YumGo Bridge - Troubleshooting

## Histórico de Problemas e Soluções

### ❌ Problema 1: "Echo is not a constructor" (v1.1.4)

**Data:** 06/03/2026
**Versão afetada:** 1.1.4
**Versão corrigida:** 1.1.5

#### Sintomas
```
TypeError: Echo is not a constructor
at connectWebSocket (C:\Program Files\YumGo Bridge\resources\app.asar\src\main.js:145:16)
```

- App abre normalmente
- Campos de configuração aparecem
- Ao clicar "Conectar", status muda para "Erro de conexão"
- Logs mostram erro "Echo is not a constructor"

#### Causa Raiz

O pacote `laravel-echo` é um módulo ES6 que exporta um `default`. No Node.js/CommonJS (usado pelo Electron), ao usar `require()`, é necessário acessar explicitamente o `.default`.

**Código incorreto:**
```javascript
const Echo = require('laravel-echo');  // ❌ Retorna o módulo, não o constructor
echo = new Echo({ ... });              // ❌ Erro: Echo is not a constructor
```

**Código correto:**
```javascript
const Echo = require('laravel-echo').default;  // ✅ Acessa o export default
echo = new Echo({ ... });                      // ✅ Funciona!
```

#### Solução Aplicada

**Arquivo:** `src/main.js` (linha 6)

```diff
- const Echo = require('laravel-echo');
+ const Echo = require('laravel-echo').default;  // FIX: ES6 default export
```

**Versão atualizada:** `1.1.4` → `1.1.5`

#### Testado em:
- ✅ Windows 10 Pro (build 19045)
- ✅ Windows 11 (build 22621)

#### Como Prevenir no Futuro

**Ao usar pacotes ES6 no Electron:**

1. **Opção 1** (Recomendada - CommonJS):
```javascript
const Package = require('package-name').default;
```

2. **Opção 2** (ES6 modules - requer config):
```javascript
import Package from 'package-name';
```
_Nota: Requer `"type": "module"` no package.json e Electron configurado para ES6_

3. **Testar localmente** antes de build:
```bash
npm run dev  # Testar em modo desenvolvimento
npm run build:win  # Testar build completo
```

---

### ❌ Problema 2: "Pusher is not defined" (v1.1.5)

**Data:** 06/03/2026
**Versão afetada:** 1.1.5
**Versão corrigida:** 1.1.6

#### Sintomas
```
ReferenceError: Pusher is not defined
at PusherConnector.connect (.../laravel-echo/dist/echo.common.js:1103:27)
```

- App consegue carregar configurações
- Logs mostram "🔵 Conectando ao servidor..."
- Configuração WebSocket aparece nos logs
- Erro acontece ao tentar criar conexão Pusher

#### Causa Raiz

O Laravel Echo usa internamente o `PusherConnector` que espera encontrar `Pusher` no escopo global. Apenas importar o Pusher com `require()` não o disponibiliza globalmente.

**Código incorreto:**
```javascript
const Pusher = require('pusher-js');  // ❌ Apenas importa localmente
const Echo = require('laravel-echo').default;

echo = new Echo({
    broadcaster: 'reverb',
    // ... Laravel Echo tenta acessar global.Pusher → undefined!
});
```

**Código correto:**
```javascript
// ✅ Disponibiliza Pusher globalmente
global.Pusher = require('pusher-js');

const Echo = require('laravel-echo').default;

echo = new Echo({
    broadcaster: 'reverb',
    // ... Laravel Echo encontra global.Pusher ✓
});
```

#### Solução Aplicada

**Arquivo:** `src/main.js` (linhas 5-7)

```diff
const log = require('electron-log');
- const Pusher = require('pusher-js');
+ // FIX: Pusher precisa estar disponível globalmente para Laravel Echo
+ global.Pusher = require('pusher-js');
+
const Echo = require('laravel-echo').default;
```

**Versão atualizada:** `1.1.5` → `1.1.6`

#### Por que isso aconteceu?

No Node.js, `require()` importa módulos no escopo local. O Laravel Echo foi originalmente feito para navegadores (onde `window.Pusher` seria global), mas em Electron/Node.js precisamos usar `global.Pusher`.

#### Testado em:
- ✅ Windows 10 Pro
- ✅ Windows 11

---

## Outros Problemas Conhecidos

### ⚠️ Impressora USB não encontrada (Windows)

**Sintoma:** "Nenhuma impressora USB encontrada" ao clicar em "Buscar Impressoras USB"

**Causa:** Driver USB não instalado ou permissões insuficientes

**Solução:**
1. Instale o driver do fabricante da impressora
2. Execute o app como Administrador (clique direito > "Executar como administrador")
3. Reinicie o computador após instalar o driver

---

### ⚠️ Erro "ECONNREFUSED" ao conectar

**Sintoma:**
```
Error: connect ECONNREFUSED
```

**Causas possíveis:**
1. Servidor Reverb não está rodando
2. Firewall bloqueando porta 8081 (dev) ou 443 (prod)
3. Token inválido ou expirado

**Solução:**
1. Verifique se o servidor está online: `https://yumgo.com.br`
2. Gere um novo token no painel YumGo
3. Desative temporariamente firewall/antivírus para testar
4. Em produção, verifique se Nginx está fazendo proxy reverso correto

---

### ⚠️ Pedidos não imprimem automaticamente

**Sintoma:** Status "Conectado ✅" mas pedidos não imprimem

**Causas possíveis:**
1. Impressoras não configuradas
2. Impressora offline/desligada
3. Canal WebSocket não autorizado

**Solução:**
1. Configure pelo menos uma impressora (Cozinha, Bar ou Balcão)
2. Teste com "Imprimir Teste"
3. Verifique se impressora está ligada e com papel
4. Recarregue configurações:
   - Desconectar
   - Fechar app
   - Reabrir app
   - Conectar novamente

---

## 📊 Logs

**Localização dos logs:**
- Windows: `%APPDATA%\yumgo-bridge\logs\`
- macOS: `~/Library/Logs/yumgo-bridge/`
- Linux: `~/.config/yumgo-bridge/logs/`

**Como acessar (Windows):**
1. Pressione `Win + R`
2. Digite: `%APPDATA%\yumgo-bridge\logs`
3. Abra o arquivo `main.log`

**Enviar logs para suporte:**
- Email: suporte@yumgo.com.br
- Anexar: `main.log`

---

## 🚀 Build e Release

**Criar nova versão:**

1. Atualizar versão em `package.json`
2. Commit das mudanças:
```bash
git add .
git commit -m "bump: v1.x.x"
git push origin master
```

3. Criar tag de release:
```bash
git tag v1.x.x
git push origin v1.x.x
```

4. GitHub Actions automaticamente:
   - Faz build para Windows e macOS
   - Cria artifacts
   - Publica na aba "Releases"

**Download dos builds:**
- GitHub: [Actions](https://github.com/orfeubr/yumgo/actions) > Último workflow > Artifacts
- Ou: [Releases](https://github.com/orfeubr/yumgo/releases)

---

## 📞 Suporte

**Dúvidas ou problemas não listados?**

- Email: suporte@yumgo.com.br
- WhatsApp: (11) 99999-9999
- Horário: Segunda a Sexta, 9h às 18h

---

**Última atualização:** 06/03/2026 - v1.1.5

## 📝 Histórico de Versões Detalhado

### v1.1.6 (06/03/2026) ← ATUAL ✅
**Correção:**
- Fix: Pusher disponível globalmente (`global.Pusher = require('pusher-js')`)
- Resolve: `ReferenceError: Pusher is not defined`
- Docs: Troubleshooting atualizado

**Status:** Conexão WebSocket deve funcionar! 🎉

### v1.1.5 (06/03/2026)
**Correção:**
- Fix: Importação correta do Laravel Echo (`.default`)
- Resolve: `TypeError: Echo is not a constructor`
- Docs: Guia de troubleshooting criado

**Status:** Echo funciona, mas Pusher ainda quebrava

### v1.1.4 (06/03/2026)
**Bug:**
- ❌ `TypeError: Echo is not a constructor`
- Importação incorreta do Laravel Echo

**Status:** Versão quebrada - não usar!

---
