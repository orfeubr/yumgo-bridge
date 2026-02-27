# 🔑 Credenciais Padrão - Sistema Automatizado

## ✅ O QUE É CRIADO AUTOMATICAMENTE

Quando você cria um **novo restaurante (tenant)** pelo painel admin, o sistema cria automaticamente:

### 1. **Domínio**
- URL: `https://{slug}.eliseus.com.br`
- Painel: `https://{slug}.eliseus.com.br/painel`
- App Cliente: `https://{slug}.eliseus.com.br`

### 2. **Sub-conta Asaas**
- Campos preenchidos:
  - `asaas_account_id` (ID da conta)
  - `asaas_wallet_id` (ID da carteira)
- Pronta para receber pagamentos

### 3. **Usuário Admin do Restaurante** ⭐ NOVO
- **E-mail:** O e-mail cadastrado no tenant
- **Senha:** `senha123` (PADRÃO)
- **Permissões:** Admin completo
- **Status:** Ativo e verificado

---

## 📧 Credenciais de Acesso Padrão

### Para o Dono do Restaurante:

```
URL: https://{slug-do-restaurante}.eliseus.com.br/painel
E-mail: email-cadastrado@dominio.com
Senha: senha123
```

**Exemplo:**
```
Restaurante: Pizzaria Bella
URL: https://pizzaria-bella.eliseus.com.br/painel
E-mail: contato@pizzariabella.com
Senha: senha123
```

---

## ⚠️ IMPORTANTE - Segurança

### 1. **Repasse as Credenciais ao Cliente**

Após criar o tenant, você receberá uma notificação com as credenciais. **Copie e envie para o cliente:**

📧 **Template de E-mail:**
```
Olá, [Nome do Cliente]!

Seu sistema de delivery está pronto! 🎉

🌐 Acesse seu painel em:
https://[slug].eliseus.com.br/painel

🔐 Credenciais de acesso:
E-mail: [email-cadastrado]
Senha: senha123

⚠️ IMPORTANTE: Troque sua senha no primeiro acesso!
Menu → Perfil → Alterar Senha

Qualquer dúvida, estamos à disposição!

Atenciosamente,
Equipe DeliveryPro
```

### 2. **Cliente DEVE Trocar a Senha**

Oriente o cliente a:
1. Fazer login com as credenciais padrão
2. Ir em **Perfil** (canto superior direito)
3. Clicar em **Alterar Senha**
4. Definir uma senha forte

### 3. **Senha Forte Recomendada**

- Mínimo 8 caracteres
- Letras maiúsculas e minúsculas
- Números
- Símbolos especiais

---

## 🔧 Como Resetar Senha (Se Cliente Esquecer)

### Opção 1: Via Tinker (Manual)

```bash
php artisan tinker

# Inicializar contexto do tenant
$tenant = App\Models\Tenant::find('slug-do-restaurante');
tenancy()->initialize($tenant);

# Buscar usuário
$user = App\Models\User::where('email', 'email@restaurante.com')->first();

# Resetar senha
$user->password = Hash::make('nova-senha-temporaria');
$user->save();

echo "✅ Senha resetada para: nova-senha-temporaria";
```

### Opção 2: Criar Novo Admin (Se Necessário)

```bash
php artisan tinker

$tenant = App\Models\Tenant::find('slug-do-restaurante');
tenancy()->initialize($tenant);

App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'novo-email@restaurante.com',
    'password' => Hash::make('senha-temporaria'),
    'role' => 'admin',
    'active' => true,
    'email_verified_at' => now(),
]);
```

---

## 📋 Checklist de Onboarding

Quando criar um novo restaurante:

- [ ] Criar tenant no painel admin
- [ ] Verificar notificação com credenciais
- [ ] Copiar credenciais
- [ ] Enviar e-mail para o cliente (usar template acima)
- [ ] Testar login no painel do restaurante
- [ ] Verificar se domínio está acessível
- [ ] Verificar se asaas_account_id foi preenchido
- [ ] Aguardar cliente trocar senha
- [ ] Agendar treinamento com cliente
- [ ] Cadastrar produtos iniciais (opcional)

---

## 🗂️ Registro de Credenciais

Todas as criações de tenant são registradas no log:

```bash
# Ver últimos logs
tail -f storage/logs/laravel.log | grep "Tenant criado"
```

Formato do log:
```json
{
  "message": "🎉 Tenant criado com sucesso",
  "tenant": "Pizzaria Bella",
  "slug": "pizzaria-bella",
  "domain": "pizzaria-bella.eliseus.com.br",
  "email": "contato@pizzariabella.com",
  "default_password": "senha123"
}
```

---

## 🔄 Para Tenants Antigos (Sem Usuário Admin)

Se você criou tenants **antes** desta atualização, rode o seeder:

```bash
php artisan db:seed --class=CreateInitialRestaurantAdmins
```

Isso cria usuários admin para todos os tenants que não possuem usuários.

**Credenciais criadas pelo seeder:**
- E-mail: `admin@{slug-do-tenant}.com`
- Senha: `password`

---

## 🆘 Troubleshooting

### "Não consigo fazer login"

**Causas possíveis:**
1. E-mail incorreto → Verifique no painel admin qual e-mail foi cadastrado
2. Senha incorreta → Senha padrão é `senha123`
3. Tenant não inicializado → Verifique se está acessando o domínio correto
4. Usuário não criado → Rode seeder ou crie manualmente via tinker

### "Cliente esqueceu a senha"

Use o método de reset via tinker (ver acima)

### "Preciso criar mais usuários para o restaurante"

O próprio cliente pode criar:
- Painel → Configurações → Equipe → + Novo Usuário
- Pode criar: Gerente, Cozinha, Atendente, Entregador

---

## 🔐 Segurança Adicional

### Para Produção (Futuro):

- [ ] Implementar recuperação de senha por e-mail
- [ ] Forçar troca de senha no primeiro login
- [ ] Autenticação de 2 fatores (2FA)
- [ ] Histórico de tentativas de login
- [ ] Bloqueio após tentativas falhas
- [ ] Expiração de senha (90 dias)

---

**✅ Sistema Automatizado Funcionando!**

Agora quando você cria um restaurante, o sistema cria **TUDO automaticamente**:
- ✅ Domínio
- ✅ Sub-conta Asaas
- ✅ **Usuário Admin** (NOVO!)

**Sem trabalho manual!** 🎉
