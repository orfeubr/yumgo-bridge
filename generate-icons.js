// Script simples para gerar ícones placeholder
// Execute: node generate-icons.js

const fs = require('fs');
const path = require('path');

console.log('📝 Criando placeholders para ícones...');
console.log('');
console.log('⚠️  IMPORTANTE: Estes são apenas placeholders!');
console.log('   Use ferramentas adequadas para criar ícones reais.');
console.log('');
console.log('Recomendado: https://www.icoconverter.com/');
console.log('');

// Criar arquivo de instrução em cada diretório
const buildReadme = `# Ícones de Build

Coloque aqui os ícones finais:

- icon.ico (Windows)
- icon.icns (macOS)
- icon.png (Linux - 512x512)

Use: https://www.icoconverter.com/ para converter o SVG
`;

fs.writeFileSync(path.join(__dirname, 'build', 'README.md'), buildReadme);

console.log('✅ Placeholder criado em build/README.md');
console.log('');
console.log('📋 Próximos passos:');
console.log('');
console.log('1. Acesse: https://www.icoconverter.com/');
console.log('2. Upload: electron-bridge/assets/icon.svg');
console.log('3. Baixe:');
console.log('   - icon.ico → salve em electron-bridge/build/');
console.log('   - icon.icns → salve em electron-bridge/build/');
console.log('   - icon.png (512x512) → salve em electron-bridge/build/');
console.log('');
console.log('Ou instale ImageMagick:');
console.log('  sudo apt-get install imagemagick');
console.log('  npm run convert-icons');
console.log('');
