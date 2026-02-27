# Persistência do Carrinho (LocalStorage)

## 🛒 Problema

O carrinho limpa quando a página é atualizada porque os dados estão apenas em memória (state).

## ✅ Solução

Salvar carrinho no **localStorage** do navegador.

---

## 📱 Implementação Vue.js 3

### 1. Composable de Carrinho

Criar `composables/useCart.js`:

```javascript
import { ref, watch } from 'vue'

const CART_STORAGE_KEY = 'deliverypro_cart'

export function useCart() {
  // Carregar do localStorage
  const loadCart = () => {
    try {
      const saved = localStorage.getItem(CART_STORAGE_KEY)
      return saved ? JSON.parse(saved) : []
    } catch (e) {
      console.error('Erro ao carregar carrinho:', e)
      return []
    }
  }

  const cart = ref(loadCart())

  // Salvar automaticamente quando mudar
  watch(cart, (newCart) => {
    try {
      localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(newCart))
    } catch (e) {
      console.error('Erro ao salvar carrinho:', e)
    }
  }, { deep: true })

  // Adicionar item
  const addItem = (product, options = {}) => {
    const item = {
      id: product.id,
      name: product.name,
      image: product.image,
      price: product.price,
      ingredients: product.ingredients,
      quantity: options.quantity || 1,
      variation: options.variation || null,
      addons: options.addons || [],
      halfAndHalf: options.halfAndHalf || null,
      notes: options.notes || '',
      subtotal: calculateItemSubtotal(product, options),
    }

    // Verifica se item já existe (mesmo produto + mesmas opções)
    const existingIndex = cart.value.findIndex(i =>
      i.id === item.id &&
      JSON.stringify(i.variation) === JSON.stringify(item.variation) &&
      JSON.stringify(i.addons) === JSON.stringify(item.addons) &&
      JSON.stringify(i.halfAndHalf) === JSON.stringify(item.halfAndHalf)
    )

    if (existingIndex >= 0) {
      cart.value[existingIndex].quantity += item.quantity
      cart.value[existingIndex].subtotal = calculateItemSubtotal(
        product,
        { ...options, quantity: cart.value[existingIndex].quantity }
      )
    } else {
      cart.value.push(item)
    }
  }

  // Remover item
  const removeItem = (index) => {
    cart.value.splice(index, 1)
  }

  // Atualizar quantidade
  const updateQuantity = (index, quantity) => {
    if (quantity <= 0) {
      removeItem(index)
      return
    }

    cart.value[index].quantity = quantity
    cart.value[index].subtotal = cart.value[index].price * quantity
  }

  // Limpar carrinho
  const clearCart = () => {
    cart.value = []
    localStorage.removeItem(CART_STORAGE_KEY)
  }

  // Calcular totais
  const subtotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.subtotal, 0)
  })

  const itemCount = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.quantity, 0)
  })

  return {
    cart,
    addItem,
    removeItem,
    updateQuantity,
    clearCart,
    subtotal,
    itemCount,
  }
}

// Helpers
function calculateItemSubtotal(product, options) {
  let total = product.price * (options.quantity || 1)

  // Adiciona preço dos adicionais
  if (options.addons && options.addons.length > 0) {
    options.addons.forEach(addon => {
      total += addon.price
    })
  }

  // Pizza meio a meio: cobra pelo maior preço
  if (options.halfAndHalf) {
    const maxPrice = Math.max(product.price, options.halfAndHalf.price)
    total = maxPrice * (options.quantity || 1)
  }

  return total
}
```

### 2. Usar no Componente

```vue
<script setup>
import { useCart } from '@/composables/useCart'

const { cart, addItem, removeItem, updateQuantity, clearCart, subtotal, itemCount } = useCart()

// Adicionar pizza meio a meio
const addHalfAndHalfPizza = (firstFlavor, secondFlavor, size) => {
  addItem(firstFlavor, {
    quantity: 1,
    variation: size,
    halfAndHalf: {
      product_id: secondFlavor.id,
      name: secondFlavor.name,
      ingredients: secondFlavor.ingredients,
      price: secondFlavor.price,
    },
  })
}

// Finalizar pedido
const checkout = async () => {
  const items = cart.value.map(item => ({
    product_id: item.id,
    quantity: item.quantity,
    variation_id: item.variation?.id || null,
    addons: item.addons.map(a => a.id),
    half_and_half: item.halfAndHalf ? {
      product_id: item.halfAndHalf.product_id,
      variation_id: item.variation?.id || null,
    } : null,
    notes: item.notes,
  }))

  try {
    const response = await fetch('/api/v1/orders', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        items,
        delivery_address: address,
        payment_method: paymentMethod,
      }),
    })

    if (response.ok) {
      clearCart() // Limpa após sucesso
      router.push('/order-success')
    }
  } catch (error) {
    console.error('Erro ao criar pedido:', error)
  }
}
</script>

<template>
  <div class="cart">
    <h2>Carrinho ({{ itemCount }} itens)</h2>

    <div v-for="(item, index) in cart" :key="index" class="cart-item">
      <img :src="item.image" :alt="item.name" />

      <div class="item-info">
        <h3>{{ item.name }}</h3>

        <!-- INGREDIENTES VISÍVEIS -->
        <p class="ingredients">
          <strong>Ingredientes:</strong> {{ item.ingredients }}
        </p>

        <!-- Meio a meio -->
        <p v-if="item.halfAndHalf" class="half-and-half">
          <strong>+ Meio a Meio:</strong> {{ item.halfAndHalf.name }}<br>
          <small>{{ item.halfAndHalf.ingredients }}</small>
        </p>

        <!-- Adicionais -->
        <p v-if="item.addons.length > 0" class="addons">
          <strong>Adicionais:</strong>
          {{ item.addons.map(a => a.name).join(', ') }}
        </p>

        <!-- Observações -->
        <p v-if="item.notes" class="notes">
          <strong>Obs:</strong> {{ item.notes }}
        </p>
      </div>

      <div class="item-actions">
        <div class="quantity-control">
          <button @click="updateQuantity(index, item.quantity - 1)">-</button>
          <span>{{ item.quantity }}</span>
          <button @click="updateQuantity(index, item.quantity + 1)">+</button>
        </div>

        <p class="price">R$ {{ item.subtotal.toFixed(2) }}</p>

        <button @click="removeItem(index)" class="remove">🗑️</button>
      </div>
    </div>

    <div class="cart-total">
      <h3>Subtotal: R$ {{ subtotal.toFixed(2) }}</h3>
      <button @click="checkout" :disabled="cart.length === 0">
        Finalizar Pedido
      </button>
    </div>
  </div>
</template>
```

---

## 📊 Pinia Store (Alternativa)

### stores/cart.js

```javascript
import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: JSON.parse(localStorage.getItem('deliverypro_cart') || '[]'),
  }),

  getters: {
    itemCount: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
    subtotal: (state) => state.items.reduce((sum, item) => sum + item.subtotal, 0),
  },

  actions: {
    addItem(product, options) {
      // ... mesma lógica do composable
      this.saveToStorage()
    },

    removeItem(index) {
      this.items.splice(index, 1)
      this.saveToStorage()
    },

    updateQuantity(index, quantity) {
      if (quantity <= 0) {
        this.removeItem(index)
        return
      }
      this.items[index].quantity = quantity
      this.items[index].subtotal = this.items[index].price * quantity
      this.saveToStorage()
    },

    clearCart() {
      this.items = []
      localStorage.removeItem('deliverypro_cart')
    },

    saveToStorage() {
      localStorage.setItem('deliverypro_cart', JSON.stringify(this.items))
    },
  },
})
```

---

## 🔐 Segurança

### Validar no Backend

**NUNCA confie nos preços do frontend!**

O backend deve:
1. Ignorar preços enviados pelo cliente
2. Recalcular tudo com base nos IDs dos produtos
3. Validar estoque e disponibilidade

```php
// ✅ CORRETO - Backend recalcula preços
$product = Product::findOrFail($item['product_id']);
$unitPrice = $product->price;

// ❌ ERRADO - Confiar no preço do frontend
$unitPrice = $item['price']; // Cliente pode manipular!
```

---

## 🧹 Limpeza Automática

### Limpar carrinho antigo (7 dias)

```javascript
const CART_EXPIRY_DAYS = 7

function loadCart() {
  const saved = localStorage.getItem(CART_STORAGE_KEY)
  if (!saved) return []

  const data = JSON.parse(saved)
  const savedAt = data.savedAt || 0
  const now = Date.now()

  // Se tem mais de 7 dias, limpa
  if (now - savedAt > CART_EXPIRY_DAYS * 24 * 60 * 60 * 1000) {
    localStorage.removeItem(CART_STORAGE_KEY)
    return []
  }

  return data.items || []
}

function saveCart(items) {
  localStorage.setItem(CART_STORAGE_KEY, JSON.stringify({
    items,
    savedAt: Date.now(),
  }))
}
```

---

## 🎯 Checklist

- [x] Carrinho salvo no localStorage
- [x] Carrinho restaurado ao abrir página
- [x] Ingredientes visíveis no carrinho
- [x] Pizza meio a meio mostra ambos sabores
- [x] Adicionais listados
- [x] Observações exibidas
- [x] Carrinho limpa após pedido bem-sucedido
- [x] Backend recalcula preços (segurança)

---

**Última atualização**: 21/02/2026
