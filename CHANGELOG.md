# Changelog

Todas as mudanças notáveis deste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

## [2026-03-09] - Auditoria de Segurança Completa

### 🔐 Segurança

#### Adicionado
- **Validação de carrinho:** Preços recalculados no backend (previne manipulação)
- **Race condition protection:** Lock pessimista em cupons (LOCK FOR UPDATE)
- **Tokenização de cartões:** Dados sensíveis não passam pelo servidor
- **Middleware BlockSensitiveCardData:** Bloqueia requisições com dados de cartão
- **Email fallback:** Gateways exigem email (usa cliente-{id}@{tenant}.yumgo.com.br)
- **Prevenção de enumeração:** forgotPassword não expõe se email existe
- **Rate limiting:** 5-100 req/min dependendo do endpoint
- **Sanitização XSS:** htmlspecialchars em inputs de texto

#### Corrigido
- **4 vulnerabilidades críticas:**
  - Manipulação de preços no carrinho
  - Race condition em cupons
  - Dados de cartão passando pelo servidor
  - Exposição de emails em forgot-password

- **6 melhorias de alto impacto:**
  - forgotPassword documentado como não implementado
  - PlatformSettings com cache (1h)
  - OrderController refatorado (complexidade 35→10)
  - MarketplaceController consolidado (50+ linhas removidas)
  - Messages.php para internacionalização
  - PHPDoc completo em Services críticos

### ⚡ Performance

#### Adicionado
- **Cache de platform settings:** 1 hora (reduz queries)
- **Cache de cashback percentage:** 1 hora (previne N+1)
- **Índices de banco:** order_number, customer_id, payment_status
- **Eager loading:** Reduz N+1 queries em pedidos

#### Otimizado
- Query de restaurantes no marketplace (with + pagination)
- Busca de products (índices + ILIKE)
- Webhooks processados em fila (assíncrono)

### 🎯 Sistema de Assinaturas

#### Adicionado
- **Planos com trial:** 14 dias grátis
- **Limites por plano:**
  - Pedidos/mês: 50 (Starter) → 500 (Pro) → Ilimitado (Enterprise)
  - Produtos: 50 → 500 → Ilimitado
- **Middleware CheckSubscription:** Valida limites antes de criar pedidos
- **Widget de limites:** Dashboard do restaurante
- **Assinaturas via Pagar.me:** Cobrança automática mensal

### 📱 Funcionalidades

#### Adicionado
- **Sistema de usuários e permissões:**
  - Roles: admin, manager, worker, finance, driver
  - 28 permissões granulares (products.edit, orders.view, etc)
  - Gestão em 2 locais (Admin Central + Painel Restaurante)

- **Importação de produtos:**
  - CSV com validação completa
  - Preview antes de importar
  - Atualização em lote
  - Template de exemplo

- **Classificação fiscal automática:**
  - SELECT de categorias (90% casos) - GRÁTIS
  - IA Tributa AI (10% específicos) - opcional
  - Token híbrido (plataforma compartilha)
  - Cache 30 dias

- **Emissão de NFC-e:**
  - SEFAZ direto via NFePHP
  - Redis + Filas assíncronas
  - Retry automático (3x)
  - Rate limiting 10/min

#### Corrigido
- **API crash resolvido (25/02/2026):**
  - Conflito middleware 'web' + 'api'
  - Removido 'api' das rotas tenant
  - PHP-FPM estável (zero crashes)

- **Gráficos do dashboard:**
  - Filtrar apenas payment_status='paid'
  - Antes: contava pending/canceled incorretamente

- **Filtros de categorias:**
  - Alpine.js x-show
  - Client-side (performance)

### 🎨 Design/UX

#### Adicionado
- **Visual estilo iFood:**
  - Cor primária: #EA1D2C
  - Botões circulares "+"
  - Layout responsivo
  - Categorias simplificadas

- **Checkout melhorado:**
  - Loading states
  - Overlay de processamento
  - Validações em tempo real
  - Feedback visual

### 🗂️ Arquitetura

#### Alterado
- **Migração Asaas → Pagar.me:**
  - Melhor para alto volume (>R$ 100k/mês)
  - Antifraude robusto
  - API v5 estável
  - Economia R$ 197/mês (1000 pedidos)

- **Multi-tenant PostgreSQL:**
  - Schema PUBLIC: Plataforma
  - Schema TENANT_*: Restaurantes
  - Isolamento total de dados
  - Zero vazamento entre tenants

### 📚 Documentação

#### Adicionado
- **CLAUDE.md:** Regras invioláveis para IA
- **MEMORY.md:** Decisões do projeto (800+ linhas)
- **messages.php:** 50+ mensagens padronizadas (i18n)
- **PHPDoc completo:** OrderService, CashbackService, PagarMeService
- **CHANGELOG.md:** Este arquivo

#### Criado
- `docs/ARQUITETURA-MULTI-TENANT.md`
- `docs/PAGARME-INTEGRATION.md`
- `docs/PAGARME-TOKENIZATION-SECURITY.md`
- `docs/USER-MANAGEMENT-SYSTEM.md`
- `docs/IMPORT-PRODUCTS-SYSTEM.md`
- `docs/SUBSCRIPTION-LIMITS-SYSTEM.md`

### 🧪 Testes

#### Adicionado
- Testes de validação de carrinho
- Testes de race condition em cupons
- Testes de exceção de valor mínimo de cashback
- Testes de sincronização de customer (central→tenant)

### 🗑️ Removido
- Dados sensíveis do storage/ (tenant*, logs)
- Arquivos de teste temporários
- Comentários de debug
- Código duplicado em MarketplaceController

---

## [Versões Anteriores]

### [2026-03-08] - Sistema de Assinaturas
- Implementação completa de planos e limites
- Widget de uso no dashboard

### [2026-03-07] - Geolocalização
- Cálculo de distância e taxa de entrega
- API ViaCep integrada

### [2026-03-06] - NFC-e Pronto
- Emissão direta na SEFAZ
- Filas Redis assíncronas

### [2026-03-05] - Sistema de Usuários
- Permissões granulares
- Roles customizáveis

### [2026-02-27] - Migração Pagar.me
- Gateway principal alterado
- Split automático configurado

### [2026-02-25] - API Corrigida
- Crash resolvido (middleware conflict)
- OrderService restaurado

### [2026-02-22] - Design iFood
- Visual atualizado
- UX melhorada

### [2026-02-20] - Multi-Tenant Launch
- Sistema multi-tenant completo
- Filament Admin operacional

---

## Próximas Releases

### [Planejado] - v2.1.0
- [ ] App mobile (React Native)
- [ ] Notificações push
- [ ] Relatórios avançados
- [ ] Sistema de fidelidade (gamificação)

### [Planejado] - v2.2.0
- [ ] Integração Mercado Pago
- [ ] Boleto bancário
- [ ] Agendamento de pedidos
- [ ] Cupons personalizados por cliente

---

**Última atualização:** 09/03/2026
**Versão atual:** 2.0.0
