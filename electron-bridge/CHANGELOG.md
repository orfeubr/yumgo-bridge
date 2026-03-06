# Changelog - YumGo Bridge

Todas as mudanças notáveis neste projeto serão documentadas aqui.

Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Versionamento Semântico](https://semver.org/lang/pt-BR/).

## [1.1.2] - 2026-03-06

### 🐛 Debug
- Adicionado console.log detalhado na função connect()
- Ajuda a diagnosticar por que o botão não está funcionando
- Logs mostram: função chamada, campos preenchidos, envio ao main process

## [1.1.1] - 2026-03-06

### 🐛 Corrigido
- Erro `ERR_FILE_NOT_FOUND` ao tentar carregar notification.mp3
- Comentado tag `<audio>` que referenciava arquivo inexistente
- Notificações agora usam apenas som nativo do sistema

## [1.1.0] - 2026-03-06

### ✅ Corrigido
- Conexão WebSocket via Nginx SSL proxy (ws.yumgo.com.br:443)
- Autenticação Pusher com assinatura HMAC-SHA256 correta
- Path WebSocket correto (vazio - Pusher adiciona /app/{key} automaticamente)
- forceTLS ativado em produção para conexão segura
- Certificado Let's Encrypt instalado (substituiu auto-assinado)

### 🔧 Melhorado
- Logging de erros mais detalhado (JSON.stringify)
- Notificações de erro mais amigáveis ao usuário
- Removido workaround SSL inseguro (NODE_TLS_REJECT_UNAUTHORIZED)

### 📝 Técnico
- wsHost: ws.yumgo.com.br (subdomínio dedicado)
- wsPort: 443 (HTTPS via Nginx proxy)
- wsPath: '' (vazio)
- enabledTransports: ['wss'] em produção
- Certificado SSL válido com renovação automática

## [1.0.0] - 2026-03-06

### 🎉 Lançamento Inicial
- Conexão WebSocket com Laravel Reverb/Pusher
- Recebimento de eventos de pedidos em tempo real
- Suporte a impressoras térmicas (USB e Rede)
- Configuração de múltiplas impressoras (Cozinha, Bar, Entrega)
- Interface gráfica com Electron
- Autenticação via token Sanctum
- Suporte a Windows, Linux e macOS
