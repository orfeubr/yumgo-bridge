# API REST - DeliveryPro

## 📋 Visão Geral

API RESTful para integração com apps mobile e sistemas externos.

**Base URL:** `https://{tenant-slug}.yumgo.com.br/api/v1`

**Formato:** JSON

**Autenticação:** Bearer Token (Sanctum)

---

## 🔐 Autenticação

### POST `/register`
Registra novo cliente

**Rate Limit:** 3 req/min

**Body:**
```json
{
  "name": "João Silva",
  "phone": "11987654321",
  "email": "joao@email.com",
  "password": "senha123",
  "password_confirmation": "senha123",
  "birth_date": "1990-05-15"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Cliente registrado com sucesso!",
  "customer": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@email.com",
    "phone": "11987654321",
    "cashback_balance": 0,
    "loyalty_tier": "bronze"
  },
  "token": "1|abc123..."
}
```

---

### POST `/login`
Autentica cliente

**Rate Limit:** 5 req/min

**Body:**
```json
{
  "identifier": "11987654321",  // celular ou email
  "password": "senha123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login realizado com sucesso!",
  "customer": {
    "id": 1,
    "name": "João Silva",
    "cashback_balance": 15.50,
    "loyalty_tier": "bronze",
    "total_orders": 10,
    "total_spent": 500.00
  },
  "token": "2|xyz789..."
}
```

---

### POST `/logout`
Desautentica cliente

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "message": "Logout realizado com sucesso!"
}
```

---

### GET `/me`
Dados do cliente autenticado

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "customer": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@email.com",
    "phone": "11987654321",
    "cashback_balance": 15.50,
    "loyalty_tier": "bronze",
    "total_orders": 10,
    "total_spent": 500.00,
    "created_at": "2026-01-15"
  }
}
```

---

## 🍕 Produtos

### GET `/products`
Lista produtos (paginado)

**Rate Limit:** 60 req/min

**Query Params:**
- `page`: Página (padrão: 1)
- `per_page`: Items por página (padrão: 15)
- `search`: Busca por nome
- `category_id`: Filtrar por categoria

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Pizza Margherita",
      "description": "Molho, queijo e manjericão",
      "price": 45.00,
      "image_url": "https://...",
      "category": {
        "id": 1,
        "name": "Pizzas"
      },
      "is_available": true,
      "preparation_time": 30
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

---

### GET `/products/{id}`
Detalhes do produto

**Rate Limit:** 60 req/min

**Response (200):**
```json
{
  "id": 1,
  "name": "Pizza Margherita",
  "description": "Molho, queijo e manjericão",
  "price": 45.00,
  "image_url": "https://...",
  "category": {
    "id": 1,
    "name": "Pizzas"
  },
  "variations": [
    {
      "id": 1,
      "name": "Tamanho",
      "options": [
        {"id": 1, "name": "Pequena", "price_modifier": 0},
        {"id": 2, "name": "Grande", "price_modifier": 10.00}
      ]
    }
  ],
  "addons": [
    {
      "id": 1,
      "name": "Borda Recheada",
      "price": 8.00
    }
  ],
  "is_available": true,
  "preparation_time": 30
}
```

---

## 📦 Categorias

### GET `/categories`
Lista categorias

**Rate Limit:** 60 req/min

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Pizzas",
      "slug": "pizzas",
      "products_count": 25
    }
  ]
}
```

---

## 🛒 Pedidos

### GET `/orders`
Lista pedidos do cliente

**Headers:** `Authorization: Bearer {token}`

**Rate Limit:** 60 req/min

**Response (200):**
```json
{
  "data": [
    {
      "id": 123,
      "order_number": "20260309-001",
      "status": "confirmed",
      "payment_status": "paid",
      "subtotal": 45.00,
      "delivery_fee": 5.00,
      "cashback_used": 0,
      "total": 50.00,
      "cashback_earned": 2.25,
      "created_at": "2026-03-09T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 10,
    "total": 15
  }
}
```

---

### GET `/orders/{id}`
Detalhes do pedido

**Headers:** `Authorization: Bearer {token}`

**Rate Limit:** 60 req/min

**Response (200):**
```json
{
  "id": 123,
  "order_number": "20260309-001",
  "status": "confirmed",
  "payment_status": "paid",
  "subtotal": 45.00,
  "delivery_fee": 5.00,
  "cashback_used": 0,
  "total": 50.00,
  "cashback_earned": 2.25,
  "delivery_address": "Rua Exemplo, 123",
  "created_at": "2026-03-09T10:30:00Z",
  "items": [
    {
      "product_name": "Pizza Margherita",
      "quantity": 1,
      "unit_price": 45.00,
      "subtotal": 45.00,
      "notes": "Sem cebola"
    }
  ]
}
```

---

### POST `/orders`
Cria novo pedido

**Headers:** `Authorization: Bearer {token}`

**Rate Limit:** 30 req/hora

**Body:**
```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "variation_id": 2,
      "addons": [1, 3],
      "notes": "Sem cebola"
    }
  ],
  "delivery_address": "Rua Exemplo, 123",
  "delivery_city": "São Paulo",
  "delivery_neighborhood": "Centro",
  "payment_method": "pix",
  "use_cashback": true,
  "coupon_code": "DESCONTO10",
  "notes": "Entregar no portão"
}
```

**Response (201):**
```json
{
  "message": "Pedido criado com sucesso!",
  "order": {
    "id": 123,
    "order_number": "20260309-001",
    "total": 50.00,
    "payment_status": "pending"
  },
  "payment": {
    "method": "pix",
    "qrcode_image": "data:image/png;base64,...",
    "qrcode_text": "00020126...",
    "transaction_id": "chr_abc123"
  }
}
```

---

### POST `/orders/{orderNumber}/pay-with-card`
Processa pagamento com cartão tokenizado

**Headers:** `Authorization: Bearer {token}`

**Rate Limit:** 5 req/min

**Body:**
```json
{
  "card_id": "card_abc123",  // Token do Pagar.me JS SDK
  "method": "credit_card",
  "installments": 1
}
```

**Response (200):**
```json
{
  "message": "Pagamento aprovado!",
  "status": "paid",
  "order_number": "20260309-001"
}
```

---

## 💰 Cashback

### GET `/cashback/balance`
Saldo de cashback do cliente

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "balance": 15.50,
  "loyalty_tier": "bronze",
  "next_expiration": {
    "amount": 5.00,
    "expires_at": "2026-06-15"
  }
}
```

---

### GET `/cashback/transactions`
Histórico de cashback

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "type": "earned",
      "amount": 2.25,
      "balance_before": 13.25,
      "balance_after": 15.50,
      "description": "Cashback ganho no pedido #20260309-001",
      "created_at": "2026-03-09T10:35:00Z",
      "expires_at": "2026-09-09"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "total": 25
  }
}
```

---

## 🎟️ Cupons

### POST `/coupons/validate`
Valida cupom de desconto

**Rate Limit:** 30 req/min

**Body:**
```json
{
  "code": "DESCONTO10",
  "total": 50.00
}
```

**Response (200):**
```json
{
  "valid": true,
  "coupon": {
    "code": "DESCONTO10",
    "type": "percentage",
    "value": 10,
    "discount_amount": 5.00,
    "message": "Cupom aplicado com sucesso!"
  }
}
```

**Response (422) - Inválido:**
```json
{
  "valid": false,
  "message": "Cupom inválido ou expirado"
}
```

---

## ⚙️ Configurações

### GET `/settings`
Configurações do restaurante

**Rate Limit:** 60 req/min

**Response (200):**
```json
{
  "restaurant_name": "Marmitaria da Gi",
  "phone": "11987654321",
  "business_hours": {
    "segunda": "11:00 - 14:00",
    "terça": "11:00 - 14:00"
  },
  "min_order_value": 20.00,
  "delivery_time_estimate": "30-45 minutos",
  "is_open": true
}
```

---

## 📍 Localização

### GET `/location/enabled-cities`
Cidades atendidas

**Response (200):**
```json
{
  "cities": ["São Paulo", "Guarulhos"]
}
```

---

### GET `/location/neighborhoods`
Bairros atendidos e taxas

**Query Params:**
- `city`: Cidade (obrigatório)

**Response (200):**
```json
{
  "neighborhoods": [
    {
      "id": 1,
      "name": "Centro",
      "city": "São Paulo",
      "delivery_fee": 5.00,
      "delivery_time": 30,
      "enabled": true
    }
  ]
}
```

---

## ❌ Erros Comuns

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 422 Validation Error
```json
{
  "message": "Os dados fornecidos são inválidos.",
  "errors": {
    "email": ["O campo email é obrigatório."]
  }
}
```

### 429 Too Many Requests
```json
{
  "message": "Too Many Attempts."
}
```

### 500 Internal Server Error
```json
{
  "message": "Erro interno do servidor. Por favor, tente novamente."
}
```

---

## 🔒 Segurança

### Headers Obrigatórios
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
```

### Rate Limiting
- Autenticação: 3-5 req/min
- Leitura: 60 req/min
- Escrita: 30 req/hora
- Webhooks: 100 req/min

### Tokenização de Cartões
**⚠️ NUNCA envie dados brutos de cartão!**

Use Pagar.me JS SDK para tokenizar:
```javascript
const pagarme = await PagarMe({
  encryptionKey: 'ek_live_...'
});

const card = pagarme.card({
  number: '4111111111111111',
  holder_name: 'João Silva',
  exp_month: '12',
  exp_year: '2030',
  cvv: '123'
});

const token = await card.tokenize();
// Enviar apenas token.id para API
```

---

## 📚 Referências

- [Documentação Pagar.me](https://docs.pagar.me)
- [Sanctum Authentication](https://laravel.com/docs/sanctum)
- [REST API Best Practices](https://restfulapi.net)

---

**Última atualização:** 09/03/2026
**Versão API:** v1
