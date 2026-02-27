# 🗺️ Roadmap de Funcionalidades - DeliveryPro

## ✅ Funcionalidades IMPLEMENTADAS

### Core System
- ✅ Multi-tenant (PostgreSQL Schemas)
- ✅ Domínios automáticos
- ✅ Painel Admin Plataforma (Filament 3)
- ✅ Painel Admin Restaurante (Filament 3)
- ✅ PWA Instalável
- ✅ Layout Responsivo (Mobile + Desktop)
- ✅ SSL Cloudflare

### Produtos & Cardápio
- ✅ CRUD Produtos
- ✅ Categorias
- ✅ Variações (P, M, G)
- ✅ Adicionais
- ✅ Pizza (meio a meio, bordas)
- ✅ Imagens HD
- ✅ Tags (JSON - backend pronto)
- ✅ **Cardápio Digital** (catálogo público)
- ✅ **QR Code do Cardápio** (impressão para mesas)

### Pedidos
- ✅ Carrinho localStorage
- ✅ Checkout completo
- ✅ Pagamento Asaas (PIX + Cartão)
- ✅ Pagamento na entrega
- ✅ Numeração customizável
- ✅ Status tracking
- ✅ Histórico de pedidos

### Pagamentos
- ✅ **Integração Asaas**
- ✅ Split automático (restaurante + plataforma)
- ✅ Webhook (confirmação)
- ✅ PIX QR Code
- ✅ Cartão de crédito/débito
- ✅ Dinheiro na entrega
- ✅ Maquininha na entrega

### Cashback & Fidelidade
- ✅ 4 níveis (Bronze, Prata, Ouro, Platina)
- ✅ Configurável por restaurante
- ✅ Aniversário com bônus
- ✅ Expiração configurável
- ✅ Usado como desconto

### Configurações
- ✅ 10 abas de configurações
- ✅ NFCe (estrutura)
- ✅ Numeração de pedidos
- ✅ Zonas de entrega
- ✅ Horários de funcionamento
- ✅ Informações do estabelecimento
- ✅ Gestão de Equipe (UserResource)

### Dashboard & Relatórios
- ✅ **Dashboard com Gráficos** (8 widgets)
  - Métricas principais
  - Vendas últimos 7 dias
  - Faturamento últimos 7 dias
  - Top 10 produtos mais vendidos
  - Distribuição por status
  - Faturamento por categoria
  - Evolução mensal (6 meses)
  - Últimos pedidos

---

## 🔨 Funcionalidades EM DESENVOLVIMENTO

### 1. **Tags nos Produtos** (Interface Filament)
**Status:** Backend pronto, falta UI

**Campos criados:**
```php
$table->json('tags')->nullable(); // ['Vegano', 'Sem Glúten', 'Apimentado', etc.]
```

**TODO:**
- [ ] Adicionar campo TagsInput no ProductResource
- [ ] Criar filtro por tags na listagem
- [ ] Exibir badges no catálogo público
- [ ] Ícones visuais (🌱 Vegano, 🚫 Sem Glúten, 🌶️ Apimentado)

---

### 2. **Gestão de Estoque** (Lógica de Decremento)
**Status:** Backend pronto, falta lógica

**Campos criados:**
```php
$table->boolean('stock_enabled')->default(false);
$table->integer('stock_quantity')->default(0);
$table->integer('stock_min_alert')->default(5);
$table->boolean('stock_alert_sent')->default(false);
```

**TODO:**
- [ ] Observer em Order: decrementar estoque ao confirmar pedido
- [ ] Notificação quando atingir estoque mínimo
- [ ] Bloquear pedido se estoque = 0
- [ ] Relatório de produtos com estoque baixo
- [ ] Histórico de movimentação de estoque

---

### 3. **Integração Asaas - Auto-preenchimento do ID da Conta**
**Status:** Bug identificado

**Problema:**
- Campo `asaas_account_id` NÃO está sendo preenchido automaticamente
- TenantObserver deveria criar sub-conta Asaas ao criar tenant

**TODO:**
- [ ] Corrigir TenantObserver para criar sub-conta automaticamente
- [ ] Preencher `asaas_account_id` após criação
- [ ] Criar webhook automaticamente
- [ ] Testar fluxo completo de onboarding
- [ ] Adicionar validação: bloquear ativação se asaas_account_id vazio

---

## 📋 ROADMAP - Próximas Funcionalidades

### Prioridade ALTA (Próximas 2 semanas)

#### 4. **Cadastro de Entregadores** 🚗
**Descrição:** Gestão completa de entregadores

**Recursos:**
- [ ] CRUD de entregadores
- [ ] Campos: Nome, CPF, CNH, Telefone, Veículo, Placa
- [ ] Status: Disponível, Em entrega, Offline
- [ ] Histórico de entregas
- [ ] **Pagamento de entrega** (calcular comissão/diária)
- [ ] **Contato direto** (WhatsApp integrado)
- [ ] Geolocalização em tempo real (Google Maps API)
- [ ] Ranking de desempenho

**Modelo:**
```php
app/Models/DeliveryDriver.php
- id, tenant_id, name, cpf, cnh, phone
- vehicle_type, vehicle_plate
- status, rating, total_deliveries
- bank_account (para pagamento)
```

---

#### 5. **Agendamento de Pedidos** 📅
**Descrição:** Cliente pode agendar pedido para data/hora futura

**Recursos:**
- [ ] Seletor de data e hora no checkout
- [ ] Validar horário de funcionamento
- [ ] Notificar restaurante com antecedência
- [ ] Fila de pedidos agendados
- [ ] Dashboard: pedidos agendados vs imediatos
- [ ] Limite de pedidos por horário

**Campos em Order:**
```php
$table->boolean('is_scheduled')->default(false);
$table->dateTime('scheduled_for')->nullable();
$table->enum('schedule_status', ['pending', 'confirmed', 'cancelled']);
```

---

#### 6. **Display para Cozinha (KDS - Kitchen Display System)** 👨‍🍳
**Descrição:** Tela para cozinha acompanhar pedidos

**Recursos:**
- [ ] Tela dedicada `/painel/cozinha`
- [ ] Cards grandes com pedidos
- [ ] Cores por tempo (verde, amarelo, vermelho)
- [ ] Botão "Pedido Pronto"
- [ ] Som de notificação (novo pedido)
- [ ] Auto-refresh (10s)
- [ ] Impressão térmica automática
- [ ] Filtros: Pendente, Preparando, Pronto

**Interface:**
```
┌─────────────────────────────────────┐
│ 🔴 PEDIDO #0001 - 15 min atrás      │
│ Cliente: João Silva                  │
│ 2x Pizza Margherita G                │
│ 1x Refrigerante 2L                   │
│ [MARCAR COMO PRONTO]                 │
└─────────────────────────────────────┘
```

---

### Prioridade MÉDIA (Próximas 4 semanas)

#### 7. **Gestão Financeira NOVA** 💰
**Descrição:** Módulo financeiro completo

**Recursos:**
- [ ] Fluxo de caixa (entradas e saídas)
- [ ] Contas a pagar
- [ ] Contas a receber
- [ ] Categorias de despesas
- [ ] Relatório DRE (Demonstrativo de Resultados)
- [ ] Gráfico de lucratividade
- [ ] Conciliação bancária
- [ ] Exportar XLSX

**Modelos:**
```php
app/Models/FinancialTransaction.php
- type: 'income' | 'expense'
- category_id, amount, date
- description, payment_method
- status: 'paid' | 'pending'
```

---

#### 8. **Robô com IA para WhatsApp, Facebook e Instagram** 🤖
**Descrição:** Atendimento automático com IA

**Recursos:**
- [ ] Integração WhatsApp Business API
- [ ] Integração Facebook Messenger
- [ ] Integração Instagram Direct
- [ ] IA para responder perguntas (OpenAI GPT-4)
- [ ] **Suporte a áudios do WhatsApp** (transcrição + IA)
- [ ] Enviar cardápio
- [ ] Fazer pedidos via chat
- [ ] Status do pedido
- [ ] Horários de atendimento
- [ ] Fallback para humano (se IA não souber)

**Fluxo:**
```
Cliente: "Oi, qual o cardápio?"
Bot: "Olá! 😊 Somos a Marmitaria da Gi.
      Nosso cardápio: [link]
      Posso te ajudar com algum pedido?"

Cliente: [envia áudio: "quero uma pizza grande calabresa"]
Bot: [transcreve áudio]
     "Entendi! Pizza Calabresa tamanho Grande, certo?
      Valor: R$ 45,00
      Deseja adicionar borda recheada? (+R$ 8,00)"
```

**Tecnologias:**
- Evolution API (WhatsApp)
- Facebook Graph API
- OpenAI Whisper (transcrição de áudio)
- OpenAI GPT-4 (conversação)

---

#### 9. **Relatórios XLSX Exportáveis** 📊
**Descrição:** Exportar dados em Excel

**Relatórios:**
- [ ] Vendas por período
- [ ] Produtos mais vendidos
- [ ] Clientes frequentes
- [ ] Faturamento por categoria
- [ ] Relatório financeiro
- [ ] Relatório de estoque
- [ ] Relatório de cashback
- [ ] Relatório de entregadores

**Pacote:**
```bash
composer require maatwebsite/excel
```

**Uso:**
```php
Route::get('/relatorios/vendas/export', [ReportController::class, 'exportSales']);
```

---

### Prioridade BAIXA (Futuro)

#### 10. **Modo Escuro** 🌙
- [ ] Toggle no painel admin
- [ ] Salvar preferência por usuário
- [ ] Aplicar em todas as páginas

#### 11. **Notificação Push** 🔔
- [ ] Firebase Cloud Messaging
- [ ] Notificar quando pedido chega
- [ ] Notificar mudança de status
- [ ] Notificar promoções

#### 12. **Chat em Tempo Real** 💬
- [ ] Livewire + Pusher
- [ ] Cliente ↔ Restaurante
- [ ] Restaurante ↔ Entregador
- [ ] Histórico de conversas

#### 13. **App Mobile Nativo (Flutter)** 📱
- [ ] App Android/iOS
- [ ] Todas as funcionalidades do PWA
- [ ] Notificações nativas
- [ ] Geolocalização
- [ ] Publicar nas stores

---

## 🐛 BUGS & MELHORIAS Identificadas

### Bugs para Corrigir:
1. ❌ **Asaas Account ID não preenche automaticamente** - CRÍTICO
2. ⚠️ Reformular outras páginas (Profile, Orders) - design novo
3. ⚠️ Recuperação de senha não implementada
4. ⚠️ Verificação de e-mail não implementada

### Melhorias Sugeridas:
1. Auto-refresh do dashboard (polling 30s)
2. Filtros de data nos relatórios
3. Comparação com período anterior
4. Meta de vendas vs realizado
5. Mapa de entregas (Google Maps)
6. NPS (satisfação dos clientes)
7. Previsão de vendas (ML)

---

## 📅 Cronograma Estimado

| Funcionalidade | Prioridade | Prazo Estimado | Complexidade |
|---------------|-----------|----------------|--------------|
| Tags Produtos | Alta | 2 dias | Baixa |
| Gestão Estoque | Alta | 3 dias | Média |
| Fix Asaas Auto-create | **CRÍTICA** | 1 dia | Média |
| Cadastro Entregadores | Alta | 5 dias | Média |
| Agendamento Pedidos | Alta | 4 dias | Média |
| KDS Cozinha | Alta | 3 dias | Baixa |
| Gestão Financeira | Média | 10 dias | Alta |
| Robô IA WhatsApp | Média | 15 dias | **Muito Alta** |
| Relatórios XLSX | Média | 5 dias | Média |
| Modo Escuro | Baixa | 2 dias | Baixa |
| Push Notifications | Média | 4 dias | Média |
| Chat Tempo Real | Média | 6 dias | Alta |
| App Flutter | Baixa | 60 dias | **Muito Alta** |

---

## 💰 ROI Estimado por Funcionalidade

| Funcionalidade | Impacto Vendas | Diferencial Competitivo | Urgência |
|---------------|----------------|------------------------|----------|
| Cadastro Entregadores | 🔥🔥🔥 Alto | ⭐⭐⭐ Essencial | 🚨 Urgente |
| KDS Cozinha | 🔥🔥🔥 Alto | ⭐⭐⭐ Essencial | 🚨 Urgente |
| Agendamento Pedidos | 🔥🔥 Médio | ⭐⭐ Importante | ⚡ Alta |
| Robô IA WhatsApp | 🔥🔥🔥 Alto | ⭐⭐⭐⭐ Inovador | ⚡ Alta |
| Gestão Financeira | 🔥 Baixo | ⭐⭐ Importante | 📌 Média |
| Relatórios XLSX | 🔥 Baixo | ⭐ Bom ter | 📌 Média |
| App Flutter | 🔥🔥 Médio | ⭐⭐⭐ Essencial | 📅 Longo prazo |

---

## 🎯 Foco para Próxima Sprint

**Prioridade #1:**
1. ✅ Corrigir bug Asaas Account ID
2. ✅ Implementar Cadastro de Entregadores
3. ✅ Implementar KDS (Cozinha)

**Prioridade #2:**
4. ✅ Agendamento de Pedidos
5. ✅ Tags nos Produtos (UI)
6. ✅ Gestão de Estoque (lógica)

**Depois:**
7. Gestão Financeira
8. Robô IA WhatsApp
9. Relatórios XLSX

---

**📝 Atualizado em:** 22/02/2026
**👨‍💻 Status:** Em desenvolvimento ativo
**🚀 Objetivo:** Dominar mercado de delivery!
