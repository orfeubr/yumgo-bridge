# 🚀 Guia de Instalação - DeliveryPro

## Requisitos

- Docker & Docker Compose
- Git

## Instalação

### 1. Preparar Ambiente

```bash
# Copiar env para Docker
cp .env.docker .env

# Gerar APP_KEY
php artisan key:generate
# ou manualmente edite .env e adicione uma chave
```

### 2. Iniciar Containers

```bash
# Build e start
docker-compose up -d --build

# Verificar containers
docker-compose ps
```

### 3. Instalar Dependências

```bash
# Entrar no container PHP
docker-compose exec php sh

# Instalar dependências Composer
composer install

# Sair
exit
```

### 4. Rodar Migrations

```bash
# Migrations da plataforma (schema public)
docker-compose exec php php artisan migrate

# Seeds (opcional)
docker-compose exec php php artisan db:seed
```

### 5. Acessar Aplicação

- **App**: http://localhost
- **Email Testing**: http://localhost:8025 (Mailpit)

## Comandos Úteis

```bash
# Ver logs
docker-compose logs -f

# Parar containers
docker-compose down

# Recriar containers
docker-compose up -d --force-recreate

# Limpar tudo (CUIDADO: apaga dados!)
docker-compose down -v

# Artisan commands
docker-compose exec php php artisan [comando]

# Tinker
docker-compose exec php php artisan tinker

# Criar tenant
docker-compose exec php php artisan tenant:create

# Testes
docker-compose exec php php artisan test
```

## Próximos Passos

1. ✅ Docker configurado
2. ⏳ Implementar multi-tenancy
3. ⏳ Integrar Asaas
4. ⏳ Sistema de cashback
5. ⏳ Painel admin

**Vamos ficar ricos! 🚀💰**
