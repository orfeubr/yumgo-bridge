# 🖨️ YumGo Bridge - App de Impressão Local

App desktop para impressão automática de pedidos em impressoras térmicas locais.

## 📋 Requisitos

- **Windows 10+**, **macOS 10.13+** ou **Linux** (Ubuntu 18.04+)
- **Node.js 18+** e **npm** (para desenvolvimento)
- **Impressora térmica** 80mm compatível com ESC/POS

## 🚀 Instalação Rápida (Usuário Final)

### Windows

1. Baixe `YumGo-Bridge-Setup-1.0.0.exe`
2. Execute o instalador
3. Siga as instruções na tela
4. Pronto! O app estará no menu Iniciar

### macOS

1. Baixe `YumGo-Bridge-1.0.0.dmg`
2. Abra o arquivo .dmg
3. Arraste o app para a pasta Aplicativos
4. Pronto! Abra pela pasta Aplicativos

### Linux

1. Baixe `YumGo-Bridge-1.0.0.AppImage`
2. Torne executável: `chmod +x YumGo-Bridge-1.0.0.AppImage`
3. Execute: `./YumGo-Bridge-1.0.0.AppImage`

---

## ⚙️ Configuração Inicial

### 1. Obter Credenciais

Acesse o painel YumGo do seu restaurante:

1. **Menu**: Configurações > Impressão Automática
2. Copie o **ID do Restaurante**
3. Clique em **Gerar Token de Acesso**
4. Copie o **Token**

### 2. Conectar o App

1. Abra o YumGo Bridge
2. Cole o **ID do Restaurante**
3. Cole o **Token de Acesso**
4. Clique em **Conectar**

✅ **Status deve mudar para "Conectado"**

### 3. Configurar Impressoras

#### Impressora USB:

1. Conecte a impressora via USB
2. Selecione "USB" no tipo
3. Clique em **Buscar Impressoras USB**
4. Os IDs serão preenchidos automaticamente
5. Clique em **Configurar**
6. Teste com **Imprimir Teste**

**Impressoras USB testadas:**
- Epson TM-T20
- Bematech MP-4200 TH
- Elgin i9
- Daruma DR700
- Diebold TSP143

#### Impressora de Rede (IP):

1. Anote o IP da impressora (ex: 192.168.1.100)
2. Selecione "Rede (IP)" no tipo
3. Digite o IP
4. Porta: 9100 (padrão)
5. Clique em **Configurar**
6. Teste com **Imprimir Teste**

### 4. Pronto! 🎉

Agora, quando um pedido for pago no sistema YumGo, ele será impresso automaticamente nas impressoras configuradas!

---

## 🖨️ Tipos de Impressão

| Impressora | Onde imprime | Conteúdo |
|------------|--------------|----------|
| **Cozinha** | Itens marcados como "Cozinha" | Pedido sem valores |
| **Bar** | Itens marcados como "Bar" | Pedido sem valores |
| **Balcão** | Todos os itens | Pedido completo com valores |

**Dica:** Configure o local de impressão de cada produto no cadastro de produtos do painel YumGo.

---

## 🔧 Desenvolvimento

### Requisitos:

```bash
Node.js 18+
npm ou yarn
```

### Instalação:

```bash
cd electron-bridge
npm install
```

### Executar em modo desenvolvimento:

```bash
npm run dev
```

### Build:

```bash
# Windows
npm run build:win

# macOS
npm run build:mac

# Linux
npm run build:linux

# Todos
npm run build:all
```

Os executáveis estarão em `dist/`.

---

## 📁 Estrutura do Projeto

```
electron-bridge/
├── src/
│   ├── main.js          # Processo principal Electron
│   ├── renderer.js      # Interface (IPC handlers)
│   ├── printer.js       # Módulo de impressão ESC/POS
│   └── index.html       # Interface HTML
├── assets/
│   ├── icon.png         # Ícone do app
│   └── notification.mp3 # Som de notificação
├── build/
│   ├── icon.ico         # Ícone Windows
│   ├── icon.icns        # Ícone macOS
│   └── icon.png         # Ícone Linux
├── package.json
└── README.md
```

---

## 🐛 Solução de Problemas

### App não conecta

**Causa:** Token inválido ou expirado
**Solução:** Gere um novo token no painel YumGo

---

### Impressora USB não encontrada

**Causa:** Driver não instalado ou permissões
**Solução Windows:** Instale driver do fabricante
**Solução Linux:**
```bash
sudo usermod -a -G lp $USER
sudo chmod 666 /dev/usb/lp0
```

---

### Impressão em branco

**Causa:** Impressora não compatível com ESC/POS
**Solução:** Verifique se impressora é térmica ESC/POS 80mm

---

### Pedido não imprime

**Causa:** Impressora não configurada ou offline
**Solução:**
1. Verifique status da impressora (luz acesa)
2. Teste com **Imprimir Teste**
3. Reconfigure a impressora

---

### App fecha sozinho

**Causa:** Erro não tratado
**Solução:**
1. Verifique logs em:
   - Windows: `%APPDATA%/yumgo-bridge/logs/`
   - macOS: `~/Library/Logs/yumgo-bridge/`
   - Linux: `~/.config/yumgo-bridge/logs/`
2. Envie logs para suporte@yumgo.com.br

---

## 🔒 Segurança

- ✅ Token armazenado criptografado localmente
- ✅ Conexão WebSocket segura (WSS)
- ✅ Autenticação em cada conexão
- ✅ Dados não transitam por terceiros

**Nunca compartilhe seu token!**

---

## 📞 Suporte

**Email:** suporte@yumgo.com.br
**WhatsApp:** (11) 99999-9999
**Horário:** Segunda a Sexta, 9h às 18h

**Documentação completa:**
https://docs.yumgo.com.br/bridge

---

## 📝 Changelog

### v1.0.0 (2026-03-06)
- ✨ Primeira versão
- 🖨️ Suporte a impressoras USB e Rede
- 🔔 Notificações de novos pedidos
- 📊 Dashboard de pedidos recentes
- ⚙️ Configuração de múltiplas impressoras
- 🎨 Interface moderna e intuitiva

---

## 📄 Licença

© 2026 YumGo. Todos os direitos reservados.

Este software é propriedade da YumGo e está licenciado apenas para uso por clientes ativos da plataforma YumGo.

---

**Desenvolvido com ❤️ pela equipe YumGo**
