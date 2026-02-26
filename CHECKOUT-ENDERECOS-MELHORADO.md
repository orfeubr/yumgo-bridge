# ✅ Checkout - Sistema de Endereços Melhorado

**Data:** 26/02/2026
**Status:** ✅ IMPLEMENTADO

---

## 🎯 O que foi implementado

Sistema completo de gerenciamento de endereços no checkout, com:

1. ✅ **Lista de endereços salvos** (cards selecionáveis)
2. ✅ **Botão "Editar"** em cada endereço
3. ✅ **Modal para adicionar/editar** endereços
4. ✅ **Seleção de cidade/bairro filtrados** (apenas os liberados pelo restaurante)
5. ✅ **Salvar na API** ao criar/editar endereços
6. ✅ **Checkbox "Salvar endereço"** (opcional)
7. ✅ **Removido formulário inline** (sempre usa modal)
8. ✅ **Cálculo automático** de taxa de entrega

---

## 📋 Funcionalidades

### 1. Lista de Endereços Salvos

**Visual:**
```
┌─────────────────────────────────────────────────┐
│ ⦿ Casa                                    ✏️   │
│   Rua das Flores, 123 - Apto 45                │
│   Centro - São Paulo                            │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ ○ Trabalho                                ✏️   │
│   Av. Paulista, 1000 - 10º andar               │
│   Bela Vista - São Paulo                        │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ + Adicionar Novo Endereço                      │
└─────────────────────────────────────────────────┘
```

**Características:**
- Radio button para seleção
- Label personalizado (Casa, Trabalho, etc.)
- Endereço completo exibido
- Bairro e cidade
- Botão "Editar" (ícone de lápis)
- Borda vermelha quando selecionado

### 2. Sem Endereços Salvos

**Visual:**
```
┌─────────────────────────────────────────────────┐
│             📍                                  │
│   Você ainda não tem endereços salvos           │
│                                                 │
│   [+ Adicionar Endereço]                        │
└─────────────────────────────────────────────────┘
```

**Comportamento:**
- Exibe ícone de localização
- Mensagem amigável
- Botão para abrir modal
- **NÃO mostra formulário inline** (sempre usa modal)

### 3. Modal de Adicionar/Editar

**Campos do formulário:**

```
┌─────────────────────────────────────────────────┐
│ Novo Endereço                              ✕   │
├─────────────────────────────────────────────────┤
│                                                 │
│ Identificação                                   │
│ [Ex: Casa, Trabalho, Casa da Vó...        ]    │
│                                                 │
│ Cidade *                                        │
│ [Selecione a cidade ▼                     ]    │
│                                                 │
│ Bairro *                                        │
│ [Centro - R$ 5,00 ▼                       ]    │
│                                                 │
│ Rua *                                           │
│ [Nome da rua                              ]    │
│                                                 │
│ Número *          Complemento                   │
│ [123        ]     [Apto, Bloco...         ]    │
│                                                 │
│ CEP (opcional)                                  │
│ [00000-000                                ]    │
│                                                 │
│ ☑ Salvar este endereço para pedidos futuros    │
│                                                 │
│ Taxa de entrega: R$ 5,00                       │
│                                                 │
│ * Campos obrigatórios                           │
├─────────────────────────────────────────────────┤
│ [Cancelar]                    [Confirmar]       │
└─────────────────────────────────────────────────┘
```

**Validações:**
- ✅ Cidade obrigatória
- ✅ Bairro obrigatório (apenas bairros da cidade selecionada)
- ✅ Rua obrigatória
- ✅ Número obrigatório
- ✅ CEP opcional (máximo 9 caracteres)
- ✅ Botão desabilitado se campos obrigatórios vazios

**Comportamento:**
- Cidade: SELECT com apenas cidades liberadas pelo restaurante
- Bairro: SELECT dinâmico baseado na cidade
- Mostra taxa de entrega do bairro selecionado
- Checkbox "Salvar endereço" marcado por padrão
- Pode usar endereço SEM salvar (apenas para este pedido)

### 4. Edição de Endereço

**Como funciona:**
1. Cliente clica no botão "Editar" (ícone de lápis)
2. Modal abre pré-preenchido com dados do endereço
3. Título muda para "Editar Endereço"
4. Botão muda para "Atualizar"
5. Ao salvar, atualiza via API PUT `/api/v1/addresses/{id}`
6. Lista de endereços recarrega automaticamente

---

## 🔧 Arquivos Modificados

### 1. Backend - AddressController.php

**Arquivo:** `app/Http/Controllers/Api/AddressController.php`

**Método adicionado:**
```php
public function update(Request $request, $id)
{
    $validated = $request->validate([...]);

    $address = Address::where('customer_id', $customer->id)
        ->findOrFail($id);

    // Se marcar como padrão, desmarcar os outros
    if ($validated['is_default'] ?? false) {
        Address::where('customer_id', $customer->id)
            ->where('id', '!=', $id)
            ->update(['is_default' => false]);
    }

    $address->update($validated);

    return response()->json([
        'message' => 'Endereço atualizado com sucesso',
        'data' => $address->fresh()
    ]);
}
```

### 2. Rotas

**Arquivo:** `routes/tenant.php`

**Rotas adicionadas:**
```php
// Endereços
Route::get('/addresses', [AddressController::class, 'index']);
Route::post('/addresses', [AddressController::class, 'store']);
Route::put('/addresses/{id}', [AddressController::class, 'update']); // ← NOVO
Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
```

### 3. Frontend - checkout.blade.php

**Arquivo:** `resources/views/tenant/checkout.blade.php`

**Seções modificadas:**

#### 3.1 Cards de Endereços (linhas ~247-306)

**Antes:**
```html
<label class="flex items-start...">
    <input type="radio" ...>
    <div class="flex-1">
        <p>Endereço</p>
    </div>
</label>
```

**Depois:**
```html
<div class="flex items-start...">
    <input type="radio" ...>
    <div class="flex-1" @click="selectAddress">
        <p>Casa</p>
        <p>Rua, Número</p>
        <p>Bairro - Cidade</p>
    </div>
    <button @click="editAddress(address)">
        <svg>✏️</svg>
    </button>
</div>
```

**Novos recursos:**
- Botão "Editar" em cada card
- Mostra bairro e cidade completos
- Click no card inteiro seleciona
- Estado vazio elegante (ícone + mensagem)

#### 3.2 Modal (linhas ~509-650)

**Campos novos:**
- ✅ Label (identificação)
- ✅ SELECT Cidade (filtrado)
- ✅ SELECT Bairro (filtrado por cidade)
- ✅ Rua
- ✅ Número
- ✅ Complemento
- ✅ CEP
- ✅ Checkbox "Salvar endereço"
- ✅ Indicador de taxa de entrega

**Validações front-end:**
- Campos obrigatórios marcados com *
- Botão desabilitado se inválido
- Loading state ao salvar

#### 3.3 Variáveis JavaScript (linhas ~654-688)

**Variáveis adicionadas:**
```javascript
addressLabel: '',           // "Casa", "Trabalho", etc
deliveryZipcode: '',        // CEP
editingAddressId: null,     // ID do endereço sendo editado
shouldSaveAddress: true,    // Checkbox salvar
savingAddress: false,       // Loading state
```

#### 3.4 Funções JavaScript

**Funções novas:**

```javascript
// Abrir modal limpo
openAddressModal() {
    this.showAddressModal = true;
    this.editingAddressId = null;
    // Limpa todos os campos
}

// Fechar modal
closeAddressModal() {
    this.showAddressModal = false;
    this.editingAddressId = null;
}

// Editar endereço existente
async editAddress(address) {
    this.showAddressModal = true;
    this.editingAddressId = address.id;
    // Pré-preenche campos
    await this.loadNeighborhoodsForModal();
}

// Salvar (criar ou atualizar)
async saveAddress() {
    const method = this.editingAddressId ? 'PUT' : 'POST';
    const url = this.editingAddressId
        ? `/api/v1/addresses/${this.editingAddressId}`
        : '/api/v1/addresses';

    // Salva na API se shouldSaveAddress ou editando
    if (this.shouldSaveAddress || this.editingAddressId) {
        await fetch(url, {...});
        await this.loadSavedAddresses(); // Recarrega lista
    }

    // Seleciona o endereço automaticamente
    await this.selectSavedAddress(savedAddress);
    this.closeAddressModal();
}

// Carregar bairros no modal
async loadNeighborhoodsForModal() {
    const response = await fetch(`/api/v1/location/neighborhoods?city=${this.selectedCity}`);
    this.availableNeighborhoods = data.data;
}
```

---

## 🔄 Fluxo Completo

### Adicionar Novo Endereço

```
1. Cliente clica "Adicionar Novo Endereço"
   ↓
2. Modal abre com campos vazios
   ↓
3. Cliente seleciona Cidade
   ↓
4. Bairros dessa cidade são carregados (API)
   ↓
5. Cliente seleciona Bairro
   ↓
6. Taxa de entrega é exibida automaticamente
   ↓
7. Cliente preenche Rua, Número, etc
   ↓
8. Checkbox "Salvar endereço" marcado (padrão)
   ↓
9. Cliente clica "Confirmar"
   ↓
10. POST /api/v1/addresses (salva no banco)
    ↓
11. Lista de endereços recarrega
    ↓
12. Endereço novo já vem selecionado
    ↓
13. Modal fecha
```

### Editar Endereço

```
1. Cliente clica botão "Editar" (lápis)
   ↓
2. Modal abre pré-preenchido
   ↓
3. Título: "Editar Endereço"
   ↓
4. Cliente modifica campos
   ↓
5. Cliente clica "Atualizar"
   ↓
6. PUT /api/v1/addresses/{id} (atualiza no banco)
   ↓
7. Lista de endereços recarrega
   ↓
8. Endereço atualizado mantém seleção
   ↓
9. Modal fecha
```

### Usar Sem Salvar

```
1. Cliente abre modal
   ↓
2. Cliente desmarca "Salvar endereço"
   ↓
3. Preenche campos normalmente
   ↓
4. Clica "Confirmar"
   ↓
5. NÃO salva na API
   ↓
6. Apenas usa para este pedido
   ↓
7. selectedAddressId = null (não marcado)
   ↓
8. Modal fecha
```

---

## 🎨 Design

### Cores

- **Selecionado:** Borda vermelha (#EA1D2C) + fundo vermelho claro (bg-red-50)
- **Hover:** Borda cinza (#E5E7EB)
- **Botão Editar:** Cinza → Vermelho no hover
- **Taxa de entrega:** Fundo azul claro (bg-blue-50)
- **Checkbox salvar:** Fundo verde claro (bg-green-50)

### Responsividade

- **Mobile:** Modal ocupa tela inteira (rounded-t-xl)
- **Desktop:** Modal centralizado (max-w-lg, rounded-xl)
- **Scroll:** Cabeçalho e rodapé fixos, conteúdo scrollável

---

## ✅ Validações Backend

**Campos:**
```php
'label' => 'nullable|string|max:100',
'street' => 'required|string|max:255',
'number' => 'required|string|max:20',
'complement' => 'nullable|string|max:255',
'neighborhood' => 'required|string|max:100',
'city' => 'required|string|max:100',
'zipcode' => 'nullable|string|max:10',
'is_default' => 'nullable|boolean',
```

**Regra de negócio:**
- ✅ Apenas um endereço pode ser padrão por cliente
- ✅ Primeiro endereço criado é automaticamente padrão
- ✅ Ao marcar outro como padrão, desmarca o anterior
- ✅ Cliente só pode editar/deletar próprios endereços

---

## 🧪 Como Testar

### 1. Sem endereços salvos

```
1. Fazer logout e login (cliente novo)
2. Adicionar item ao carrinho
3. Ir para checkout
4. Verificar:
   ✅ Ícone de localização exibido
   ✅ Mensagem "Você ainda não tem endereços salvos"
   ✅ Botão "Adicionar Endereço"
   ✅ NÃO tem formulário inline
```

### 2. Adicionar primeiro endereço

```
1. Clicar "Adicionar Endereço"
2. Verificar:
   ✅ Modal abre
   ✅ Título "Novo Endereço"
   ✅ Campo "Identificação" vazio
   ✅ SELECT "Cidade" com apenas cidades liberadas
3. Selecionar cidade
4. Verificar:
   ✅ SELECT "Bairro" carrega (loading...)
   ✅ Bairros da cidade aparecem com preço
5. Selecionar bairro "Centro - R$ 5,00"
6. Verificar:
   ✅ Caixa azul mostra "Taxa de entrega: R$ 5,00"
7. Preencher:
   - Identificação: Casa
   - Rua: Rua das Flores
   - Número: 123
   - Complemento: Apto 45
8. Verificar:
   ✅ Checkbox "Salvar endereço" marcado
   ✅ Botão "Confirmar" habilitado
9. Clicar "Confirmar"
10. Verificar:
    ✅ Modal fecha
    ✅ Card do endereço aparece
    ✅ Radio button selecionado (borda vermelha)
    ✅ Label "Casa" exibida
    ✅ Endereço completo: "Rua das Flores, 123 - Apto 45"
    ✅ Bairro/cidade: "Centro - São Paulo"
    ✅ Botão de editar (lápis) visível
```

### 3. Adicionar segundo endereço

```
1. Clicar "+ Adicionar Novo Endereço"
2. Preencher:
   - Identificação: Trabalho
   - Cidade: São Paulo
   - Bairro: Bela Vista
   - Rua: Av. Paulista
   - Número: 1000
   - Complemento: 10º andar
3. Clicar "Confirmar"
4. Verificar:
   ✅ Dois cards exibidos
   ✅ Segundo endereço selecionado (último adicionado)
   ✅ Taxa de entrega atualizada
```

### 4. Editar endereço

```
1. Clicar botão "Editar" (lápis) do primeiro endereço
2. Verificar:
   ✅ Modal abre
   ✅ Título "Editar Endereço"
   ✅ Campos pré-preenchidos
   ✅ Cidade e bairro corretos
   ✅ Botão "Atualizar" (não "Confirmar")
3. Mudar número: 123 → 456
4. Clicar "Atualizar"
5. Verificar:
   ✅ Card atualiza: "Rua das Flores, 456 - Apto 45"
   ✅ Endereço continua selecionado
```

### 5. Usar sem salvar

```
1. Clicar "+ Adicionar Novo Endereço"
2. Desmarcar "Salvar este endereço"
3. Preencher campos normalmente
4. Clicar "Confirmar"
5. Verificar:
   ✅ Modal fecha
   ✅ Endereço NÃO aparece na lista de salvos
   ✅ selectedAddressId = null
   ✅ Pode continuar checkout normalmente
```

### 6. Validações

```
1. Abrir modal
2. Deixar cidade vazia → Botão desabilitado ✅
3. Selecionar cidade mas não bairro → Botão desabilitado ✅
4. Deixar rua vazia → Botão desabilitado ✅
5. Deixar número vazio → Botão desabilitado ✅
6. Preencher todos obrigatórios → Botão habilitado ✅
```

---

## 📊 API Endpoints

### GET /api/v1/addresses

**Descrição:** Lista endereços salvos do cliente

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "customer_id": 2,
      "label": "Casa",
      "city": "São Paulo",
      "neighborhood": "Centro",
      "street": "Rua das Flores",
      "number": "123",
      "complement": "Apto 45",
      "zipcode": "01310-100",
      "is_default": true,
      "created_at": "2026-02-26T20:00:00.000000Z",
      "updated_at": "2026-02-26T20:00:00.000000Z"
    }
  ]
}
```

### POST /api/v1/addresses

**Descrição:** Criar novo endereço

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "label": "Casa",
  "city": "São Paulo",
  "neighborhood": "Centro",
  "street": "Rua das Flores",
  "number": "123",
  "complement": "Apto 45",
  "zipcode": "01310-100",
  "is_default": false
}
```

**Response 201:**
```json
{
  "message": "Endereço salvo com sucesso",
  "data": { ... }
}
```

### PUT /api/v1/addresses/{id}

**Descrição:** Atualizar endereço existente

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body:** (mesma estrutura do POST)

**Response 200:**
```json
{
  "message": "Endereço atualizado com sucesso",
  "data": { ... }
}
```

### DELETE /api/v1/addresses/{id}

**Descrição:** Excluir endereço

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response 200:**
```json
{
  "message": "Endereço excluído com sucesso"
}
```

---

## 🎯 Benefícios

### Para o Cliente:
- ✅ Não precisa digitar endereço toda vez
- ✅ Pode ter múltiplos endereços (casa, trabalho, etc)
- ✅ Fácil edição de dados
- ✅ Pode usar endereço temporário sem salvar
- ✅ Taxa de entrega transparente

### Para o Restaurante:
- ✅ Endereços validados (apenas cidades/bairros atendidos)
- ✅ Menos erros de digitação
- ✅ Informações completas para entrega
- ✅ Controle total sobre áreas de entrega

### Para a Plataforma:
- ✅ UX superior ao iFood
- ✅ Dados estruturados
- ✅ Facilita análises futuras
- ✅ Possibilita sugestões inteligentes

---

## 📝 Próximos Passos (Opcionais)

### 1. Endereço Padrão
- ✅ Já implementado (primeiro é padrão)
- [ ] Permitir marcar/desmarcar como padrão
- [ ] Badge "Padrão" no card

### 2. Geolocalização
- [ ] Botão "Usar localização atual"
- [ ] Preencher automaticamente via API Google Maps
- [ ] Validar se está na área de entrega

### 3. CEP Autocomplete
- [ ] Buscar endereço por CEP (ViaCEP API)
- [ ] Preencher rua, bairro, cidade automaticamente

### 4. Histórico
- [ ] Mostrar últimos endereços usados (não salvos)
- [ ] Sugestão rápida

---

**Implementado com sucesso! 🎉**

**Data:** 26/02/2026 20:00 UTC
**Status:** ✅ PRONTO PARA USO
