# ✅ Let's Encrypt Instalado - ws.yumgo.com.br

**Data:** 06/03/2026 15:16 UTC
**Status:** 🎉 CERTIFICADO VÁLIDO FUNCIONANDO

## 🔐 Certificado SSL

### Instalado
- **Tipo:** Let's Encrypt (gratuito, renovação automática)
- **Domínio:** ws.yumgo.com.br
- **Validade:** Até 04/06/2026 (90 dias)
- **Renovação:** Automática via Certbot

### Localização
```
Certificado: /etc/letsencrypt/live/ws.yumgo.com.br/fullchain.pem
Chave Privada: /etc/letsencrypt/live/ws.yumgo.com.br/privkey.pem
```

### Nginx Atualizado
Arquivo `/etc/nginx/sites-available/ws.yumgo.com.br` agora usa:
```nginx
ssl_certificate /etc/letsencrypt/live/ws.yumgo.com.br/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/ws.yumgo.com.br/privkey.pem;
```

## ✅ Teste de Conexão

```bash
$ timeout 15 node test-pusher.cjs

🧪 Teste de Conexão Pusher/Reverb

Restaurant ID: a48efe45-872d-403e-a522-2cf445b1229b
Token: 9|G3rqtMDNitAkxtl6dk...

⏳ Conectando...

🔄 Estado: connecting → connected
✅ CONECTADO ao Reverb!
   Socket ID: 704114107.208180886

📡 Inscrevendo no canal privado: restaurant.a48efe45-872d-403e-a522-2cf445b1229b
✅ INSCRITO no canal com sucesso!
   Aguardando eventos de pedido...
```

**IMPORTANTE:** Teste rodado SEM `NODE_TLS_REJECT_UNAUTHORIZED=0` → Certificado válido!

## 📱 Electron App Atualizado

### Mudanças (Commit a009882)
1. ❌ Removido workaround `NODE_TLS_REJECT_UNAUTHORIZED=0`
2. ✅ Melhor logging de erros (JSON.stringify)
3. ✅ Notificação de erro mais amigável
4. ✅ Conexão 100% segura com certificado válido

### GitHub Actions
- **Status:** Build em andamento
- **URL:** https://github.com/orfeubr/yumgo/actions
- **Tempo estimado:** 5-10 minutos

### Como Testar
1. Aguardar build completar
2. Baixar nova versão em: https://github.com/orfeubr/yumgo/releases
3. Instalar e configurar com suas credenciais
4. Verificar conexão (deve funcionar sem erros SSL)

## 🔄 Renovação Automática

O Certbot configurou renovação automática:
```bash
# Verificar status do timer
sudo systemctl status certbot.timer

# Testar renovação (dry-run)
sudo certbot renew --dry-run

# Forçar renovação manual (se necessário)
sudo certbot renew --force-renewal
```

Certificados Let's Encrypt expiram a cada 90 dias, mas renovam automaticamente aos 30 dias antes do vencimento.

## 🎯 Próximos Testes

1. **Baixar app atualizado** (quando build terminar)
2. **Testar conexão** com suas credenciais
3. **Fazer pedido de teste** para verificar recebimento do evento
4. **Verificar impressão** (se impressora configurada)

## 📝 Comandos Úteis

```bash
# Ver certificados instalados
sudo certbot certificates

# Renovar manualmente
sudo certbot renew

# Recarregar Nginx após renovação
sudo systemctl reload nginx

# Testar conexão WebSocket
node test-pusher.cjs
```

---

**Certificado instalado por:** Claude Sonnet 4.5
**Testado e Aprovado:** ✅ 06/03/2026 15:16 UTC
