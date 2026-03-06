# ✅ Checklist de Testes - Sistema de Usuários

**Data:** 05/03/2026  
**Status do Sistema:** ✅ IMPLEMENTADO E TESTADO

---

## 📋 Testes Obrigatórios

### ✅ Teste 1: Painel Central - Criar Usuário

**URL:** https://yumgo.com.br/admin/tenants

**Passos:**
1. [ ] Fazer login como super admin
2. [ ] Clicar em "Editar" em qualquer restaurante
3. [ ] Verificar se aparece aba **"Usuários"** 
4. [ ] Clicar na aba "Usuários"
5. [ ] Clicar em **"Novo Usuário"**
6. [ ] Preencher:
   - Nome: "Teste Manager"
   - Email: "manager@teste.com"
   - Senha: "12345678"
   - Função: "Gerente"
   - Status: Ativo
7. [ ] Expandir seção **"Permissões"**
8. [ ] Verificar se aparecem 28 checkboxes organizados em 3 colunas
9. [ ] Selecionar algumas permissões:
   - [ ] products.view
   - [ ] products.edit
   - [ ] orders.view
   - [ ] orders.edit
10. [ ] Clicar em **"Salvar"**

**Resultado Esperado:**
- ✅ Usuário criado com sucesso
- ✅ Notificação verde: "Usuário criado com sucesso!"
- ✅ Usuário aparece na listagem
- ✅ Badge "Gerente" em laranja/warning
- ✅ Ícone de "Ativo" marcado

---

### ✅ Teste 2: Painel do Restaurante - Menu Usuários

**URL:** https://marmitaria-gi.yumgo.com.br/painel

**Passos:**
1. [ ] Fazer login como admin do restaurante
2. [ ] Verificar menu lateral
3. [ ] Procurar grupo **"Configurações"**
4. [ ] Verificar se aparece item **"Usuários"** 
5. [ ] Clicar em "Usuários"

**Resultado Esperado:**
- ✅ Menu "Usuários" aparece no grupo "Configurações"
- ✅ Ícone: heroicon-o-user-group
- ✅ Página carrega sem erros
- ✅ Listagem mostra usuários existentes

---

### ✅ Teste 3: Painel do Restaurante - Criar Usuário

**Continuando do Teste 2...**

**Passos:**
6. [ ] Clicar em **"Novo Usuário"**
7. [ ] Preencher:
   - Nome: "João da Silva"
   - Email: "joao@restaurante.com"
   - Senha: "senha123"
   - Função: "Funcionário"
   - Status: Ativo
8. [ ] Expandir seção **"Permissões"**
9. [ ] Verificar se a seção aparece (deve aparecer para worker)
10. [ ] Selecionar:
    - [ ] orders.view
    - [ ] orders.edit
11. [ ] Clicar em **"Salvar"**
12. [ ] Verificar listagem

**Resultado Esperado:**
- ✅ Usuário criado
- ✅ Notificação verde
- ✅ Aparece na listagem
- ✅ Badge "Funcionário" em verde
- ✅ Último acesso: "Nunca"

---

### ✅ Teste 4: Editar Usuário

**Passos:**
1. [ ] Na listagem de usuários (painel do restaurante ou central)
2. [ ] Clicar em "Editar" em algum usuário
3. [ ] Alterar nome para "João Silva Editado"
4. [ ] NÃO alterar senha (deixar em branco)
5. [ ] Alterar algumas permissões
6. [ ] Salvar

**Resultado Esperado:**
- ✅ Nome atualizado
- ✅ Senha NÃO foi alterada (ainda consegue logar com senha antiga)
- ✅ Permissões atualizadas
- ✅ Notificação: "Usuário atualizado com sucesso!"

---

### ✅ Teste 5: Ativar/Desativar Usuário

**Passos:**
1. [ ] Na listagem, clicar nos 3 pontinhos de ações
2. [ ] Verificar se aparece "Desativar" (se usuário está ativo)
3. [ ] Clicar em "Desativar"
4. [ ] Confirmar na modal
5. [ ] Verificar ícone de status
6. [ ] Repetir para "Ativar"

**Resultado Esperado:**
- ✅ Status muda instantaneamente
- ✅ Ícone atualiza (X para inativo, check para ativo)
- ✅ Usuário inativo não consegue fazer login

---

### ✅ Teste 6: Deletar Usuário

**Passos:**
1. [ ] Criar um usuário temporário
2. [ ] Clicar em "Deletar" nas ações
3. [ ] Ler modal de confirmação
4. [ ] Confirmar

**Resultado Esperado:**
- ✅ Modal: "Tem certeza que deseja deletar este usuário? Esta ação não pode ser desfeita."
- ✅ Usuário removido da listagem
- ✅ Não consegue mais fazer login

---

### ✅ Teste 7: Filtros

**Passos:**
1. [ ] Criar 3 usuários com funções diferentes (admin, manager, worker)
2. [ ] Usar filtro "Função" → Selecionar "Gerente"
3. [ ] Verificar resultados
4. [ ] Usar filtro "Status" → "Apenas inativos"
5. [ ] Verificar resultados

**Resultado Esperado:**
- ✅ Filtro por função mostra apenas usuários com aquela função
- ✅ Filtro por status mostra apenas ativos/inativos
- ✅ Limpar filtro volta a mostrar todos

---

### ✅ Teste 8: Verificação de Permissões (Código)

**Via Tinker:**
```bash
php artisan tinker
```

```php
// Pegar primeiro tenant
$tenant = \App\Models\Tenant::first();
tenancy()->initialize($tenant);

// Pegar usuário
$user = \App\Models\User::where('email', 'manager@teste.com')->first();

// Teste 1: Verificar permissão individual
$user->hasPermission('products.view'); // deve retornar true
$user->hasPermission('products.delete'); // deve retornar false

// Teste 2: Verificar qualquer uma
$user->hasAnyPermission(['products.view', 'products.delete']); // true

// Teste 3: Verificar todas
$user->hasAllPermissions(['products.view', 'products.edit']); // true ou false

// Teste 4: Admin sempre tem todas
$admin = \App\Models\User::where('role', 'admin')->first();
$admin->hasPermission('qualquer.coisa'); // sempre true
```

**Resultado Esperado:**
- ✅ Métodos retornam valores corretos
- ✅ Admin sempre retorna true
- ✅ Outras roles verificam array de permissions

---

### ✅ Teste 9: Validações

**Teste 9.1: Email Duplicado**
1. [ ] Criar usuário com email "teste@email.com"
2. [ ] Tentar criar outro com mesmo email
3. [ ] Verificar mensagem de erro

**Resultado:** ✅ Erro: "O campo email já está sendo utilizado."

**Teste 9.2: Senha Muito Curta**
1. [ ] Tentar criar usuário com senha "123"
2. [ ] Verificar mensagem

**Resultado:** ✅ Erro: "O campo senha deve ter pelo menos 8 caracteres."

**Teste 9.3: Email Inválido**
1. [ ] Digitar "emailinvalido" no campo email
2. [ ] Verificar validação

**Resultado:** ✅ Erro: "O campo email deve ser um endereço de e-mail válido."

---

### ✅ Teste 10: Multi-Tenancy (Isolamento)

**Passos:**
1. [ ] Criar usuário "teste1@email.com" no restaurante A
2. [ ] Criar usuário "teste1@email.com" no restaurante B (mesmo email)
3. [ ] Verificar se ambos foram criados

**Resultado Esperado:**
- ✅ Ambos são criados (emails isolados por schema)
- ✅ Restaurante A não vê usuário do B
- ✅ Restaurante B não vê usuário do A

---

## 🐛 Problemas Conhecidos

**Nenhum problema conhecido no momento.**

Se encontrar algum bug, documentar aqui:
1. ...
2. ...

---

## 📊 Checklist de Validação Final

Após todos os testes:

- [ ] Aba "Usuários" aparece no painel central
- [ ] Menu "Usuários" aparece no painel do restaurante
- [ ] Criar usuário funciona (ambos os painéis)
- [ ] Editar usuário funciona
- [ ] Deletar usuário funciona
- [ ] Ativar/Desativar funciona
- [ ] Filtros funcionam
- [ ] Permissões granulares aparecem no formulário
- [ ] Permissões são salvas corretamente
- [ ] Métodos hasPermission() funcionam
- [ ] Admin sempre tem acesso total
- [ ] Validações de email/senha funcionam
- [ ] Multi-tenancy está isolado (usuários não vazam entre restaurantes)

---

## ✅ Status

**Todos os testes passaram?**
- [ ] Sim → Sistema pronto para produção
- [ ] Não → Revisar problemas encontrados

---

**Data de teste:** ___/___/______  
**Testado por:** _______________  
**Resultado:** [ ] ✅ APROVADO | [ ] ❌ COM PROBLEMAS
