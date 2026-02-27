# API de Sabores de Pizza (Scroll Infinito)

## 📋 Visão Geral

Endpoint otimizado para seleção de sabores de pizza em modo **meio a meio** com **scroll infinito** e exibição de **recheio**.

## 🎯 Endpoint

```
GET /api/v1/products/pizza/flavors
```

**Autenticação**: Não requerida (público)

## 📝 Parâmetros

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `search` | string | Não | Busca por nome ou recheio |
| `per_page` | integer | Não | Itens por página (padrão: 20) |
| `page` | integer | Não | Número da página |

## 📤 Resposta

```json
{
  "data": [
    {
      "id": 1,
      "name": "Pizza de Calabresa",
      "filling": "Calabresa fatiada, cebola, azeitonas e orégano",
      "price": 45.00,
      "image": "https://exemplo.com/calabresa.jpg",
      "description": "A clássica pizza de calabresa",
      "category_name": "Pizzas Salgadas"
    },
    {
      "id": 2,
      "name": "Pizza Margherita",
      "filling": "Mussarela, tomate, manjericão fresco e azeite",
      "price": 42.00,
      "image": "https://exemplo.com/margherita.jpg",
      "description": "Tradicional pizza italiana",
      "category_name": "Pizzas Salgadas"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 95,
    "has_more": true
  }
}
```

## 💡 Exemplos de Uso

### 1. Carregar Primeira Página

```bash
GET /api/v1/products/pizza/flavors?per_page=20
```

### 2. Scroll - Carregar Próxima Página

```bash
GET /api/v1/products/pizza/flavors?per_page=20&page=2
```

### 3. Buscar Sabores

```bash
GET /api/v1/products/pizza/flavors?search=calabresa
```

Busca por nome **ou** recheio.

### 4. Buscar por Recheio Específico

```bash
GET /api/v1/products/pizza/flavors?search=mussarela
```

Retorna todas pizzas que contenham "mussarela" no nome ou recheio.

## 📱 Implementação no App Mobile

### Vue.js / React Native

```javascript
export default {
  data() {
    return {
      flavors: [],
      page: 1,
      perPage: 20,
      loading: false,
      hasMore: true,
    }
  },

  methods: {
    async loadFlavors() {
      if (this.loading || !this.hasMore) return;

      this.loading = true;

      const response = await fetch(
        `/api/v1/products/pizza/flavors?page=${this.page}&per_page=${this.perPage}`
      );

      const data = await response.json();

      this.flavors.push(...data.data);
      this.hasMore = data.pagination.has_more;
      this.page++;
      this.loading = false;
    },

    onScroll(event) {
      const { scrollTop, scrollHeight, clientHeight } = event.target;

      // Carrega mais quando chegar a 80% do scroll
      if (scrollTop + clientHeight >= scrollHeight * 0.8) {
        this.loadFlavors();
      }
    },

    async searchFlavors(query) {
      this.flavors = [];
      this.page = 1;
      this.hasMore = true;

      const response = await fetch(
        `/api/v1/products/pizza/flavors?search=${query}&per_page=${this.perPage}`
      );

      const data = await response.json();
      this.flavors = data.data;
      this.hasMore = data.pagination.has_more;
      this.page = 2;
    }
  },

  mounted() {
    this.loadFlavors();
  }
}
```

### Template HTML

```html
<div class="pizza-flavors" @scroll="onScroll">
  <!-- Barra de Busca -->
  <div class="search-bar">
    <input
      type="text"
      placeholder="Buscar sabor ou recheio..."
      @input="searchFlavors($event.target.value)"
    />
  </div>

  <!-- Lista de Sabores -->
  <div class="flavors-list">
    <div
      v-for="flavor in flavors"
      :key="flavor.id"
      class="flavor-card"
      @click="selectFlavor(flavor)"
    >
      <img :src="flavor.image" :alt="flavor.name" />

      <div class="flavor-info">
        <h3>{{ flavor.name }}</h3>

        <!-- RECHEIO EM DESTAQUE -->
        <p class="filling">
          <strong>Recheio:</strong> {{ flavor.filling }}
        </p>

        <p class="price">R$ {{ flavor.price.toFixed(2) }}</p>
      </div>
    </div>
  </div>

  <!-- Loading Indicator -->
  <div v-if="loading" class="loading">
    Carregando mais sabores...
  </div>
</div>
```

### CSS Sugerido

```css
.pizza-flavors {
  max-height: 70vh;
  overflow-y: auto;
  padding: 16px;
}

.flavor-card {
  display: flex;
  gap: 12px;
  padding: 12px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  margin-bottom: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.flavor-card:hover {
  border-color: #ff6b35;
  box-shadow: 0 2px 8px rgba(255, 107, 53, 0.1);
}

.flavor-card img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
}

.flavor-info {
  flex: 1;
}

.flavor-info h3 {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 4px;
  color: #333;
}

.flavor-info .filling {
  font-size: 13px;
  color: #666;
  margin-bottom: 8px;
  line-height: 1.4;
}

.flavor-info .filling strong {
  color: #ff6b35;
  font-weight: 600;
}

.flavor-info .price {
  font-size: 18px;
  font-weight: 700;
  color: #ff6b35;
}

.loading {
  text-align: center;
  padding: 16px;
  color: #999;
}
```

## 🎨 UX/UI Recomendações

### Seleção Meio a Meio

```html
<!-- Primeiro Sabor -->
<div class="half-selection">
  <h2>1ª Metade</h2>
  <div class="selected-flavor">
    <img :src="firstHalf.image" />
    <div>
      <h3>{{ firstHalf.name }}</h3>
      <p class="filling">{{ firstHalf.filling }}</p>
      <span class="price">R$ {{ firstHalf.price }}</span>
    </div>
  </div>
</div>

<!-- Segundo Sabor (COM SCROLL) -->
<div class="half-selection">
  <h2>2ª Metade</h2>

  <!-- Lista em Scroll -->
  <div class="flavors-scroll" @scroll="onScroll">
    <div
      v-for="flavor in flavors"
      :key="flavor.id"
      class="flavor-option"
      @click="selectSecondHalf(flavor)"
    >
      <img :src="flavor.image" />
      <div>
        <h4>{{ flavor.name }}</h4>
        <p class="filling-preview">{{ flavor.filling }}</p>
      </div>
      <span class="price">+R$ {{ calculateHalfPrice(flavor) }}</span>
    </div>
  </div>
</div>
```

### Cálculo Meio a Meio

```javascript
calculateHalfPrice(flavor) {
  // Cobra pelo maior valor
  const maxPrice = Math.max(this.firstHalf.price, flavor.price);
  return maxPrice.toFixed(2);
}
```

## 🔍 Filtros Avançados (Futuro)

Adicionar filtros por:
- Categoria (Salgadas, Doces, Especiais)
- Faixa de preço
- Ingredientes (sem cebola, sem tomate, etc)
- Vegetarianas/Veganas

```bash
GET /api/v1/products/pizza/flavors?category=salgadas&max_price=50&vegetarian=true
```

## 📊 Performance

- **Cache**: Considere cachear os sabores por 5-10 minutos
- **Lazy Loading**: Imagens carregam conforme scroll
- **Debounce**: Na busca, aguarde 300ms antes de pesquisar
- **Paginação**: 20 itens por página balanceia UX e performance

## 🚀 Melhorias Futuras

1. **Imagens Otimizadas**: Retornar thumbs (150x150) para scroll
2. **Favoritos**: Marcar sabores preferidos
3. **Últimos Pedidos**: Mostrar sabores já pedidos no topo
4. **Recomendações**: IA sugere combinações populares

---

**Última atualização**: 21/02/2026
