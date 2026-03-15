# 🚀 YumGo Bridge v3.0.0 - Plano de Implementação

## 🎯 Objetivos

Criar uma versão **PROFISSIONAL** e **COMPLETA** do bridge, com:

1. ✅ Interface moderna e intuitiva
2. ✅ Configuração de caracteres por linha (32-48)
3. ✅ Autostart (iniciar com Windows)
4. ✅ Persistência total de configurações
5. ✅ Debug visual em tempo real
6. ✅ Testes integrados
7. ✅ Logs organizados
8. ✅ Melhor tratamento de erros
9. ✅ Código modular e limpo

---

## 📋 Funcionalidades

### 1. Configuração de Impressoras

**Tipos suportados:**
- 🖨️ **Sistema** (Recomendado - funciona com qualquer impressora instalada)
- 🔌 **USB** (Impressoras térmicas diretas)
- 🌐 **Rede** (IP:Porta)

**Configurações avançadas:**
- 📏 **Caracteres por linha:** 32-48 (customizável - NOVO!)
- 📋 **Número de cópias:** 1-4
- 🔤 **Tamanho da fonte:** Pequeno/Normal/Grande
- 🖼️ **Logo:** Opcional (PNG/JPG)
- ✂️ **Remover acentos:** Sim/Não
- 🎨 **Espaçamento:** Compacto/Normal/Espaçado (NOVO!)

### 2. Conexão WebSocket

- ✅ Conexão automática ao iniciar (se configurado)
- ✅ Reconexão automática em caso de queda
- ✅ Indicador visual de status (conectado/desconectado/reconectando)
- ✅ Logs de conexão em tempo real

### 3. Impressão Automática

- ✅ Impressão automática de novos pedidos
- ✅ Proteção contra duplicação (cooldown 5 min)
- ✅ Som de notificação (opcional)
- ✅ Notificação do sistema
- ✅ Histórico de pedidos impressos

### 4. Interface

**Tabs organizadas:**
- 🏠 **Home:** Status + Logs em tempo real
- ⚙️ **Configuração:** Credenciais + Autostart
- 🖨️ **Impressoras:** Configurar Cozinha/Bar/Balcão
- 🧪 **Testes:** Testar impressão e conexão
- 📊 **Histórico:** Pedidos impressos (últimos 50)

### 5. Debug e Logs

- 📝 Logs visuais em tempo real (Home tab)
- 🔍 Filtros de log (Info/Warn/Error)
- 💾 Exportar logs para arquivo
- 🧪 Teste de impressão integrado
- 🌐 Teste de conexão integrado

---

## 🎨 Design

### Cores
- **Primary:** #667eea (roxo)
- **Success:** #10b981 (verde)
- **Warning:** #f59e0b (laranja)
- **Error:** #ef4444 (vermelho)
- **Background:** #f9fafb (cinza claro)

### Tipografia
- **Fonte:** 'Inter' ou 'Segoe UI'
- **Tamanhos:** 12px (small), 14px (normal), 16px (large)

### Layout
- **Sidebar:** Navegação entre tabs
- **Main area:** Conteúdo da tab ativa
- **Status bar:** Topo com status da conexão

---

## 📁 Estrutura de Arquivos

```
electron-bridge-v3/
├── src/
│   ├── main.js                  # Process principal (Electron)
│   ├── preload.js               # Bridge IPC seguro
│   ├── index.html               # Interface principal
│   ├── renderer.js              # Lógica da UI
│   ├── styles/
│   │   └── main.css             # Estilos globais
│   ├── modules/
│   │   ├── websocket.js         # Gerenciador WebSocket
│   │   ├── printer.js           # Gerenciador de impressoras
│   │   ├── storage.js           # Persistência (electron-store)
│   │   ├── autostart.js         # Auto-launch
│   │   └── logger.js            # Sistema de logs
│   └── components/
│       ├── tabs.js              # Componente de tabs
│       ├── printer-config.js    # Formulário de impressora
│       └── log-viewer.js        # Visualizador de logs
├── assets/
│   ├── icon.png
│   └── sounds/
│       └── notification.mp3
├── build/
│   └── icon.ico
└── package.json
```

---

## 🛠️ Tecnologias

- **Electron:** 29.x
- **Pusher-JS:** 8.4.x (WebSocket client)
- **Laravel Echo:** 1.16.x
- **electron-store:** 8.2.x (Persistência)
- **auto-launch:** 5.0.x (Autostart)
- **electron-log:** 5.1.x (Logs)
- **escpos:** 3.0.x (Impressão térmica)

---

## ⚙️ Configurações Salvas

```javascript
{
  // Credenciais
  "restaurantId": "marmitariadagi",
  "token": "7|...",

  // Autostart
  "autostart": true,

  // Impressoras
  "printers": {
    "kitchen": {
      "enabled": true,
      "type": "system",
      "printerName": "Epson TM-T20",
      "charsPerLine": 48,        // NOVO!
      "copies": 2,
      "fontSize": "normal",
      "spacing": "normal",       // NOVO!
      "printLogo": false,
      "logoPath": "",
      "removeAccents": false
    },
    "bar": { ... },
    "counter": { ... }
  },

  // Preferências
  "preferences": {
    "soundEnabled": true,
    "notificationsEnabled": true,
    "autoPrint": true,
    "logLevel": "info"
  }
}
```

---

## 🚀 Roadmap de Implementação

### Fase 1: Estrutura Base ✅
- [x] Criar estrutura de pastas
- [x] package.json atualizado
- [ ] main.js simplificado e modular
- [ ] preload.js para IPC seguro

### Fase 2: Interface ✅
- [ ] index.html com tabs
- [ ] CSS moderno e responsivo
- [ ] Componente de tabs
- [ ] Status bar

### Fase 3: Configuração ✅
- [ ] Form de credenciais
- [ ] Form de impressoras (COM campo charsPerLine)
- [ ] Toggle de autostart
- [ ] Salvar/Restaurar configs

### Fase 4: WebSocket ✅
- [ ] Módulo websocket.js
- [ ] Conexão automática
- [ ] Reconexão automática
- [ ] Logs de conexão

### Fase 5: Impressão ✅
- [ ] Módulo printer.js
- [ ] Impressão automática
- [ ] Teste de impressão
- [ ] Histórico de impressões

### Fase 6: Debug ✅
- [ ] Log viewer visual
- [ ] Filtros de log
- [ ] Exportar logs
- [ ] Testes integrados

### Fase 7: Polish ✅
- [ ] Ícones
- [ ] Sons
- [ ] Animações
- [ ] Build final

---

## 🧪 Testes

### Manual
- [ ] Conexão WebSocket
- [ ] Impressão teste
- [ ] Autostart
- [ ] Persistência
- [ ] Reconexão

### Automatizado (futuro)
- [ ] Testes unitários (Jest)
- [ ] Testes E2E (Spectron)

---

## 📝 Notas

### Diferenças v2 → v3

| Feature | v2.x | v3.0 |
|---------|------|------|
| Caracteres por linha | Fixo (48) | **Customizável (32-48)** |
| Espaçamento | Fixo | **Configurável** |
| Interface | Single page | **Tabs organizadas** |
| Logs | Arquivo apenas | **Visual + Arquivo** |
| Testes | Manual externo | **Integrados na UI** |
| Código | Monolítico | **Modular** |
| Autostart | ❌ Não tinha | **✅ Incluído** |
| Persistência | Parcial | **Total** |

---

**Início:** 15/03/2026
**Prazo estimado:** 3-4 horas
**Status:** 🚧 Em desenvolvimento
