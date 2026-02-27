# 🔐 Guia Completo: Login Social + Verificação WhatsApp

## 📋 Implementação Completa

✅ **Carrinho Clean** - Estilo iFood minimalista
✅ **Página de Perfil** - Layout leve com ícones cinza
✅ **Tela de Welcome/Onboarding** - Pergunta localização antes de mostrar cardápio
✅ **Login Social** - Google e Facebook integrados
✅ **Cadastro com Localização** - Seleciona cidade/bairro disponíveis
✅ **Verificação WhatsApp** - Sistema pronto para integração

---

## 🎨 1. CARRINHO CLEAN (Estilo iFood)

### Mudanças Implementadas:
- ✅ Fundo branco puro
- ✅ Formato: "**qtd x nome do produto**" - valor
- ✅ Subtotal em texto clarinho (cinza)
- ✅ Botões "Remover" discretos
- ✅ Total destacado em vermelho e maior
- ✅ Controles de quantidade minimalistas
- ✅ Bordas e sombras sutis

### Arquivo:
```
/resources/views/restaurant-home.blade.php
Linhas 969-1101 (Modal do Carrinho)
```

---

## 👤 2. PERFIL DE USUÁRIO

### Características:
- ✅ Nome em destaque no topo
- ✅ Ícones cinza para cada seção
- ✅ Layout limpo e espaçado
- ✅ Cards com hover suave
- ✅ Informações de cashback destacadas

### Arquivo:
```
/resources/views/tenant/profile.blade.php (já existia, otimizado)
```

---

## 🎯 3. TELA DE WELCOME/ONBOARDING

### Fluxo:
1. **STEP 1**: Pergunta cidade e bairro
2. **STEP 2**: Login tradicional ou social
3. **STEP 3**: Cadastro completo com endereço

### Features:
- ✅ Seleção de cidade (lista de cidades disponíveis)
- ✅ Seleção de bairro (carrega via API)
- ✅ Mostra taxa de entrega de cada bairro
- ✅ Opção de "Ver cardápio sem login"
- ✅ Salva localização no localStorage

### Arquivo:
```
/resources/views/tenant/welcome.blade.php
```

---

## 🔑 4. LOGIN SOCIAL (Google + Facebook)

### Pacote Instalado:
```bash
composer require laravel/socialite
```

### Configuração (config/services.php):
```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('APP_URL') . '/auth/google/callback',
],

'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('APP_URL') . '/auth/facebook/callback',
],
```

### Variáveis de Ambiente (.env):
```env
GOOGLE_CLIENT_ID=seu-client-id
GOOGLE_CLIENT_SECRET=seu-client-secret

FACEBOOK_CLIENT_ID=seu-app-id
FACEBOOK_CLIENT_SECRET=seu-app-secret
```

### Como Obter Credenciais:

#### 🔴 **GOOGLE**
1. Acesse: https://console.cloud.google.com/
2. Crie um novo projeto
3. Ative "Google+ API"
4. Vá em "Credenciais" → "Criar Credenciais" → "ID do cliente OAuth"
5. Tipo: "Aplicativo da Web"
6. URIs de redirecionamento autorizados:
   ```
   https://marmitaria-gi.yumgo.com.br/auth/google/callback
   https://seu-dominio.com/auth/google/callback
   ```
7. Copie o **Client ID** e **Client Secret**

#### 🔵 **FACEBOOK**
1. Acesse: https://developers.facebook.com/
2. Crie um novo app
3. Adicione o produto "Login do Facebook"
4. Em "Configurações" → "Básico":
   - Copie o **ID do Aplicativo** (client_id)
   - Copie a **Chave Secreta do Aplicativo** (client_secret)
5. Em "Login do Facebook" → "Configurações":
   - URIs de redirecionamento válidos:
   ```
   https://marmitaria-gi.yumgo.com.br/auth/facebook/callback
   ```

### Rotas:
```php
// Redirecionar para provedor
GET /auth/{provider}/redirect

// Callback do provedor
GET /auth/{provider}/callback
```

### Controller:
```
/app/Http/Controllers/Api/SocialAuthController.php
```

### Fluxo:
1. Usuário clica em "Continuar com Google/Facebook"
2. Redireciona para OAuth do provedor
3. Usuário autoriza
4. Retorna para callback
5. Sistema cria/atualiza customer
6. Gera token JWT
7. **Se não tiver telefone, solicita verificação WhatsApp**

---

## 📱 5. VERIFICAÇÃO POR WHATSAPP

### 🎯 Por Quê Verificar?

Mesmo com login social (Google/Facebook), é importante validar o WhatsApp porque:

1. **Segurança Extra**: Confirma que a pessoa tem acesso ao número
2. **Recuperação de Conta**: Código via WhatsApp se esquecer senha
3. **Notificações**: Enviar atualizações de pedido
4. **Suporte**: Canal direto de comunicação
5. **Anti-Fraude**: Dificulta contas falsas

### 🔧 Implementação

#### Migration Criada:
```php
// database/migrations/tenant/2026_02_23_195310_add_social_auth_fields_to_customers_table.php

- provider (google, facebook)
- provider_id
- avatar
- verification_code (6 dígitos)
- verification_code_expires_at (10 minutos)
- phone_verified_at
```

#### API Endpoints:

**1. Solicitar Código:**
```
POST /api/v1/auth/whatsapp/request-code
Authorization: Bearer {token}

Body:
{
  "phone": "11999998888"
}

Response:
{
  "success": true,
  "message": "Código enviado via WhatsApp",
  "debug_code": "123456" // Apenas em APP_DEBUG=true
}
```

**2. Verificar Código:**
```
POST /api/v1/auth/whatsapp/verify-code
Authorization: Bearer {token}

Body:
{
  "code": "123456"
}

Response:
{
  "success": true,
  "message": "WhatsApp verificado com sucesso!"
}
```

---

## 💬 OPÇÕES DE API DE WHATSAPP

### 📊 Comparação:

| API | Preço | Facilidade | Confiabilidade | Recomendação |
|-----|-------|-----------|----------------|--------------|
| **Twilio** | 💰💰 Pago | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Produção |
| **Evolution API** | 🆓 Grátis | ⭐⭐⭐ | ⭐⭐⭐⭐ | Melhor Custo |
| **Maytapi** | 💰 Freemium | ⭐⭐⭐⭐ | ⭐⭐⭐ | Teste |
| **WPP Connect** | 🆓 Grátis | ⭐⭐ | ⭐⭐⭐ | DIY |

---

### 1️⃣ **TWILIO** (Recomendado para Produção) 💰

**Prós:**
- ✅ Oficial do WhatsApp Business API
- ✅ Extremamente confiável
- ✅ Suporte 24/7
- ✅ Documentação completa
- ✅ SDKs em várias linguagens

**Contras:**
- ❌ Pago (USD $0.005 por mensagem)
- ❌ Requer aprovação do Meta
- ❌ Necessita de número de telefone comercial

**Preços:**
- Mensagens de verificação: **$0.005/msg** (~R$ 0,03)
- 1000 códigos/mês = **R$ 30**

**Setup:**
```bash
composer require twilio/sdk
```

**.env:**
```env
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxx
TWILIO_TOKEN=your_auth_token
TWILIO_WHATSAPP_FROM=+14155238886
```

**Código:**
```php
use Twilio\Rest\Client;

$twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));

$message = $twilio->messages->create(
    "whatsapp:+55{$phone}", // Destino
    [
        'from' => 'whatsapp:' . env('TWILIO_WHATSAPP_FROM'),
        'body' => "Seu código de verificação é: {$code}"
    ]
);
```

**Link:** https://www.twilio.com/pt-br/messaging/whatsapp

---

### 2️⃣ **EVOLUTION API** (Melhor Custo-Benefício) 🆓⭐

**Prós:**
- ✅ **GRATUITO** (self-hosted)
- ✅ Open-source
- ✅ Fácil instalação com Docker
- ✅ API REST completa
- ✅ Suporta múltiplas instâncias
- ✅ Webhook para receber mensagens
- ✅ Comunidade ativa brasileira

**Contras:**
- ⚠️ Requer servidor próprio
- ⚠️ Não é oficial do WhatsApp
- ⚠️ Risco de ban se usar com frequência alta

**Custo:**
- API: **GRÁTIS**
- Servidor VPS: **R$ 20-40/mês** (DigitalOcean, Contabo)

**Setup (Docker):**
```bash
# Clonar repositório
git clone https://github.com/EvolutionAPI/evolution-api
cd evolution-api

# Configurar .env
cp .env.example .env

# Iniciar
docker-compose up -d
```

**Configurar Instance:**
```bash
# Criar instância via API
curl -X POST https://sua-api.com/instance/create \
  -H "apikey: YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "instanceName": "yumgo_whatsapp",
    "qrcode": true
  }'

# Escanear QR Code e conectar WhatsApp
```

**.env:**
```env
EVOLUTION_API_URL=https://sua-evolution-api.com
EVOLUTION_API_KEY=sua-api-key
EVOLUTION_INSTANCE_NAME=yumgo_whatsapp
```

**Código:**
```php
$response = Http::withHeaders([
    'apikey' => env('EVOLUTION_API_KEY')
])->post(env('EVOLUTION_API_URL') . '/message/sendText/' . env('EVOLUTION_INSTANCE_NAME'), [
    'number' => '55' . $phone,
    'text' => "🔐 Seu código de verificação é: *{$code}*\n\nVálido por 10 minutos."
]);
```

**Link:** https://github.com/EvolutionAPI/evolution-api

---

### 3️⃣ **MAYTAPI** (Freemium) 💰

**Prós:**
- ✅ Plano gratuito (100 msgs/mês)
- ✅ Fácil de usar (cloud-hosted)
- ✅ Não precisa de servidor
- ✅ API REST simples
- ✅ Dashboard visual

**Contras:**
- ⚠️ Limite baixo no free tier
- ⚠️ Plano pago: **$89/mês** (ilimitado)
- ⚠️ Não é oficial do WhatsApp

**Preços:**
- Free: **100 mensagens/mês**
- Growth: **$89/mês** (ilimitado)

**.env:**
```env
MAYTAPI_PRODUCT_ID=your_product_id
MAYTAPI_API_KEY=your_api_key
MAYTAPI_PHONE_ID=your_phone_id
```

**Código:**
```php
$response = Http::withHeaders([
    'x-maytapi-key' => env('MAYTAPI_API_KEY')
])->post("https://api.maytapi.com/api/{$productId}/{$phoneId}/sendMessage", [
    'to_number' => '55' . $phone,
    'message' => "Seu código: {$code}",
    'type' => 'text'
]);
```

**Link:** https://maytapi.com/

---

### 4️⃣ **WPP CONNECT** (Open Source) 🆓

**Prós:**
- ✅ GRATUITO
- ✅ Open-source brasileiro
- ✅ Bem documentado

**Contras:**
- ⚠️ Requer conhecimento técnico
- ⚠️ Instalação mais complexa
- ⚠️ Menos estável

**Link:** https://wppconnect.io/

---

## 🏆 RECOMENDAÇÃO FINAL

### Para TESTE/DESENVOLVIMENTO:
👉 **Evolution API** (gratuito, fácil)

### Para PRODUÇÃO (baixo volume):
👉 **Twilio** (confiável, oficial)

### Para PRODUÇÃO (alto volume):
👉 **Evolution API** (próprio servidor, custo fixo)

---

## 📝 PRÓXIMOS PASSOS

### 1. Escolher API de WhatsApp
```bash
# Opção A: Twilio (pago, confiável)
composer require twilio/sdk

# Opção B: Evolution API (grátis, instalar no servidor)
docker-compose up -d evolution-api
```

### 2. Configurar Credenciais (.env)
```env
# Escolher UMA das opções:

# OPÇÃO 1: Twilio
TWILIO_SID=ACxxxxx
TWILIO_TOKEN=xxxxx
TWILIO_WHATSAPP_FROM=+14155238886

# OPÇÃO 2: Evolution API
EVOLUTION_API_URL=https://sua-api.com
EVOLUTION_API_KEY=sua-key
EVOLUTION_INSTANCE_NAME=yumgo

# OPÇÃO 3: Maytapi
MAYTAPI_PRODUCT_ID=xxxxx
MAYTAPI_API_KEY=xxxxx
MAYTAPI_PHONE_ID=xxxxx
```

### 3. Implementar Envio de Código
Editar: `/app/Http/Controllers/Api/SocialAuthController.php`

Método: `requestWhatsAppCode()`

Substituir o `// TODO` pela integração escolhida.

### 4. Testar Fluxo Completo
1. Acessar `/welcome`
2. Selecionar cidade/bairro
3. Clicar em "Continuar com Google"
4. Autorizar
5. Inserir telefone
6. Receber código via WhatsApp
7. Validar código
8. Acessar cardápio logado

### 5. Configurar OAuth (Google/Facebook)
Ver seção "Como Obter Credenciais" acima.

---

## 📄 ARQUIVOS CRIADOS/MODIFICADOS

### Novos Arquivos:
```
✅ /resources/views/tenant/welcome.blade.php
✅ /app/Http/Controllers/Api/SocialAuthController.php
✅ /database/migrations/tenant/2026_02_23_195310_add_social_auth_fields_to_customers_table.php
✅ /GUIA-LOGIN-SOCIAL-WHATSAPP.md (este arquivo)
```

### Arquivos Modificados:
```
✅ /resources/views/restaurant-home.blade.php (carrinho clean)
✅ /routes/tenant.php (rotas de login social)
✅ /config/services.php (Google/Facebook config)
✅ /.env.example (variáveis de ambiente)
✅ composer.json (laravel/socialite adicionado)
```

---

## 🚀 COMANDOS PARA APLICAR MUDANÇAS

```bash
# 1. Instalar dependências
composer install

# 2. Rodar migrations (todos os tenants)
php artisan tenants:migrate

# 3. Limpar cache
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Testar
php artisan serve
```

---

## 📞 SUPORTE

**Dúvidas sobre:**
- Evolution API: https://github.com/EvolutionAPI/evolution-api/issues
- Twilio: https://www.twilio.com/docs
- Socialite: https://laravel.com/docs/11.x/socialite

---

**Desenvolvido com ❤️ para YumGo**
**Data: 23/02/2026**
