# ✅ Cashback Automático - Toggle Simples

**Data:** 28/02/2026
**Status:** ✅ IMPLEMENTADO E FUNCIONANDO

---

## 🎯 O Que Foi Implementado

Sistema simplificado de cashback com **toggle/checkbox** ao invés de input numérico.

### Antes (Complicado) ❌
```
Cliente tinha R$ 50 de cashback
Pedido: R$ 200
---------------------------------
❌ Cliente precisava:
  1. Ver quanto tinha de saldo
  2. Digitar valor manualmente no campo
  3. Calcular mentalmente quanto pagaria
```

### Agora (Simples) ✅
```
Cliente tem R$ 50 de cashback
Pedido: R$ 200
---------------------------------
✅ Cliente apenas:
  1. Marca checkbox "Usar meu cashback"
  2. Sistema usa automaticamente os R$ 50
  3. Cliente paga R$ 150 via PIX
```

---

## 📡 API - Como Usar

### Endpoint: POST /api/v1/orders

### Opção 1: Usar TODO Cashback (NOVO) ⭐
```json
{
  "items": [...],
  "delivery_address": "Rua X, 123",
  "delivery_city": "São Paulo",
  "delivery_neighborhood": "Centro",
  "payment_method": "pix",
  "use_all_cashback": true,  // ⭐ Simples toggle!
  "notes": "Sem cebola"
}
```

**O que acontece:**
- ✅ Backend busca saldo do cliente (ex: R$ 50,00)
- ✅ Calcula total do pedido (ex: R$ 200,00)
- ✅ Usa automaticamente `min(saldo, total)` = R$ 50,00
- ✅ Cliente paga diferença: R$ 150,00

**Caso especial - Saldo maior que pedido:**
```
Saldo: R$ 100,00
Pedido: R$ 30,00
→ Usa apenas R$ 30,00 (zera o pedido)
→ Sobra R$ 70,00 para próximo pedido
```

### Opção 2: Valor Específico (Legacy)
```json
{
  "items": [...],
  "delivery_address": "Rua X, 123",
  "delivery_city": "São Paulo",
  "delivery_neighborhood": "Centro",
  "payment_method": "pix",
  "use_cashback": 25.50,  // Valor exato
  "notes": "Sem cebola"
}
```

**Quando usar:**
- Cliente quer guardar parte do cashback
- Implementações antigas que já enviam valor numérico

---

## 🔧 Backend - Lógica Implementada

### OrderController.php - Validação

```php
// Aceita ambos os formatos
'use_cashback' => 'nullable|numeric|min:0|max:10000',     // Legacy
'use_all_cashback' => 'nullable|boolean',                  // Novo ⭐
```

### OrderController.php - Cálculo Automático

```php
if ($request->use_all_cashback) {
    // 1. Busca saldo real do banco
    $cashbackBalance = (float) $customer->cashback_balance;

    // 2. Calcula total estimado do pedido
    $estimatedSubtotal = /* soma dos items */;
    $estimatedTotal = $estimatedSubtotal + $deliveryFee;

    // 3. Usa o menor valor (não pode descontar mais que o total)
    $useCashback = min($cashbackBalance, $estimatedTotal);
}
```

**Segurança:**
- ✅ Sempre busca saldo do BANCO (nunca confia no frontend)
- ✅ `refresh()` garante dados atualizados
- ✅ Usa conexão correta do tenant (schema isolation)
- ✅ Não permite desconto maior que o total

---

## 💡 Frontend - Exemplo de Implementação

### Vue.js / React

```vue
<template>
  <div class="checkout">
    <!-- Outros campos... -->

    <!-- CASHBACK TOGGLE -->
    <div v-if="customerCashback > 0" class="cashback-section">
      <label class="checkbox-label">
        <input
          type="checkbox"
          v-model="useAllCashback"
        />
        <span>
          Usar meu cashback (R$ {{ customerCashback.toFixed(2) }})
        </span>
      </label>

      <!-- Preview do desconto -->
      <p v-if="useAllCashback" class="preview">
        Você vai pagar apenas
        <strong>R$ {{ finalTotal.toFixed(2) }}</strong>
      </p>
    </div>

    <button @click="createOrder">Finalizar Pedido</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      useAllCashback: false,
      customerCashback: 50.00,  // Vem da API /cashback/balance
      cartTotal: 200.00,
    };
  },
  computed: {
    finalTotal() {
      if (this.useAllCashback) {
        const discount = Math.min(this.customerCashback, this.cartTotal);
        return this.cartTotal - discount;
      }
      return this.cartTotal;
    }
  },
  methods: {
    async createOrder() {
      const payload = {
        items: this.cartItems,
        delivery_address: this.address,
        delivery_city: this.city,
        delivery_neighborhood: this.neighborhood,
        payment_method: this.paymentMethod,
        use_all_cashback: this.useAllCashback,  // ⭐ Boolean simples
      };

      const response = await axios.post('/api/v1/orders', payload);
      // ...
    }
  }
}
</script>
```

### JavaScript Vanilla

```html
<div class="checkout">
  <label>
    <input type="checkbox" id="useCashback" />
    Usar meu cashback (R$ <span id="cashbackAmount">50.00</span>)
  </label>

  <p id="totalPreview">
    Total: R$ <span id="totalAmount">200.00</span>
  </p>
</div>

<script>
const checkbox = document.getElementById('useCashback');
const cashback = 50.00;
const cartTotal = 200.00;

checkbox.addEventListener('change', function() {
  const total = document.getElementById('totalAmount');

  if (this.checked) {
    const discount = Math.min(cashback, cartTotal);
    total.textContent = (cartTotal - discount).toFixed(2);
  } else {
    total.textContent = cartTotal.toFixed(2);
  }
});

// Ao criar pedido
async function createOrder() {
  const payload = {
    items: [...],
    delivery_address: "...",
    delivery_city: "...",
    delivery_neighborhood: "...",
    payment_method: "pix",
    use_all_cashback: checkbox.checked,  // ⭐ true ou false
  };

  const response = await fetch('/api/v1/orders', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
}
</script>
```

---

## 🧪 Testes

### Cenário 1: Saldo Suficiente Para Zerar
```bash
curl -X POST https://marmitaria-gi.yumgo.com.br/api/v1/orders \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [{"product_id": 1, "quantity": 1}],
    "delivery_address": "Rua Teste, 123",
    "delivery_city": "São Paulo",
    "delivery_neighborhood": "Centro",
    "payment_method": "pix",
    "use_all_cashback": true
  }'
```

**Resultado Esperado:**
```json
{
  "message": "Pedido criado com sucesso!",
  "order": {
    "subtotal": 30.00,
    "delivery_fee": 5.00,
    "cashback_used": 35.00,  // Usou todo saldo (zerou pedido)
    "total": 0.00,            // ✅ Pedido pago com cashback!
    "payment_status": "paid"  // Sem necessidade de PIX
  }
}
```

### Cenário 2: Saldo Insuficiente
```bash
# Cliente tem R$ 10,00 de cashback
# Pedido de R$ 50,00

curl -X POST ... (mesmo comando acima)
```

**Resultado Esperado:**
```json
{
  "message": "Pedido criado com sucesso!",
  "order": {
    "subtotal": 45.00,
    "delivery_fee": 5.00,
    "cashback_used": 10.00,  // Usou todo saldo disponível
    "total": 40.00,           // Ainda precisa pagar R$ 40
    "payment_status": "pending"
  },
  "payment": {
    "method": "pix",
    "qrcode_image": "data:image/png;base64,...",  // ✅ QR Code para R$ 40
    "qrcode_text": "00020126..."
  }
}
```

### Cenário 3: Não Usar Cashback
```bash
curl -X POST ... \
  -d '{
    ...
    "use_all_cashback": false  // Ou omitir campo
  }'
```

**Resultado Esperado:**
```json
{
  "order": {
    "cashback_used": 0.00,
    "total": 50.00  // Total integral
  },
  "payment": {
    "qrcode_image": "..."  // QR Code para R$ 50
  }
}
```

---

## 📊 Comparação: Antes vs Depois

### UX - Experiência do Cliente

| Aspecto | Antes ❌ | Agora ✅ |
|---------|---------|---------|
| **Campos** | 2 (checkbox + input numérico) | 1 (apenas checkbox) |
| **Passos** | Ver saldo → Calcular → Digitar valor | Apenas marcar toggle |
| **Cálculo** | Manual pelo cliente | Automático pelo sistema |
| **Erros** | "Saldo insuficiente" comum | Impossível errar |
| **Mobile** | Ruim (digitar números) | Ótimo (tap no toggle) |

### Backend - Segurança

| Validação | Antes | Agora |
|-----------|-------|-------|
| **Saldo sempre do banco** | ✅ | ✅ |
| **Não aceita valor maior** | ✅ | ✅ (automático) |
| **Schema isolation** | ✅ | ✅ |
| **Refresh antes de usar** | ✅ | ✅ |

---

## 🔒 Segurança

### Proteções Implementadas

1. **Saldo sempre do banco**
```php
$customer->refresh();  // Dados frescos
$cashbackBalance = (float) $customer->cashback_balance;  // Do tenant
```

2. **Não pode descontar mais que total**
```php
$useCashback = min($cashbackBalance, $estimatedTotal);
```

3. **Validação de schema**
```php
// Customer já está na conexão do tenant (schema isolation OK)
```

4. **Logs para auditoria**
```php
\Log::info('💰 Calculado cashback automático', [
    'balance' => $cashbackBalance,
    'estimated_total' => $estimatedTotal,
    'cashback_to_use' => $useCashback,
]);
```

---

## ✅ Arquivos Modificados

**Backend:**
- `app/Http/Controllers/Api/OrderController.php`
  - Adicionado validação `use_all_cashback` (boolean)
  - Lógica de cálculo automático de cashback
  - Logs detalhados

**Documentação:**
- `CASHBACK-AUTOMATICO-28-02-2026.md` (este arquivo)

---

## 🎯 Status

- ✅ Backend implementado
- ✅ Validações OK
- ✅ Segurança OK
- ✅ Logs OK
- ⏳ Frontend precisa implementar toggle
- ⏳ Documentar para equipe de frontend

---

## 📝 TODO - Frontend

Para equipe de frontend implementar:

1. **Adicionar toggle/checkbox** na tela de checkout
2. **Buscar saldo** via `GET /api/v1/cashback/balance`
3. **Mostrar preview** do total com desconto
4. **Enviar** `use_all_cashback: true` ao criar pedido
5. **Testar** cenários (saldo maior/menor que total)

---

**🚀 Sistema agora é mais simples e intuitivo para o cliente!**
