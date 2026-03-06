# 🚀 Guia Rápido - Sistema de Usuários

**Sistema implementado em:** 05/03/2026

---

## 📖 O Que É?

Sistema completo para gerenciar usuários dos restaurantes com controle de permissões granulares.

**Onde acessar:**
- **Painel Central:** https://yumgo.com.br/admin/tenants/{id}/edit → Aba "Usuários"
- **Painel Restaurante:** https://{slug}.yumgo.com.br/painel/users

---

## 👥 Funções Disponíveis

| Função | Quando Usar | Acesso |
|--------|-------------|--------|
| **Administrador** | Dono/Gerente Geral | Total (todas permissões) |
| **Gerente** | Gerente de Loja | Customizável |
| **Funcionário** | Atendente/Cozinha | Limitado (pedidos/produtos) |
| **Financeiro** | Contador | Apenas relatórios |
| **Entregador** | Motoboy | App mobile (futuro) |

---

## 🔐 Permissões Disponíveis

**Produtos:** ver, criar, editar, deletar  
**Pedidos:** ver, editar, cancelar  
**Cupons:** ver, criar, editar, deletar  
**Clientes:** ver, editar  
**Configurações:** ver, editar  
**Relatórios:** ver, exportar  
**Usuários:** ver, criar, editar, deletar  

---

## 🎯 Casos de Uso Comuns

### Caso 1: Funcionário de Balcão

**Função:** Funcionário  
**Permissões:**
- ✅ orders.view
- ✅ orders.edit
- ✅ products.view
- ✅ customers.view

**Pode fazer:**
- Ver e editar pedidos
- Visualizar cardápio
- Consultar clientes

**Não pode:**
- Criar/deletar produtos
- Ver relatórios financeiros
- Alterar configurações

---

### Caso 2: Gerente da Loja

**Função:** Gerente  
**Permissões:**
- ✅ products.* (todas)
- ✅ orders.* (todas)
- ✅ coupons.* (todas)
- ✅ customers.*
- ✅ reports.view
- ✅ reports.export

**Pode fazer:**
- Gerenciar produtos completo
- Gerenciar pedidos
- Criar cupons promocionais
- Ver relatórios

**Não pode:**
- Criar outros usuários
- Alterar configurações críticas

---

### Caso 3: Contador/Financeiro

**Função:** Financeiro  
**Permissões:**
- ✅ reports.view
- ✅ reports.export
- ✅ orders.view

**Pode fazer:**
- Visualizar todos os pedidos
- Gerar relatórios financeiros
- Exportar dados

**Não pode:**
- Editar produtos
- Cancelar pedidos
- Criar cupons

---

## 📝 Como Criar um Usuário

### Via Painel Central

```
1. Acesse: https://yumgo.com.br/admin/tenants
2. Clique em "Editar" no restaurante desejado
3. Aba "Usuários" → "Novo Usuário"
4. Preencha:
   - Nome: "João Silva"
   - Email: "joao@restaurante.com"
   - Senha: "senha123" (mínimo 8 caracteres)
   - Função: Selecione conforme caso de uso
   - Status: Ativo
5. Se for Gerente/Funcionário/Financeiro:
   - Expanda "Permissões"
   - Selecione as permissões necessárias
6. Salvar
```

### Via Painel do Restaurante

```
1. Acesse: https://{seu-slug}.yumgo.com.br/painel
2. Menu "Configurações" → "Usuários"
3. "Novo Usuário"
4. Preencha formulário (mesmo processo acima)
5. Salvar
```

---

## 🔧 Como Editar Permissões

```
1. Listagem de usuários
2. Clique em "Editar" no usuário desejado
3. Role até "Permissões"
4. Marque/Desmarque conforme necessário
5. Salvar
```

**Dica:** Deixe campo senha em branco para manter a senha atual.

---

## 🚫 Como Desativar um Usuário

**Opção 1: Ação Rápida**
```
1. Listagem de usuários
2. Clique nos 3 pontinhos (...)
3. "Desativar"
4. Confirmar
```

**Opção 2: No Formulário**
```
1. Editar usuário
2. Desmarcar toggle "Ativo"
3. Salvar
```

**Resultado:** Usuário não consegue mais fazer login.

---

## 🗑️ Como Deletar um Usuário

```
1. Listagem de usuários
2. Clique nos 3 pontinhos (...)
3. "Deletar"
4. Ler aviso: "Esta ação não pode ser desfeita"
5. Confirmar
```

**Atenção:** Ação permanente! Usuário será removido do banco.

---

## 🔍 Como Filtrar Usuários

**Filtro por Função:**
```
1. Listagem de usuários
2. Clique no ícone de filtro
3. "Função" → Selecione (Admin, Gerente, etc)
4. Aplicar
```

**Filtro por Status:**
```
1. Ícone de filtro
2. "Status" → Apenas ativos / Apenas inativos
3. Aplicar
```

**Limpar Filtros:** Clique no X ao lado do filtro aplicado.

---

## ⚠️ Observações Importantes

### 1. Administradores

**Admins têm TODAS as permissões automaticamente.**

Não precisa selecionar checkboxes de permissões para admins.

### 2. Email Único por Restaurante

Cada restaurante tem seus próprios usuários isolados.

**Possível:**
- Restaurante A: joao@email.com
- Restaurante B: joao@email.com (mesmo email)

**Impossível:**
- Restaurante A: joao@email.com
- Restaurante A: joao@email.com (duplicado no mesmo restaurante)

### 3. Senha Segura

**Requisitos:**
- Mínimo 8 caracteres
- Armazenada com hash bcrypt
- Nunca é exibida em texto plano

### 4. Último Acesso

Sistema registra automaticamente quando usuário faz login.

**Visível na coluna:** "Último acesso" (pode estar oculta, clicar em "Colunas" para exibir)

---

## 💡 Dicas

### Dica 1: Perfis Padrão

Crie usuários com permissões padrão e duplique:

```
1. Crie "Modelo - Funcionário Balcão"
2. Configure permissões ideais
3. Ao criar novo funcionário:
   - Copie as mesmas permissões
   - Ou use como referência
```

### Dica 2: Senha Provisória

Quando criar usuário para outra pessoa:

```
1. Use senha temporária: "temp1234"
2. Informe ao usuário
3. Peça para ele trocar no primeiro login
```

### Dica 3: Testar Permissões

Crie um usuário de teste:

```
1. Email: teste@teste.com
2. Senha: test1234
3. Permissões limitadas
4. Faça login e teste o que ele consegue acessar
5. Delete após testar
```

---

## 🆘 Problemas Comuns

### Problema 1: "Email já está sendo utilizado"

**Solução:** Já existe usuário com este email neste restaurante.

- Verificar listagem
- Ou usar email diferente

### Problema 2: "Senha deve ter pelo menos 8 caracteres"

**Solução:** Usar senha mais longa.

### Problema 3: Usuário criado mas não aparece na lista

**Solução:** Limpar filtros da listagem.

### Problema 4: Aba "Usuários" não aparece no painel central

**Solução:**
```bash
php artisan filament:clear-cached-components
php artisan optimize:clear
```

---

## 📞 Suporte

**Documentação completa:** `/var/www/restaurante/docs/USER-MANAGEMENT-SYSTEM.md`

**Checklist de testes:** `/var/www/restaurante/CHECKLIST-TESTES-USUARIOS.md`

**Resumo implementação:** `/var/www/restaurante/TRABALHO-REALIZADO-05-03-2026.md`

---

**Última atualização:** 05/03/2026  
**Versão:** 1.0
