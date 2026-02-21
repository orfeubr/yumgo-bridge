# ⚙️ Configuração de Cashback por Restaurante

## 📋 Visão Geral

Cada restaurante tem **total controle** sobre seu programa de cashback e fidelidade. O sistema fornece configurações padrão, mas tudo é personalizável.

## 🎛️ O que o Restaurante Pode Configurar

### 1. Percentuais por Nível
```
Bronze:   [___]% de cashback
Prata:    [___]% de cashback
Ouro:     [___]% de cashback
Platina:  [___]% de cashback
```

**Exemplos de Configurações:**

**Restaurante Conservador:**
- Bronze: 1%
- Prata: 1.5%
- Ouro: 2%
- Platina: 3%

**Restaurante Agressivo (foco em fidelização):**
- Bronze: 3%
- Prata: 5%
- Ouro: 8%
- Platina: 10%

**Pizzaria Estratégica:**
- Bronze: 2%
- Prata: 4%
- Ouro: 6%
- Platina: 8%

### 2. Requisitos de Nível
```
Bronze:   [___] a [___] pedidos
Prata:    [___] a [___] pedidos
Ouro:     [___] a [___] pedidos
Platina:  [___]+ pedidos
```

**Padrão Sugerido:**
- Bronze: 0 a 10
- Prata: 11 a 30
- Ouro: 31 a 50
- Platina: 51+

**Pode Personalizar:**
- Bronze: 0 a 5
- Prata: 6 a 15
- Ouro: 16 a 30
- Platina: 31+

### 3. Validade do Saldo
```
Bronze:   [___] dias
Prata:    [___] dias
Ouro:     [___] dias
Platina:  [___] dias
```

**Recomendações:**
- Mínimo: 30 dias
- Máximo: 365 dias
- Padrão: 90-180 dias

### 4. Bônus de Aniversário
```
☑️ Ativar cashback dobrado no aniversário
Multiplicador: [2]x (pode ser 1.5x, 2x, 3x)
```

### 5. Programa Indique e Ganhe
```
☑️ Ativar programa de indicação

Bônus para quem indica: R$ [___]
Bônus para novo cliente: R$ [___]

Requisitos:
☑️ Novo cliente precisa fazer pelo menos [1] pedido
Limite de indicações por cliente: [Ilimitado / ___]
```

### 6. Regras de Uso
```
☑️ Permitir uso de cashback em qualquer pedido
☐ Valor mínimo do pedido para usar: R$ [___]

Percentual máximo do pedido que pode ser pago com cashback:
● 100% (total)
○ 50%
○ 30%
○ Outro: [___]%

Valor mínimo de cashback para usar: R$ [___]
(sugestão: R$ 1,00 ou R$ 5,00)
```

### 7. Geração de Cashback
```
Gerar cashback baseado em:
● Valor total do pedido
○ Apenas subtotal (sem taxa de entrega)
○ Subtotal + taxa de entrega
```

### 8. Campanhas Especiais
```
☑️ Cashback extra em produtos específicos
Exemplo: "Pizza Calabresa com +2% de cashback!"

☑️ Cashback bônus em horários específicos
Exemplo: "Pedidos após 22h ganham +3% de cashback"

☑️ Cashback progressivo no mês
Exemplo: 
- 1º pedido do mês: cashback normal
- 2º pedido do mês: +1%
- 3º pedido do mês: +2%
- 4º+ pedido do mês: +3%
```

## 💼 Painel de Configuração

### Tela do Admin (Filament)

```
╔════════════════════════════════════════════════════════╗
║  ⚙️ Configuração de Cashback e Fidelidade              ║
╠════════════════════════════════════════════════════════╣
║                                                        ║
║  ☑️ Ativar sistema de cashback                         ║
║                                                        ║
║  ┌──────────────────────────────────────────────────┐ ║
║  │  💰 Percentuais de Cashback                      │ ║
║  ├──────────────────────────────────────────────────┤ ║
║  │                                                  │ ║
║  │  🥉 Bronze (0-10 pedidos)                       │ ║
║  │  Cashback: [2.0] %                              │ ║
║  │  Validade: [90] dias                            │ ║
║  │                                                  │ ║
║  │  🥈 Prata (11-30 pedidos)                       │ ║
║  │  Cashback: [3.0] %                              │ ║
║  │  Validade: [120] dias                           │ ║
║  │                                                  │ ║
║  │  🥇 Ouro (31-50 pedidos)                        │ ║
║  │  Cashback: [5.0] %                              │ ║
║  │  Validade: [180] dias                           │ ║
║  │                                                  │ ║
║  │  💎 Platina (51+ pedidos)                       │ ║
║  │  Cashback: [7.0] %                              │ ║
║  │  Validade: [365] dias                           │ ║
║  │                                                  │ ║
║  └──────────────────────────────────────────────────┘ ║
║                                                        ║
║  ┌──────────────────────────────────────────────────┐ ║
║  │  🎁 Bônus e Campanhas                            │ ║
║  ├──────────────────────────────────────────────────┤ ║
║  │                                                  │ ║
║  │  ☑️ Cashback dobrado no aniversário              │ ║
║  │  Multiplicador: [2]x                            │ ║
║  │                                                  │ ║
║  │  ☑️ Programa Indique e Ganhe                     │ ║
║  │  Bônus indicador: R$ [10.00]                    │ ║
║  │  Bônus indicado:  R$ [10.00]                    │ ║
║  │                                                  │ ║
║  └──────────────────────────────────────────────────┘ ║
║                                                        ║
║  ┌──────────────────────────────────────────────────┐ ║
║  │  📊 Simulador de Impacto                         │ ║
║  ├──────────────────────────────────────────────────┤ ║
║  │                                                  │ ║
║  │  Cliente faz 10 pedidos de R$ 50:               │ ║
║  │  Cashback concedido: R$ 10,00 (2%)              │ ║
║  │  Custo para você: R$ 10,00                      │ ║
║  │                                                  │ ║
║  │  Cliente faz 30 pedidos de R$ 50:               │ ║
║  │  Cashback concedido: R$ 30,00 (2-3%)            │ ║
║  │  Custo para você: R$ 30,00                      │ ║
║  │  Receita extra por fidelização: ~R$ 500 📈      │ ║
║  │                                                  │ ║
║  │  💡 ROI estimado: +1500%                         │ ║
║  │                                                  │ ║
║  └──────────────────────────────────────────────────┘ ║
║                                                        ║
║  [Salvar Configurações]  [Usar Padrão]               ║
║                                                        ║
╚════════════════════════════════════════════════════════╝
```

## 🎯 Simulador de ROI

O sistema mostra ao restaurante o impacto financeiro:

```php
function calculateCashbackROI($config) {
    // Cliente médio sem cashback
    $ordersWithoutCashback = 3; // pedidos/ano
    $avgOrderValue = 50;
    $revenueWithout = $ordersWithoutCashback * $avgOrderValue; // R$ 150
    
    // Cliente médio COM cashback
    $ordersWithCashback = 12; // pedidos/ano (4x mais!)
    $revenueWith = $ordersWithCashback * $avgOrderValue; // R$ 600
    
    // Custo do cashback
    $avgCashbackPercentage = 3.5; // média ponderada
    $cashbackCost = ($revenueWith * $avgCashbackPercentage / 100); // R$ 21
    
    // ROI
    $extraRevenue = $revenueWith - $revenueWithout; // R$ 450
    $netGain = $extraRevenue - $cashbackCost; // R$ 429
    $roi = ($netGain / $cashbackCost) * 100; // +2042%!
    
    return [
        'extra_revenue' => $extraRevenue,
        'cashback_cost' => $cashbackCost,
        'net_gain' => $netGain,
        'roi' => $roi,
    ];
}
```

## 🗄️ Armazenamento no Banco

### Tabela: cashback_settings

```sql
CREATE TABLE cashback_settings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    
    -- Configurações por nível (JSONB para flexibilidade)
    tiers JSONB NOT NULL,
    /*
    {
        "bronze": {
            "min_orders": 0,
            "max_orders": 10,
            "percentage": 2.0,
            "expiry_days": 90
        },
        "silver": {
            "min_orders": 11,
            "max_orders": 30,
            "percentage": 3.0,
            "expiry_days": 120
        },
        "gold": {
            "min_orders": 31,
            "max_orders": 50,
            "percentage": 5.0,
            "expiry_days": 180
        },
        "platinum": {
            "min_orders": 51,
            "max_orders": null,
            "percentage": 7.0,
            "expiry_days": 365
        }
    }
    */
    
    -- Bônus
    birthday_multiplier DECIMAL(3, 2) DEFAULT 2.0,
    referral_bonus_inviter DECIMAL(10, 2) DEFAULT 10.00,
    referral_bonus_invited DECIMAL(10, 2) DEFAULT 10.00,
    
    -- Regras de uso
    min_order_to_use DECIMAL(10, 2) DEFAULT 0,
    max_percentage_of_order DECIMAL(5, 2) DEFAULT 100, -- até 100% do pedido
    min_cashback_to_use DECIMAL(10, 2) DEFAULT 1.00,
    
    -- Geração
    calculate_on VARCHAR(20) DEFAULT 'total', -- total, subtotal
    
    -- Status
    is_active BOOLEAN DEFAULT true,
    
    updated_at TIMESTAMP DEFAULT NOW()
);
```

## 📊 Métricas para o Restaurante

Dashboard mostra impacto do cashback:

```
┌────────────────────────────────────────┐
│  📊 Performance do Cashback            │
├────────────────────────────────────────┤
│                                        │
│  Total concedido:  R$ 12,450,00       │
│  Total usado:      R$ 8,320,00 (67%)  │
│  Saldo pendente:   R$ 4,130,00        │
│  Total expirado:   R$ 890,00          │
│                                        │
│  ────────────────────────────────────  │
│                                        │
│  Clientes por Nível:                   │
│  💎 Platina:  45 clientes (3%)        │
│  🥇 Ouro:     230 clientes (15%)      │
│  🥈 Prata:    680 clientes (45%)      │
│  🥉 Bronze:   550 clientes (37%)      │
│                                        │
│  ────────────────────────────────────  │
│                                        │
│  📈 Impacto:                           │
│  Taxa de retorno: 68% (+40% vs normal)│
│  Ticket médio: R$ 62 (+18%)           │
│  Freq. média: 8 pedidos/mês (+120%)   │
│                                        │
│  💰 ROI: +1,847% 🚀                    │
│                                        │
└────────────────────────────────────────┘
```

## ✅ Exemplos de Configuração

### 🍕 Pizzaria Conservadora
```json
{
    "tiers": {
        "bronze":   {"percentage": 1.5, "min_orders": 0,  "expiry_days": 60},
        "silver":   {"percentage": 2.0, "min_orders": 10, "expiry_days": 90},
        "gold":     {"percentage": 3.0, "min_orders": 25, "expiry_days": 120},
        "platinum": {"percentage": 4.0, "min_orders": 40, "expiry_days": 180}
    },
    "birthday_multiplier": 1.5,
    "referral_bonus_inviter": 5.00
}
```

### 🍔 Hamburgueria Agressiva
```json
{
    "tiers": {
        "bronze":   {"percentage": 3.0, "min_orders": 0,  "expiry_days": 90},
        "silver":   {"percentage": 5.0, "min_orders": 8,  "expiry_days": 120},
        "gold":     {"percentage": 7.0, "min_orders": 20, "expiry_days": 180},
        "platinum": {"percentage": 10.0, "min_orders": 35, "expiry_days": 365}
    },
    "birthday_multiplier": 3.0,
    "referral_bonus_inviter": 15.00
}
```

### 🍱 Marmitex Balanceada
```json
{
    "tiers": {
        "bronze":   {"percentage": 2.0, "min_orders": 0,  "expiry_days": 90},
        "silver":   {"percentage": 3.5, "min_orders": 12, "expiry_days": 120},
        "gold":     {"percentage": 5.0, "min_orders": 30, "expiry_days": 180},
        "platinum": {"percentage": 7.5, "min_orders": 50, "expiry_days": 365}
    },
    "birthday_multiplier": 2.0,
    "referral_bonus_inviter": 10.00
}
```

---

**Com essa flexibilidade, cada restaurante encontra a estratégia ideal! 🎯💰**
