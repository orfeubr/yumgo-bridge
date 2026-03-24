# 🎨 Ícones do YumGo Bridge

## 📁 Arquivos Necessários

### Para funcionar completamente, você precisa:

```
assets/
├── icon.svg          ✅ (Criado - ícone base)
├── icon.png          ⚠️  (Precisa criar - 512x512px)
├── icon@2x.png       ⚠️  (Precisa criar - 1024x1024px)
└── notification.mp3  ⚠️  (Precisa adicionar - som de notificação)

build/
├── icon.ico          ⚠️  (Precisa criar - Windows)
├── icon.icns         ⚠️  (Precisa criar - macOS)
└── icon.png          ⚠️  (Precisa criar - Linux - 512x512px)
```

---

## 🛠️ Como Gerar os Ícones

### Opção 1: Online (Mais Fácil)

1. Acesse: https://www.icoconverter.com/
2. Upload: `icon.svg`
3. Converta para:
   - `.ico` (Windows - 256x256)
   - `.icns` (macOS - 512x512)
   - `.png` (512x512 e 1024x1024)

### Opção 2: ImageMagick (Linux/Mac)

```bash
# Instalar ImageMagick
sudo apt-get install imagemagick  # Ubuntu/Debian
brew install imagemagick          # macOS

# Converter SVG para PNG
convert icon.svg -resize 512x512 icon.png
convert icon.svg -resize 1024x1024 icon@2x.png

# Para Windows (.ico)
convert icon.png -define icon:auto-resize=256,128,64,48,32,16 build/icon.ico

# Para Linux
cp icon.png build/icon.png
```

### Opção 3: Electron Icon Maker

```bash
npm install -g electron-icon-maker
electron-icon-maker --input=icon.svg --output=./build
```

---

## 🔊 Som de Notificação

Adicione um arquivo MP3 curto (1-2 segundos) em:
```
assets/notification.mp3
```

Sugestões:
- Notificação suave: https://notificationsounds.com
- Ou grave o seu próprio!

---

## 📋 Checklist

Antes de buildar o app:

- [ ] `assets/icon.png` (512x512) criado
- [ ] `assets/icon@2x.png` (1024x1024) criado
- [ ] `build/icon.ico` (Windows) criado
- [ ] `build/icon.icns` (macOS) criado
- [ ] `build/icon.png` (Linux) criado
- [ ] `assets/notification.mp3` adicionado

---

## 🎨 Design do Ícone Atual

- **Cor primária**: Roxo (#667eea) - mesma do gradiente do app
- **Símbolo**: Impressora térmica com papel
- **Indicador**: LED verde (status conectado)
- **Texto**: "YumGo" embaixo

---

## ✏️ Personalizar

Edite `icon.svg` com:
- Inkscape (grátis): https://inkscape.org
- Figma (online): https://figma.com
- Adobe Illustrator

---

## 🚀 Depois de Criar:

```bash
# Rebuildar o app
npm run build:win   # Windows
npm run build:mac   # macOS
```

Os ícones serão incluídos automaticamente! ✅
