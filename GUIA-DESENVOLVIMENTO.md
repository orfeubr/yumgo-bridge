# 🚀 Guia de Desenvolvimento YumGo

## ⚠️ IMPORTANTE: Não Edite Direto em Produção!

**ERRADO ❌:**
```
Editar arquivos direto no servidor AWS → Perigoso!
```

**CERTO ✅:**
```
Editar localmente → Git commit → Git push → Deploy
```

---

## 🏁 INÍCIO RÁPIDO (5 minutos)

### **Passo 1: Criar Repositório no GitHub**

1. Acesse: https://github.com/new
2. Nome: `yumgo-delivery`
3. Privado: ✅
4. **NÃO** marque "Add README"
5. Click em "Create repository"

### **Passo 2: Conectar Projeto ao GitHub**

**No servidor AWS atual (via SSH):**
```bash
cd /var/www/restaurante
git remote add origin https://github.com/SEU_USUARIO/yumgo-delivery.git
git branch -M main
git push -u origin main
```

**Pronto!** Código agora está no GitHub 🎉

### **Passo 3: Clonar no Seu PC**

**No seu computador:**
```bash
# Ir para onde quer salvar o projeto
cd ~/Projetos

# Clonar do GitHub
git clone https://github.com/SEU_USUARIO/yumgo-delivery.git
cd yumgo-delivery
```

---

## 💻 WORKFLOW DO DIA A DIA

### **Opção A: Editar Localmente (Recomendado)**

```bash
# 1. Abrir projeto no VS Code
code ~/Projetos/yumgo-delivery

# 2. Fazer mudanças nos arquivos
# (editar código normalmente)

# 3. Salvar e commitar
git add .
git commit -m "feat: adiciona nova funcionalidade"

# 4. Enviar para GitHub
git push origin main
```

**Deploy Automático:**
- GitHub Actions vai fazer deploy automaticamente! 🤖
- Acompanhe em: https://github.com/SEU_USUARIO/yumgo-delivery/actions
- Aguarde 2-3 minutos → Site atualizado!

### **Opção B: Editar no Servidor (Emergência)**

**Use APENAS para correções urgentes!**

```bash
# 1. SSH no servidor
ssh ubuntu@IP_AWS -i ~/.ssh/sua-chave.pem

# 2. Ir para projeto
cd /var/www/restaurante

# 3. Editar arquivo
nano app/algum-arquivo.php

# 4. Commit
git add .
git commit -m "hotfix: correção urgente"

# 5. Push para GitHub
git push origin main

# 6. No seu PC, puxar mudanças depois
git pull origin main
```

---

## 🔄 COMANDOS ÚTEIS

### **No seu PC:**

```bash
# Ver status
git status

# Ver histórico de commits
git log --oneline

# Puxar mudanças do GitHub
git pull origin main

# Criar branch para feature
git checkout -b feature/nome-da-feature

# Voltar para main
git checkout main

# Ver branches
git branch

# Deletar branch local
git branch -d feature/nome-da-feature
```

### **No servidor AWS:**

```bash
# Deploy manual (se GitHub Actions não estiver configurado)
cd /var/www/restaurante
./deploy.sh

# Ver logs de erro
tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log

# Limpar cache
php artisan optimize:clear

# Verificar status dos services
sudo systemctl status nginx
sudo systemctl status php8.3-fpm
```

---

## 🛠️ AMBIENTES

### **Desenvolvimento (Seu PC)**
```
URL: http://localhost
Banco: Local (Docker)
Debug: ON
Erros: Visíveis
```

### **Produção (AWS)**
```
URL: https://yumgo.com.br
Banco: RDS PostgreSQL
Debug: OFF
Erros: Logs apenas
```

---

## 🚨 REGRAS DE OURO

1. ✅ **SEMPRE** commite antes de fazer mudanças grandes
2. ✅ **SEMPRE** teste localmente antes de fazer push
3. ✅ **SEMPRE** use mensagens de commit descritivas
4. ❌ **NUNCA** commite senhas ou chaves (.env vai no .gitignore)
5. ❌ **NUNCA** force push (`git push --force`) na main
6. ❌ **NUNCA** edite direto em produção (exceto emergências)

---

## 📋 MENSAGENS DE COMMIT (Padrão)

**Formato:**
```
tipo: descrição curta

feat: adiciona nova funcionalidade
fix: corrige bug específico
docs: atualiza documentação
style: formatação, ponto e vírgula
refactor: refatora código sem mudar comportamento
test: adiciona testes
chore: tarefas de manutenção
```

**Exemplos:**
```bash
git commit -m "feat: adiciona filtro de busca no marketplace"
git commit -m "fix: corrige erro 500 no login social"
git commit -m "docs: atualiza README com instruções de deploy"
```

---

## 🔧 CONFIGURAR GITHUB ACTIONS (Deploy Automático)

### **1. Adicionar Secrets no GitHub**

Acesse: `Settings` → `Secrets and variables` → `Actions` → `New repository secret`

**Criar 3 secrets:**

| Nome | Valor |
|------|-------|
| `AWS_HOST` | `44.250.44.108` (seu IP) |
| `AWS_USERNAME` | `ubuntu` |
| `AWS_SSH_KEY` | Conteúdo completo do arquivo `.pem` |

### **2. Testar Deploy Automático**

```bash
# Fazer qualquer mudança
echo "# Test" >> README.md

# Commit e push
git add .
git commit -m "test: testando deploy automático"
git push origin main

# Acompanhar deploy
# GitHub → Actions → Ver logs em tempo real
```

**Se tudo der certo:** ✅ Deploy Success (verde)

**Se der erro:** ❌ Build Failed (vermelho) → Ver logs para debug

---

## 📦 ESTRUTURA DO PROJETO

```
yumgo-delivery/
├── .github/
│   └── workflows/
│       ├── deploy.yml          # GitHub Actions (deploy automático)
│       └── README.md           # Docs do CI/CD
├── app/                        # Código PHP (controllers, models, etc)
├── database/
│   └── migrations/             # Migrations do banco
├── public/                     # Assets públicos (CSS, JS, imagens)
├── resources/
│   └── views/                  # Templates Blade
├── routes/                     # Rotas da aplicação
├── storage/
│   └── logs/                   # Logs de erro
├── .env                        # Configurações (NÃO commitar!)
├── .env.example                # Template de .env
├── deploy.sh                   # Script de deploy
├── docker-compose.yml          # Docker (dev local)
└── GUIA-DESENVOLVIMENTO.md     # Este arquivo
```

---

## 🆘 PROBLEMAS COMUNS

### **"Permission denied" no git push**
```bash
# Adicionar chave SSH ao GitHub
ssh-keygen -t ed25519 -C "seu-email@example.com"
cat ~/.ssh/id_ed25519.pub
# Copiar e adicionar em: GitHub → Settings → SSH Keys
```

### **Deploy não funciona**
```bash
# Verificar se deploy.sh tem permissão de execução
chmod +x deploy.sh

# Ver logs de erro
tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log
```

### **Conflito no git pull**
```bash
# Ver arquivos em conflito
git status

# Resolver conflitos manualmente nos arquivos
# Depois:
git add .
git commit -m "fix: resolve merge conflicts"
```

---

## 📚 RECURSOS

- **Git Básico**: https://git-scm.com/book/pt-br/v2
- **GitHub Docs**: https://docs.github.com
- **Laravel Docs**: https://laravel.com/docs
- **VS Code**: https://code.visualstudio.com/

---

## ✅ CHECKLIST DO DESENVOLVEDOR

Antes de cada deploy:

- [ ] Código testado localmente
- [ ] Sem erros no log
- [ ] Migrations rodaram sem erro
- [ ] .env atualizado (se necessário)
- [ ] Commit com mensagem descritiva
- [ ] Push para GitHub
- [ ] Deploy (automático ou manual)
- [ ] Verificar site em produção

---

**Desenvolvido com ❤️ por YumGo Team**
**Claude Code Assistant** 🤖
