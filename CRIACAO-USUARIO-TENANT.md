# 🔐 Criação Automática de Usuário Admin para Tenants

**Data**: 24/02/2026
**Funcionalidade**: Criar usuário admin automaticamente ao criar tenant

---

## ✅ O Que Foi Implementado

### 1. **Campos no Formulário de Tenant**

Ao criar um novo tenant no painel central, agora você tem:

```
┌─────────────────────────────────────────┐
│ Usuário Administrador                   │
├─────────────────────────────────────────┤
│ Nome do Administrador: [Admin ...]      │
│ Email do Administrador: [admin@...]     │
│ ☑ Super Admin (pode gerenciar...)       │
│                                          │
│ ℹ️ Senha gerada automaticamente          │
└─────────────────────────────────────────┘
```

**Campos:**
- **Nome do Administrador**: Autopreenchido com "Admin [Nome do Restaurante]"
- **Email do Administrador**: Autopreenchido com "admin@[slug].com.br"
- **Super Admin**: Checkbox marcado por padrão
- **Senha**: Gerada automaticamente (12 caracteres)

---

## 🔄 Fluxo Automático

### Quando você cria um tenant:

1. **Tenant é criado** no schema PUBLIC
2. **Domínios são criados** automaticamente
3. **Schema do tenant é criado** (via stancl/tenancy)
4. **Usuário admin é criado** no schema do tenant com:
   - Nome e email definidos
   - Senha aleatória de 12 caracteres
   - Role "super_admin" (se marcado)
   - Email verificado automaticamente

5. **Notificação exibe**:
   ```
   ✅ Tenant criado com sucesso!

   🌐 Domínios criados:
   marmitariadagi.yumgo.com.br
   marmitariadagi.eliseus.com.br

   👤 Usuário Admin criado:
   Email: admin@marmitariadagi.com.br
   Senha: aB3dE5fG7hI9

   ⚠️ IMPORTANTE: Salve estas credenciais!

   🔗 Login: https://marmitariadagi.yumgo.com.br/painel/login
   ```

---

## 🔑 Sistema de Permissões

### Roles Disponíveis

O sistema usa **Filament Shield + Spatie Permission**:

#### **super_admin**
- Pode criar/editar/deletar usuários
- Pode gerenciar roles e permissões
- Acesso total ao painel
- Recomendado para **dono do restaurante**

#### Outras roles (a criar):
- `manager` - Gerente (produtos, pedidos)
- `kitchen` - Cozinha (visualizar pedidos)
- `delivery` - Entregador (apenas entregas)
- `cashier` - Caixa (pedidos e pagamentos)

---

## 👤 Gerenciamento de Usuários

### Como o dono do restaurante pode criar mais usuários:

1. Fazer login no painel: `https://[slug].yumgo.com.br/painel/login`
2. Ir em "Usuários" no menu lateral
3. Clicar em "Novo Usuário"
4. Preencher dados e selecionar role
5. Usuário recebe email com senha (se configurado)

### Trocar Senha

O usuário pode trocar a senha:
1. Clicar no nome (canto superior direito)
2. "Perfil" ou "Configurações"
3. "Alterar Senha"
4. Digite senha atual e nova senha

---

## 🧪 Testando

### Criar Novo Tenant com Usuário

1. Acesse: `https://yumgo.com.br/admin/tenants/create`

2. Preencha:
   ```
   Nome: Pizzaria do João
   Slug: pizzariadojoao
   Email: contato@pizzariadojoao.com.br
   Plano: [selecione]

   --- Usuário Administrador ---
   Nome: João Silva
   Email: joao@pizzariadojoao.com.br
   ☑ Super Admin
   ```

3. Clique em "Criar"

4. **Copie a senha** da notificação que aparece

5. Teste o login:
   ```
   URL: https://pizzariadojoao.yumgo.com.br/painel/login
   Email: joao@pizzariadojoao.com.br
   Senha: [a senha gerada]
   ```

---

## 🔒 Segurança

### Senha Gerada
- **12 caracteres** aleatórios
- Inclui: letras maiúsculas, minúsculas e números
- Gerada com `Str::random(12)`
- Armazenada com bcrypt

### Recomendações
1. ✅ Copie a senha imediatamente após criar o tenant
2. ✅ Envie para o dono do restaurante de forma segura
3. ✅ Peça para trocar a senha no primeiro login
4. ✅ Não compartilhe credenciais de super_admin

---

## 📋 Arquivo Modificado

### `TenantResource.php`
```php
Forms\Components\Section::make('Usuário Administrador')
    ->schema([
        TextInput::make('admin_name')
            ->label('Nome do Administrador')
            ->required()
            ->default(fn ($get) => 'Admin ' . $get('name')),

        TextInput::make('admin_email')
            ->label('Email do Administrador')
            ->email()
            ->required()
            ->default(fn ($get) => 'admin@' . $get('slug') . '.com.br'),

        Checkbox::make('admin_is_super')
            ->label('Super Admin')
            ->default(true),
    ])
```

### `CreateTenant.php`
```php
protected function afterCreate(): void
{
    $tenant = $this->record;

    // Gerar senha
    $password = \Str::random(12);

    // Inicializar tenancy
    tenancy()->initialize($tenant);

    // Criar usuário
    $user = \App\Models\User::create([
        'name' => $this->data['admin_name'],
        'email' => $this->data['admin_email'],
        'password' => \Hash::make($password),
        'email_verified_at' => now(),
    ]);

    // Atribuir role
    if ($this->data['admin_is_super']) {
        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $user->assignRole($role);
    }

    // Notificar com credenciais
    Notification::make()
        ->title('Tenant criado!')
        ->body("Email: {$email}\nSenha: {$password}")
        ->persistent()
        ->send();
}
```

---

## 🎯 Próximos Passos

### Melhorias Futuras

1. **Email de Boas-Vindas**
   - Enviar email automático com credenciais
   - Template personalizado
   - Link direto para primeiro login

2. **Reset de Senha**
   - Botão "Resetar Senha" no EditTenant
   - Gerar nova senha e notificar

3. **Múltiplos Usuários na Criação**
   - Criar gerente + cozinha de uma vez
   - Definir roles diferentes

4. **Logs de Acesso**
   - Registrar primeiro login
   - Histórico de trocas de senha
   - Auditoria de ações

---

## ⚠️ Importante

### Dados Sensíveis

A senha é exibida **apenas uma vez** na notificação após criar o tenant.

**Não é possível recuperar a senha depois!**

Se perder a senha, você terá que:
1. Acessar o banco de dados diretamente, ou
2. Implementar reset de senha via email, ou
3. Criar novo usuário admin

---

## 📞 Como Usar no Dia a Dia

### Fluxo Típico

1. **Você (Admin da Plataforma):**
   - Cria tenant no painel central
   - Copia credenciais da notificação
   - Envia para o cliente/restaurante

2. **Dono do Restaurante:**
   - Recebe email/WhatsApp com credenciais
   - Faz primeiro login
   - Troca a senha
   - Cria usuários da equipe (se precisar)

3. **Equipe do Restaurante:**
   - Recebe credenciais do dono
   - Acessa com permissões limitadas
   - Trabalha no sistema

---

**Implementado por**: Claude Code
**Framework**: Laravel 11 + Filament 3 + Shield
**Permissões**: Spatie Laravel Permission
