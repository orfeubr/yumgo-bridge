<template>
  <div class="pizza-selector">
    <!-- Passo 1: Primeiro Sabor -->
    <div class="step">
      <h2 class="step-title">1️⃣ Escolha o primeiro sabor</h2>

      <input
        v-model="search1"
        type="text"
        class="search-box"
        placeholder="🔍 Buscar por sabor ou ingrediente..."
      />

      <div class="flavors-scroll">
        <div
          v-for="flavor in filteredFirst"
          :key="flavor.id"
          class="flavor-card"
          :class="{ selected: firstFlavor?.id === flavor.id }"
          @click="selectFirst(flavor)"
        >
          <span v-if="firstFlavor?.id === flavor.id" class="badge">✓ Selecionado</span>

          <img :src="flavor.image || '/placeholder.png'" :alt="flavor.name" />

          <div class="flavor-info">
            <h3>{{ flavor.name }}</h3>

            <!-- INGREDIENTES VISÍVEIS -->
            <p class="ingredients">
              <strong>🍕 Ingredientes:</strong> {{ flavor.ingredients }}
            </p>

            <p class="price">{{ flavor.price_formatted }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Passo 2: Segundo Sabor -->
    <div v-if="firstFlavor" class="step">
      <h2 class="step-title">2️⃣ Escolha o segundo sabor</h2>

      <input
        v-model="search2"
        type="text"
        class="search-box"
        placeholder="🔍 Buscar por sabor ou ingrediente..."
      />

      <div class="flavors-scroll">
        <div
          v-for="flavor in filteredSecond"
          :key="flavor.id"
          class="flavor-card"
          :class="{ selected: secondFlavor?.id === flavor.id }"
          @click="selectSecond(flavor)"
        >
          <span v-if="secondFlavor?.id === flavor.id" class="badge">✓ Selecionado</span>

          <img :src="flavor.image || '/placeholder.png'" :alt="flavor.name" />

          <div class="flavor-info">
            <h3>{{ flavor.name }}</h3>

            <!-- INGREDIENTES VISÍVEIS -->
            <p class="ingredients">
              <strong>🍕 Ingredientes:</strong> {{ flavor.ingredients }}
            </p>

            <p class="price">{{ flavor.price_formatted }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Resumo -->
    <div class="summary">
      <div v-if="error" class="error">{{ error }}</div>
      <div v-if="success" class="success">{{ success }}</div>

      <div v-if="firstFlavor" class="selected-item">
        <img :src="firstFlavor.image" />
        <div>
          <h4>1ª Metade: {{ firstFlavor.name }}</h4>
          <p>{{ firstFlavor.ingredients }}</p>
        </div>
      </div>

      <div v-if="secondFlavor" class="selected-item">
        <img :src="secondFlavor.image" />
        <div>
          <h4>2ª Metade: {{ secondFlavor.name }}</h4>
          <p>{{ secondFlavor.ingredients }}</p>
        </div>
      </div>

      <button
        @click="checkout"
        :disabled="!canCheckout || loading"
        class="btn-checkout"
      >
        <span v-if="loading">Processando...</span>
        <span v-else>🛒 Finalizar - {{ totalPrice }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const flavors = ref([])
const firstFlavor = ref(null)
const secondFlavor = ref(null)
const search1 = ref('')
const search2 = ref('')
const loading = ref(false)
const error = ref('')
const success = ref('')

// Filtros
const filteredFirst = computed(() => {
  if (!search1.value) return flavors.value
  const q = search1.value.toLowerCase()
  return flavors.value.filter(f =>
    f.name.toLowerCase().includes(q) ||
    f.ingredients?.toLowerCase().includes(q)
  )
})

const filteredSecond = computed(() => {
  if (!search2.value) return flavors.value
  const q = search2.value.toLowerCase()
  return flavors.value.filter(f =>
    f.name.toLowerCase().includes(q) ||
    f.ingredients?.toLowerCase().includes(q)
  )
})

const canCheckout = computed(() => firstFlavor.value && secondFlavor.value)

const totalPrice = computed(() => {
  if (!firstFlavor.value || !secondFlavor.value) return 'R$ 0,00'
  const max = Math.max(firstFlavor.value.price, secondFlavor.value.price)
  return `R$ ${max.toFixed(2).replace('.', ',')}`
})

// Métodos
const loadFlavors = async () => {
  try {
    const res = await fetch('/api/v1/products/pizza/flavors?per_page=50')
    const data = await res.json()
    flavors.value = data.data
  } catch (err) {
    error.value = 'Erro ao carregar sabores'
  }
}

const selectFirst = (flavor) => {
  firstFlavor.value = flavor
  error.value = ''
}

const selectSecond = (flavor) => {
  secondFlavor.value = flavor
  error.value = ''
}

const checkout = async () => {
  if (!canCheckout.value) return

  loading.value = true
  error.value = ''

  try {
    const token = localStorage.getItem('auth_token')

    const res = await fetch('/api/v1/orders', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        items: [{
          product_id: firstFlavor.value.id,
          quantity: 1,
          half_and_half: {
            product_id: secondFlavor.value.id,
          },
        }],
        delivery_address: 'Rua Teste, 123', // Pegar de formulário
        payment_method: 'pix',
      }),
    })

    const data = await res.json()

    if (!res.ok) throw new Error(data.message)

    success.value = `Pedido #${data.order.order_number} criado!`

    setTimeout(() => {
      router.push(`/order-success/${data.order.id}`)
    }, 2000)

  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

onMounted(loadFlavors)
</script>

<style scoped>
.pizza-selector {
  max-width: 600px;
  margin: 0 auto;
}

.step {
  margin-bottom: 24px;
}

.step-title {
  font-size: 18px;
  font-weight: 600;
  padding: 16px;
  background: #f9f9f9;
  border-radius: 8px;
  margin-bottom: 12px;
}

.search-box {
  width: 100%;
  padding: 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  margin-bottom: 12px;
}

.flavors-scroll {
  max-height: 400px;
  overflow-y: auto;
  padding-right: 8px;
}

.flavor-card {
  display: flex;
  gap: 12px;
  padding: 12px;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  margin-bottom: 12px;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.flavor-card:hover {
  border-color: #ff6b35;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 107, 53, 0.15);
}

.flavor-card.selected {
  border-color: #ff6b35;
  background: #fff5f2;
}

.badge {
  position: absolute;
  top: 8px;
  right: 8px;
  background: #ff6b35;
  color: white;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.flavor-card img {
  width: 80px;
  height: 80px;
  border-radius: 8px;
  object-fit: cover;
}

.flavor-info h3 {
  font-size: 16px;
  margin-bottom: 4px;
}

.ingredients {
  font-size: 13px;
  color: #666;
  line-height: 1.4;
  margin-bottom: 8px;
}

.ingredients strong {
  color: #ff6b35;
}

.price {
  font-size: 18px;
  font-weight: 700;
  color: #ff6b35;
}

.summary {
  padding: 20px;
  background: #f9f9f9;
  border-radius: 12px;
}

.selected-item {
  display: flex;
  gap: 12px;
  padding: 12px;
  background: white;
  border-radius: 8px;
  margin-bottom: 12px;
}

.selected-item img {
  width: 60px;
  height: 60px;
  border-radius: 8px;
}

.btn-checkout {
  width: 100%;
  padding: 16px;
  background: #ff6b35;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 18px;
  font-weight: 700;
  cursor: pointer;
}

.btn-checkout:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.error {
  background: #fee;
  color: #c00;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 12px;
}

.success {
  background: #efe;
  color: #060;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 12px;
}
</style>
