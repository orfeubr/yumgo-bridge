# 🍕 YumGo - Plataforma de Delivery Multi-Tenant

> Sistema de delivery completo com comissão justa, cashback configurável e impressão automática de pedidos.

[![Licença](https://img.shields.io/badge/Licença-MIT-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![Electron](https://img.shields.io/badge/Electron-29-blue.svg)](https://electronjs.org)
[![PHP](https://img.shields.io/badge/PHP-8.3-purple.svg)](https://php.net)
[![Security](https://img.shields.io/badge/Security-95%25-brightgreen.svg)](#-segurança)
[![Performance](https://img.shields.io/badge/Performance-A-green.svg)](#-performance)
[![Code Quality](https://img.shields.io/badge/Code%20Quality-A-blue.svg)](docs/)

---

## 📋 Sobre o Projeto

**YumGo** é uma plataforma de delivery multi-tenant que oferece:

- **💰 Comissão Baixa:** 1-3% vs 30% do iFood
- **🎁 Cashback Configurável:** Cada restaurante define suas regras
- **🖨️ Impressão Automática:** App desktop imprime pedidos em tempo real
- **🔐 Multi-Tenant:** PostgreSQL schemas isolados por restaurante
- **💳 Gateway de Pagamento:** Pagar.me (PIX 0,99%, Cartão 3-4%)
- **📄 Nota Fiscal:** Emissão NFC-e direto na SEFAZ

---

## 🚀 Funcionalidades Principais

### Para Restaurantes

- ✅ **Painel Admin Completo** (Filament)
  - Gerenciamento de produtos, categorias e variações
  - Configuração de horários e áreas de entrega
  - Dashboard com gráficos em tempo real
  - Relatórios financeiros e de vendas

- ✅ **Sistema de Cashback**
  - 4 níveis (Bronze, Prata, Ouro, Platina)
  - Configurável por restaurante
  - Bônus de aniversário
  - Indique e ganhe

- ✅ **Impressão Automática** ([YumGo Bridge](./electron-bridge))
  - App desktop para Windows/macOS/Linux
  - Detecta impressoras USB automaticamente
  - Configurações avançadas (cópias, logo, largura papel)
  - WebSocket em tempo real
  - Auto-update automático

- ✅ **Integração de Pagamento**
  - Pagar.me (principal)
  - Asaas (legado)
  - Split automático (restaurante + plataforma)
  - PIX e Cartão

### Para Clientes

- ✅ **Loja Online Responsiva**
  - Design estilo iFood
  - Busca e filtros por categoria
  - Carrinho com cashback
  - Cupons de desconto

- ✅ **Sistema de Pedidos**
  - Delivery ou retirada
  - Rastreamento em tempo real
  - Histórico de pedidos
  - Favoritos

---

## 🏗️ Arquitetura

```
┌─────────────────────────────────────────────────────────┐
│ FRONTEND (Cliente)                                      │
│ ├─ Blade + Alpine.js                                   │
│ ├─ Tailwind CSS                                        │
│ └─ PWA (offline-first)                                 │
└─────────────────────────────────────────────────────────┘
                           │
                           ↓
┌─────────────────────────────────────────────────────────┐
│ BACKEND (Laravel 11 + PHP 8.3)                         │
│ ├─ Filament 3 (Admin Panel)                           │
│ ├─ Multi-Tenant (stancl/tenancy)                      │
│ ├─ PostgreSQL 16 (Schemas isolados)                   │
│ ├─ Redis 7 (Cache + Queues)                           │
│ ├─ Laravel Reverb (WebSocket)                         │
│ └─ NFePHP (NFC-e SEFAZ)                               │
└─────────────────────────────────────────────────────────┘
                           │
                           ├─ WebSocket ─────────────────┐
                           │                              │
                           ↓                              ↓
┌───────────────────────────────────┐   ┌─────────────────────────────┐
│ GATEWAY DE PAGAMENTO              │   │ APP DESKTOP                 │
│ ├─ Pagar.me (PIX + Cartão)       │   │ ├─ Electron 29             │
│ └─ Asaas (Legado)                 │   │ ├─ Impressão USB/Rede      │
└───────────────────────────────────┘   │ └─ Auto-update (GitHub)    │
                                        └─────────────────────────────┘
```

---

## 📦 Stack Tecnológica

### Backend
- **Framework:** Laravel 11
- **PHP:** 8.3
- **Database:** PostgreSQL 16 (multi-schema)
- **Cache:** Redis 7
- **Queue:** Redis + Supervisor
- **WebSocket:** Laravel Reverb
- **Admin:** Filament 3

### Frontend
- **Template:** Blade
- **JS Framework:** Alpine.js
- **CSS:** Tailwind CSS 3
- **PWA:** Workbox

### App Desktop (YumGo Bridge)
- **Framework:** Electron 29
- **Impressão:** ESC/POS (node-escpos)
- **WebSocket:** Pusher-js + Laravel Echo
- **Storage:** electron-store
- **Update:** electron-updater

### Infraestrutura
- **Servidor:** AWS EC2
- **Proxy:** Nginx
- **SSL:** Let's Encrypt
- **CI/CD:** GitHub Actions

---

## 🚀 Instalação

### Pré-requisitos

```bash
- PHP 8.3+
- PostgreSQL 16+
- Redis 7+
- Composer 2.6+
- Node.js 18+
- Nginx
```

### Passo a Passo

1. **Clone o repositório:**
```bash
git clone https://github.com/orfeubr/yumgo.git
cd yumgo
```

2. **Instale dependências:**
```bash
composer install
npm install
```

3. **Configure ambiente:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure banco de dados** (.env):
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=yumgo
DB_USERNAME=postgres
DB_PASSWORD=senha
```

5. **Execute migrations:**
```bash
# Migrations centrais (plataforma)
php artisan migrate

# Migrations tenant (restaurantes)
php artisan tenants:migrate
```

6. **Inicie serviços:**
```bash
# Reverb (WebSocket)
php artisan reverb:start

# Queue workers
php artisan queue:work

# Servidor dev
php artisan serve
```

### Docker (Alternativa)

```bash
cp .env.docker .env
docker-compose up -d
docker-compose exec php php artisan migrate
docker-compose exec php php artisan tenants:migrate
```

---

## 📱 YumGo Bridge (App de Impressão)

### Para Usuários (Restaurantes)

**Download:**
- [Windows (64-bit)](https://github.com/orfeubr/yumgo/releases/latest)
- [macOS (Intel/M1/M2)](https://github.com/orfeubr/yumgo/releases/latest)
- [Linux (AppImage)](https://github.com/orfeubr/yumgo/releases/latest)

**Instalação e Configuração:**
1. Baixe e instale o app
2. Acesse o painel: `https://seurestaurante.yumgo.com.br/painel/configuracoes?tab=-impressora-tab`
3. Gere Token de Acesso
4. Cole credenciais no app
5. Configure impressoras (USB/Rede)
6. Pronto! Pedidos imprimem automaticamente

**Documentação Completa:** [electron-bridge/README.md](./electron-bridge/README.md)

### Para Desenvolvedores

**Setup:**
```bash
cd electron-bridge
npm install
npm run dev
```

**Build:**
```bash
npm run build:win    # Windows
npm run build:mac    # macOS
npm run build:linux  # Linux
```

**Release Automático:**
```bash
git tag -a v1.9.0 -m "Release v1.9.0"
git push origin v1.9.0
# GitHub Actions compila automaticamente!
```

---

## 📚 Documentação

- [Arquitetura Multi-Tenant](docs/ARQUITETURA-MULTI-TENANT.md)
- [Sistema de Cashback](docs/CASHBACK.md)
- [Integração Pagar.me](docs/PAGARME-INTEGRATION.md)
- [Emissão NFC-e](docs/NFCE-EMISSION.md)
- [API REST](docs/API.md)
- [YumGo Bridge](electron-bridge/README.md)

---

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor:

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/NovaFuncionalidade`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/NovaFuncionalidade`)
5. Abra um Pull Request

### Convenção de Commits

```
feat: Nova funcionalidade
fix: Correção de bug
docs: Documentação
style: Formatação
refactor: Refatoração
test: Testes
chore: Manutenção
```

---

## 🐛 Reportar Bugs

Encontrou um problema? [Abra uma issue](https://github.com/orfeubr/yumgo/issues/new)

---

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

---

## 👥 Autores

- **Equipe YumGo** - [GitHub](https://github.com/orfeubr)
- **Claude Code** - Assistente AI de desenvolvimento

---

## 🔗 Links Úteis

- **Site:** https://yumgo.com.br
- **Documentação:** https://docs.yumgo.com.br
- **Suporte:** suporte@yumgo.com.br
- **WhatsApp:** (11) 99999-9999

---

## ⭐ Star o Projeto!

Se este projeto te ajudou, deixe uma ⭐ no GitHub!

---

**Desenvolvido com ❤️ no Brasil 🇧🇷**
