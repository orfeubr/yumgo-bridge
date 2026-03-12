# 🚀 YumGo - Sistema Multi-Tenant de Delivery

## 📋 Visão Geral

Sistema completo de delivery multi-tenant (melhor que iFood!) com funcionalidades avançadas.
**Foco em: segurança, escalabilidade, organização e LUCRO! 💰**

## 🎯 Diferenciais Competitivos

| Feature | iFood | **YumGo** |
|---------|-------|-----------------|
| Comissão | ~30% 😱 | **1-3%** 🎉 |
| Cashback Cliente | ❌ | **✅ Até 10%** |
| Gateway Pagamento | Deles | **Asaas (menor taxa)** |
| Custo Gateway | ~5% | **0,99-2,99%** |
| Dados Cliente | Deles | **✅ Seus!** |
| White-label | ❌ | **✅ Sim** |
| Modo Offline | ❌ | **✅ PWA** |

## ✨ Principais Funcionalidades

### 🏢 Multi-Tenant
- Schema PostgreSQL isolado por restaurante
- Subdomínios personalizados
- Impossível vazamento de dados

### 💳 Pagamentos (Asaas)
- **PIX**: R$ 0,99/transação
- **Cartão**: 2,99% + R$ 0,49
- **Split automático**: restaurante recebe direto
- **Sub-contas**: multi-tenant perfeito

### 💎 Cashback Configurável
- Restaurante define % de cada nível
- Bronze → Prata → Ouro → Platina
- Saldo usado em compras futuras
- Aniversário: cashback DOBRADO
- Indique e ganhe

### 🍕 Tipos de Estabelecimento
- Pizzaria (meio a meio, borda, tamanhos)
- Marmitex (proteínas, acompanhamentos)
- Burger (ponto, adicionais)
- Sistema flexível

### 👨‍🍳 Painel Admin
- Dashboard real-time
- Kitchen Display System
- Impressora térmica
- Controle de estoque
- Analytics completo

### 📱 App Cliente (PWA)
- Funciona offline
- Rastreamento real-time
- Cashback visível
- Gamificação

## 🛠️ Stack

```
Backend:  Laravel 11 + PostgreSQL 16 + Redis
Frontend: Filament 3 + Vue.js 3
Mobile:   PWA + Service Workers
Gateway:  Asaas (melhor custo-benefício)
Infra:    Docker + Nginx
```

## 💰 Modelo de Negócio

### Planos (Restaurantes)
- 🥉 Starter: R$ 79/mês + 3% comissão
- 🥈 Pro: R$ 149/mês + 2% comissão  
- 🥇 Enterprise: R$ 299/mês + 1% comissão

### Exemplo de Lucro

**1000 pedidos/mês, ticket R$ 50:**
```
Receita comissão: R$ 1.500 (3%)
Custo Asaas (PIX): R$ 990
LUCRO LÍQUIDO: R$ 510/mês 💰

vs Mercado Pago: PREJUÍZO de R$ 995/mês 😱
```

## 📁 Documentação

```
/docs
├── /features
│   ├── 01-cashback-configuration.md    ⭐ Configurável
│   ├── 02-payment-system.md            ⭐ Asaas
│   └── cashback-loyalty.md
├── /database
│   └── 01-schema-design.md             ⭐ Multi-tenant
├── /architecture
└── /api
```

## 🗓️ Roadmap (13 semanas)

- ✅ Fase 0: Planejamento e documentação
- 🔄 **Fase 1: Fundação (Semanas 1-2)** ← AGORA
  - Docker + ambiente
  - Multi-tenancy
  - Migrations
- ⏳ Fase 2: MVP Core (Semanas 3-5)
- ⏳ Fase 3: Cashback (Semanas 6-7)
- ⏳ Fase 4: Features Avançadas (Semanas 8-10)
- ⏳ Fase 5: PWA (Semanas 11-12)
- ⏳ Fase 6: Launch (Semana 13)

## 🎯 Próximos Passos

1. ✅ Documentação completa
2. 🔄 **Configurar Docker** ← AGORA
3. ⏳ Implementar multi-tenancy
4. ⏳ Integrar Asaas
5. ⏳ Sistema de cashback
6. ⏳ MVP

---

**Status**: 🔥 EM IMPLEMENTAÇÃO  
**Objetivo**: Dominar o mercado e FICAR RICOS! 🚀💰
