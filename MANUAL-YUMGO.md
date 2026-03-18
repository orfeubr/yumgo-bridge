# 📘 Manual do Sistema YumGo

**Versão:** 1.0
**Data:** Março de 2026
**Sistema de Delivery Multi-Tenant Completo**

---

## 📑 Índice

1. [Introdução](#introdução)
2. [Diferencial vs iFood](#diferencial-vs-ifood)
3. [Arquitetura do Sistema](#arquitetura-do-sistema)
4. [Painel Central (Administrador)](#painel-central-administrador)
5. [Painel do Restaurante](#painel-do-restaurante)
6. [Sistema de Pedidos](#sistema-de-pedidos)
7. [Sistema de Cashback](#sistema-de-cashback)
8. [Impressão Automática (YumGo Bridge)](#impressão-automática-yumgo-bridge)
9. [Sistema Fiscal (NFC-e)](#sistema-fiscal-nfc-e)
10. [Configurações Avançadas](#configurações-avançadas)
11. [Solução de Problemas](#solução-de-problemas)
12. [Perguntas Frequentes](#perguntas-frequentes)

---

## 🎯 Introdução

### O que é o YumGo?

O **YumGo** é uma plataforma completa de delivery multi-tenant, desenvolvida para competir com o iFood oferecendo:

- ✅ **Comissão ultra-baixa**: 1-3% vs 30% do iFood
- ✅ **Cashback configurável**: Cada restaurante define suas regras
- ✅ **Isolamento total**: Dados 100% separados por restaurante
- ✅ **Impressão automática**: Bridge para impressoras térmicas
- ✅ **Sistema fiscal completo**: Emissão de NFC-e integrada
- ✅ **Gateway de pagamento**: Pagar.me + Asaas

### Público-Alvo

- 🍕 Pizzarias
- 🍔 Hamburguerias
- 🍱 Marmitarias
- 🍺 Botecos
- 🍰 Docerias
- 🥖 Padarias
- E mais 9 tipos de estabelecimento!

---

## 💰 Diferencial vs iFood

### Comparação de Custos

| Item | iFood | YumGo | Economia |
|------|-------|-------|----------|
| **Comissão** | 27-30% | 1-3% | **90%** ✅ |
| **Taxa PIX** | R$ 2,50 | R$ 0,99 | **60%** ✅ |
| **Taxa Cartão** | 4,99% | 3,99% | **20%** ✅ |
| **Mensalidade** | R$ 0 | R$ 79-299 | - |

#### Exemplo Real (1000 pedidos/mês × R$ 50):

**iFood:**
- Faturamento: R$ 50.000
- Comissão (30%): **-R$ 15.000** ❌
- Lucro líquido: R$ 35.000

**YumGo (Plano Pro - 2%):**
- Faturamento: R$ 50.000
- Mensalidade: -R$ 149
- Comissão (2%): -R$ 1.000
- Gateway: -R$ 1.093
- **Lucro líquido: R$ 47.758** ✅

**💰 Economia mensal: R$ 12.758 (36% a mais de lucro!)**

### Funcionalidades Exclusivas

| Funcionalidade | iFood | YumGo |
|----------------|-------|-------|
| Cashback configurável | ❌ | ✅ |
| Impressão automática | ❌ | ✅ |
| Emissão NFC-e integrada | ❌ | ✅ |
| Dados isolados (LGPD) | ❌ | ✅ |
| Customização completa | ❌ | ✅ |
| Sem compartilhar clientes | ❌ | ✅ |

---

## 🏗️ Arquitetura do Sistema

### Multi-Tenant com PostgreSQL Schemas

```
┌─────────────────────────────────────────┐
│         Schema PUBLIC (Central)         │
├─────────────────────────────────────────┤
│  • tenants (restaurantes)               │
│  • plans (planos)                       │
│  • subscriptions (assinaturas)          │
│  • domains (domínios)                   │
│  • central_customers (login único)      │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│    Schema TENANT_marmitariadagi         │
├─────────────────────────────────────────┤
│  • customers (clientes do restaurante)  │
│  • products (produtos)                  │
│  • orders (pedidos)                     │
│  • cashback_transactions                │
│  • settings (configurações)             │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│    Schema TENANT_parkerpizzaria         │
├─────────────────────────────────────────┤
│  • customers (clientes isolados)        │
│  • products                             │
│  • orders                               │
│  • cashback_transactions                │
│  • settings                             │
└─────────────────────────────────────────┘
```

### Vantagens do Isolamento:

- ✅ **Segurança Total**: Impossível vazamento de dados entre restaurantes
- ✅ **Performance**: Queries rápidas (não há JOIN entre milhões de registros)
- ✅ **LGPD Compliance**: Dados isolados facilitam exclusão (direito ao esquecimento)
- ✅ **Backup Simples**: Um único backup do PostgreSQL
- ✅ **Escalabilidade**: Suporta milhares de restaurantes

---

## 👨‍💼 Painel Central (Administrador)

**URL:** `https://yumgo.com.br/admin`

### 1. Dashboard

**Métricas em tempo real:**
- 📊 Total de restaurantes ativos
- 💰 Receita mensal (comissões)
- 📈 Gráfico de crescimento
- 🆕 Novos cadastros

### 2. Gerenciar Restaurantes

**Lista de Restaurantes:**
- Nome, slug, tipo (pizzaria, marmitaria, etc)
- Status (ativo/inativo)
- Plano contratado
- Data de cadastro

**Criar Novo Restaurante:**

```
Dados Básicos:
  • Nome: Marmitaria da Gi
  • Slug: marmitariadagi (URL: marmitariadagi.yumgo.com.br)
  • Email: contato@marmitariadagi.com.br
  • Telefone: (19) 98765-4321
  • Tipo: Marmitaria

Plano:
  • Starter (R$ 79/mês + 3% comissão)
  • Pro (R$ 149/mês + 2% comissão)
  • Enterprise (R$ 299/mês + 1% comissão)

Gateway de Pagamento:
  • Pagar.me (recomendado)
  • Asaas (legado)

Dados Bancários:
  • Banco: 341 - Itaú
  • Agência: 1234
  • Conta: 56789-0
  • Tipo: Conta Corrente
  • CPF/CNPJ do titular
```

**Ação: Criar Recebedor Pagar.me**
- Cria automaticamente recipient no Pagar.me
- Habilita split de pagamentos
- Comissão configurada automaticamente

### 3. Planos e Preços

**Planos Padrão:**

| Plano | Mensalidade | Comissão | Features |
|-------|-------------|----------|----------|
| **Starter** | R$ 79 | 3% | Básico |
| **Pro** | R$ 149 | 2% | + Cashback + NFC-e |
| **Enterprise** | R$ 299 | 1% | Tudo ilimitado |

**Criar/Editar Plano:**
- Nome do plano
- Preço mensal
- Percentual de comissão
- Features (JSON): `["cashback", "nfce", "api_access"]`
- Limite de pedidos/mês

### 4. Configurações da Plataforma

**Gateway Pagar.me:**
```env
PAGARME_API_KEY=sk_live_...
PAGARME_ENCRYPTION_KEY=pk_live_...
PAGARME_PLATFORM_RECIPIENT_ID=re_...
PAGARME_WEBHOOK_TOKEN=...
```

**Tributa AI (Classificação Fiscal):**
```env
TRIBUTAAI_PLATFORM_TOKEN=...
```

**Reverb (WebSocket):**
```env
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
```

---

## 🍕 Painel do Restaurante

**URL:** `https://{slug}.yumgo.com.br/painel`
**Exemplo:** `https://marmitariadagi.yumgo.com.br/painel`

### 1. Dashboard

**Widgets em Tempo Real:**

```
┌─────────────────────────────────────────┐
│  Pedidos Hoje: 47        💰 R$ 2.340,00 │
│  Ticket Médio: R$ 49,79  📈 +12% vs ontem│
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│         Gráfico de Vendas (7 dias)      │
│  R$                                     │
│  3k ┤                          ●        │
│  2k ┤              ●     ●              │
│  1k ┤      ●   ●                        │
│   0 └─────┴───┴───┴───┴───┴───┴───     │
│     Seg Ter Qua Qui Sex Sab Dom        │
└─────────────────────────────────────────┘

Top 5 Produtos:
1. Marmitex Grande (R$ 450)
2. Feijoada Completa (R$ 380)
3. Frango Grelhado (R$ 320)
4. Refrigerante 2L (R$ 180)
5. Sobremesa (R$ 150)
```

**Filtros:**
- Hoje / Última semana / Último mês / Personalizado
- Por forma de pagamento
- Por status

### 2. Produtos

**Lista de Produtos:**

| Nome | Categoria | Preço | Status | Ações |
|------|-----------|-------|--------|-------|
| Marmitex Grande | Marmitas | R$ 20,00 | ✅ Ativo | Editar / Desativar |
| Feijoada Completa | Especiais | R$ 35,00 | ✅ Ativo | Editar / Desativar |
| Refrigerante 2L | Bebidas | R$ 8,00 | ✅ Ativo | Editar / Desativar |

**Criar/Editar Produto:**

```
Informações Básicas:
  • Nome: Marmitex Grande
  • Descrição: Arroz, feijão, proteína e acompanhamentos
  • Categoria: Marmitas
  • Preço: R$ 20,00
  • Foto: [Upload]

Configurações:
  ☑ Produto ativo
  ☑ Destaque (aparece primeiro)
  ☐ Esgotado

Variações (Opcional):
  + Adicionar variação
    • Nome: Tamanho
    • Opções:
      - Pequena (+R$ 0,00)
      - Média (+R$ 3,00)
      - Grande (+R$ 5,00)

Adicionais (Opcional):
  + Adicionar adicional
    • Ovo frito (+R$ 2,00)
    • Batata frita (+R$ 5,00)
    • Vinagrete (+R$ 0,00)

Informações Fiscais:
  • NCM: 19059090 (Alimentos Produzidos)
  • CFOP: 5102 (Venda dentro do estado)
  • CEST: - (não obrigatório)

  [Buscar com IA] (Tributa AI - opcional)

Local de Impressão:
  • Cozinha (imprimir na cozinha)
  • Balcão (imprimir no balcão)
  • Ambos
```

**Tipos Especiais:**

**Pizza (Meio a Meio):**
```
Configuração Especial de Pizza:
  ☑ Permitir meio a meio
  ☑ Cobrar pelo maior valor

  Sabores Disponíveis:
    ☑ Calabresa
    ☑ Mussarela
    ☑ Portuguesa
    ☑ 4 Queijos

  Bordas:
    ☑ Sem borda (+R$ 0,00)
    ☑ Catupiry (+R$ 5,00)
    ☑ Cheddar (+R$ 6,00)
```

**Marmitex (Escolha de Proteína):**
```
Configuração Especial de Marmitex:
  Proteínas (escolher 1):
    ☑ Frango Grelhado
    ☑ Carne Moída
    ☑ Peixe
    ☑ Linguiça

  Acompanhamentos (até 3):
    ☑ Arroz
    ☑ Feijão
    ☑ Salada
    ☑ Farofa
    ☑ Macarrão

  Limite de acompanhamentos: 3
```

### 3. Categorias

**Gerenciar Categorias:**

| Ordem | Nome | Produtos | Status | Ações |
|-------|------|----------|--------|-------|
| 1 | Marmitas | 8 | ✅ Ativo | ↑ ↓ Editar |
| 2 | Bebidas | 12 | ✅ Ativo | ↑ ↓ Editar |
| 3 | Sobremesas | 5 | ✅ Ativo | ↑ ↓ Editar |

**Criar Categoria:**
```
Nome: Marmitas
Descrição: Marmitex com arroz, feijão e proteína
Ícone: 🍱 (emoji ou imagem)
Ordem: 1
☑ Ativa
```

**Templates por Tipo de Restaurante:**

Quando você cria um restaurante e escolhe o tipo, categorias são criadas automaticamente:

- **Pizzaria:** Pizzas Tradicionais, Pizzas Especiais, Pizzas Doces, Bebidas, Sobremesas
- **Marmitaria:** Marmitas, Porções, Bebidas, Sobremesas
- **Hamburgueria:** Hambúrgueres, Porções, Bebidas, Sobremesas, Combos

### 4. Pedidos

**Lista de Pedidos:**

| # | Cliente | Total | Pagamento | Status | Criado | Ações |
|---|---------|-------|-----------|--------|--------|-------|
| #1234 | João Silva | R$ 50 | 🔵 **Pagar na Entrega** | 🟡 Pendente | 10:30 | Ver / Confirmar |
| #1233 | Maria Santos | R$ 35 | 🟢 Pago | 🟢 Em preparo | 10:15 | Ver |
| #1232 | Pedro Costa | R$ 80 | 🟡 Pendente | 🟡 Aguardando | 10:00 | Ver |

**Status de Pagamento:**
- 🟡 **Pendente**: Aguardando pagamento PIX/Cartão
- 🔵 **Pagar na Entrega**: Impresso, aguardando recebimento
- 🟢 **Pago**: Pagamento confirmado
- 🔴 **Falhou**: Pagamento recusado

**Fluxo do Pedido:**
```
1. Pendente (novo pedido)
   ↓ [Confirmar Pedido]
2. Confirmado
   ↓ [Iniciar Preparo]
3. Em Preparo
   ↓ [Marcar como Pronto]
4. Pronto
   ↓ [Saiu para Entrega]
5. Saindo para Entrega
   ↓ [Marcar como Entregue]
6. Entregue ✅
```

**Detalhes do Pedido:**

```
┌─────────────────────────────────────────┐
│  Pedido #1234                           │
│  Status: 🟡 Pendente                    │
│  Pagamento: 🔵 Pagar na Entrega (Dinheiro)│
├─────────────────────────────────────────┤
│  Cliente: João Silva                    │
│  Telefone: (19) 98765-4321             │
│  Email: joao@email.com                 │
├─────────────────────────────────────────┤
│  Entrega:                               │
│  Rua das Flores, 123                   │
│  Bairro: Centro                        │
│  Cidade: Louveira                      │
│  Taxa: R$ 5,00                         │
├─────────────────────────────────────────┤
│  Itens:                                 │
│  1x Marmitex Grande      R$ 20,00      │
│     Obs: Sem cebola                    │
│  2x Refrigerante 2L      R$ 16,00      │
│  1x Sobremesa           R$ 9,00       │
├─────────────────────────────────────────┤
│  Subtotal:              R$ 45,00       │
│  Taxa de entrega:       R$ 5,00        │
│  TOTAL:                 R$ 50,00       │
├─────────────────────────────────────────┤
│  Observações do Cliente:                │
│  "Por favor mandar troco para 100"     │
├─────────────────────────────────────────┤
│  [Confirmar]  [Cancelar]  [Reimprimir] │
└─────────────────────────────────────────┘
```

**Ações Disponíveis:**
- ✅ Confirmar Pedido
- ❌ Cancelar Pedido
- 🖨️ Reimprimir
- 💰 Marcar como Pago (para pagamento na entrega)
- 📞 Ligar para Cliente (abre WhatsApp)

### 5. Sistema de Cashback

**Configurações de Cashback:**

```
┌─────────────────────────────────────────┐
│  Cashback Configurável                  │
├─────────────────────────────────────────┤
│  ☑ Sistema de cashback ativo            │
│                                         │
│  Percentual padrão: 5%                  │
│  (cliente ganha 5% do valor do pedido)  │
│                                         │
│  Valor mínimo para usar: R$ 5,00        │
│  (cliente precisa ter pelo menos R$ 5)  │
│                                         │
│  Validade do saldo: 90 dias             │
│  (saldo expira após 3 meses)            │
├─────────────────────────────────────────┤
│  Níveis de Fidelidade:                  │
│                                         │
│  🥉 Bronze (0-5 pedidos)                │
│     Cashback: 3%                        │
│     Requisitos: Cadastro               │
│                                         │
│  🥈 Prata (6-15 pedidos)                │
│     Cashback: 5%                        │
│     Requisitos: 6 pedidos              │
│                                         │
│  🥇 Ouro (16-30 pedidos)                │
│     Cashback: 7%                        │
│     Requisitos: 16 pedidos             │
│                                         │
│  💎 Platina (31+ pedidos)               │
│     Cashback: 10%                       │
│     Requisitos: 31 pedidos + R$ 1000   │
├─────────────────────────────────────────┤
│  Bônus Especiais:                       │
│  ☑ Dobrar cashback no aniversário       │
│  ☑ Indique e ganhe (R$ 10 por indicação)│
└─────────────────────────────────────────┘
```

**Como Funciona:**

1. **Cliente faz pedido de R$ 100**
2. Sistema calcula cashback (5% = R$ 5,00)
3. Adiciona R$ 5,00 ao saldo do cliente
4. Cliente pode usar em próximo pedido
5. Na hora de pagar: "Usar meu cashback" ✅
6. Desconto aplicado automaticamente

**Exemplo Prático:**
```
Pedido de R$ 80:
  Subtotal:        R$ 80,00
  + Entrega:       R$ 5,00
  = Total:         R$ 85,00
  - Cashback:      -R$ 10,00 (saldo do cliente)
  = TOTAL FINAL:   R$ 75,00 ✅

Cashback ganho neste pedido: R$ 4,25 (5% de R$ 85)
Novo saldo: R$ 4,25
```

### 6. Zonas de Entrega (Bairros)

**Gerenciar Bairros:**

| Cidade | Bairro | Taxa | Status | Ações |
|--------|--------|------|--------|-------|
| Louveira | Centro | R$ 5,00 | ✅ Ativo | Editar / Desativar |
| Louveira | Jardim Brasil | R$ 7,00 | ✅ Ativo | Editar / Desativar |
| Louveira | Vila Real | R$ 8,00 | ❌ Inativo | Editar / Ativar |

**Adicionar Bairro:**
```
Cidade: Louveira
Bairro: Santo Antônio
Taxa de entrega: R$ 6,00
☑ Ativo
```

**Buscar CEP (Integração ViaCEP):**
```
Digite o CEP: 13290-000
[Buscar]

Resultado:
  Cidade: Louveira
  Bairro: Centro

[Adicionar à lista]
```

**Taxa de Entrega Grátis:**
```
☑ Habilitar entrega grátis
Valor mínimo: R$ 50,00

(Pedidos acima de R$ 50 = frete grátis)
```

### 7. Cupons de Desconto

**Lista de Cupons:**

| Código | Tipo | Valor | Uso | Validade | Status | Ações |
|--------|------|-------|-----|----------|--------|-------|
| PRIMEIRACOMPRA | % | 20% | 0/100 | 31/12/2026 | ✅ Ativo | Editar |
| NATAL2026 | R$ | R$ 10 | 45/∞ | 25/12/2026 | ✅ Ativo | Editar |
| FIDELIDADE | % | 15% | 12/50 | 30/06/2026 | ✅ Ativo | Editar |

**Criar Cupom:**

```
Código: PRIMEIRACOMPRA
(em maiúsculas, sem espaços)

Tipo de Desconto:
  ○ Percentual (%)
  ○ Valor fixo (R$)

Valor do Desconto:
  20% (ou R$ 10,00)

Valor Mínimo do Pedido:
  R$ 30,00
  (cupom só funciona em pedidos acima deste valor)

Limite de Uso:
  ○ Ilimitado
  ● Limitado: 100 vezes
  ○ 1 uso por cliente

Validade:
  De: 01/03/2026
  Até: 31/12/2026

☑ Cupom ativo
```

**Exemplo de Uso:**
```
Cliente aplica cupom "PRIMEIRACOMPRA":

Pedido:
  Subtotal:        R$ 50,00
  + Entrega:       R$ 5,00
  = Total:         R$ 55,00
  - Cupom (20%):   -R$ 11,00
  = TOTAL:         R$ 44,00 ✅
```

### 8. Configurações do Restaurante

**Informações Básicas:**
```
Nome: Marmitaria da Gi
Telefone: (19) 98765-4321
Email: contato@marmitariadagi.com.br
Endereço: Rua Principal, 123 - Centro - Louveira/SP
```

**Horário de Funcionamento:**
```
Segunda:    11:00 - 14:30, 18:00 - 22:00
Terça:      11:00 - 14:30, 18:00 - 22:00
Quarta:     11:00 - 14:30, 18:00 - 22:00
Quinta:     11:00 - 14:30, 18:00 - 22:00
Sexta:      11:00 - 14:30, 18:00 - 23:00
Sábado:     11:00 - 15:00, 18:00 - 23:00
Domingo:    ☐ Fechado

☑ Aceitar pedidos fora do horário
☐ Fechar temporariamente
```

**Formas de Pagamento:**
```
☑ PIX (recomendado - taxa 0,99%)
☑ Cartão de Crédito (taxa 3,99%)
☑ Cartão de Débito (taxa 2,99%)
☑ Dinheiro na entrega
☑ Cartão na maquininha (entregador)
```

**Tempo de Preparo:**
```
Tempo médio: 45 minutos
Tempo mínimo: 30 minutos
Tempo máximo: 60 minutos
```

**Logo e Imagens:**
```
Logo do restaurante: [Upload]
Banner principal: [Upload]
Favicon: [Upload]
```

### 9. Usuários e Permissões

**Lista de Usuários:**

| Nome | Função | Email | Status | Último acesso | Ações |
|------|--------|-------|--------|---------------|-------|
| João Silva | 👑 Admin | joao@email.com | ✅ Ativo | Agora | Editar |
| Maria Santos | 👨‍🍳 Gerente | maria@email.com | ✅ Ativo | 2h atrás | Editar |
| Pedro Costa | 👤 Funcionário | pedro@email.com | ✅ Ativo | Hoje 10:00 | Editar / Desativar |

**Criar Usuário:**

```
Nome: Maria Santos
Email: maria@marmitariadagi.com.br
Senha: ••••••••
Confirmar senha: ••••••••

Função:
  ○ Admin (acesso total)
  ● Gerente (sem acesso a configurações críticas)
  ○ Funcionário (apenas pedidos)
  ○ Financeiro (apenas relatórios)
  ○ Entregador (app mobile - futuro)

Permissões (para Gerente/Funcionário):

  Produtos:
    ☑ Visualizar produtos
    ☑ Criar produtos
    ☑ Editar produtos
    ☐ Deletar produtos

  Pedidos:
    ☑ Visualizar pedidos
    ☑ Editar status
    ☑ Cancelar pedidos

  Cupons:
    ☑ Visualizar cupons
    ☐ Criar cupons
    ☐ Editar cupons
    ☐ Deletar cupons

  Clientes:
    ☑ Visualizar clientes
    ☐ Editar clientes

  Configurações:
    ☐ Editar configurações
    ☐ Gerenciar usuários

  Relatórios:
    ☑ Visualizar relatórios
    ☐ Exportar relatórios

☑ Usuário ativo
```

**Funções Pré-definidas:**

- **👑 Admin**: Acesso total (dono do restaurante)
- **👨‍🍳 Gerente**: Tudo menos config críticas e usuários
- **👤 Funcionário**: Apenas pedidos e produtos (visualizar)
- **💰 Financeiro**: Apenas relatórios financeiros
- **🚗 Entregador**: App mobile (futuro)

---

## 🛒 Sistema de Pedidos

### Fluxo Completo (Cliente)

**1. Escolher Cidade e Bairro:**
```
📍 Onde você está?

Cidade: [Louveira ▼]
Bairro: [Centro ▼]

Taxa de entrega: R$ 5,00
✅ Entregamos no seu bairro!

[Continuar]
```

**2. Navegar no Cardápio:**
```
┌─────────────────────────────────────┐
│  🍱 Marmitas    🍔 Porções          │
│  🥤 Bebidas     🍰 Sobremesas       │
└─────────────────────────────────────┘

[🔍 Buscar produtos...]

┌──────────────────┐  ┌──────────────────┐
│ Marmitex Grande  │  │ Feijoada Completa│
│ R$ 20,00         │  │ R$ 35,00         │
│ [+ Adicionar]    │  │ [+ Adicionar]    │
└──────────────────┘  └──────────────────┘
```

**3. Adicionar ao Carrinho:**
```
Marmitex Grande - R$ 20,00

Escolha a proteína:
  ○ Frango Grelhado
  ● Carne Moída
  ○ Peixe

Acompanhamentos (até 3):
  ☑ Arroz
  ☑ Feijão
  ☑ Salada
  ☐ Farofa

Quantidade: [1] [-] [+]

Observações:
[Sem cebola, sem molho]

Total: R$ 20,00

[Adicionar ao Carrinho - R$ 20,00]
```

**4. Finalizar Pedido:**
```
┌─────────────────────────────────────┐
│  Seu Pedido                         │
├─────────────────────────────────────┤
│  1x Marmitex Grande      R$ 20,00   │
│     Obs: Sem cebola                 │
│  2x Refrigerante 2L      R$ 16,00   │
├─────────────────────────────────────┤
│  Subtotal:              R$ 36,00    │
│  Taxa de entrega:       R$ 5,00     │
│  TOTAL:                 R$ 41,00    │
└─────────────────────────────────────┘

Endereço de Entrega:
  ● Usar endereço salvo:
    Rua das Flores, 123 - Centro
  ○ Novo endereço

Cupom de Desconto:
  [PRIMEIRACOMPRA] [Aplicar]
  ✅ Cupom aplicado! -R$ 8,20

💰 Cashback Disponível: R$ 10,00
  ☑ Usar meu cashback (-R$ 10,00)

Forma de Pagamento:
  ○ PIX
  ○ Cartão de Crédito
  ● Dinheiro na entrega
    Troco para: [R$ 50,00]

Observações do Pedido:
[Por favor mandar troco para 50]

TOTAL FINAL: R$ 22,80

[Confirmar Pedido - R$ 22,80]
```

**5. Pagamento:**

**PIX:**
```
┌─────────────────────────────────────┐
│  Pedido #1234                       │
│  Total: R$ 22,80                    │
├─────────────────────────────────────┤
│  [QR CODE PIX]                      │
│                                     │
│  Pix Copia e Cola:                  │
│  00020126330014BR.GOV.BCB.PIX...    │
│  [Copiar Código]                    │
│                                     │
│  ⏱️ Aguardando pagamento...          │
│  Expira em: 09:45                   │
└─────────────────────────────────────┘
```

**Dinheiro na Entrega:**
```
┌─────────────────────────────────────┐
│  ✅ Pedido Confirmado!              │
│                                     │
│  Pedido #1234                       │
│  Total: R$ 22,80                    │
│  Forma: Dinheiro na entrega         │
│  Troco para: R$ 50,00               │
│                                     │
│  Tempo estimado: 45 minutos         │
│                                     │
│  [Acompanhar Pedido]                │
└─────────────────────────────────────┘
```

### Status do Pedido (Acompanhamento)

```
Pedido #1234

┌─●────○────○────○────○────○─┐
│ Pendente                    │
│ 10:30                       │
└─────────────────────────────┘

┌─●────●────○────○────○────○─┐
│ Confirmado                  │
│ 10:32                       │
└─────────────────────────────┘

┌─●────●────●────○────○────○─┐
│ Em Preparo                  │
│ 10:35                       │
│ ⏱️ Tempo estimado: 30 min    │
└─────────────────────────────┘

┌─●────●────●────●────○────○─┐
│ Pronto                      │
│ 11:05                       │
└─────────────────────────────┘

┌─●────●────●────●────●────○─┐
│ Saiu para Entrega           │
│ 11:10                       │
│ 🚗 Entregador a caminho      │
└─────────────────────────────┘

┌─●────●────●────●────●────●─┐
│ Entregue ✅                 │
│ 11:25                       │
│                             │
│ [Avaliar Pedido]            │
└─────────────────────────────┘
```

### Estados de Pagamento

| Status | Badge | Quando | O que fazer |
|--------|-------|--------|-------------|
| `pending` | 🟡 Pendente | Aguardando PIX/Cartão | Aguardar pagamento |
| `awaiting_delivery` | 🔵 Pagar na Entrega | Dinheiro/Cartão entregador | Preparar e entregar |
| `paid` | 🟢 Pago | Confirmado | Preparar pedido |
| `failed` | 🔴 Falhou | Recusado | Entrar em contato |

---

## 🖨️ Impressão Automática (YumGo Bridge)

### O que é o YumGo Bridge?

**Aplicação Desktop** (Windows) que conecta impressoras térmicas ao sistema YumGo para **impressão automática** de pedidos.

### Instalação

**1. Download:**
- Acessar: https://github.com/orfeubr/yumgo/releases/latest
- Baixar: `YumGo-Bridge-3.23.0-win-x64.exe`

**2. Instalar:**
- Executar o instalador
- Seguir o assistente
- ✅ Instalar drivers da impressora (se necessário)

**3. Configurar:**

```
┌─────────────────────────────────────┐
│  YumGo Bridge - Configuração        │
├─────────────────────────────────────┤
│  Token de Autenticação:             │
│  [Copie o token do painel]          │
│                                     │
│  ID do Restaurante:                 │
│  [marmitariadagi]                   │
│                                     │
│  [Conectar]                         │
└─────────────────────────────────────┘
```

**Onde pegar o Token:**
1. Ir para: `/painel/impressao-automatica`
2. Clicar em "Gerar Token"
3. Copiar token
4. Colar no Bridge

### Configurar Impressoras

```
Local: Balcão (Recibo Completo)
  Tipo: Impressora do Sistema
  Impressora: [POS58 ▼]
  Largura do Papel: 58mm
  [Salvar]

Local: Cozinha (Apenas Items)
  Tipo: Impressora do Sistema
  Impressora: [POS58-Kitchen ▼]
  Largura do Papel: 58mm
  [Salvar]
```

### Funcionamento

**Fluxo Automático:**
```
1. Cliente faz pedido → payment_status = 'awaiting_delivery'
2. Backend dispara evento WebSocket
3. Bridge recebe evento em tempo real
4. Bridge gera recibo formatado
5. Imprime automaticamente ✅
6. Reporta sucesso ao servidor
```

**Se falhar:**
```
1. Bridge tenta imprimir → ERRO (sem papel)
2. Aguarda 1 minuto → tenta novamente
3. Aguarda 2 minutos → tenta novamente
4. Aguarda 5 minutos → tenta novamente
5. Se todas falharem → Reporta falha ao servidor
6. Dashboard mostra alerta vermelho ⚠️
7. Restaurante recoloca papel
8. Clica "Reimprimir" no painel ✅
```

### Exemplo de Impressão

```
=====================================
     MARMITARIA DA GI
=====================================
     ** NOVO PEDIDO **
-------------------------------------

       PEDIDO #1234
     16/03/2026 10:30
-------------------------------------

CLIENTE
João Silva
Tel: (19) 98765-4321

ENTREGA
Rua das Flores, 123
Bairro: Centro
Cidade: Louveira

-------------------------------------
ITENS DO PEDIDO
-------------------------------------
1 Marmitex Grande           R$ 20,00
  Proteina: Carne Moída
  Acomp: Arroz, Feijão, Salada
  Obs: Sem cebola            ← ITEM

2 Refrigerante 2L           R$ 16,00

-------------------------------------
Subtotal:              R$ 36,00
+ Entrega:             R$ 5,00
- Cupom (20%):         -R$ 8,20
- Cashback:            -R$ 10,00
TOTAL A PAGAR:         R$ 22,80
-------------------------------------

Dinheiro - PAGAR NA ENTREGA
                       R$ 22,80

-------------------------------------
OBSERVACOES DO CLIENTE:            ← GERAL
Por favor mandar troco para 50

-------------------------------------
   Obrigado pela preferencia!
 marmitariadagi.yumgo.com.br
      Powered by YumGo
-------------------------------------
```

### Monitor de Impressão

**URL:** `/painel/monitor-impressao`

```
┌─────────────────────────────────────┐
│  📊 Estatísticas (24h)              │
├─────────────────────────────────────┤
│  Total: 47    Impressos: 45         │
│  Pendentes: 0  Falhas: 2            │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  🖨️ Status do Bridge                │
├─────────────────────────────────────┤
│  ● 🟢 Online (há 15s)                │
│  Versão: 3.23.0                     │
│                                     │
│  Impressoras:                       │
│  • POS58 (Balcão) - ✅ Pronta       │
│  • POS58-Kitchen (Cozinha) - ✅ Pronta│
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  ⚠️ Alertas (2)                     │
├─────────────────────────────────────┤
│  Pedido #1230 - Falhou              │
│  ❌ Impressora sem papel             │
│  3 tentativas                       │
│  [Reimprimir]                       │
│                                     │
│  Pedido #1228 - Falhou              │
│  ❌ Impressora desconectada          │
│  3 tentativas                       │
│  [Reimprimir]                       │
└─────────────────────────────────────┘

[Reimprimir Todas Falhas]
[Testar Impressora]
```

### Auto-Update

**O Bridge se atualiza automaticamente!**

```
1. Bridge verifica updates a cada 3 segundos
2. Nova versão disponível → Notificação
┌─────────────────────────────────────┐
│  🔔 Nova Atualização Disponível!    │
│                                     │
│  Versão Atual:  3.22.0              │
│  Nova Versão:   3.23.0              │
│                                     │
│  [Baixar Atualização] [Depois]     │
└─────────────────────────────────────┘

3. Usuário clica "Baixar"
4. Download em background (com barra de progresso)
5. Quando termina: "Instalar e Reiniciar"
6. Instala automaticamente
7. ✅ Atualizado para v3.23.0!
```

---

## 📄 Sistema Fiscal (NFC-e)

### Emissão de NFC-e (Nota Fiscal)

**Integração direta com SEFAZ** via NFePHP (sem intermediários).

### Configuração

**1. Certificado Digital A1:**
```
Certificado (.pfx): [Upload]
Senha do certificado: ••••••••

Validade: 01/2026 - 12/2026 ✅
```

**2. CSC (Código de Segurança):**
```
CSC ID: 1
CSC Token: ABC123XYZ789...

(Obtido no portal da SEFAZ)
```

**3. Configurações NFC-e:**
```
Série NFC-e: 1
Próximo número: 1234

Ambiente:
  ○ Homologação (testes)
  ● Produção

☑ Emitir NFC-e automaticamente ao pagar
```

**4. Dados Fiscais:**
```
Razão Social: MARMITARIA DA GI LTDA
CNPJ: 12.345.678/0001-90
Inscrição Estadual: 123.456.789.110
Regime Tributário: Simples Nacional

Endereço Fiscal:
  CEP: 13290-000
  Rua: Rua Principal
  Número: 123
  Bairro: Centro
  Cidade: Louveira
  UF: SP
```

### Classificação Fiscal de Produtos

**Opção 1: SELECT de Categorias (90% dos casos - GRÁTIS):**

```
Produto: Marmitex Grande

Categoria Fiscal:
  ○ 🍕 Alimentos Produzidos (NCM 19059090)     ← RECOMENDADO
  ○ 🥤 Bebidas Gerais (NCM 22029900)
  ○ 🍺 Bebidas Alcoólicas (NCM 22030000)
  ○ 💧 Águas (NCM 22021000)
  ○ 🍦 Sorvetes (NCM 21050000)
  ○ 🍰 Doces (NCM 19059090)
  ○ 🥖 Pães (NCM 19059010)

CFOP: 5102 (Venda dentro do estado)
CEST: - (não obrigatório para alimentos)

[Salvar]
```

**Opção 2: IA Tributa AI (10% específicos - PAGO):**

```
[Buscar com IA]

┌─────────────────────────────────────┐
│  ⚠️ Classificação com IA             │
├─────────────────────────────────────┤
│  ATENÇÃO: Este recurso utiliza IA   │
│  para classificação fiscal.         │
│                                     │
│  • Não substitui um contador        │
│  • Revise os dados antes de salvar  │
│  • Responsabilidade é sua           │
│                                     │
│  ☐ Li e entendo os termos           │
│  [Continuar]                        │
└─────────────────────────────────────┘

Buscando... ⏳

Resultado:
  NCM: 21069090
  Descrição: Marmitex (Preparação alimentar)
  CFOP: 5102
  CEST: -
  Confiança: 🟢 85%

☑ Revisei e confirmo os dados

[Salvar]
```

### Fluxo de Emissão

```
1. Cliente paga pedido → payment_status = 'paid'
2. OrderFiscalObserver dispara job EmitirNFCeJob
3. Job processa em background (fila 'nfce')
4. SefazService monta XML da nota
5. Assina com certificado A1
6. Envia para SEFAZ
7. SEFAZ valida e retorna chave de acesso (44 dígitos)
8. Sistema armazena em fiscal_notes table
9. ✅ NFC-e emitida!
```

**Proteções:**
- Lock distribuído (evita duplicação)
- Rate limiting (10 NFC-e/min por tenant)
- Retry automático (3x: 30s, 60s, 120s)
- Timeout 120s por tentativa

### Consultar NFC-e

**Lista de Notas Emitidas:**

| Número | Chave | Pedido | Cliente | Valor | Status | Data | Ações |
|--------|-------|--------|---------|-------|--------|------|-------|
| 1234 | 3526... | #1234 | João | R$ 22,80 | ✅ Autorizada | 16/03 10:30 | XML / DANFE |
| 1233 | 3526... | #1233 | Maria | R$ 35,00 | ✅ Autorizada | 16/03 10:15 | XML / DANFE |
| 1232 | 3526... | #1232 | Pedro | R$ 80,00 | ⏳ Processando | 16/03 10:00 | - |
| 1231 | 3526... | #1231 | Ana | R$ 45,00 | ❌ Rejeitada | 16/03 09:45 | Erro |

**Ações:**
- 📄 **Download XML**: Arquivo da nota fiscal
- 🖨️ **DANFE**: Imprimir cupom fiscal
- ❌ **Cancelar NFC-e**: (até 24h após emissão)
- 🔄 **Reemitir**: (se rejeitada)

---

## ⚙️ Configurações Avançadas

### 1. Gateway de Pagamento (Pagar.me)

**Criar Recebedor:**

```
┌─────────────────────────────────────┐
│  Criar Recebedor Pagar.me           │
├─────────────────────────────────────┤
│  Dados Pessoais:                    │
│  Nome completo: João da Silva       │
│  CPF/CNPJ: 123.456.789-00          │
│  Email: joao@marmitariadagi.com.br │
│  Telefone: (19) 98765-4321         │
│                                     │
│  Dados Bancários:                   │
│  Banco: [341 - Itaú ▼]             │
│  Tipo de conta: Conta Corrente     │
│  Agência: 1234                     │
│  Dígito agência: 5                 │
│  Conta: 56789                      │
│  Dígito conta: 0                   │
│                                     │
│  [Criar Recebedor]                 │
└─────────────────────────────────────┘

Aguarde... ⏳

✅ Recebedor criado com sucesso!
ID: re_cmm5d1tp701mh0l9t6uaaovn3
Status: Ativo

Agora você pode receber pagamentos!
```

**Split de Pagamentos (Automático):**

```
Pedido de R$ 100 com PIX:

Split automático:
  R$ 97,00 → Restaurante (97%)
  R$ 3,00 → Plataforma YumGo (3% comissão)

Taxas Pagar.me:
  PIX: R$ 0,99 (0,99%)
  Total descontado: R$ 0,99

Restaurante recebe: R$ 96,01 ✅
(deposita em D+1 útil na conta cadastrada)
```

### 2. Webhooks

**O que são Webhooks?**

Notificações automáticas enviadas pelo gateway (Pagar.me) para o YumGo quando algo acontece.

**Eventos Suportados:**

| Evento | Quando | O que faz |
|--------|--------|-----------|
| `order.paid` | PIX/Cartão confirmado | Marca pedido como pago + Dispara impressão |
| `order.payment_failed` | Pagamento recusado | Marca como falhou + Notifica restaurante |
| `order.refunded` | Reembolso processado | Marca como reembolsado |

**Configuração (Automática):**

URL do Webhook: `https://yumgo.com.br/api/v1/webhooks/pagarme`
Token: Configurado automaticamente

### 3. Reverb (WebSocket - Tempo Real)

**Para que serve?**

Comunicação em tempo real entre:
- Backend Laravel ↔ YumGo Bridge (impressão)
- Backend Laravel ↔ Cliente (acompanhamento pedido)
- Backend Laravel ↔ Painel (notificações)

**Configuração:**

```env
REVERB_APP_ID=123456
REVERB_APP_KEY=abc123...
REVERB_APP_SECRET=xyz789...
REVERB_HOST=ws.yumgo.com.br
REVERB_PORT=443
REVERB_SCHEME=wss
```

**Como funciona:**

```
1. Cliente faz pedido
2. Backend dispara: NewOrderEvent
3. Reverb broadcasting envia via WebSocket
4. Bridge recebe em tempo real (< 1 segundo)
5. Bridge imprime automaticamente ✅
```

### 4. Tipos de Restaurante

**15 Tipos Disponíveis:**

1. 🍕 Pizzaria
2. 🍔 Hamburgueria
3. 🍱 Marmitaria
4. 🍺 Boteco/Bar
5. 🍰 Doceria/Confeitaria
6. 🥖 Padaria
7. 🍣 Japonês/Sushi
8. 🍝 Italiana
9. 🌮 Mexicana
10. 🥗 Saudável/Natural
11. 🍗 Frango/Churrasco
12. 🍜 Asiática
13. 🥘 Brasileira
14. 🦞 Frutos do Mar
15. ☕ Café/Lanchonete

**Templates Automáticos:**

Quando você cria um restaurante, categorias são criadas automaticamente:

**Pizzaria:**
- Pizzas Tradicionais
- Pizzas Especiais
- Pizzas Doces
- Bebidas
- Sobremesas

**Marmitaria:**
- Marmitas
- Porções
- Bebidas
- Sobremesas

*(Economiza 30 minutos de configuração inicial!)*

---

## 🔧 Solução de Problemas

### Pedidos não estão sendo impressos

**Verificar:**

1. ✅ Bridge está aberto e conectado?
   - Abrir YumGo Bridge
   - Verificar status: "🟢 Online"

2. ✅ Impressora está ligada?
   - Verificar cabo USB/Rede
   - Imprimir teste pelo Windows

3. ✅ Papel na impressora?
   - Recolocar papel
   - Clicar "Reimprimir" no painel

4. ✅ Payment status correto?
   - Pedidos com PIX: aguardam pagamento
   - Pedidos com dinheiro: imprimem imediatamente

**Monitor de Impressão:**

Ir para: `/painel/monitor-impressao`
- Ver alertas de falha
- Reimprimir pedidos falhados
- Verificar status do Bridge

### Cliente não consegue finalizar pedido

**Erro comum: "Selecione um endereço de entrega"**

**Solução:**
1. Verificar se bairro está ativo em "Zonas de Entrega"
2. Adicionar bairro se não existir
3. Cliente tentar novamente

**Erro: "Cupom inválido"**

**Verificar:**
- Cupom está ativo?
- Dentro da validade?
- Valor mínimo atingido?
- Limite de uso não excedido?

### NFC-e não está sendo emitida

**Verificar:**

1. ✅ Certificado Digital válido?
   - Ir para: "Configurações Fiscais"
   - Verificar validade do certificado
   - Reenviar se expirado

2. ✅ CSC configurado?
   - CSC ID e Token corretos
   - Obtidos no portal da SEFAZ

3. ✅ Produtos com NCM?
   - Todos produtos precisam ter NCM/CFOP
   - Editar produtos e adicionar

4. ✅ Ambiente correto?
   - Homologação (testes)
   - Produção (real)

**Ver logs:**
- Ir para: "Notas Fiscais"
- Clicar em nota rejeitada
- Ver mensagem de erro da SEFAZ

### Gateway de pagamento não funciona

**Pagar.me:**

**Verificar:**
1. ✅ Recebedor criado?
   - Ir para: "Dados para Recebimento"
   - Status deve ser "✅ Configurada"

2. ✅ Chaves corretas?
   - API Key (sk_live_...)
   - Encryption Key (pk_live_...)

3. ✅ Webhook configurado?
   - Verificar se URL está acessível
   - Testar: `https://yumgo.com.br/api/v1/webhooks/pagarme`

**Teste:**
- Fazer pedido de teste
- Pagar com PIX
- Verificar se mudou para "Pago"

---

## ❓ Perguntas Frequentes

### 1. Como faço para mudar o plano do meu restaurante?

**Admin Central:**
1. Ir para "Restaurantes"
2. Editar restaurante
3. Mudar campo "Plano"
4. Salvar

A comissão é ajustada automaticamente nos próximos pedidos.

### 2. Posso ter mais de um restaurante?

**Sim!** Você pode cadastrar quantos restaurantes quiser no painel central.

Cada restaurante tem:
- ✅ Dados isolados (LGPD)
- ✅ URL própria (slug.yumgo.com.br)
- ✅ Configurações independentes
- ✅ Cashback próprio
- ✅ Gateway de pagamento próprio

### 3. Como funcionam os níveis de cashback?

O cliente sobe de nível automaticamente baseado no número de pedidos:

- 🥉 **Bronze**: 0-5 pedidos (3% cashback)
- 🥈 **Prata**: 6-15 pedidos (5% cashback)
- 🥇 **Ouro**: 16-30 pedidos (7% cashback)
- 💎 **Platina**: 31+ pedidos + R$ 1000 gastos (10% cashback)

O restaurante pode personalizar totalmente esses valores!

### 4. O cliente pode usar cashback em qualquer restaurante?

**NÃO!** O cashback é isolado por restaurante.

- Cliente tem R$ 10 na Marmitaria da Gi
- Cliente tem R$ 5 na Parker Pizzaria
- São saldos separados

**Por quê?**
- Cada restaurante paga seu próprio cashback
- Evita subsídio cruzado
- Incentiva fidelidade ao mesmo lugar

### 5. Quanto tempo demora para receber o dinheiro?

**Pagar.me:**
- PIX: D+1 útil (próximo dia útil)
- Cartão de Crédito: D+30 (30 dias)
- Cartão de Débito: D+1 útil

**Asaas:**
- Similar ao Pagar.me

O dinheiro cai automaticamente na conta cadastrada.

### 6. Posso emitir NFC-e sem certificado digital?

**NÃO.** A SEFAZ exige certificado digital A1 ou A3.

**Como obter:**
1. Comprar em uma Autoridade Certificadora (Serasa, Certisign, etc)
2. Escolher tipo A1 (arquivo .pfx - mais fácil)
3. Custo: ~R$ 200/ano

### 7. Como adiciono um novo usuário no painel?

1. Ir para: `/painel/users`
2. Clicar "Novo Usuário"
3. Preencher dados
4. Escolher função (Admin, Gerente, Funcionário)
5. Definir permissões
6. Salvar

O usuário receberá email com acesso.

### 8. Posso mudar a taxa de entrega por bairro?

**SIM!**

1. Ir para: "Zonas de Entrega"
2. Editar bairro
3. Mudar taxa (ex: R$ 5,00 → R$ 7,00)
4. Salvar

A mudança afeta apenas pedidos novos.

### 9. Como funciona a entrega grátis?

Configure em "Configurações" → "Entrega":

```
☑ Habilitar entrega grátis
Valor mínimo: R$ 50,00
```

Pedidos acima de R$ 50 = frete grátis automaticamente!

### 10. O Bridge funciona em qual sistema operacional?

**Apenas Windows** (por enquanto).

Versões suportadas:
- ✅ Windows 10
- ✅ Windows 11
- ❌ Windows 7 (não suportado)
- ❌ Mac / Linux (futuro)

### 11. Quantas impressoras posso conectar?

**Ilimitadas!**

Você pode configurar:
- 1x Balcão (recibo completo)
- 1x Cozinha (apenas items)
- 1x Bar (apenas bebidas)
- E quantas mais precisar!

### 12. Como faço backup dos dados?

**Automático!**

O YumGo faz backup automático:
- ✅ Diário (retido 7 dias)
- ✅ Semanal (retido 4 semanas)
- ✅ Mensal (retido 12 meses)

**Em caso de problema:**
- Entre em contato com suporte
- Informamos qual backup restaurar
- Dados são recuperados

### 13. Posso customizar as cores do site?

**Sim! (em breve)**

Próxima versão terá:
- Escolher cor primária
- Upload de logo
- Customizar fonte
- Tema claro/escuro

### 14. Como cancelar assinatura?

**Admin Central:**
1. Ir para "Restaurantes"
2. Editar restaurante
3. Status: "Inativo"
4. Confirmar

O restaurante para de receber pedidos mas os dados são mantidos por 30 dias.

### 15. Tem limite de pedidos por mês?

**Depende do plano:**

- **Starter**: 500 pedidos/mês
- **Pro**: 2000 pedidos/mês
- **Enterprise**: Ilimitado

Se ultrapassar, é cobrado:
- R$ 0,50 por pedido extra (Starter/Pro)
- R$ 0,00 (Enterprise)

---

## 📞 Suporte

**Email:** suporte@yumgo.com.br
**WhatsApp:** (19) 99999-9999
**Horário:** Seg-Sex 9h-18h

**Documentação Online:**
https://docs.yumgo.com.br

**GitHub (Bugs/Features):**
https://github.com/orfeubr/yumgo/issues

---

## 🎉 Conclusão

O **YumGo** é a plataforma completa para transformar seu restaurante em um delivery de sucesso, com:

- ✅ **Economia de até 90%** em comissões
- ✅ **Cashback configurável** para fidelizar clientes
- ✅ **Impressão automática** sem erros
- ✅ **Sistema fiscal** integrado
- ✅ **Dados 100% isolados** (LGPD)
- ✅ **Suporte dedicado**

**Comece agora e veja a diferença!** 🚀

---

**Manual YumGo v1.0**
*Última atualização: 16/03/2026*
*© 2026 YumGo - Todos os direitos reservados*
