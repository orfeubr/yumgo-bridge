# 📋 Changelog - YumGo Bridge

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

---

## [1.8.0] - 2026-03-06

### ✨ Adicionado
- **Auto-Update Automático**
  - Verifica atualizações ao iniciar (após 3 segundos)
  - Dialog pergunta se quer baixar atualização
  - Barra de progresso durante download
  - Dialog pergunta se quer instalar e reiniciar
  - Menu Tray: Item "🔄 Verificar Atualizações"
  - Configurado via electron-updater + GitHub releases

- **Botão Reimprimir no Painel Web**
  - Action "Reimprimir" em pedidos pagos (OrderResource)
  - Modal de confirmação antes de reimprimir
  - Dispara evento WebSocket manualmente
  - Notificação de sucesso/erro
  - Útil quando acaba tinta ou papel

- **Proteção Contra Impressão Duplicada**
  - Map rastreando pedidos impressos recentemente
  - Cooldown de 5 minutos por pedido
  - Impede impressão duplicada por webhook/bug
  - Log de aviso quando bloqueado
  - Evento 'print-skipped' para UI
  - Limpeza automática de registros > 10min

### 📝 Modificado
- **Painel Web - Aba "Impressora" Reformulada**
  - Alerta destacado: "Esta página NÃO configura impressoras"
  - Fluxo claro: Gerar Token → Baixar App → Configurar no App
  - Cards visuais de download (Windows/macOS)
  - Links diretos para release v1.8.0
  - Tamanho dos arquivos exibido
  - Link "Ver todas as versões"

### 🐛 Corrigido
- N/A (release focada em features)

---

## [1.7.0] - 2026-03-06

### ✨ Adicionado
- **Configurações Avançadas de Impressão** (inspirado no Anota Aí)
  - Número de cópias (1-4 vias)
  - Largura do papel (58mm / 80mm)
  - Tamanho da fonte (Pequeno / Normal / Grande)
  - Logo do restaurante (PNG/JPG/BMP)
  - Toggle remover acentos (impressoras antigas)

---

## [1.6.0] - 2026-03-06

### ✨ Adicionado
- **Lista de Impressoras USB com Nomes Amigáveis**
  - Dicionário de fabricantes (Epson, Bematech, Elgin, etc)
  - Dropdown selecionável: "Epson TM-T20"
  - Campos técnicos (vendor/product ID) escondidos

---

## [1.5.0] - 2026-03-06

### ✨ Adicionado
- **WebSocket Funcionando 100%**
  - Conexão estável com Laravel Reverb
  - Autenticação de canal privado
  - Recebe pedidos em tempo real

---

## 🔗 Links

- **Repositório:** https://github.com/orfeubr/yumgo
- **Releases:** https://github.com/orfeubr/yumgo/releases
- **Issues:** https://github.com/orfeubr/yumgo/issues

---

**Formato:** Baseado em [Keep a Changelog](https://keepachangelog.com/)
**Versionamento:** [Semantic Versioning](https://semver.org/)
