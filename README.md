# 🍔 YumGo - Sistema de Delivery Multi-tenant

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?style=flat&logo=postgresql)](https://postgresql.org)
[![Filament](https://img.shields.io/badge/Filament-3-FFAA00?style=flat)](https://filamentphp.com)
[![License](https://img.shields.io/badge/License-Proprietário-red.svg)](LICENSE)

Sistema de delivery multi-tenant completo, com comissão justa, cashback configurável e gestão inteligente de pedidos.

---

## 🎯 Diferenciais

### 💰 **Comissão Muito Menor que iFood**
- **YumGo:** 1-3% de comissão
- **iFood:** ~30% de comissão
- **Economia:** R$ 1.500+/mês em 1000 pedidos!

### 💳 **Pagamentos Baratos (Pagar.me)**
- **PIX:** R$ 0,99/transação
- **Cartão:** 2,99% + R$ 0,49
- **Split automático:** 1 transação = 1 taxa

### 🎁 **Cashback Configurável**
- Restaurante define % de cada nível
- Tiers: Bronze → Prata → Ouro → Platina
- Proteção completa contra fraudes
- Estorno automático em cancelamentos

### 🔒 **Isolamento Total (Multi-tenant)**
- PostgreSQL schemas separados
- Impossível vazamento de dados
- 1 restaurante = 1 schema isolado

---

## ⚡ Stack Tecnológica

**Backend:**
- **Laravel 11** (PHP 8.3) - Framework principal
- **PostgreSQL 16** - Banco de dados multi-schema
- **Redis 7** - Cache e filas
- **Filament 3** - Painel administrativo

**Frontend:**
- **Blade** - Templates server-side
- **Alpine.js** - Interatividade leve
- **Tailwind CSS** - Estilização
- **Design:** Inspirado no iFood (clean e moderno)

**Pagamentos:**
- **Pagar.me API v5** - Gateway principal
- **PIX + Cartão** - Métodos suportados
- **Webhooks** - Confirmação automática

**Infra:**
- **Nginx** - Web server
- **Supervisor** - Gerenciamento de filas
- **Cloudflare** - CDN e SSL

---

## 🚀 Funcionalidades Principais

### 👨‍💼 **Plataforma Central**
- ✅ Gestão de restaurantes (tenants)
- ✅ Planos e assinaturas
- ✅ Dashboard com métricas em tempo real
- ✅ Auditoria completa de ações
- ✅ Faturamento automático

### 🍕 **Painel do Restaurante**
- ✅ Gestão de produtos e categorias
- ✅ Cardápio semanal configurável
- ✅ Zonas de entrega por bairros
- ✅ Configurações de pagamento
- ✅ Relatórios e estatísticas
- ✅ Gestão de cupons de desconto

### 📱 **Aplicativo do Cliente**
- ✅ Cardápio responsivo (mobile + desktop)
- ✅ Carrinho persistente
- ✅ Checkout simplificado
- ✅ Pagamento PIX com QR Code
- ✅ Rastreamento de pedidos
- ✅ Histórico e cashback

### 🎨 **Produtos Especiais**
- ✅ **Pizzas:** Meio a meio, bordas, tamanhos
- ✅ **Marmitex:** Proteínas + acompanhamentos
- ✅ **Combos:** Produtos agrupados
- ✅ **Adicionais:** Extras configuráveis

---

## 💰 Sistema de Cashback

### **Configuração Flexível**
- Restaurante define % de cada tier
- Validade configurável (30, 60, 90 dias)
- Bônus de aniversário (dobro)
- Indique e ganhe

### **Segurança Total**
- ✅ Cashback só em pagamentos aprovados
- ✅ Estorno automático em cancelamentos
- ✅ Proteção contra fraudes
- ✅ Auditoria completa (cashback_transactions)

### **Tiers de Fidelidade**
```
Bronze  → 0-10 pedidos   → 2% cashback
Prata   → 11-30 pedidos  → 3% cashback
Ouro    → 31-60 pedidos  → 5% cashback
Platina → 61+ pedidos    → 7% cashback
```

---

## 🗄️ Arquitetura do Banco

### **Schema PUBLIC (Plataforma)**
```sql
- tenants (restaurantes)
- plans (planos de assinatura)
- subscriptions (assinaturas ativas)
- invoices (faturas)
- domains (domínios personalizados)
- platform_users (admins)
- audit_logs (auditoria)
```

### **Schema TENANT_* (Por Restaurante)**
```sql
- customers (clientes + cashback)
- orders (pedidos)
- order_items (items do pedido)
- payments (transações)
- products (cardápio)
- categories (categorias)
- cashback_transactions (histórico cashback)
- cashback_settings (config cashback)
- neighborhoods (zonas de entrega)
- coupons (cupons de desconto)
```

---

## 📦 Instalação

### **Requisitos**
- PHP 8.3+
- PostgreSQL 16+
- Redis 7+
- Composer 2+
- Node.js 20+ (opcional)

### **Passo a Passo**

1. **Clone o repositório:**
```bash
git clone https://github.com/orfeubr/yumgo.git
cd yumgo
```

2. **Instale as dependências:**
```bash
composer install
```

3. **Configure o .env:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure o banco:**
```bash
# Edite .env com suas credenciais PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=yumgo
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

5. **Execute as migrations:**
```bash
php artisan migrate
php artisan db:seed
```

6. **Configure Pagar.me:**
```bash
# Obtenha as chaves em: https://dashboard.pagar.me
PAGARME_API_KEY=sk_test_...
PAGARME_ENCRYPTION_KEY=pk_test_...
```

7. **Inicie o servidor:**
```bash
php artisan serve
```

8. **Acesse:**
- **Plataforma:** http://localhost:8000/admin
- **Restaurante:** http://tenant.localhost:8000

---

## 🧪 Testes Realizados

### ✅ **QR Code PIX**
```
✅ Pedido criado com sucesso
✅ QR Code gerado automaticamente
✅ Imagem base64 salva no banco
✅ Código copiar/colar disponível
✅ Transaction ID registrado
```

### ✅ **Cashback**
```
✅ Só gerado em pagamentos aprovados
✅ Estorno completo em cancelamentos
✅ Proteção contra webhooks duplicados
✅ Previne saldo negativo
✅ Logs completos para auditoria
```

---

## 📊 Modelo de Negócio

### **Planos para Restaurantes**

| Plano | Mensalidade | Comissão | Features |
|-------|-------------|----------|----------|
| **Starter** | R$ 79/mês | 3% | Básico completo |
| **Pro** | R$ 149/mês | 2% | + Relatórios avançados |
| **Enterprise** | R$ 299/mês | 1% | + API, webhook, suporte |

**Trial:** 15 dias grátis em todos os planos

### **ROI Estimado**
```
1000 pedidos/mês × R$ 50 = R$ 50.000

Receita comissão: R$ 1.500 (3%)
Custo Pagar.me PIX: R$ 990
Lucro líquido: R$ 510/mês

vs Mercado Pago: PREJUÍZO -R$ 995/mês
ECONOMIA: R$ 1.505/mês! 🚀
```

---

## 🔧 Últimas Atualizações

### **01/03/2026**

**✅ QR Code PIX Corrigido**
- Validação obrigatória de credenciais
- Logs detalhados em todas etapas
- Mensagens de erro claras
- [Ver detalhes](CORRECAO-PAGARME-01-03-2026.md)

**✅ Proteção de Cashback**
- Cashback só em pagamentos aprovados
- Estorno completo em cancelamentos
- Proteção contra fraudes
- [Ver detalhes](PROTECAO-CASHBACK-01-03-2026.md)

**✅ Animação de Cozinha**
- Animação CSS temática no checkout
- Textos em português claro
- [Ver detalhes](ANIMACAO-COZINHA-01-03-2026.md)

---

## 📚 Documentação

- [Visão Geral do Projeto](PROJETO.md)
- [Guia de Instalação](INSTALL.md)
- [Sistema de Cashback](README-CASHBACK.md)
- [Integração Pagar.me](docs/ASAAS-INTEGRATION.md)
- [API REST](docs/API-LOCALIZACAO.md)
- [Roadmap](docs/ROADMAP-FUNCIONALIDADES.md)

---

## 🤝 Contribuindo

Este é um projeto proprietário. Para colaborar, entre em contato.

---

## 📝 Licença

Código proprietário. Todos os direitos reservados.

---

## 🏆 Créditos

**Desenvolvido com:**
- Laravel Framework
- Filament Admin Panel
- Pagar.me Payment Gateway
- PostgreSQL Database

**Inspiração de Design:**
- iFood Brasil

---

## 📞 Contato

- **GitHub:** [@orfeubr](https://github.com/orfeubr)
- **Repositório:** [github.com/orfeubr/yumgo](https://github.com/orfeubr/yumgo)

---

<div align="center">

**💪 Feito com Laravel e ❤️ para revolucionar o mercado de delivery!**

[🚀 Ver Demo](https://yumgo.com.br) · [📖 Documentação](docs/) · [🐛 Reportar Bug](https://github.com/orfeubr/yumgo/issues)

</div>
