# ✅ Correções Página de Perfil (26/02/2026)

## 🔴 Problemas Identificados

### 1. Botão "Salvar Endereço" Não Funcionava
**Sintoma:**
- Cliente preenche endereço mas não salva
- Botão "Salvar Endereço" sem resposta

**Causa Raiz:**
- `AddressController::store()` retornava array vazio (mock)
- Não salvava no banco de dados

**✅ CORRIGIDO:**
Implementado corretamente em `app/Http/Controllers/Api/AddressController.php`:
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'label' => 'nullable|string|max:100',
        'street' => 'required|string|max:255',
        'number' => 'required|string|max:20',
        'complement' => 'nullable|string|max:255',
        'neighborhood' => 'required|string|max:100',
        'city' => 'required|string|max:100',
        'zipcode' => 'nullable|string|max:10',
        'is_default' => 'nullable|boolean',
    ]);

    $customer = $request->user();

    // Se marcar como padrão, desmarcar outros
    if ($validated['is_default'] ?? false) {
        Address::where('customer_id', $customer->id)
            ->update(['is_default' => false]);
    }

    $address = Address::create([
        'customer_id' => $customer->id,
        // ... outros campos
    ]);

    return response()->json([
        'message' => 'Endereço salvo com sucesso',
        'data' => $address
    ], 201);
}
```

---

### 2. Sem Botão "Voltar"
**Sintoma:**
- Usuário entra em /perfil mas não tem como voltar
- Precisa usar botão "Voltar" do navegador

**Causa:**
- Header da página não tinha link de navegação

**✅ CORRIGIDO:**
Adicionado em `resources/views/tenant/profile.blade.php` (linha 11):
```html
<a href="/" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-4 font-medium">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    Voltar ao cardápio
</a>
```

---

### 3. Rotas Duplicadas e Confusas
**Sintoma:**
- Rotas `/api/v1/addresses` duplicadas
- Algumas apontando para `AddressController`, outras para `CustomerController`

**Causa:**
- Código de debug e workarounds deixaram rotas duplicadas

**✅ CORRIGIDO:**
Consolidado em `routes/tenant.php` para usar apenas `AddressController`:
```php
// Endereços (AddressController)
Route::get('/addresses', [\App\Http\Controllers\Api\AddressController::class, 'index']);
Route::post('/addresses', [\App\Http\Controllers\Api\AddressController::class, 'store']);
Route::delete('/addresses/{id}', [\App\Http\Controllers\Api\AddressController::class, 'destroy']);
```

---

## 📋 Funcionalidades do Perfil

### ✅ Implementado e Funcionando

**1. Visualizar Dados**
- Nome, email, telefone, data de nascimento
- Saldo de cashback e tier (Bronze/Prata/Ouro/Platina)
- Total de pedidos e valor gasto

**2. Editar Perfil**
- Atualizar nome, telefone, data de nascimento
- Email não pode ser alterado (regra de negócio)
- Salva via `PUT /api/v1/me`

**3. Gerenciar Endereços**
- Listar endereços salvos
- Adicionar novo endereço
- Excluir endereço
- Selecionar cidade (carrega bairros dinamicamente)
- Campo de busca para bairro
- Validação de campos obrigatórios

**4. Histórico de Cashback**
- Ver todas transações (ganhou/usou)
- Data e valor de cada transação
- Tipo (earned = verde, used = vermelho)

**5. Logout**
- Sair da conta com confirmação

---

## 🔧 Arquivos Alterados

1. **app/Http/Controllers/Api/AddressController.php**
   - Implementado `index()` - listar endereços
   - Implementado `store()` - salvar endereço
   - Implementado `destroy()` - excluir endereço

2. **resources/views/tenant/profile.blade.php**
   - Adicionado botão "Voltar ao cardápio" no header
   - JavaScript já estava funcional (sem alterações)

3. **routes/tenant.php**
   - Removido rotas duplicadas de addresses
   - Consolidado para usar apenas AddressController

---

## 📊 Estrutura de Endereços

**Tabela: `addresses` (tenant schema)**
```sql
id                 bigint
customer_id        bigint       (FK -> customers.id)
label              varchar(100) (Ex: Casa, Trabalho)
city               varchar(100)
neighborhood       varchar(100)
street             varchar(255)
number             varchar(20)
complement         varchar(255) (nullable)
zipcode            varchar(10)  (nullable)
is_default         boolean      (default: false)
created_at         timestamp
updated_at         timestamp
```

**Validações no Backend:**
- label: opcional, max 100 chars
- city: obrigatório, max 100 chars
- neighborhood: obrigatório, max 100 chars
- street: obrigatório, max 255 chars
- number: obrigatório, max 20 chars
- complement: opcional, max 255 chars
- zipcode: opcional, max 10 chars
- is_default: opcional, boolean

**Regra de Negócio:**
- Ao marcar endereço como padrão (`is_default = true`), todos outros endereços do cliente são desmarcados
- Endereços são carregados ordenados por: padrão primeiro, depois mais recentes

---

## 🧪 Como Testar

### 1. Teste de Adicionar Endereço
```
1. Fazer login
2. Ir para /perfil
3. Clicar em "Meus Endereços"
4. Clicar em "+ Adicionar Endereço"
5. Preencher:
   - Nome: Casa
   - Cidade: Louveira
   - Bairro: Jardim Bela Vista
   - Rua: Rua Teste
   - Número: 123
   - Complemento: Apto 45 (opcional)
6. Clicar em "Salvar Endereço"
7. ✅ Deve aparecer toast "Endereço salvo!"
8. ✅ Deve voltar para lista de endereços
9. ✅ Deve mostrar endereço na lista
```

### 2. Teste de Excluir Endereço
```
1. Na lista de endereços
2. Clicar no ícone de lixeira
3. Confirmar exclusão
4. ✅ Deve aparecer toast "Endereço excluído"
5. ✅ Endereço deve sumir da lista
```

### 3. Teste de Voltar
```
1. Ir para /perfil
2. Clicar em "← Voltar ao cardápio" (topo da página)
3. ✅ Deve redirecionar para /
```

---

## 🚀 Resultado Final

```
✅ Botão "Salvar Endereço" funciona corretamente
✅ Endereços salvos no banco de dados
✅ Botão "Voltar" adicionado no header
✅ Rotas consolidadas e limpas
✅ CRUD completo de endereços funcionando
✅ Integração com cidades/bairros do tenant
✅ UX clean e responsivo
```

---

**Data**: 26/02/2026
**Status**: ✅ CORREÇÕES APLICADAS
