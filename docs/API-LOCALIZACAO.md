# 📍 API de Localização - Validação de Cidades e Bairros

## 🎯 Objetivo

O sistema **APENAS permite** que clientes selecionem cidades e bairros **cadastrados e habilitados** no painel de administração (`/painel/bairros`).

---

## 🔐 Validações Implementadas

### ✅ Backend
- **OrderController**: Valida cidade/bairro ao criar pedido
- **CustomerController**: Valida cidade/bairro ao criar/editar endereço
- **Retorno 422**: Se bairro não atendido

### ✅ API Endpoints
- Listar apenas cidades habilitadas
- Listar apenas bairros habilitados de uma cidade
- Buscar taxa de entrega por bairro

---

## 📡 Endpoints Disponíveis

### 1. **Listar Cidades Habilitadas**
Retorna apenas cidades que possuem bairros habilitados.

```http
GET /api/v1/location/enabled-cities
```

**Response:**
```json
{
  "success": true,
  "total": 3,
  "data": [
    "Jundiaí",
    "Vinhedo",
    "Itupeva"
  ]
}
```

---

### 2. **Listar Bairros Habilitados de uma Cidade**
Retorna apenas bairros habilitados para delivery.

```http
GET /api/v1/location/enabled-neighborhoods/{city}
```

**Exemplo:**
```
GET /api/v1/location/enabled-neighborhoods/Jundiaí
```

**Response:**
```json
{
  "success": true,
  "city": "Jundiaí",
  "total": 5,
  "neighborhoods": [
    {
      "id": 1,
      "name": "Centro",
      "fee": 5.00,
      "time": "30-45 min",
      "minimum_order": 20.00
    },
    {
      "id": 2,
      "name": "Vila Arens",
      "fee": 6.50,
      "time": "35-50 min",
      "minimum_order": null
    }
  ]
}
```

---

### 3. **Buscar por CEP**
Retorna endereço e verifica se o bairro é atendido.

```http
GET /api/v1/location/cep/{cep}
```

**Exemplo:**
```
GET /api/v1/location/cep/13201005
```

**Response (Bairro Atendido):**
```json
{
  "success": true,
  "address": {
    "zipcode": "13201-005",
    "street": "Avenida Nove de Julho",
    "neighborhood": "Centro",
    "city": "Jundiaí",
    "state": "SP"
  },
  "neighborhood_info": {
    "name": "Centro",
    "fee": 5.00,
    "time": "30-45 min",
    "available": true
  }
}
```

**Response (Bairro NÃO Atendido):**
```json
{
  "success": true,
  "address": {
    "zipcode": "13201-005",
    "street": "Avenida Nove de Julho",
    "neighborhood": "Bairro Distante",
    "city": "Jundiaí",
    "state": "SP"
  },
  "neighborhood_info": {
    "name": "Bairro Distante",
    "available": false,
    "message": "Não entregamos neste bairro"
  }
}
```

---

## 🛡️ Validações ao Criar Pedido

### Endpoint: `POST /api/v1/orders`

**Request:**
```json
{
  "items": [...],
  "delivery_city": "Jundiaí",
  "delivery_neighborhood": "Centro",
  "delivery_address": "Rua X, 123",
  "payment_method": "pix"
}
```

**Validações:**
1. ✅ `delivery_city` e `delivery_neighborhood` são **obrigatórios**
2. ✅ Sistema busca bairro no banco: `Neighborhood::getFeeByName(city, neighborhood)`
3. ✅ Se bairro **não existir** ou **não estiver habilitado** → **Erro 422**
4. ✅ Se OK → Calcula taxa automaticamente

**Erro (Bairro não atendido):**
```json
{
  "message": "Não atendemos o bairro informado. Por favor, selecione um bairro válido."
}
```

**Sucesso:**
```json
{
  "message": "Pedido criado com sucesso!",
  "order": {
    "id": 123,
    "order_number": "20260223-ABC123",
    "subtotal": 50.00,
    "delivery_fee": 5.00,
    "total": 55.00,
    "delivery_city": "Jundiaí",
    "delivery_neighborhood": "Centro"
  }
}
```

---

## 🏠 Validações ao Criar/Editar Endereço

### Endpoint: `POST /api/v1/customer/addresses`

**Request:**
```json
{
  "label": "Casa",
  "city": "Jundiaí",
  "neighborhood": "Centro",
  "street": "Rua X",
  "number": "123",
  "complement": "Apto 45",
  "zipcode": "13201-005",
  "is_default": true
}
```

**Validações:**
1. ✅ Verifica se bairro está habilitado no banco
2. ✅ Se não estiver → **Erro 422**
3. ✅ Se OK → Salva e retorna info de entrega

**Erro:**
```json
{
  "message": "Não atendemos este bairro. Por favor, selecione um bairro disponível.",
  "error": "neighborhood_not_available"
}
```

**Sucesso:**
```json
{
  "message": "Endereço criado com sucesso!",
  "data": {
    "id": 1,
    "city": "Jundiaí",
    "neighborhood": "Centro",
    "street": "Rua X",
    "number": "123"
  },
  "delivery_info": {
    "fee": 5.00,
    "time": "30-45 min"
  }
}
```

---

## 🎨 Implementação no Frontend

### Fluxo Recomendado (Checkout/Cadastro)

```javascript
// 1. Carregar cidades habilitadas
const loadCities = async () => {
  const response = await fetch('/api/v1/location/enabled-cities');
  const data = await response.json();

  // Popular select: data.data = ["Jundiaí", "Vinhedo", "Itupeva"]
  populateCitySelect(data.data);
};

// 2. Quando usuário seleciona cidade, carregar bairros
const loadNeighborhoods = async (city) => {
  const response = await fetch(`/api/v1/location/enabled-neighborhoods/${city}`);
  const data = await response.json();

  // Popular select com bairros
  populateNeighborhoodSelect(data.neighborhoods);
};

// 3. Ao criar pedido, enviar cidade e bairro
const createOrder = async () => {
  const response = await fetch('/api/v1/orders', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      items: cart.items,
      delivery_city: selectedCity,
      delivery_neighborhood: selectedNeighborhood,
      delivery_address: fullAddress,
      payment_method: 'pix'
    })
  });

  if (response.status === 422) {
    // Bairro não atendido
    alert('Não entregamos neste bairro');
  }
};
```

### Exibir Taxa de Entrega em Tempo Real

```javascript
// Quando usuário seleciona bairro
const onNeighborhoodSelected = (neighborhoodData) => {
  // neighborhoodData vem da API enabled-neighborhoods
  document.getElementById('delivery-fee').textContent =
    `R$ ${neighborhoodData.fee.toFixed(2)}`;

  document.getElementById('delivery-time').textContent =
    neighborhoodData.time;

  // Atualizar total
  updateTotal();
};
```

---

## 🧪 Como Testar

### 1. Cadastrar Bairros no Painel
```
URL: https://marmitaria-gi.yumgo.com.br/painel/bairros
```

1. Clique em **"Cadastrar Bairros"**
2. Digite a cidade (ex: Jundiaí)
3. Bairros serão criados como **INATIVOS**
4. Ative os bairros desejados e configure:
   - Taxa de entrega
   - Tempo de entrega
   - Pedido mínimo (opcional)

### 2. Testar API
```bash
# Listar cidades habilitadas
curl https://marmitaria-gi.yumgo.com.br/api/v1/location/enabled-cities

# Listar bairros de Jundiaí
curl https://marmitaria-gi.yumgo.com.br/api/v1/location/enabled-neighborhoods/Jundiaí

# Buscar CEP
curl https://marmitaria-gi.yumgo.com.br/api/v1/location/cep/13201005
```

### 3. Testar Validação
```bash
# Tentar criar pedido com bairro não habilitado (deve retornar erro 422)
curl -X POST https://marmitaria-gi.yumgo.com.br/api/v1/orders \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [...],
    "delivery_city": "Jundiaí",
    "delivery_neighborhood": "BairroInexistente",
    "delivery_address": "Rua X, 123",
    "payment_method": "pix"
  }'
```

---

## ✅ Checklist de Implementação

- [x] API de cidades habilitadas
- [x] API de bairros habilitados por cidade
- [x] Validação no OrderController
- [x] Validação no CustomerController (endereços)
- [x] Cálculo automático de taxa de entrega
- [x] Migration para campos city/neighborhood em orders
- [x] Retorno 422 com mensagem clara
- [x] Documentação completa

---

## 🚀 Benefícios

1. **Impossível criar pedido fora da área de entrega**
2. **Taxa calculada automaticamente** (sem input manual)
3. **UX melhorada**: Cliente vê apenas opções disponíveis
4. **Gestão centralizada**: Admin controla tudo no painel
5. **Auditoria**: Todos os pedidos têm cidade/bairro registrados

---

## 📞 Suporte

Se precisar adicionar novos bairros:
1. Acesse: `/painel/bairros`
2. Clique em **"Cadastrar Bairros"**
3. Digite a cidade
4. Ative os bairros desejados
5. Configure taxas e tempos

**Pronto!** Os bairros estarão disponíveis automaticamente na API.
