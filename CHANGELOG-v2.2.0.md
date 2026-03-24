# YumGo Bridge v2.2.0 - Changelog

**Data:** 15/03/2026

## 🎉 Novas Funcionalidades

### 🚀 Iniciar com Windows (Autostart)
- **Problema resolvido:** App não tinha opção de iniciar automaticamente
- **Solução:** Toggle "Iniciar automaticamente com o Windows" na tela de configuração
- **Como usar:**
  1. Marque a caixinha "🚀 Iniciar automaticamente com o Windows"
  2. App será aberto automaticamente ao ligar o PC
  3. Desmarque para desabilitar

### 💾 Configurações Persistentes de Impressora
- **Problema resolvido:** Configurações de impressora se perdiam ao fechar o app
- **Solução:** Todas as configurações agora são salvas e restauradas automaticamente

**O que é salvo:**
- ✅ Tipo de impressora (USB/Rede/Sistema)
- ✅ Impressora selecionada
- ✅ Largura do papel (58mm/80mm)
- ✅ Número de cópias (1-4 vias)
- ✅ Tamanho da fonte (pequeno/normal/grande)
- ✅ Imprimir logo (sim/não + caminho da imagem)
- ✅ Remover acentos (sim/não)
- ✅ IP e porta (para impressoras de rede)

**Como funciona:**
1. Configure a impressora normalmente
2. Feche o app
3. Ao reabrir, TODAS as configurações estarão lá! ✨

---

## 🔧 Melhorias Técnicas

### Persistência de Dados
- Configurações armazenadas em `electron-store` (arquivo JSON local)
- Restauração automática ao iniciar o app
- Validação de campos antes de salvar

### Autostart
- Usa pacote `auto-launch` (compatível Windows/macOS/Linux)
- Registra app no startup do sistema
- Toggle on/off sem reiniciar o app

---

## 📦 Dependências Adicionadas

```json
"auto-launch": "^5.0.6"
```

---

## 🐛 Bugs Corrigidos

- ❌ **ANTES:** Configurações de impressora sumiam ao fechar
- ✅ **AGORA:** Tudo salvo e restaurado automaticamente

- ❌ **ANTES:** Tinha que configurar toda vez que abria
- ✅ **AGORA:** Configure uma vez, funciona sempre

- ❌ **ANTES:** Tinha que abrir manualmente o app todo dia
- ✅ **AGORA:** App abre sozinho ao ligar o PC (opcional)

---

## 🛠️ Para Fazer o Build (Windows)

### Método 1: Build Direto no Windows

```bash
# 1. Clone o repositório
git clone https://github.com/orfeubr/yumgo.git
cd yumgo/electron-bridge

# 2. Instale dependências
npm install

# 3. Build
npm run build:win

# 4. Instalador estará em: dist/YumGo-Bridge-2.2.0-win-x64.exe
```

### Método 2: Build com Docker (Linux)

```bash
# Usar Windows container (futuro)
```

---

## 📝 Notas de Upgrade

**Usuários que atualizarem de v2.1.6 → v2.2.0:**
- Configurações existentes serão preservadas ✅
- Autostart vem DESABILITADO por padrão (você precisa marcar manualmente)
- Nenhuma ação necessária, tudo funcionará automaticamente

---

## 🚀 Próxima Versão (v3.0.0)

**Em desenvolvimento:**
- ✨ Interface completamente nova (moderna e profissional)
- 📏 Configuração de caracteres por linha (32-48)
- 📊 Dashboard com estatísticas
- 📝 Logs visuais em tempo real
- 🧪 Testes integrados na interface
- 📋 Histórico de pedidos impressos

---

**Versão:** 2.2.0
**Build Date:** 15/03/2026
**Compatibilidade:** Windows 10+, macOS 10.14+, Linux (Ubuntu 18.04+)
