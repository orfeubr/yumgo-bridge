# 📦 Guia de Release - YumGo Bridge

## 🎯 Objetivo

Criar releases no GitHub para distribuir o YumGo Bridge e habilitar auto-update.

---

## 📋 Pré-requisitos

- Tag criada no git (já feito: `v1.8.0`)
- Executáveis compilados (Windows, macOS, Linux)
- Acesso ao repositório GitHub

---

## 🔨 Como Fazer Build

### Opção 1: Build em Máquina Windows (Recomendado para .exe)

**Pré-requisitos:**
```bash
# Windows 10/11
# Node.js 18+ instalado
# Git instalado
```

**Passos:**
```bash
# 1. Clonar repositório
git clone https://github.com/orfeubr/yumgo.git
cd yumgo/electron-bridge

# 2. Instalar dependências
npm install

# 3. Build Windows
npm run build:win

# 4. Executável gerado em:
# dist/YumGo Bridge-1.8.0-win-x64.exe (~80-100 MB)
```

---

### Opção 2: Build em Linux (AppImage/deb)

**Já rodando no servidor!** Arquivo será gerado em:
- `dist/YumGo Bridge-1.8.0.AppImage`
- `dist/yumgo-bridge_1.8.0_amd64.deb`

---

### Opção 3: Build em macOS (dmg)

**Pré-requisitos:**
```bash
# macOS 12+ (Monterey ou superior)
# Xcode Command Line Tools instalado
```

**Passos:**
```bash
git clone https://github.com/orfeubr/yumgo.git
cd yumgo/electron-bridge
npm install
npm run build:mac

# Gerado:
# dist/YumGo Bridge-1.8.0.dmg
# dist/YumGo Bridge-1.8.0-mac.zip
```

---

### Opção 4: GitHub Actions (Automático) - RECOMENDADO! 🌟

Crie arquivo `.github/workflows/build-release.yml`:

```yaml
name: Build and Release

on:
  push:
    tags:
      - 'v*'

jobs:
  build:
    strategy:
      matrix:
        os: [windows-latest, macos-latest, ubuntu-latest]

    runs-on: ${{ matrix.os }}

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '18'

      - name: Install dependencies
        run: |
          cd electron-bridge
          npm install

      - name: Build Windows
        if: matrix.os == 'windows-latest'
        run: |
          cd electron-bridge
          npm run build:win

      - name: Build macOS
        if: matrix.os == 'macos-latest'
        run: |
          cd electron-bridge
          npm run build:mac

      - name: Build Linux
        if: matrix.os == 'ubuntu-latest'
        run: |
          cd electron-bridge
          npm run build:linux

      - name: Upload artifacts
        uses: actions/upload-artifact@v4
        with:
          name: ${{ matrix.os }}-build
          path: electron-bridge/dist/*

      - name: Create Release
        if: matrix.os == 'windows-latest'
        uses: softprops/action-gh-release@v1
        with:
          files: |
            electron-bridge/dist/*.exe
            electron-bridge/dist/*.dmg
            electron-bridge/dist/*.AppImage
            electron-bridge/dist/*.deb
          draft: false
          prerelease: false
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
```

**Vantagens:**
- ✅ Build automático ao fazer `git push --tags`
- ✅ Compila para Windows, macOS e Linux simultaneamente
- ✅ Cria release automaticamente
- ✅ Anexa todos os executáveis

---

## 🌐 Criar Release Manualmente (Interface Web)

### 1. Acessar GitHub Releases

```
https://github.com/orfeubr/yumgo/releases/new
```

### 2. Preencher Formulário

**Tag version:** `v1.8.0` (selecionar da lista)

**Release title:** `YumGo Bridge v1.8.0 - Auto-update, Reimprimir e Proteção Duplicada`

**Description:**
```markdown
## 🎉 YumGo Bridge v1.8.0

### ✨ Novidades

#### 🔄 Auto-Update Automático
- Verifica atualizações ao iniciar
- Pergunta se quer baixar/instalar
- Menu: "Verificar Atualizações"
- Sem necessidade de baixar manualmente novamente!

#### 🖨️ Botão Reimprimir
- Reimprima pedidos quando acabar tinta/papel
- Disponível no painel de pedidos
- Apenas para pedidos pagos

#### 🛡️ Proteção Contra Duplicação
- Evita imprimir mesmo pedido 2x seguidas
- Cooldown de 5 minutos
- Economia de papel

#### 📱 Painel Web Melhorado
- Instruções mais claras
- Links diretos de download
- Menos confusão sobre configuração

---

## 📥 Download

Escolha o arquivo do seu sistema operacional:

### 🪟 Windows (Recomendado)
**Arquivo:** `YumGo Bridge-1.8.0-win-x64.exe`
- Windows 10/11 (64-bit)
- Tamanho: ~80 MB
- Instalador NSIS (permite escolher pasta)

### 🍎 macOS
**Arquivo:** `YumGo Bridge-1.8.0.dmg`
- macOS 12+ (Monterey ou superior)
- Intel e Apple Silicon (M1/M2/M3)
- Tamanho: ~90 MB

### 🐧 Linux
**Arquivo:** `YumGo Bridge-1.8.0.AppImage`
- Ubuntu 20.04+, Debian 11+, Fedora 35+
- Tamanho: ~85 MB
- Basta dar permissão de execução e rodar

---

## 🚀 Como Instalar

### Windows:
1. Baixe `YumGo Bridge-1.8.0-win-x64.exe`
2. Execute o instalador
3. Siga as instruções na tela
4. Abra o app e configure

### macOS:
1. Baixe `YumGo Bridge-1.8.0.dmg`
2. Abra o arquivo DMG
3. Arraste para a pasta Aplicativos
4. Abra o app (pode precisar permitir em Preferências > Segurança)

### Linux:
1. Baixe `YumGo Bridge-1.8.0.AppImage`
2. Dê permissão: `chmod +x YumGo*.AppImage`
3. Execute: `./YumGo*.AppImage`

---

## 🔧 Como Configurar

1. **Obtenha credenciais no painel:**
   - Acesse `https://seurestaurante.yumgo.com.br/painel/configuracoes?tab=-impressora-tab`
   - Clique "Gerar Token de Acesso"
   - Copie o ID do Restaurante e o Token

2. **Configure no app:**
   - Abra o YumGo Bridge
   - Cole as credenciais
   - Clique "Conectar"

3. **Configure impressoras:**
   - Clique "Buscar Impressoras USB"
   - Selecione sua impressora
   - Ajuste configurações (cópias, largura, etc)
   - Salve

4. **Pronto!**
   - Pedidos pagos imprimem automaticamente
   - App fica na bandeja do sistema

---

## 📝 Changelog Completo

### Adicionado
- ✅ Auto-update via electron-updater
- ✅ Botão "Reimprimir" no painel web (OrderResource)
- ✅ Proteção contra impressão duplicada (Map + cooldown)
- ✅ Links diretos de download no painel (v1.8.0)
- ✅ Alerta destacado na aba Impressora (menos confuso)

### Modificado
- 📝 Painel de configuração de impressora reformulado
- 📝 Cards visuais de download (Windows/macOS)

### Corrigido
- 🐛 N/A (release focada em features)

---

## 🔮 Próximas Versões

### v1.9.0 (Planejado)
- [ ] Pré-visualização de cupom na tela
- [ ] Teste de impressão sem pedido real
- [ ] QR Code no cupom para rastreamento
- [ ] Estatísticas de impressão (papel gasto)

### v2.0.0 (Futuro)
- [ ] Suporte a impressoras térmicas coloridas
- [ ] Templates de layout customizáveis
- [ ] Backup/restore de configurações
- [ ] App mobile para entregadores

---

## 🐛 Reportar Bugs

Encontrou um problema? Abra uma issue:
https://github.com/orfeubr/yumgo/issues/new

---

## 📚 Documentação

- [Changelog v1.8.0](../CHANGELOG-v1.8.0.md)
- [Troubleshooting](../TROUBLESHOOTING.md)
- [Configurações Avançadas](../CHANGELOG-v1.7.0.md)

---

**Data de Release:** 06/03/2026
**Commit:** 0439d88
**Desenvolvido por:** YumGo Team + Claude Code
```

### 3. Anexar Arquivos

**Arrastar e soltar:**
- `YumGo Bridge-1.8.0-win-x64.exe` (quando tiver)
- `YumGo Bridge-1.8.0.dmg` (quando tiver)
- `YumGo Bridge-1.8.0.AppImage` (quando tiver)

**Importante:** O electron-builder também gera arquivos `latest.yml`, `latest-mac.yml`, `latest-linux.yml` - **ANEXAR ESSES TAMBÉM!** (são usados pelo auto-updater)

### 4. Publicar

- ✅ Marcar: "Set as the latest release"
- ✅ NÃO marcar: "Set as a pre-release"
- Clicar: "Publish release"

---

## 🤖 Fazer Tudo Automaticamente (GitHub Actions)

**Mais fácil e recomendado!**

### 1. Criar arquivo de workflow:

Salvar em: `.github/workflows/build-release.yml`

(Conteúdo já está acima na "Opção 4")

### 2. Commitar e enviar:

```bash
git add .github/workflows/build-release.yml
git commit -m "ci: Adiciona workflow de build e release automático"
git push
```

### 3. Criar nova tag:

```bash
git tag -a v1.8.1 -m "Test release automation"
git push origin v1.8.1
```

### 4. Aguardar:

- GitHub Actions inicia automaticamente
- Compila para Windows, macOS e Linux
- Cria release com todos os arquivos
- ~10-15 minutos total

### 5. Pronto!

Release criado automaticamente em:
https://github.com/orfeubr/yumgo/releases

---

## 📊 Estrutura de Arquivos Gerados

```
electron-bridge/dist/
├── YumGo Bridge-1.8.0-win-x64.exe        # Windows installer
├── YumGo Bridge-1.8.0.AppImage           # Linux portable
├── yumgo-bridge_1.8.0_amd64.deb          # Debian/Ubuntu
├── YumGo Bridge-1.8.0.dmg                # macOS installer
├── YumGo Bridge-1.8.0-mac.zip            # macOS portable
├── latest.yml                            # Auto-update metadata (Windows)
├── latest-mac.yml                        # Auto-update metadata (macOS)
└── latest-linux.yml                      # Auto-update metadata (Linux)
```

**IMPORTANTE:** Anexar **TODOS** os arquivos no release!

---

## ✅ Checklist de Release

- [ ] Versão bumped no package.json
- [ ] Commit das mudanças
- [ ] Tag criada (`git tag -a v1.8.0`)
- [ ] Tag enviada (`git push origin v1.8.0`)
- [ ] Build gerado (Windows/macOS/Linux)
- [ ] Release criado no GitHub
- [ ] Todos os executáveis anexados
- [ ] Arquivos latest*.yml anexados
- [ ] Marcado como "latest release"
- [ ] Testado download e instalação
- [ ] Testado auto-update de versão anterior

---

## 🎓 Dicas

1. **Primeiro release:** Pode ser trabalhoso, mas próximos serão automáticos
2. **GitHub Actions:** Invista tempo configurando, economiza muito depois
3. **Versões:** Seguir semver (1.8.0, 1.8.1, 1.9.0, 2.0.0)
4. **Tags:** Sempre prefixar com 'v' (v1.8.0)
5. **Changelog:** Manter atualizado ajuda usuários
6. **Assets:** Arquivos latest*.yml são ESSENCIAIS para auto-update

---

**Autor:** YumGo Team
**Data:** 06/03/2026
**Versão deste guia:** 1.0
