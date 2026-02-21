# 🗄️ Schema Completo do Banco de Dados

## Estrutura Multi-Tenant

### Schema PUBLIC (Central/Plataforma)
Gerenciamento da plataforma, tenants, assinaturas

### Schema TENANT_* (Por Restaurante)
Dados isolados de cada restaurante

---

# 📊 SCHEMA PUBLIC (Central)

## tenants
```sql
CREATE TABLE tenants (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    document VARCHAR(20),
    
    type VARCHAR(50) NOT NULL, -- restaurant, pizzeria, marmitex
    
    -- Endereço
    address JSONB,
    
    -- Branding
    logo_url VARCHAR(500),
    primary_color VARCHAR(7),
    
    -- Status
    status VARCHAR(20) DEFAULT 'trial',
    plan_id UUID,
    trial_ends_at TIMESTAMP,
    subscription_ends_at TIMESTAMP,
    
    database_schema VARCHAR(100) NOT NULL,
    settings JSONB DEFAULT '{}'::jsonb,
    
    total_orders INTEGER DEFAULT 0,
    total_revenue DECIMAL(15, 2) DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP
);
```

## plans
```sql
CREATE TABLE plans (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    price_monthly DECIMAL(10, 2) NOT NULL,
    commission_percentage DECIMAL(5, 2) DEFAULT 0,
    
    max_products INTEGER,
    features JSONB DEFAULT '{}'::jsonb,
    
    is_active BOOLEAN DEFAULT true,
    
    created_at TIMESTAMP DEFAULT NOW()
);
```

## subscriptions
```sql
CREATE TABLE subscriptions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id UUID REFERENCES tenants(id),
    plan_id UUID REFERENCES plans(id),
    
    status VARCHAR(20) NOT NULL, -- active, cancelled, suspended
    starts_at TIMESTAMP NOT NULL,
    ends_at TIMESTAMP NOT NULL,
    
    auto_renew BOOLEAN DEFAULT true,
    
    created_at TIMESTAMP DEFAULT NOW()
);
```

---

# 📊 SCHEMA TENANT_* (Por Restaurante)

## users
```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    avatar_url VARCHAR(500),
    
    role VARCHAR(50) NOT NULL, -- admin, manager, kitchen, delivery
    permissions JSONB DEFAULT '[]'::jsonb,
    
    is_active BOOLEAN DEFAULT true,
    email_verified_at TIMESTAMP,
    
    created_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP
);
```

## customers ⭐ COM CASHBACK
```sql
CREATE TABLE customers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20) NOT NULL UNIQUE,
    document VARCHAR(20),
    
    birth_date DATE, -- Para cashback aniversário
    
    addresses JSONB DEFAULT '[]'::jsonb,
    
    -- CASHBACK E FIDELIDADE ⭐
    cashback_balance DECIMAL(10, 2) DEFAULT 0,
    total_cashback_earned DECIMAL(10, 2) DEFAULT 0,
    total_cashback_used DECIMAL(10, 2) DEFAULT 0,
    loyalty_tier VARCHAR(20) DEFAULT 'bronze', -- bronze, silver, gold, platinum
    loyalty_points INTEGER DEFAULT 0,
    
    -- Estatísticas
    total_orders INTEGER DEFAULT 0,
    total_spent DECIMAL(15, 2) DEFAULT 0,
    last_order_at TIMESTAMP,
    
    -- Indicação
    referral_code VARCHAR(20) UNIQUE,
    referred_by_customer_id UUID REFERENCES customers(id),
    
    accepts_marketing BOOLEAN DEFAULT true,
    
    created_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP
);

CREATE INDEX idx_customers_phone ON customers(phone);
CREATE INDEX idx_customers_loyalty_tier ON customers(loyalty_tier);
CREATE INDEX idx_customers_referral_code ON customers(referral_code);
```

## cashback_transactions ⭐ NOVO
```sql
CREATE TABLE cashback_transactions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_id UUID NOT NULL REFERENCES customers(id),
    order_id UUID REFERENCES orders(id),
    
    type VARCHAR(20) NOT NULL, -- earned, used, expired, bonus, referral
    amount DECIMAL(10, 2) NOT NULL,
    balance_before DECIMAL(10, 2) NOT NULL,
    balance_after DECIMAL(10, 2) NOT NULL,
    
    description TEXT,
    metadata JSONB, -- tier, is_birthday, etc
    
    expires_at TIMESTAMP,
    expired_at TIMESTAMP,
    
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_cashback_customer ON cashback_transactions(customer_id);
CREATE INDEX idx_cashback_order ON cashback_transactions(order_id);
CREATE INDEX idx_cashback_expires ON cashback_transactions(expires_at);
```

## loyalty_badges ⭐ NOVO
```sql
CREATE TABLE loyalty_badges (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_id UUID NOT NULL REFERENCES customers(id),
    
    badge_code VARCHAR(50) NOT NULL, -- first_order, 10_orders, big_spender
    badge_name VARCHAR(100) NOT NULL,
    badge_description TEXT,
    badge_icon VARCHAR(50), -- emoji ou classe
    
    earned_at TIMESTAMP DEFAULT NOW(),
    
    UNIQUE(customer_id, badge_code)
);

CREATE INDEX idx_badges_customer ON loyalty_badges(customer_id);
```

## categories
```sql
CREATE TABLE categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    parent_id UUID REFERENCES categories(id),
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    icon VARCHAR(50),
    
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    
    created_at TIMESTAMP DEFAULT NOW()
);
```

## products
```sql
CREATE TABLE products (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    category_id UUID NOT NULL REFERENCES categories(id),
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    
    price DECIMAL(10, 2) NOT NULL,
    promotional_price DECIMAL(10, 2),
    cost_price DECIMAL(10, 2),
    
    images JSONB DEFAULT '[]'::jsonb,
    
    product_type VARCHAR(50) DEFAULT 'simple', -- simple, pizza, marmitex
    
    -- Configurações especiais
    pizza_config JSONB,
    marmitex_config JSONB,
    
    is_active BOOLEAN DEFAULT true,
    is_featured BOOLEAN DEFAULT false,
    is_available BOOLEAN DEFAULT true,
    
    -- Estoque
    track_inventory BOOLEAN DEFAULT false,
    current_stock INTEGER,
    min_stock_alert INTEGER,
    
    created_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP
);

CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_active ON products(is_active, is_available);
```

## product_variations
```sql
CREATE TABLE product_variations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id UUID NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL, -- size, flavor, type
    price_adjustment DECIMAL(10, 2) DEFAULT 0,
    
    is_default BOOLEAN DEFAULT false,
    is_available BOOLEAN DEFAULT true,
    
    created_at TIMESTAMP DEFAULT NOW()
);
```

## product_addons
```sql
CREATE TABLE product_addons (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id UUID REFERENCES products(id) ON DELETE CASCADE,
    
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    
    min_quantity INTEGER DEFAULT 0,
    max_quantity INTEGER DEFAULT 10,
    
    is_active BOOLEAN DEFAULT true,
    
    created_at TIMESTAMP DEFAULT NOW()
);
```

## coupons ⭐ COM INTEGRAÇÃO CASHBACK
```sql
CREATE TABLE coupons (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    
    type VARCHAR(20) NOT NULL, -- percentage, fixed, free_delivery
    value DECIMAL(10, 2) NOT NULL,
    
    min_order_value DECIMAL(10, 2) DEFAULT 0,
    max_discount DECIMAL(10, 2),
    
    -- Limites
    max_uses INTEGER,
    max_uses_per_customer INTEGER DEFAULT 1,
    current_uses INTEGER DEFAULT 0,
    
    -- Cashback bônus
    bonus_cashback_percentage DECIMAL(5, 2) DEFAULT 0,
    
    starts_at TIMESTAMP,
    expires_at TIMESTAMP,
    
    is_active BOOLEAN DEFAULT true,
    
    created_at TIMESTAMP DEFAULT NOW()
);
```

## orders ⭐ COM CASHBACK
```sql
CREATE TABLE orders (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_number VARCHAR(20) UNIQUE NOT NULL,
    
    customer_id UUID NOT NULL REFERENCES customers(id),
    
    -- Status
    status VARCHAR(20) DEFAULT 'pending',
    payment_status VARCHAR(20) DEFAULT 'pending',
    
    -- Valores
    subtotal DECIMAL(10, 2) NOT NULL,
    delivery_fee DECIMAL(10, 2) DEFAULT 0,
    discount DECIMAL(10, 2) DEFAULT 0,
    cashback_used DECIMAL(10, 2) DEFAULT 0, -- ⭐ CASHBACK USADO
    cashback_earned DECIMAL(10, 2) DEFAULT 0, -- ⭐ CASHBACK GANHO
    total DECIMAL(10, 2) NOT NULL,
    
    -- Endereço de entrega
    delivery_address JSONB NOT NULL,
    
    -- Pagamento
    payment_method VARCHAR(50) NOT NULL,
    payment_data JSONB,
    
    -- Cupom
    coupon_code VARCHAR(50),
    
    -- Notas
    customer_notes TEXT,
    kitchen_notes TEXT,
    
    -- Horários
    estimated_delivery_time TIMESTAMP,
    delivered_at TIMESTAMP,
    
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created ON orders(created_at);
```

## order_items
```sql
CREATE TABLE order_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id UUID NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id UUID NOT NULL REFERENCES products(id),
    
    product_name VARCHAR(255) NOT NULL,
    product_price DECIMAL(10, 2) NOT NULL,
    
    quantity INTEGER NOT NULL DEFAULT 1,
    
    -- Pizza meio a meio, marmitex, etc
    customizations JSONB,
    
    -- Adicionais
    addons JSONB DEFAULT '[]'::jsonb,
    
    notes TEXT,
    
    subtotal DECIMAL(10, 2) NOT NULL,
    
    created_at TIMESTAMP DEFAULT NOW()
);
```

## payments
```sql
CREATE TABLE payments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id UUID NOT NULL REFERENCES orders(id),
    
    gateway VARCHAR(50) NOT NULL, -- stripe, mercadopago, asaas
    transaction_id VARCHAR(255),
    
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) NOT NULL, -- pending, completed, failed, refunded
    
    payment_method VARCHAR(50), -- credit_card, pix, cash
    
    metadata JSONB,
    
    paid_at TIMESTAMP,
    
    created_at TIMESTAMP DEFAULT NOW()
);
```

## deliveries
```sql
CREATE TABLE deliveries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id UUID NOT NULL REFERENCES orders(id),
    delivery_person_id UUID REFERENCES users(id),
    
    status VARCHAR(20) DEFAULT 'pending',
    
    pickup_at TIMESTAMP,
    delivered_at TIMESTAMP,
    
    -- Rastreamento
    current_location JSONB,
    tracking_history JSONB DEFAULT '[]'::jsonb,
    
    created_at TIMESTAMP DEFAULT NOW()
);
```

## inventory
```sql
CREATE TABLE inventory (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id UUID NOT NULL REFERENCES products(id),
    
    type VARCHAR(20) NOT NULL, -- in, out, adjustment
    quantity INTEGER NOT NULL,
    reason TEXT,
    
    user_id UUID REFERENCES users(id),
    
    created_at TIMESTAMP DEFAULT NOW()
);
```

## reviews ⭐ COM BADGES
```sql
CREATE TABLE reviews (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id UUID NOT NULL REFERENCES orders(id),
    customer_id UUID NOT NULL REFERENCES customers(id),
    
    rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    
    -- Avaliações separadas
    food_rating INTEGER,
    delivery_rating INTEGER,
    service_rating INTEGER,
    
    response TEXT, -- Resposta do restaurante
    response_at TIMESTAMP,
    
    is_visible BOOLEAN DEFAULT true,
    
    created_at TIMESTAMP DEFAULT NOW(),
    
    UNIQUE(order_id)
);
```

## settings
```sql
CREATE TABLE settings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    key VARCHAR(100) UNIQUE NOT NULL,
    value JSONB NOT NULL,
    
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Configurações de cashback
INSERT INTO settings (key, value) VALUES
('cashback_config', '{
    "enabled": true,
    "tiers": {
        "bronze": {"percentage": 2, "min_orders": 0, "expiry_days": 90},
        "silver": {"percentage": 3, "min_orders": 11, "expiry_days": 120},
        "gold": {"percentage": 5, "min_orders": 31, "expiry_days": 180},
        "platinum": {"percentage": 7, "min_orders": 51, "expiry_days": 365}
    },
    "birthday_multiplier": 2,
    "referral_bonus": 10.00
}');
```

---

## 📊 Índices para Performance

```sql
-- Customers
CREATE INDEX idx_customers_loyalty_tier ON customers(loyalty_tier);
CREATE INDEX idx_customers_total_orders ON customers(total_orders);

-- Orders
CREATE INDEX idx_orders_customer_status ON orders(customer_id, status);
CREATE INDEX idx_orders_created_at_status ON orders(created_at, status);

-- Cashback
CREATE INDEX idx_cashback_customer_created ON cashback_transactions(customer_id, created_at);
CREATE INDEX idx_cashback_expires_at ON cashback_transactions(expires_at) WHERE expires_at IS NOT NULL;

-- Products
CREATE INDEX idx_products_category_active ON products(category_id, is_active, is_available);
```

---

**Schema projetado para suportar MILHÕES de pedidos com performance! 🚀**
