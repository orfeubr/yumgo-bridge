# 🤖 GitHub Actions - Builds Automáticos

## 📋 O Que Foi Configurado

### Workflow: `electron-build.yml`

Build automático do **YumGo Bridge** (app Electron) para **3 plataformas**:

- ✅ **Windows** → `.exe` (NSIS installer)
- ✅ **macOS** → `.dmg` + `.zip`
- ✅ **Linux** → `.AppImage` + `.deb`

## 🚀 Como Funciona

### Triggers (Quando Roda)

1. **Push na branch main/master** (apenas se mudar `electron-bridge/`)
2. **Pull Request** (apenas se mudar `electron-bridge/`)
3. **Release** (quando criar tag)
4. **Manual** (botão "Run workflow" no GitHub)

### O Que Faz

```
1. Checkout do código
2. Instala Node.js 20
3. Instala dependências (npm ci)
4. Builda para a plataforma específica
5. Upload dos instaladores como artifacts
6. (Opcional) Cria release com os arquivos
```

## 📦 Onde Baixar os Instaladores

### Opção 1: Artifacts (Builds de desenvolvimento)

1. Acesse: `https://github.com/SEU_USER/SEU_REPO/actions`
2. Clique no workflow mais recente
3. Role até **Artifacts**
4. Baixe:
   - `yumgo-bridge-windows` → Instalador Windows
   - `yumgo-bridge-macos` → Instalador macOS
   - `yumgo-bridge-linux` → Instalador Linux

**Artifacts expiram em 30 dias**

### Opção 2: Releases (Builds de produção)

Para criar uma release com instaladores:

```bash
# 1. Criar tag de versão
git tag v1.0.0
git push origin v1.0.0

# 2. GitHub Actions roda automaticamente
# 3. Instaladores aparecem na release
```

Acesse: `https://github.com/SEU_USER/SEU_REPO/releases`

## 🔧 Configuração Necessária

### 1. Habilitar GitHub Actions (se não estiver)

```
Settings → Actions → General → Allow all actions
```

### 2. Permissões de Workflow

```
Settings → Actions → General → Workflow permissions
☑ Read and write permissions
```

### 3. (Opcional) Assinatura de Código

**Windows:**
```yaml
# Adicionar secrets:
WINDOWS_CERTIFICATE_BASE64
WINDOWS_CERTIFICATE_PASSWORD
```

**macOS:**
```yaml
# Adicionar secrets:
APPLE_ID
APPLE_ID_PASSWORD
APPLE_TEAM_ID
CSC_LINK (certificado)
CSC_KEY_PASSWORD
```

**Sem assinatura:** Instaladores funcionam, mas dão aviso de "unknown publisher"

## ⚡ Execução Manual

1. Acesse: `Actions` no GitHub
2. Selecione: `Build Electron App`
3. Clique: `Run workflow`
4. Escolha: branch (main)
5. Clique: `Run workflow` (confirmar)

## 📊 Status do Build

Badge para README principal:

```markdown
![Build Status](https://github.com/SEU_USER/SEU_REPO/actions/workflows/electron-build.yml/badge.svg)
```

## 🐛 Troubleshooting

### Build falha no Windows

**Problema:** Módulos nativos (usb) não compilam

**Solução:** Já está configurado com `npm ci` (usa package-lock.json)

### Build falha no macOS

**Problema:** `dmg-license` não encontrado

**Solução:** Adicionar em `electron-bridge/package.json`:
```json
"devDependencies": {
  "dmg-license": "^1.0.11"
}
```

### Artifacts não aparecem

**Problema:** Paths incorretos

**Solução:** Verificar se `dist/` tem os arquivos esperados no log do workflow

## 🎯 Próximos Passos

### 1. Primeiro Push

```bash
git add .github/
git commit -m "ci: Adiciona GitHub Actions para builds automáticos"
git push
```

### 2. Monitorar Build

- Acesse: `Actions` no GitHub
- Veja: Logs em tempo real
- Aguarde: ~10-15 minutos (todas plataformas)

### 3. Baixar Instaladores

- Artifacts: Disponíveis imediatamente
- Ou criar release: `git tag v1.0.0 && git push origin v1.0.0`

## 💡 Dicas

### Builds Mais Rápidos

```yaml
# Rodar apenas quando necessário
on:
  push:
    paths:
      - 'electron-bridge/**'
```

### Cache de Dependências

```yaml
# Já configurado!
- uses: actions/setup-node@v4
  with:
    cache: 'npm'
```

### Notificações

- GitHub envia email quando build falha
- Configure: `Settings → Notifications`

## 📝 Manutenção

### Atualizar Versão do App

```bash
cd electron-bridge
npm version patch  # 1.0.0 → 1.0.1
npm version minor  # 1.0.0 → 1.1.0
npm version major  # 1.0.0 → 2.0.0
```

Isso atualiza `package.json` e cria commit + tag automaticamente.

### Atualizar Actions

```yaml
# Verificar versões mais recentes:
- uses: actions/checkout@v4      # ← Sempre usar latest
- uses: actions/setup-node@v4
- uses: actions/upload-artifact@v4
```

## ✅ Checklist de Deploy

- [ ] Workflow criado (`.github/workflows/electron-build.yml`)
- [ ] Push para GitHub
- [ ] Actions habilitado no repositório
- [ ] Build passa (Windows + macOS + Linux)
- [ ] Artifacts disponíveis para download
- [ ] (Opcional) Tag criada para release
- [ ] (Opcional) Certificados configurados

---

**Configurado em:** 06/03/2026
**Por:** Claude Sonnet 4.5
**Status:** ✅ Pronto para uso
