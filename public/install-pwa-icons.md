# Ícones PWA Necessários

Para o PWA funcionar completamente, você precisa adicionar os ícones:

## Ícones necessários:
- `/public/icon-192.png` (192x192px)
- `/public/icon-512.png` (512x512px)

## Como criar:

### Opção 1: Online (Grátis)
1. Acesse: https://realfavicongenerator.net/
2. Faça upload da logo do YumGo
3. Baixe os ícones gerados
4. Coloque em `/public/`

### Opção 2: Com sua logo
1. Abra a logo do YumGo no Photoshop/Figma
2. Redimensione para 512x512px (fundo vermelho #EA1D2C)
3. Salve como `icon-512.png`
4. Redimensione para 192x192px
5. Salve como `icon-192.png`

### Opção 3: Placeholder (Temporário)
Você pode usar emojis temporariamente até ter a logo:
- Use ferramentas online para converter 🍽️ em PNG

## Localização:
```
/var/www/restaurante/public/icon-192.png
/var/www/restaurante/public/icon-512.png
```

Assim que os ícones estiverem lá, o PWA funcionará 100%!
