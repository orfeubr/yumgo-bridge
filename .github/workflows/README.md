# GitHub Actions - Deploy Automático

## Como Configurar

### 1. Adicionar Secrets no GitHub

Acesse: `https://github.com/SEU_USUARIO/yumgo-delivery/settings/secrets/actions`

**Criar 3 secrets:**

1. **AWS_HOST**
   - Valor: `44.250.44.108` (seu IP elástico)

2. **AWS_USERNAME**
   - Valor: `ubuntu`

3. **AWS_SSH_KEY**
   - Valor: Conteúdo completo da sua chave privada `.pem`
   ```
   -----BEGIN RSA PRIVATE KEY-----
   MIIEpAIBAAKCAQEA...
   (todo o conteúdo)
   ...
   -----END RSA PRIVATE KEY-----
   ```

### 2. Como Funciona

Após configurar os secrets, **toda vez que você der push na branch main**:

```bash
git push origin main
```

**O GitHub vai:**
1. ✅ Conectar no servidor AWS via SSH
2. ✅ Executar `./deploy.sh` automaticamente
3. ✅ Notificar se deu certo ou errado

### 3. Acompanhar Deploy

Acesse: `https://github.com/SEU_USUARIO/yumgo-delivery/actions`

Você vai ver:
- ✅ Build Success (verde)
- ❌ Build Failed (vermelho)
- Logs completos do deploy

### 4. Workflow Manual (se quiser)

Também pode executar deploy manualmente:
- Vá em "Actions" no GitHub
- Selecione "Deploy to Production"
- Click em "Run workflow"

---

## 🔒 Segurança

**IMPORTANTE:**
- ✅ Secrets são **criptografados** pelo GitHub
- ✅ Nunca aparecem nos logs
- ✅ Só você tem acesso
- ❌ Nunca commite chaves no código

---

## 🚀 Resultado

**Workflow completo:**
```
💻 Você edita código localmente
↓
📝 git commit -m "feat: nova feature"
↓
⬆️ git push origin main
↓
🤖 GitHub Actions pega o push
↓
🔗 Conecta no servidor AWS (SSH)
↓
📦 Executa ./deploy.sh
↓
✅ Deploy concluído!
↓
🌐 Site atualizado em https://yumgo.com.br
```

**Tempo total: ~2-3 minutos automático!** ⚡

---

**Vantagens:**
- ✅ Deploy com 1 comando (`git push`)
- ✅ Histórico completo de deploys
- ✅ Rollback fácil (reverter commit)
- ✅ Notificações automáticas
- ✅ Deploy de qualquer lugar (até do celular!)
