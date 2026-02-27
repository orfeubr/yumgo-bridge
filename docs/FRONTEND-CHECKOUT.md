# Frontend - Botão "Finalizar Pedido"

## 🛒 Problema

O botão "Finalizar Pedido" não envia os dados corretamente para a API.

## ✅ Solução Completa

### 1. Componente de Checkout (Vue.js 3)

```vue
<script setup>
import { ref, computed } from 'vue'
import { useCart } from '@/composables/useCart'
import { useRouter } from 'vue-router'

const router = useRouter()
const { cart, clearCart, subtotal } = useCart()

const loading = ref(false)
const error = ref(null)

// Dados do formulário
const deliveryAddress = ref('')
const paymentMethod = ref('pix')
const useCashback = ref(0)
const notes = ref('')

// Card data (se cartão)
const cardData = ref({
  holderName: '',
  number: '',
  expiryMonth: '',
  expiryYear: '',
  ccv: '',
})

// Calcular total
const deliveryFee = ref(5.00)
const total = computed(() => {
  return subtotal.value + deliveryFee.value - useCashback.value
})

// Finalizar pedido
const checkout = async () => {
  if (cart.value.length === 0) {
    error.value = 'Carrinho vazio!'
    return
  }

  if (!deliveryAddress.value) {
    error.value = 'Informe o endereço de entrega!'
    return
  }

  loading.value = true
  error.value = null

  try {
    // Preparar itens
    const items = cart.value.map(item => ({
      product_id: item.id,
      quantity: item.quantity,
      variation_id: item.variation?.id || null,
      addons: item.addons?.map(a => a.id) || [],
      half_and_half: item.halfAndHalf ? {
        product_id: item.halfAndHalf.product_id,
        variation_id: item.variation?.id || null,
      } : null,
      notes: item.notes || '',
    }))

    // Preparar payload
    const payload = {
      items,
      delivery_address: deliveryAddress.value,
      payment_method: paymentMethod.value,
      use_cashback: useCashback.value,
      notes: notes.value,
    }

    // Se for cartão, adicionar dados
    if (paymentMethod.value === 'credit_card' || paymentMethod.value === 'debit_card') {
      payload.card = {
        holderName: cardData.value.holderName,
        number: cardData.value.number.replace(/\s/g, ''),
        expiryMonth: cardData.value.expiryMonth,
        expiryYear: cardData.value.expiryYear,
        ccv: cardData.value.ccv,
      }
      payload.card_holder = {
        name: cardData.value.holderName,
        // Outros dados do titular se necessário
      }
    }

    // Fazer request
    const token = localStorage.getItem('auth_token')

    const response = await fetch('/api/v1/orders', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Erro ao criar pedido')
    }

    // Sucesso!
    clearCart()

    // Se for PIX, redirecionar para QR Code
    if (data.payment?.qrcode_image) {
      router.push({
        name: 'pix-payment',
        params: { orderId: data.order.id },
        state: { payment: data.payment }
      })
    } else {
      // Outros métodos
      router.push({
        name: 'order-success',
        params: { orderId: data.order.id }
      })
    }

  } catch (err) {
    console.error('Erro ao finalizar pedido:', err)
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="checkout-page">
    <h1>Finalizar Pedido</h1>

    <!-- Carrinho -->
    <div class="cart-summary">
      <h2>Resumo do Pedido</h2>
      <div v-for="item in cart" :key="item.id" class="cart-item">
        <span>{{ item.quantity }}x {{ item.name }}</span>
        <span>R$ {{ item.subtotal.toFixed(2) }}</span>
      </div>
      <div class="totals">
        <div class="total-line">
          <span>Subtotal:</span>
          <span>R$ {{ subtotal.toFixed(2) }}</span>
        </div>
        <div class="total-line">
          <span>Taxa de entrega:</span>
          <span>R$ {{ deliveryFee.toFixed(2) }}</span>
        </div>
        <div v-if="useCashback > 0" class="total-line discount">
          <span>Cashback usado:</span>
          <span>- R$ {{ useCashback.toFixed(2) }}</span>
        </div>
        <div class="total-line total">
          <span>Total:</span>
          <span>R$ {{ total.toFixed(2) }}</span>
        </div>
      </div>
    </div>

    <!-- Formulário -->
    <form @submit.prevent="checkout" class="checkout-form">
      <!-- Endereço -->
      <div class="form-section">
        <h3>Endereço de Entrega</h3>
        <textarea
          v-model="deliveryAddress"
          placeholder="Rua, número, complemento, bairro, cidade"
          rows="3"
          required
        ></textarea>
      </div>

      <!-- Método de Pagamento -->
      <div class="form-section">
        <h3>Método de Pagamento</h3>
        <div class="payment-methods">
          <label class="payment-option">
            <input type="radio" v-model="paymentMethod" value="pix" />
            <span>💳 PIX</span>
          </label>
          <label class="payment-option">
            <input type="radio" v-model="paymentMethod" value="credit_card" />
            <span>💳 Cartão de Crédito</span>
          </label>
          <label class="payment-option">
            <input type="radio" v-model="paymentMethod" value="cash" />
            <span>💵 Dinheiro</span>
          </label>
        </div>
      </div>

      <!-- Dados do Cartão (se selecionado) -->
      <div v-if="paymentMethod === 'credit_card' || paymentMethod === 'debit_card'" class="form-section">
        <h3>Dados do Cartão</h3>
        <input
          v-model="cardData.holderName"
          type="text"
          placeholder="Nome no cartão"
          required
        />
        <input
          v-model="cardData.number"
          type="text"
          placeholder="Número do cartão"
          maxlength="19"
          required
        />
        <div class="card-details">
          <input
            v-model="cardData.expiryMonth"
            type="text"
            placeholder="MM"
            maxlength="2"
            required
          />
          <input
            v-model="cardData.expiryYear"
            type="text"
            placeholder="AA"
            maxlength="2"
            required
          />
          <input
            v-model="cardData.ccv"
            type="text"
            placeholder="CVV"
            maxlength="3"
            required
          />
        </div>
      </div>

      <!-- Cashback -->
      <div class="form-section">
        <h3>Usar Cashback</h3>
        <input
          v-model.number="useCashback"
          type="number"
          step="0.01"
          min="0"
          placeholder="R$ 0,00"
        />
      </div>

      <!-- Observações -->
      <div class="form-section">
        <h3>Observações</h3>
        <textarea
          v-model="notes"
          placeholder="Ex: Sem cebola, ponto da carne, etc..."
          rows="2"
        ></textarea>
      </div>

      <!-- Erro -->
      <div v-if="error" class="error-message">
        ❌ {{ error }}
      </div>

      <!-- Botão -->
      <button
        type="submit"
        class="btn-checkout"
        :disabled="loading || cart.length === 0"
      >
        <span v-if="loading">Processando...</span>
        <span v-else>🛒 Finalizar Pedido - R$ {{ total.toFixed(2) }}</span>
      </button>
    </form>
  </div>
</template>

<style scoped>
.checkout-page {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}

.cart-summary {
  background: #f9f9f9;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 24px;
}

.cart-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #e0e0e0;
}

.totals {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 2px solid #e0e0e0;
}

.total-line {
  display: flex;
  justify-content: space-between;
  padding: 4px 0;
}

.total-line.total {
  font-size: 18px;
  font-weight: bold;
  color: #ff6b35;
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid #e0e0e0;
}

.total-line.discount {
  color: #28a745;
}

.checkout-form {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-section {
  margin-bottom: 24px;
}

.form-section h3 {
  margin-bottom: 12px;
  font-size: 16px;
  font-weight: 600;
}

.form-section input,
.form-section textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
}

.payment-methods {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
}

.payment-option {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.payment-option:has(input:checked) {
  border-color: #ff6b35;
  background: #fff5f2;
}

.card-details {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 8px;
  margin-top: 8px;
}

.error-message {
  background: #fee;
  color: #c00;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.btn-checkout {
  width: 100%;
  padding: 16px;
  background: #ff6b35;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-checkout:hover:not(:disabled) {
  background: #e85a25;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
}

.btn-checkout:disabled {
  background: #ccc;
  cursor: not-allowed;
}
</style>
```

---

## 📱 Página de PIX

```vue
<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const payment = ref(route.state?.payment || null)
const orderId = ref(route.params.orderId)

const copyPixCode = () => {
  navigator.clipboard.writeText(payment.value.qrcode_text)
  alert('✅ Código PIX copiado!')
}

// Polling para verificar se foi pago
const checkPaymentStatus = async () => {
  const token = localStorage.getItem('auth_token')

  const response = await fetch(`/api/v1/orders/${orderId.value}/payment`, {
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  })

  const data = await response.json()

  if (data.status === 'confirmed') {
    // Pago! Redirecionar
    window.location.href = `/order-success/${orderId.value}`
  }
}

onMounted(() => {
  // Verificar a cada 3 segundos
  setInterval(checkPaymentStatus, 3000)
})
</script>

<template>
  <div class="pix-page">
    <h1>Pagamento via PIX</h1>

    <div class="qrcode-container">
      <img :src="payment.qrcode_image" alt="QR Code PIX" />
    </div>

    <p class="instructions">
      Escaneie o QR Code acima com o app do seu banco<br>
      ou copie o código PIX abaixo:
    </p>

    <div class="pix-code">
      <code>{{ payment.qrcode_text }}</code>
      <button @click="copyPixCode" class="btn-copy">
        📋 Copiar Código
      </button>
    </div>

    <div class="waiting">
      <div class="spinner"></div>
      <p>Aguardando confirmação do pagamento...</p>
    </div>
  </div>
</template>
```

---

## 🎯 Checklist

- [ ] Instalar localStorage para carrinho
- [ ] Criar composable `useCart()`
- [ ] Criar página de checkout
- [ ] Implementar validação de formulário
- [ ] Adicionar loading states
- [ ] Criar página de PIX
- [ ] Implementar polling de status
- [ ] Testar fluxo completo

---

**Com isso, o botão "Finalizar Pedido" vai funcionar perfeitamente!** ✅
