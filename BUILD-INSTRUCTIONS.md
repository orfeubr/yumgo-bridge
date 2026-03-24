# 🏗️ Como Fazer o Build do YumGo Bridge

## ⚠️ IMPORTANTE: Build DEVE ser feito no Windows

Devido às dependências nativas (USB), o build DEVE ser feito em uma máquina **Windows**.

---

## 📋 Pré-requisitos

### Windows 10/11
- ✅ Node.js 18+ instalado
- ✅ Git instalado
- ✅ Visual Studio Build Tools (para compilar módulos nativos)

### Instalar Build Tools

```powershell
# Abra PowerShell como Administrador e rode:
npm install --global windows-build-tools

# OU baixe manualmente:
# https://visualstudio.microsoft.com/downloads/ (Build Tools for Visual Studio)
```

---

## 🚀 Passos para Build

### 1. Clone o Repositório

```bash
git clone https://github.com/orfeubr/yumgo.git
cd yumgo/electron-bridge
```

### 2. Instale as Dependências

```bash
npm install
```

Se der erro no `usb` ou `escpos-usb`:
```bash
npm install --force
```

### 3. Teste Localmente (Opcional)

```bash
npm start
```

Isso abre o app em modo de desenvolvimento.

### 4. Faça o Build

```bash
npm run build:win
```

Aguarde 5-10 minutos (primeira vez demora mais).

### 5. Instalador Gerado

```
dist/YumGo-Bridge-2.2.1-win-x64.exe
```

Tamanho aproximado: **150-200 MB** (inclui Electron + Node.js)

---

## 📦 Alternativa: GitHub Actions (Automático)

### Criar Workflow no GitHub

Crie `.github/workflows/build-bridge.yml`:

```yaml
name: Build YumGo Bridge

on:
  push:
    tags:
      - 'bridge-v*'
  workflow_dispatch:

jobs:
  build:
    runs-on: windows-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '20'

      - name: Install Dependencies
        working-directory: electron-bridge
        run: npm install

      - name: Build
        working-directory: electron-bridge
        run: npm run build:win

      - name: Upload Artifact
        uses: actions/upload-artifact@v3
        with:
          name: YumGo-Bridge-Installer
          path: electron-bridge/dist/*.exe
```

### Como usar:

1. **Push com tag:**
   ```bash
   git tag bridge-v2.2.1
   git push origin bridge-v2.2.1
   ```

2. **Ou rodar manualmente:**
   - GitHub > Actions > Build YumGo Bridge > Run workflow

3. **Download:**
   - GitHub > Actions > Workflow Run > Artifacts > Download

---

## 🐛 Problemas Comuns

### Erro: `node-gyp rebuild failed`

**Solução:**
```bash
npm install --global node-gyp
npm install --force
```

### Erro: `Cannot find module 'usb'`

**Solução:**
```bash
cd node_modules/usb
npm rebuild
```

### Erro: `MSBuild not found`

**Solução:**
Instale Visual Studio Build Tools (link acima)

---

## ✅ Checklist de Build

- [ ] Rodando no Windows 10/11?
- [ ] Node.js 18+ instalado?
- [ ] Build Tools instalados?
- [ ] `npm install` sem erros?
- [ ] `npm run build:win` completo?
- [ ] Arquivo `.exe` gerado em `dist/`?
- [ ] Testou o instalador?

---

## 🎯 Resultado Final

### Antes do Build:
```
electron-bridge/
├── src/
├── assets/
├── package.json
└── node_modules/
```

### Depois do Build:
```
electron-bridge/
├── dist/
│   ├── YumGo-Bridge-2.2.1-win-x64.exe  ← INSTALADOR!
│   ├── win-unpacked/ (pasta temporária)
│   └── latest.yml (metadata)
└── ...
```

### Instalador Final:
- **Nome:** `YumGo-Bridge-2.2.1-win-x64.exe`
- **Tamanho:** ~150-200 MB
- **Tipo:** NSIS Installer (Next, Next, Finish)
- **Instalação:** `C:\Users\{User}\AppData\Local\Programs\yumgo-bridge\`

---

## 🌐 Upload para GitHub Releases

```bash
# 1. Criar release no GitHub
gh release create bridge-v2.2.1 \
  --title "YumGo Bridge v2.2.1" \
  --notes "Autostart + Persistência + Tray fix" \
  electron-bridge/dist/YumGo-Bridge-2.2.1-win-x64.exe

# 2. Link de download:
# https://github.com/orfeubr/yumgo/releases/download/bridge-v2.2.1/YumGo-Bridge-2.2.1-win-x64.exe
```

---

## 🔥 Build Rápido (TL;DR)

```bash
git clone https://github.com/orfeubr/yumgo.git
cd yumgo/electron-bridge
npm install --force
npm run build:win

# Instalador em: dist/YumGo-Bridge-2.2.1-win-x64.exe
```

---

**Dúvidas?** Veja os logs em `dist/builder-debug.yml`
