# ⚠️ OPERAÇÕES QUE EXIGEM APROVAÇÃO MANUAL

**Regra:** Claude NUNCA executa estas operações sem aprovação explícita do usuário.

---

## 🔥 NÍVEL CRÍTICO - Perda de Dados Permanente

### **Banco de Dados**
```sql
❌ DELETE FROM tenants
❌ DELETE FROM plans
❌ DELETE FROM users
❌ DELETE FROM restaurant_types
❌ DROP TABLE qualquer_tabela
❌ DROP DATABASE
❌ TRUNCATE TABLE qualquer_tabela
❌ DROP SCHEMA tenant_*
```

**Protocolo:**
1. Claude explica exatamente o que será deletado
2. Claude mostra consequências (dados perdidos, relações quebradas)
3. Claude pergunta: "Tem certeza? Digite 'SIM' para confirmar"
4. Usuário digita 'SIM' (maiúsculas)
5. Claude executa

**Alternativas oferecidas:**
- Soft delete (is_active = false)
- Backup antes de deletar
- Arquivar em vez de deletar

---

## 🟠 NÍVEL ALTO - Mudanças Irreversíveis

### **Git**
```bash
❌ git reset --hard
❌ git push --force
❌ git push --force-with-lease
❌ git branch -D nome_branch
❌ git clean -fd
```

**Protocolo:**
1. Claude explica o que vai ser perdido
2. Claude sugere alternativas (git stash, backup)
3. Claude pede confirmação
4. Usuário confirma
5. Claude executa

---

### **Arquivos de Sistema**
```bash
❌ Deletar .env
❌ Deletar composer.json
❌ Deletar package.json
❌ Deletar migrations (database/migrations/*.php)
❌ Deletar config/*.php
❌ rm -rf storage/*
❌ rm -rf vendor/ (sem backup)
```

**Protocolo:**
1. Claude pergunta: "Por que precisa deletar?"
2. Claude oferece alternativa (renomear, mover, backup)
3. Claude pede confirmação
4. Usuário confirma
5. Claude executa

---

## 🟡 NÍVEL MÉDIO - Mudanças Significativas

### **Refatorações Grandes**
```
❌ Renomear tabelas (migrations)
❌ Mudar estrutura de schema (adicionar/remover colunas críticas)
❌ Alterar fluxo de pagamento
❌ Mudar ordem de descontos (cupom/cashback)
❌ Mexer em CashbackService
❌ Mexer em OrderService
❌ Mexer em PagarMeService
```

**Protocolo:**
1. Claude explica mudança
2. Claude mostra código antes/depois
3. Claude lista possíveis quebras
4. Claude pede confirmação
5. Usuário analisa e aprova
6. Claude executa

---

### **Deploy e Produção**
```bash
❌ Rodar migrations em produção
❌ Fazer rollback de migration em produção
❌ Alterar .env de produção
❌ Reiniciar serviços (nginx, php-fpm, postgres)
❌ Aplicar proteções de banco (REVOKE permissions)
```

**Protocolo:**
1. Claude avisa: "Isso afeta PRODUÇÃO!"
2. Claude sugere teste em staging/local primeiro
3. Claude recomenda backup
4. Claude pede confirmação dupla
5. Usuário confirma 2x
6. Claude executa

---

## 🟢 NÍVEL BAIXO - Operações Seguras (SEM Aprovação)

### **Pode fazer livremente:**
```bash
✅ Criar novos arquivos
✅ Editar código (adicionar features)
✅ Rodar testes
✅ git add
✅ git commit
✅ git pull
✅ composer install
✅ npm install
✅ php artisan optimize:clear
✅ Ler arquivos
✅ Buscar no código (grep, find)
✅ Criar documentação
```

**Sem necessidade de aprovação!**

---

## 📋 Template de Confirmação

### **Para Operações Críticas:**

```
⚠️ CONFIRMAÇÃO NECESSÁRIA

Operação: [DESCRIÇÃO]
Comando: [COMANDO EXATO]

Consequências:
- [Lista de dados que serão perdidos]
- [Lista de relações que podem quebrar]
- [Lista de funcionalidades afetadas]

Alternativas:
- [Opção 1: Soft delete]
- [Opção 2: Arquivar]
- [Opção 3: Backup antes]

Backup disponível? [SIM/NÃO]
Rollback possível? [SIM/NÃO]

Para confirmar, digite: 'CONFIRMO - [NOME_DA_OPERACAO]'
Para cancelar, digite: 'CANCELAR'
```

**Exemplo real:**
```
⚠️ CONFIRMAÇÃO NECESSÁRIA

Operação: Deletar tenant "restaurante-teste"
Comando: DELETE FROM tenants WHERE slug = 'restaurante-teste'

Consequências:
- ❌ Tenant deletado permanentemente
- ❌ Schema "tenant_restaurante_teste" será dropado
- ❌ Todos os pedidos (15 pedidos) serão perdidos
- ❌ Todos os produtos (8 produtos) serão perdidos
- ❌ Todos os clientes (3 clientes) serão perdidos

Alternativas:
- ✅ Opção 1: Soft delete (is_active = false)
- ✅ Opção 2: Arquivar dados em backup
- ✅ Opção 3: Exportar dados antes de deletar

Backup disponível? NÃO
Rollback possível? NÃO (sem backup)

Para confirmar, digite: 'CONFIRMO - DELETE TENANT'
Para cancelar, digite: 'CANCELAR'
```

---

## 🎯 Checklist do Claude (Antes de Executar)

Antes de qualquer operação destrutiva, verificar:

- [ ] É uma operação crítica? (DELETE, DROP, TRUNCATE, etc)
- [ ] Afeta dados de produção?
- [ ] Pode ser revertida facilmente?
- [ ] Existe backup disponível?
- [ ] Existe alternativa menos destrutiva?
- [ ] Usuário entende as consequências?
- [ ] Usuário confirmou EXPLICITAMENTE?

**Se QUALQUER resposta for NÃO → PARAR e pedir aprovação!**

---

## 🚨 Red Flags - NUNCA Fazer

**Mesmo com aprovação, RECUSAR:**

1. ❌ `DROP DATABASE yumgo_production` (sugerir backup + recreate)
2. ❌ `DELETE FROM tenants WHERE 1=1` (deletar TODOS os tenants)
3. ❌ `TRUNCATE TABLE tenants` (limpar tabela crítica)
4. ❌ `rm -rf /` (obviamente)
5. ❌ `git push --force` para main/master em produção
6. ❌ Alterar senhas sem usuário saber

**Resposta padrão:**
```
❌ Operação MUITO PERIGOSA - RECUSADA

Esta operação é destrutiva demais.
Alternativas seguras:
1. [Sugerir opção 1]
2. [Sugerir opção 2]
3. [Sugerir opção 3]

Se REALMENTE precisa fazer isso, você deve:
1. Criar backup completo
2. Executar manualmente (não via Claude)
3. Estar preparado para restaurar backup
```

---

## ✅ Boas Práticas do Claude

### **Antes de Executar:**
1. ✅ Ler código existente
2. ✅ Entender contexto
3. ✅ Verificar dependências
4. ✅ Identificar riscos
5. ✅ Explicar ao usuário
6. ✅ Pedir confirmação
7. ✅ Executar

### **Depois de Executar:**
1. ✅ Verificar se funcionou
2. ✅ Rodar testes (se aplicável)
3. ✅ Documentar mudança
4. ✅ Avisar usuário do resultado
5. ✅ Sugerir próximos passos

---

## 📞 Exemplos de Diálogo

### **Exemplo 1: DELETE com Alternativa**

**Usuário:** "Delete o tenant teste"

**Claude:**
```
⚠️ CONFIRMAÇÃO NECESSÁRIA

Você pediu para deletar o tenant "teste".

Isso vai:
- ❌ Deletar permanentemente o schema "tenant_teste"
- ❌ Perder 5 pedidos, 3 produtos, 2 clientes

Alternativas:
✅ Opção 1: Desativar (soft delete)
   → tenant->update(['is_active' => false])
   → Dados preservados, tenant apenas oculto

✅ Opção 2: Arquivar
   → Exportar dados para backup
   → Depois deletar com segurança

Qual prefere?
1 - Soft delete (RECOMENDADO)
2 - Arquivar e deletar
3 - Deletar permanentemente (NÃO PODE DESFAZER)
```

**Usuário:** "1"

**Claude:**
```
✅ Tenant "teste" desativado com sucesso!
   Status: is_active = false
   Dados preservados: ✅

Pode reativar depois com:
Tenant::where('slug', 'teste')->update(['is_active' => true]);
```

---

### **Exemplo 2: Migration Perigosa**

**Usuário:** "Remover coluna cashback_balance da tabela customers"

**Claude:**
```
🚨 OPERAÇÃO PERIGOSA DETECTADA

Migration que remove coluna "cashback_balance":
- ❌ Perda de dados de cashback de TODOS os clientes
- ❌ Sistema de cashback VAI QUEBRAR
- ❌ NÃO É REVERSÍVEL (dados perdidos)

Consequências:
- CashbackService vai dar erro
- API /api/v1/cashback/balance vai quebrar
- Checkout não vai funcionar

Você TEM CERTEZA que quer fazer isso?
Isso NÃO parece correto baseado na arquitetura do sistema.

Digite 'ENTENDO OS RISCOS' se tiver certeza.
Ou me explique o que você quer realmente fazer.
```

**Usuário:** "Quero migrar cashback para outra tabela"

**Claude:**
```
✅ Ah, entendi! Quer MIGRAR dados, não deletar!

Plano correto:
1. Criar nova tabela cashback_balances
2. Copiar dados customers.cashback_balance → cashback_balances
3. Atualizar código para usar nova tabela
4. Rodar testes
5. SÓ DEPOIS remover coluna antiga

Posso fazer isso? (muito mais seguro!)
```

---

## 🎯 Resumo

**Regra de Ouro:**
> "Se deletar, sempre perguntar. Se for crítico, sempre confirmar."

**Hierarquia de Decisão:**
1. 🟢 Seguro → Executa direto
2. 🟡 Médio risco → Explica e pede confirmação
3. 🟠 Alto risco → Explica, oferece alternativas, pede confirmação dupla
4. 🔥 Crítico → Explica, oferece alternativas, backup obrigatório, confirmação explícita
5. 🚨 Muito perigoso → RECUSA e sugere método manual

---

**Criado por:** Claude Sonnet 4.5
**Data:** 12/03/2026
**Status:** ✅ Regra Ativa
