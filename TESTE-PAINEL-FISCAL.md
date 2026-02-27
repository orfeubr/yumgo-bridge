# 🧪 Teste do Painel Fiscal - Tributa AI

## ✅ Status: PRONTO PARA TESTE

---

## 📍 URLs de Acesso

### Painel da Marmitaria da Gi

**Dashboard:**
- https://marmitaria-gi.yumgo.com.br/painel

**Configuração Fiscal:**
- https://marmitaria-gi.yumgo.com.br/painel/fiscal-settings

**Notas Fiscais:**
- https://marmitaria-gi.yumgo.com.br/painel/fiscal-notes

---

## 🧪 Testes Realizados

### ✅ 1. Rotas Registradas
```bash
✅ GET /painel/fiscal-settings
✅ GET /painel/fiscal-notes
✅ GET /painel/fiscal-notes/{record}
✅ POST /api/v1/webhooks/tributaai
```

### ✅ 2. Páginas Descobertas pelo Filament
```
✅ FiscalSettings - Página de configuração
✅ FiscalNoteResource - CRUD de notas fiscais
```

### ✅ 3. Migrations Aplicadas
```
✅ Tabela tenants - 24 campos novos
✅ Tabela fiscal_notes (tenant) - Criada em todos os tenants
```

### ✅ 4. Campos do Tenant (Valores Padrão)
```
- tributaai_enabled: NÃO ❌ (precisa habilitar)
- tributaai_environment: sandbox ✅
- regime_tributario: simples_nacional ✅
- nfce_serie: 1 ✅
- nfce_numero: 1 ✅
- Demais campos: Não configurados (ok para teste)
```

### ✅ 5. Sintaxe Blade
```
✅ Sem erros de sintaxe
```

### ✅ 6. Cache Limpo
```
✅ Config, routes, views, filament
```

---

## 🎯 Como Testar

### Teste 1: Acessar Configuração Fiscal

1. **Login no painel:**
   - URL: https://marmitaria-gi.yumgo.com.br/painel
   - Usar credenciais de admin da Marmitaria

2. **Menu lateral:**
   - Procurar por grupo "Configurações"
   - Clicar em "Configuração Fiscal"
   - Ícone: 📄 (document-check)

3. **O que você deve ver:**
   ```
   Título: "Configuração Fiscal - Tributa AI"

   Seções:
   - 📋 Tributa AI - Integração
     - Toggle: Habilitar Tributa AI
     - Token API (campo password)
     - Ambiente (Sandbox/Produção)

   - 📋 Dados da Empresa
     - CNPJ (com máscara)
     - Razão Social
     - Inscrição Estadual
     - Inscrição Municipal
     - Regime Tributário (select)

   - 📋 Configuração NFC-e
     - Série NFC-e
     - Número Atual
     - CSC ID
     - CSC Token

   - 📋 Endereço Fiscal
     - CEP, Logradouro, Número
     - Complemento, Bairro
     - Cidade, Estado

   Botão: "Salvar Configurações"

   Card Azul: "Como Configurar o Tributa AI"
   - Passo a passo
   - Links para tributa.ai
   - Avisos importantes
   ```

4. **Preencher campos de teste:**
   ```
   ✅ Habilitar Tributa AI: ON
   ✅ Token API: test_token_123456 (qualquer valor para teste)
   ✅ Ambiente: Sandbox
   ✅ CNPJ: 99.999.999/0001-99
   ✅ Razão Social: Marmitaria da Gi LTDA
   ✅ IE: 123456789
   ✅ Regime: Simples Nacional
   ✅ CSC ID: 1
   ✅ CSC Token: test_csc_token
   ✅ CEP: 12345-678
   ✅ Logradouro: Rua Teste
   ✅ Número: 123
   ✅ Bairro: Centro
   ✅ Cidade: São Paulo
   ✅ Estado: São Paulo
   ```

5. **Clicar em "Salvar Configurações"**
   - Deve mostrar notificação: "Configurações salvas com sucesso!"

6. **Recarregar a página**
   - Verificar se os dados foram salvos

---

### Teste 2: Acessar Notas Fiscais

1. **Menu lateral:**
   - Clicar em "Notas Fiscais"
   - Ícone: 📄 (document-text)

2. **O que você deve ver:**
   ```
   Título: "Notas Fiscais"

   Tabela com colunas:
   - Número
   - Pedido
   - Status (badge colorido)
   - Valor
   - Emissão
   - Chave (oculta por padrão)

   Filtros:
   - Status (select)

   Mensagem: "Nenhum registro encontrado"
   (porque ainda não há notas emitidas)
   ```

3. **Ações disponíveis:**
   - Não deve ter botão "Criar" (notas só são criadas automaticamente)

---

### Teste 3: Verificar Dados Salvos no Banco

```bash
php artisan tinker --execute="
\$tenant = App\Models\Tenant::where('id', '144c5973-f985-4309-8f9a-c404dd11feae')->first();
echo 'Dados salvos:' . PHP_EOL;
echo '- tributaai_enabled: ' . (\$tenant->tributaai_enabled ? 'SIM' : 'NÃO') . PHP_EOL;
echo '- cnpj: ' . \$tenant->cnpj . PHP_EOL;
echo '- razao_social: ' . \$tenant->razao_social . PHP_EOL;
"
```

---

### Teste 4: Simular Emissão de Nota (Teste Completo)

**Este teste simula o fluxo completo de emissão automática:**

1. **Configurar Tributa AI** (Teste 1)

2. **Criar pedido de teste:**
   - Via POS: https://marmitaria-gi.yumgo.com.br/painel/p-o-s
   - Adicionar produtos
   - Selecionar cliente
   - Método de pagamento: PIX
   - Finalizar pedido

3. **Simular confirmação de pagamento:**
   ```bash
   # Via tinker (para teste)
   php artisan tinker

   # Encontrar o pedido
   $order = App\Models\Order::latest()->first();

   # Simular pagamento confirmado
   $order->update(['payment_status' => 'paid']);

   # Verificar se nota foi criada
   $fiscalNote = App\Models\FiscalNote::where('order_id', $order->id)->first();

   if ($fiscalNote) {
       echo "✅ Nota fiscal criada!" . PHP_EOL;
       echo "Status: " . $fiscalNote->status . PHP_EOL;
       echo "Número: " . $fiscalNote->note_number . PHP_EOL;
   }
   ```

4. **Ver nota em /painel/fiscal-notes**

**IMPORTANTE:** Como o token é de teste, a nota ficará com status "error" porque a API do Tributa AI vai rejeitar. Mas isso comprova que o Observer está funcionando!

---

## ⚠️ Possíveis Erros e Soluções

### Erro 1: Página não aparece no menu

**Solução:**
```bash
php artisan optimize:clear
php artisan filament:cache-components
```

### Erro 2: "Page not found"

**Verificar:**
- Route está registrada: `php artisan route:list --path=fiscal`
- Cache de rotas: `php artisan route:clear`

### Erro 3: Form não salva

**Verificar:**
- Logs: `tail -f storage/logs/laravel.log`
- Tenant context: `tenancy()->initialized` deve ser `true`

### Erro 4: Campos não aparecem preenchidos

**Verificar:**
- Migration foi executada: `php artisan migrate:status`
- Campos existem na tabela: `describe tenants` (via SQL)

---

## 📊 Verificações de Banco

### Verificar campos na tabela tenants:

```sql
-- Via psql ou pgAdmin
SELECT column_name, data_type, is_nullable
FROM information_schema.columns
WHERE table_name = 'tenants'
  AND column_name LIKE 'tributa%'
ORDER BY ordinal_position;
```

Deve retornar:
```
tributaai_token
tributaai_enabled
tributaai_environment
```

### Verificar tabela fiscal_notes no schema do tenant:

```sql
-- Trocar para schema do tenant
SET search_path TO 'tenant144c5973-f985-4309-8f9a-c404dd11feae';

-- Verificar tabela
\d fiscal_notes
```

Deve mostrar estrutura completa da tabela.

---

## 🎯 Checklist de Teste

### Interface
- [ ] Página aparece no menu "Configurações"
- [ ] Ícone correto (document-check)
- [ ] Título correto "Configuração Fiscal - Tributa AI"
- [ ] Todas as seções aparecem
- [ ] Card azul informativo aparece
- [ ] Botão "Salvar Configurações" funciona

### Funcionalidade
- [ ] Form carrega valores padrão
- [ ] Form valida campos obrigatórios (apenas se toggle habilitado)
- [ ] Form salva dados no banco
- [ ] Form recarrega dados salvos
- [ ] Notificação de sucesso aparece
- [ ] Máscaras funcionam (CNPJ, CEP)

### Notas Fiscais
- [ ] Página de listagem aparece
- [ ] Tabela vazia mostra "Nenhum registro"
- [ ] Não há botão "Criar" (correto)
- [ ] Filtro de status funciona

### Observer (Teste Avançado)
- [ ] Criar pedido
- [ ] Mudar payment_status para 'paid'
- [ ] Nota fiscal é criada automaticamente
- [ ] Log mostra tentativa de emissão

---

## 📸 Screenshots Esperados

### 1. Menu Lateral
```
┌─────────────────────────┐
│ Dashboard               │
│ PDV - Frente de Caixa   │
│ ...                     │
│                         │
│ ▼ Configurações         │
│   - Configurações       │
│   - Conta de Pagamento  │
│   - Configuração Fiscal │ ← AQUI
│   - Gerenciar Estoque   │
└─────────────────────────┘
```

### 2. Página de Configuração
```
┌──────────────────────────────────────────┐
│ Configuração Fiscal - Tributa AI        │
├──────────────────────────────────────────┤
│                                          │
│ Tributa AI - Integração                  │
│ ┌────────────────────────────────────┐   │
│ │ ○ Habilitar Tributa AI             │   │
│ │ Token API: **************          │   │
│ │ Ambiente: ⦿ Sandbox  ○ Produção    │   │
│ └────────────────────────────────────┘   │
│                                          │
│ Dados da Empresa                         │
│ ┌────────────────────────────────────┐   │
│ │ CNPJ: 99.999.999/0001-99          │   │
│ │ Razão Social: ...                 │   │
│ │ ...                               │   │
│ └────────────────────────────────────┘   │
│                                          │
│ [Salvar Configurações]                   │
│                                          │
│ ┌──────────────────────────────────┐     │
│ │ 📋 Como Configurar o Tributa AI  │     │
│ │                                  │     │
│ │ 1. Crie sua conta...             │     │
│ │ 2. Obtenha seu Token...          │     │
│ │ ...                              │     │
│ └──────────────────────────────────┘     │
└──────────────────────────────────────────┘
```

---

## ✅ Teste Aprovado Se:

1. ✅ Página carrega sem erros
2. ✅ Form aparece completo com todas as seções
3. ✅ Form salva dados no banco
4. ✅ Form recarrega dados salvos
5. ✅ Notificação de sucesso aparece
6. ✅ Página de notas fiscais carrega
7. ✅ Observer cria nota quando pagamento confirmado

---

## 🚀 Próximo Passo Após Teste

Se tudo funcionar, próximo passo é:
1. Criar conta real no Tributa AI (sandbox gratuito)
2. Obter token real
3. Configurar CSC real (SEFAZ)
4. Testar emissão real de nota

---

**Status:** AGUARDANDO TESTE DO USUÁRIO
**Data:** 25/02/2026
