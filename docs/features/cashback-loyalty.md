# 💎 Sistema de Cashback e Fidelidade

## 📋 Visão Geral

Sistema completo de cashback e fidelidade que **recompensa clientes fiéis** e aumenta o ticket médio e recorrência de pedidos.

## 🎯 Objetivos

1. **Fidelizar clientes** através de recompensas
2. **Aumentar frequência** de pedidos
3. **Elevar ticket médio** com incentivos
4. **Reduzir CAC** (custo de aquisição) via indicações
5. **Criar vínculo emocional** com gamificação

## 💰 Como Funciona o Cashback

### Regra Básica
```
Cashback = Valor do Pedido × Percentual do Nível
```

### Exemplo Prático
```
Pedido de R$ 100,00
Cliente Nível Ouro (5%)
Cashback = R$ 100 × 5% = R$ 5,00
```

O valor cai automaticamente no saldo e pode ser usado na próxima compra!

## 🏆 Níveis de Fidelidade

### 🥉 Nível BRONZE
- **Requisito**: 0 a 10 pedidos
- **Cashback**: 2%
- **Validade do saldo**: 90 dias
- **Benefícios extras**: Nenhum

### 🥈 Nível PRATA  
- **Requisito**: 11 a 30 pedidos
- **Cashback**: 3%
- **Validade do saldo**: 120 dias
- **Benefícios extras**:
  - Frete grátis 1x por mês
  - Acesso a promoções antecipadas

### 🥇 Nível OURO
- **Requisito**: 31 a 50 pedidos
- **Cashback**: 5%
- **Validade do saldo**: 180 dias
- **Benefícios extras**:
  - Frete grátis 3x por mês
  - Cupons exclusivos mensais
  - Suporte prioritário

### 💎 Nível PLATINA
- **Requisito**: 51+ pedidos
- **Cashback**: 7%
- **Validade do saldo**: 365 dias (1 ano!)
- **Benefícios extras**:
  - Frete grátis ILIMITADO
  - Cupons VIP semanais
  - Atendimento VIP
  - Degustação de novos produtos
  - Cashback dobrado no aniversário

## 🎂 Bônus de Aniversário

No mês do aniversário do cliente:
- Cashback **DOBRADO** em todos os pedidos
- Cupom especial de R$ 20
- Badge comemorativo

Exemplo: Cliente Ouro (5%) no aniversário = 10% de cashback!

## 👥 Programa Indique e Ganhe

### Como Funciona
1. Cliente A compartilha seu código único
2. Cliente B usa o código no primeiro pedido
3. Ambos ganham R$ 10 de cashback!

### Regras
- Cliente indicado precisa fazer pelo menos 1 pedido
- Cashback liberado após conclusão do pedido
- Sem limite de indicações
- Código único por cliente

## 🎮 Gamificação

### Badges (Conquistas)
```
🍕 Primeira Compra
🎉 5 Pedidos
🏆 10 Pedidos
👑 50 Pedidos
💎 100 Pedidos
🔥 5 Pedidos em 1 Semana
🌙 Pedido de Madrugada
🍔 Conhecedor (experimentou 20+ produtos)
💰 Grande Gastador (R$ 1000+ em pedidos)
```

### Desafios Semanais
```
"Peça 3x esta semana e ganhe +R$ 10"
"Experimente 2 produtos novos e ganhe +R$ 5"
"Indique 2 amigos e ganhe +R$ 20"
```

## 💳 Usando o Cashback

### Na Finalização do Pedido
```
Subtotal:          R$ 80,00
Taxa de entrega:   R$ 5,00
────────────────────────────
Total:             R$ 85,00

Saldo disponível:  R$ 15,00
Usar cashback?     ✅ Sim

Cashback usado:   -R$ 15,00
────────────────────────────
Total a pagar:     R$ 70,00
```

### Regras de Uso
- Pode usar até 100% do saldo disponível
- Mínimo de R$ 1,00 para usar
- Não pode sacar, apenas usar em pedidos
- Saldo expira conforme nível de fidelidade

## 🧮 Cálculos do Sistema

### Ganho de Cashback
```php
function calculateCashback($orderTotal, $customerTier) {
    $percentages = [
        'bronze'   => 0.02, // 2%
        'silver'   => 0.03, // 3%
        'gold'     => 0.05, // 5%
        'platinum' => 0.07, // 7%
    ];
    
    $cashback = $orderTotal * $percentages[$customerTier];
    
    // Dobra no aniversário
    if (isBirthdayMonth($customer)) {
        $cashback *= 2;
    }
    
    return round($cashback, 2);
}
```

### Upgrade de Nível
```php
function checkTierUpgrade($customer) {
    $totalOrders = $customer->total_orders;
    
    if ($totalOrders >= 51) return 'platinum';
    if ($totalOrders >= 31) return 'gold';
    if ($totalOrders >= 11) return 'silver';
    return 'bronze';
}
```

### Expiração de Saldo
```php
function getCashbackExpiration($tier) {
    return [
        'bronze'   => 90,  // dias
        'silver'   => 120,
        'gold'     => 180,
        'platinum' => 365,
    ][$tier];
}
```

## 📊 Tabela: cashback_transactions

```sql
CREATE TABLE cashback_transactions (
    id UUID PRIMARY KEY,
    customer_id UUID REFERENCES customers(id),
    order_id UUID REFERENCES orders(id),
    
    type VARCHAR(20), -- earned, used, expired, bonus
    amount DECIMAL(10, 2),
    balance_after DECIMAL(10, 2),
    
    description TEXT,
    expires_at TIMESTAMP,
    
    created_at TIMESTAMP
);
```

## 📊 Tabela: loyalty_badges

```sql
CREATE TABLE loyalty_badges (
    id UUID PRIMARY KEY,
    customer_id UUID REFERENCES customers(id),
    badge_code VARCHAR(50),
    badge_name VARCHAR(100),
    earned_at TIMESTAMP
);
```

## 🎨 UI/UX - Tela do Cliente

### Saldo Visível
```
╔════════════════════════════════════╗
║  💰 Seu Saldo de Cashback          ║
║                                    ║
║       R$ 25,50                     ║
║                                    ║
║  🥇 Nível: OURO (5% cashback)     ║
║  📊 Próximo nível: 19 pedidos      ║
║  ⏰ Expira em: 90 dias             ║
╚════════════════════════════════════╝

┌────────────────────────────────────┐
│  📈 Histórico de Cashback          │
├────────────────────────────────────┤
│  + R$ 5,00  | Pedido #1234         │
│  + R$ 3,50  | Pedido #1235         │
│  - R$ 10,00 | Usado no pedido      │
│  + R$ 10,00 | Indicação de amigo   │
│  + R$ 12,00 | Pedido #1236 (Aniv!) │
└────────────────────────────────────┘

┌────────────────────────────────────┐
│  🏆 Suas Conquistas (8/15)         │
├────────────────────────────────────┤
│  ✅ 🍕 Primeira Compra             │
│  ✅ 🎉 5 Pedidos                   │
│  ✅ 🏆 10 Pedidos                  │
│  ⬜ 👑 50 Pedidos (faltam 32)      │
└────────────────────────────────────┘
```

## 📱 Notificações

### Ganhou Cashback
```
🎉 Você ganhou R$ 5,00 de cashback!
Seu saldo agora é R$ 25,50
```

### Subiu de Nível
```
🏆 PARABÉNS! Você é OURO agora!
Ganhe 5% de cashback em todos os pedidos!
+ Frete grátis 3x/mês
```

### Cashback Expirando
```
⚠️ R$ 10,00 de cashback expira em 7 dias!
Faça um pedido e use seu saldo!
```

### Aniversário
```
🎂 FELIZ ANIVERSÁRIO! 
Cashback DOBRADO no mês inteiro!
+ Cupom de R$ 20 de presente!
```

## 💼 Painel do Restaurante

### Configurações de Cashback
```
┌──────────────────────────────────────┐
│  ⚙️ Configurar Cashback              │
├──────────────────────────────────────┤
│                                      │
│  ☑️ Ativar sistema de cashback       │
│                                      │
│  Percentuais por Nível:              │
│  🥉 Bronze:   [2]% ───────────────   │
│  🥈 Prata:    [3]% ───────────────   │
│  🥇 Ouro:     [5]% ───────────────   │
│  💎 Platina:  [7]% ───────────────   │
│                                      │
│  Validade do Saldo:                  │
│  🥉 Bronze:   [90 ] dias             │
│  🥈 Prata:    [120] dias             │
│  🥇 Ouro:     [180] dias             │
│  💎 Platina:  [365] dias             │
│                                      │
│  ☑️ Dobrar cashback no aniversário   │
│  ☑️ Programa indique e ganhe         │
│                                      │
│  [Salvar Configurações]              │
└──────────────────────────────────────┘
```

### Métricas
```
┌──────────────────────────────────────┐
│  📊 Métricas de Fidelidade           │
├──────────────────────────────────────┤
│  Total concedido: R$ 12,450,00       │
│  Total usado:     R$ 8,320,00        │
│  Saldo pendente:  R$ 4,130,00        │
│                                      │
│  Clientes Platinum: 45 (3%)          │
│  Clientes Ouro:     230 (15%)        │
│  Clientes Prata:    680 (45%)        │
│  Clientes Bronze:   550 (37%)        │
│                                      │
│  ROI do cashback: +35% 📈            │
│  Taxa de retorno: 68% 📈             │
└──────────────────────────────────────┘
```

## 🎯 Estratégias de Negócio

### Para Restaurantes
1. **Aquisição**: Cashback agressivo nos primeiros pedidos
2. **Retenção**: Níveis incentivam compras recorrentes
3. **Ticket Médio**: "Faltam R$ 5 para ganhar +R$ 2 de cashback"
4. **Viralização**: Programa de indicação
5. **Dados**: Analytics de comportamento

### Exemplo de ROI
```
Cliente Bronze:
- Pedido médio: R$ 50
- Cashback: 2% = R$ 1,00
- Custo por pedido: R$ 1,00

Cliente retorna 5x (vs 1x sem cashback):
- Receita extra: R$ 200
- Custo cashback: R$ 5
- ROI: +3900%! 🚀
```

## ✅ Checklist de Implementação

- [ ] Tabelas de banco de dados
- [ ] Cálculo automático de cashback
- [ ] Sistema de níveis
- [ ] Expiração de saldo
- [ ] Bônus de aniversário
- [ ] Programa de indicação
- [ ] Badges e conquistas
- [ ] UI do saldo
- [ ] Notificações
- [ ] Painel admin
- [ ] Métricas e relatórios
- [ ] Testes automatizados

---

**Este sistema vai nos diferenciar COMPLETAMENTE do iFood! 🚀💰**
