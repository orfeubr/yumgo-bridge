# 🤖 Instruções para Claude Code - DeliveryPro

## ❌ REGRAS INVIOLÁVEIS - NUNCA QUEBRAR

### 0. SEMPRE PEDIR AUTORIZAÇÃO ANTES DE DELETAR ⭐⭐⭐

**OPERAÇÕES QUE EXIGEM APROVAÇÃO MANUAL:**

❌ **NUNCA fazer sem perguntar primeiro:**
- `DELETE FROM tenants`
- `DROP TABLE`
- `TRUNCATE TABLE`
- `DELETE FROM` em tabelas críticas (tenants, plans, users, restaurant_types)
- Deletar arquivos de migration
- Deletar arquivos de configuração (.env, config/*.php)
- Deletar schemas PostgreSQL
- `git reset --hard` (perda de dados)
- `git push --force`

✅ **SEMPRE:**
1. Identificar operação destrutiva
2. Explicar o que vai acontecer
3. Mostrar exatamente o que será deletado
4. Perguntar: "Posso prosseguir?"
5. Esperar aprovação explícita do usuário
6. Só então executar

**Exemplo:**
```
Você: "Preciso deletar o tenant 'teste'?"

Eu: "⚠️ CONFIRMAÇÃO NECESSÁRIA
     Vou executar: DELETE FROM tenants WHERE slug = 'teste'
     Isso vai:
     - Deletar o tenant permanentemente
     - Remover schema tenant_teste
     - Perder TODOS os dados (pedidos, produtos, etc)

     Tem certeza? (Digite 'SIM' para confirmar)"

Você: "SIM"

Eu: [executa]
```

**Se você NÃO aprovar:**
- ❌ Eu cancelo a operação
- ✅ Eu busco alternativa (soft delete, backup, etc)

---

### 1. Multi-Tenant com Cashback Isolado

**DECISÃO DE NEGÓCIO (01/03/2026):**
- ✅ Cada restaurante tem seu próprio cashback
- ✅ Cliente não pode usar cashback entre restaurantes
- ✅ Schemas PostgreSQL completamente isolados

**❌ PROIBIDO:**
- Unificar cashback em schema CENTRAL
- JOIN entre schemas (PUBLIC ↔ TENANT_*)
- Compartilhar saldo de cashback entre tenants

**✅ SEMPRE:**
- Manter `cashback_balance` no schema TENANT
- Buscar customer do tenant: `Customer::where('email', $centralCustomer->email)->first()`
- Validar que customer vem do schema correto

**Por quê:**
- Evita subsídio cruzado (Pizzaria não paga cashback da Marmitaria)
- Incentiva fidelidade ao mesmo restaurante
- Contabilidade separada por restaurante
- Isolamento de dados (segurança)

---

### 2. Ordem de Aplicação de Descontos

**FÓRMULA CORRETA:**
```
1. Subtotal + Taxa de Entrega
2. - Cupom de Desconto (PRIMEIRO)
3. - Cashback (DEPOIS)
= Total Final
```

**❌ NUNCA:**
- Inverter ordem (cashback antes de cupom)
- Permitir total negativo
- Aplicar cupom sobre valor já com desconto de cashback

**✅ SEMPRE:**
- Cupom aplica em: `subtotal + deliveryFee`
- Cashback aplica em: `(subtotal + deliveryFee) - cupom`
- Limitar cashback ao total: `min(cashbackBalance, totalAfterCoupon)`

---

### 3. Customer: Central vs Tenant

**ERRO COMUM:**
```php
❌ $customer = $request->user(); // Schema CENTRAL!
❌ $cashback = $customer->cashback_balance; // ERRADO - pega do central
```

**CORRETO:**
```php
✅ $centralCustomer = $request->user();
✅ $customer = Customer::where('email', $centralCustomer->email)
                      ->orWhere('phone', $centralCustomer->phone)
                      ->first();
✅ $cashback = $customer->cashback_balance; // CORRETO - pega do tenant
```

**❌ NUNCA:**
- Usar `$request->user()->cashback_balance` diretamente
- Assumir que IDs são iguais entre central e tenant
- Esquecer de sincronizar customer (OrderService já faz isso)

**✅ SEMPRE:**
- Controllers de API: Buscar customer do tenant primeiro
- Validações de ownership: Comparar IDs do tenant, não do central
- Email fallback: `cliente-{id}@{tenant-slug}.yumgo.com.br`

---

### 4. Schema Isolation (Multi-Tenant)

**ARQUITETURA:**
- `PUBLIC` → Dados da plataforma (tenants, plans, subscriptions)
- `TENANT_*` → Dados do restaurante (customers, orders, cashback)

**❌ PROIBIDO:**
- `SELECT * FROM public.customers JOIN tenant_marmitaria.orders` ❌
- Foreign keys entre schemas
- Queries cruzadas
- Compartilhar conexões entre tenants

**✅ SEMPRE:**
- Usar `tenancy()->initialize($tenant)` antes de queries tenant
- Sincronizar dados manualmente entre schemas
- Manter isolamento total de dados

---

### 5. Gateway de Pagamento (Pagar.me/Asaas)

**❌ NUNCA:**
- Enviar valor ANTES de descontos (subtotal sem cashback/cupom)
- Usar email do customer central
- Criar cobrança antes de validar cupom/cashback

**✅ SEMPRE:**
- Enviar `total` DEPOIS de todos os descontos aplicados
- Email fallback se customer não tiver email
- Validar cupom e cashback ANTES de criar cobrança no gateway

**Fluxo correto:**
```
1. Calcula subtotal
2. Aplica cupom
3. Aplica cashback
4. Total final
5. Cria cobrança no gateway com TOTAL FINAL ← IMPORTANTE
```

---

### 6. Migrations e Database

**❌ PROIBIDO:**
- Rodar migrations tenant na central: `php artisan migrate`
- Rodar migrations central nos tenants: `php artisan tenants:migrate --path=central`
- Criar migrations que afetam ambos schemas

**✅ SEMPRE:**
- Migrations centrais: `database/migrations/` → `php artisan migrate`
- Migrations tenant: `database/migrations/tenant/` → `php artisan tenants:migrate`
- Nunca misturar os dois

---

### 7. Git e Commits

**❌ NUNCA (sem aprovação manual):**
- `git push` para branch main/master
- `git push --force`
- `git reset --hard` (perda de dados)
- Deletar branches remotas

**✅ SEMPRE pode (com autonomia):**
- `git add`
- `git commit`
- `git checkout` (mudar branch)
- `git pull`

**Mensagens de commit:**
- Usar conventional commits: `feat:`, `fix:`, `docs:`
- Co-authored: `Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>`

---

## ⚠️ SEMPRE VALIDAR ANTES DE MUDAR

### Checklist de Segurança

Antes de fazer qualquer mudança em:
- [ ] CashbackService
- [ ] OrderService
- [ ] CashbackController
- [ ] OrderController
- [ ] PagarMeService

**Perguntas obrigatórias:**
1. ✅ Cashback continua isolado por tenant?
2. ✅ Customer vem do schema TENANT (não central)?
3. ✅ Ordem de descontos está correta (cupom → cashback)?
4. ✅ Total nunca fica negativo?
5. ✅ Gateway recebe total DEPOIS de descontos?

**Se resposta for NÃO para qualquer uma: PARE e revise!**

---

## 🧪 Testes Obrigatórios

**Antes de commitar mudanças em cashback/orders:**

```bash
# Roda testes específicos
php artisan test --filter=Cashback
php artisan test --filter=Order
php artisan test --filter=Coupon

# Ou suite completa
php artisan test
```

**Se QUALQUER teste falhar:**
- ❌ NÃO commitar
- ✅ Reverter mudanças
- ✅ Analisar por que falhou
- ✅ Corrigir e testar novamente

---

## 🔄 Auto-Rollback

**Se eu commitar algo que quebra testes:**
- Hook automático reverte o commit
- Eu analiso o problema
- Eu corrijo e tento novamente
- Você é notificado do rollback

---

## 💡 Princípios de Desenvolvimento

### 1. Simplicidade
- Não adicionar features não pedidas
- Não "melhorar" algo que funciona
- Não refatorar sem motivo
- Código simples > Código "esperto"

### 2. Testes First
- Sempre rodar testes antes de commitar
- Adicionar testes para bugs corrigidos
- Manter cobertura de testes alta

### 3. Documentação
- Atualizar MEMORY.md com decisões importantes
- Comentar código não-óbvio
- Documentar breaking changes

### 4. Segurança
- Nunca expor dados entre tenants
- Validar SEMPRE inputs do usuário
- SQL injection protection
- XSS protection

---

## 📝 Decisões Arquiteturais Importantes

### Por que PostgreSQL Schemas? (25/02/2026)
- ✅ Isolamento total de dados
- ✅ Performance superior (mesma conexão)
- ✅ Backup único
- ✅ Migrations centralizadas
- ❌ Não pode usar Foreign Keys entre schemas (aceitamos essa limitação)

### Por que Cashback Isolado? (01/03/2026)
- ✅ Incentiva fidelidade ao mesmo restaurante
- ✅ Contabilidade separada (cada um paga seu cashback)
- ✅ Evita subsídio cruzado
- ✅ Cada restaurante define suas próprias regras
- ❌ Cliente não usa cashback entre restaurantes (aceitamos)

### Por que Asaas/Pagar.me? (20/02/2026)
- ✅ PIX barato (R$ 0,99 vs R$ 2,50 do Mercado Pago)
- ✅ Split automático (restaurante + plataforma)
- ✅ Sub-contas isoladas por tenant
- ✅ Economia de ~R$ 1.500/mês em 1000 pedidos

### Por que Email Fallback? (28/02/2026)
- ✅ Clientes podem fazer login só com WhatsApp (sem email)
- ✅ Gateway exige email
- ✅ Solução: `cliente-{id}@{tenant-slug}.yumgo.com.br`
- ✅ LGPD compliance (não expõe dados pessoais)

---

## 🚨 Red Flags - Me Alerte Se Ver

Se eu tentar fazer QUALQUER uma dessas coisas, **PARE** e me questione:

1. ❌ Unificar cashback entre restaurantes
2. ❌ JOIN entre schemas PUBLIC e TENANT
3. ❌ Usar `$request->user()` para cashback
4. ❌ Mudar ordem de descontos (cupom/cashback)
5. ❌ Remover validações de isolamento multi-tenant
6. ❌ Push direto para main/master
7. ❌ Migrations que afetam ambos schemas
8. ❌ Compartilhar dados sensíveis entre tenants

**Se eu sugerir qualquer coisa acima:**
→ Me pergunte: "Tem certeza? Isso não quebra [REGRA X]?"
→ Eu devo justificar POR QUE é necessário
→ Você aprova manualmente

---

## 📚 Arquivos de Referência

- `/docs/ARQUITETURA-MULTI-TENANT.md` - Arquitetura completa
- `/MEMORY.md` - Decisões e padrões do projeto
- `/EMAIL-FALLBACK-28-02-2026.md` - Email fallback
- `/CASHBACK-AUTOMATICO-28-02-2026.md` - Toggle de cashback

---

## 🎯 Modo de Operação com Autonomia

**Nível 3 Ativado:**
- ✅ Eu posso editar código sem aprovação
- ✅ Eu posso fazer commits automaticamente
- ✅ Eu posso rodar comandos (artisan, composer, etc)
- ❌ Eu NÃO posso fazer push sem aprovação
- ❌ Eu NÃO posso quebrar regras invioláveis

**Proteções Ativas:**
- Pre-commit hooks validam regras
- Testes automáticos antes de commit
- Auto-rollback se testes falharem
- CLAUDE.md sempre consultado

**Se eu tentar algo que viola as regras:**
→ Hook bloqueia
→ Eu analiso alternativa
→ Eu corrijo sem violar regras

---

**Última atualização:** 01/03/2026
**Versão:** 2.0 (com autonomia + proteções)
