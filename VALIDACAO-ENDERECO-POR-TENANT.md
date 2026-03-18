# ✅ Validação de Endereço por Restaurante

**Data:** 18/03/2026
**Funcionalidade:** Validar se endereço salvo está na área de entrega do restaurante atual

---

## 🎯 Problema

Com **customers centrais** e **bairros por tenant**, um cliente pode ter endereços salvos que:
- ✅ **Estão** na área de entrega do Restaurante A
- ❌ **Não estão** na área de entrega do Restaurante B

**Exemplo:**
```
Customer: João Silva (central)
Endereço Salvo: Jardim Bela Vista, Louveira

Marmitaria da Gi:
- Entrega em: Louveira (Jardim Bela Vista, Santo Antônio, Vila Pasti)
- ✅ Endereço válido para este restaurante

Los Pampas:
- Entrega em: Campinas (Centro, Cambuí)
- ❌ NÃO entrega em Louveira!
```

**Risco:**
- Cliente seleciona endereço de Louveira no Los Pampas
- Sistema aceita pedido que não pode ser entregue
- Restaurante perde dinheiro ou cliente fica insatisfeito

---

## ✅ Solução Implementada

### 1. Validação no Model Address

**Arquivo:** `app/Models/Address.php`

**Métodos adicionados:**

```php
/**
 * Verifica se este endereço está na área de entrega do restaurante atual
 */
public function isInDeliveryArea(): bool
{
    $neighborhood = Neighborhood::where('city', $this->city)
        ->where('name', $this->neighborhood)
        ->where('is_active', true)
        ->first();

    return $neighborhood !== null;
}

/**
 * Busca informações de entrega (taxa, tempo)
 */
public function getDeliveryInfo(): ?object
{
    $neighborhood = Neighborhood::where('city', $this->city)
        ->where('name', $this->neighborhood)
        ->where('is_active', true)
        ->first();

    if (!$neighborhood) {
        return null;
    }

    return (object) [
        'available' => true,
        'fee' => (float) $neighborhood->delivery_fee,
        'time' => $neighborhood->delivery_time,
        'minimum_order' => $neighborhood->minimum_order ? (float) $neighborhood->minimum_order : null,
    ];
}

/**
 * Scope: Apenas endereços na área de entrega
 */
public function scopeInDeliveryArea($query)
{
    return $query->whereExists(function ($q) {
        $q->select(\DB::raw(1))
          ->from('neighborhoods')
          ->whereColumn('neighborhoods.city', 'addresses.city')
          ->whereColumn('neighborhoods.name', 'addresses.neighborhood')
          ->where('neighborhoods.is_active', true);
    });
}
```

---

### 2. Validação no Frontend (Checkout)

**Arquivo:** `resources/views/tenant/checkout.blade.php`

#### Função JavaScript: checkAddressInDeliveryArea()

```javascript
async checkAddressInDeliveryArea(address) {
    try {
        const response = await fetch(`/api/v1/location/enabled-neighborhoods/${encodeURIComponent(address.city)}`);
        if (response.ok) {
            const data = await response.json();
            const neighborhoods = data.data || [];
            // Verifica se o bairro do endereço está na lista
            return neighborhoods.some(n => n.name === address.neighborhood);
        }
        return false;
    } catch (error) {
        console.error('Erro ao validar endereço:', error);
        return false;
    }
}
```

#### Modificação em loadSavedAddresses()

```javascript
async loadSavedAddresses() {
    const token = localStorage.getItem('auth_token');
    try {
        const response = await fetch('/api/v1/addresses', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        if (response.ok) {
            const data = await response.json();
            const addresses = data.data || [];

            // Validar cada endereço ⭐
            for (let address of addresses) {
                address.in_delivery_area = await this.checkAddressInDeliveryArea(address);
            }

            this.savedAddresses = addresses;
        }
    } catch (error) {
        console.error('Erro ao carregar endereços:', error);
    }
}
```

#### Seleção Automática Inteligente

```javascript
// Seleciona o primeiro endereço que está na área de entrega
if (this.savedAddresses.length > 0) {
    const defaultAddress = this.savedAddresses.find(a => a.in_delivery_area !== false) || this.savedAddresses[0];
    if (defaultAddress.in_delivery_area !== false) {
        this.selectedAddressId = defaultAddress.id;
        await this.selectSavedAddress(defaultAddress);
    }
}
```

---

### 3. UI Visual (Badge + Alerta)

**Template HTML:**

```html
<template x-for="address in savedAddresses" :key="address.id">
    <div :class="[
            selectedAddressId === address.id ? 'border-primary bg-red-50' : 'border-gray-200',
            address.in_delivery_area === false ? 'opacity-50' : 'hover:border-gray-300'
        ]"
        class="flex items-start gap-3 p-3 border-2 rounded-lg transition">

        <!-- Radio desabilitado se fora da área -->
        <input type="radio"
               :value="address.id"
               x-model="selectedAddressId"
               :disabled="address.in_delivery_area === false"
               class="mt-1 w-4 h-4 text-primary cursor-pointer disabled:cursor-not-allowed disabled:opacity-50">

        <div class="flex-1 cursor-pointer">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="font-semibold text-sm text-gray-900" x-text="address.label || 'Endereço'"></p>

                <!-- Badge: Fora da área ⭐ -->
                <span x-show="address.in_delivery_area === false"
                      class="px-2 py-0.5 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded">
                    Fora da área
                </span>
            </div>

            <p class="text-xs text-gray-600 mt-0.5">
                <span x-text="`${address.street}, ${address.number}${address.complement ? ' - ' + address.complement : ''}`"></span>
            </p>
            <p class="text-xs text-gray-500 mt-0.5">
                <span x-text="`${address.neighborhood} - ${address.city}`"></span>
            </p>

            <!-- Alerta inline ⭐ -->
            <p x-show="address.in_delivery_area === false"
               class="text-xs text-yellow-700 mt-1 flex items-start gap-1">
                <svg class="w-3 h-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                Este restaurante não entrega neste bairro
            </p>
        </div>
    </div>
</template>
```

---

## 🔄 Fluxo Completo

### Cenário 1: Endereço Válido

```
1. João acessa https://marmitariadagi.yumgo.com.br/checkout
2. Sistema carrega endereços salvos via API
3. Para cada endereço:
   - Busca bairros habilitados da cidade (API: /api/v1/location/enabled-neighborhoods/Louveira)
   - Verifica se "Jardim Bela Vista" está na lista
   - ✅ Está! (address.in_delivery_area = true)
4. Radio button HABILITADO ✅
5. Endereço pode ser selecionado
6. Taxa de entrega calculada normalmente
```

### Cenário 2: Endereço Fora da Área

```
1. João acessa https://lospampas.yumgo.com.br/checkout
2. Sistema carrega endereços salvos via API
3. Para endereço "Jardim Bela Vista, Louveira":
   - Busca bairros habilitados de Louveira
   - Los Pampas só entrega em Campinas
   - ❌ Não encontrou! (address.in_delivery_area = false)
4. Radio button DESABILITADO ❌
5. Badge "Fora da área" exibido
6. Alerta: "Este restaurante não entrega neste bairro"
7. Endereço NÃO pode ser selecionado
```

---

## 📊 Exemplo Visual

### Marmitaria da Gi (Entrega em Louveira)

```
[ • ] 🏠 Casa
      Rua das Flores, 123
      Jardim Bela Vista - Louveira
      ✅ Pode selecionar

[ ○ ] 🏢 Trabalho
      Av. Principal, 456
      Centro - Campinas
      [Fora da área]
      ⚠️ Este restaurante não entrega neste bairro
      ❌ NÃO pode selecionar
```

### Los Pampas (Entrega em Campinas)

```
[ ○ ] 🏠 Casa
      Rua das Flores, 123
      Jardim Bela Vista - Louveira
      [Fora da área]
      ⚠️ Este restaurante não entrega neste bairro
      ❌ NÃO pode selecionar

[ • ] 🏢 Trabalho
      Av. Principal, 456
      Centro - Campinas
      ✅ Pode selecionar
```

---

## 🧪 Como Testar

### Teste 1: Criar Endereço em Área Diferente

```bash
# 1. Acessar Marmitaria da Gi
https://marmitariadagi.yumgo.com.br/checkout

# 2. Adicionar endereço em Louveira (área de entrega)
Bairro: Jardim Bela Vista
Cidade: Louveira
✅ Deve funcionar normalmente

# 3. Acessar Los Pampas (NÃO entrega em Louveira)
https://lospampas.yumgo.com.br/checkout

# 4. Verificar endereço salvo
❌ Deve aparecer com badge "Fora da área"
❌ Radio desabilitado
⚠️ Alerta de não entrega
```

### Teste 2: Verificar Seleção Automática

```bash
# 1. Customer com 2 endereços salvos:
   - Endereço A: Fora da área
   - Endereço B: Dentro da área

# 2. Acessar checkout
✅ Sistema deve selecionar automaticamente Endereço B (que está na área)
❌ NÃO deve selecionar Endereço A (fora da área)
```

---

## 📝 Arquivos Modificados

```
✅ app/Models/Address.php
   - isInDeliveryArea()
   - getDeliveryInfo()
   - scopeInDeliveryArea()

✅ resources/views/tenant/checkout.blade.php
   - Template: Badge + alerta visual
   - checkAddressInDeliveryArea()
   - loadSavedAddresses() modificado
   - Seleção automática inteligente
```

---

## 🎯 Benefícios

| Item | Antes | Depois |
|------|-------|--------|
| Validação de área | ❌ Não tinha | ✅ Automática |
| UX | Confuso (aceitava qualquer endereço) | Claro (mostra se está fora) |
| Erros de entrega | Alto risco | Baixo risco |
| Seleção automática | Primeiro endereço | Primeiro válido |
| Feedback visual | Nenhum | Badge + alerta |

---

## ⚠️ Considerações

### Performance
- Validação roda **1 vez ao carregar** página de checkout
- Resultado é **cacheado** no objeto `address.in_delivery_area`
- **Não impacta** performance durante navegação

### Endereços Antigos
- Endereços salvos **antes** desta feature continuam funcionando
- São validados automaticamente ao acessar checkout
- Se fora da área, são marcados visualmente

### Múltiplos Restaurantes
- Cliente pode ter endereços **válidos** em um restaurante
- E **inválidos** em outro
- Sistema valida dinamicamente por tenant

---

## 📚 Documentação Relacionada

- ✅ `ARQUITETURA-CUSTOMERS-DUAL-SCHEMA.md` - Customers central + tenant
- ✅ `CORRECAO-CUSTOMERS-TABLE-18-03-2026.md` - Tabela customers recriada
- ✅ `app/Models/Neighborhood.php` - Modelo de bairros

---

**✅ Validação implementada e funcionando!**

**Impacto:** Agora o sistema previne automaticamente que clientes selecionem endereços fora da área de entrega do restaurante, melhorando a experiência e evitando pedidos que não podem ser entregues.
