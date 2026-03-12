# 🏗️ Arquitetura de Storage - YumGo (Profissional)

## ✅ Implementado: Storage Central (Como iFood começou)

### 📊 Arquitetura Escolhida: Opção #2

```
┌─────────────────────────────────────────────────────────┐
│ STORAGE CENTRAL (Único)                                 │
│ /var/www/restaurante/storage/app/public/                │
│                                                          │
│ └── tenants/                                            │
│     └── logos/                                          │
│         ├── 01KKDE7Q90E3VPTGRPVEWG7MET.png (Marmitaria)│
│         ├── 02XXYYZZ11AABBCC22DDEE33FF.png (Parker)    │
│         └── 03AABBCC22DDEE33FF44GGHHII.png (Boteco)    │
│                                                          │
│ Symlink: /public/storage → /storage/app/public         │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ SINCRONIZAÇÃO AUTOMÁTICA                                 │
│                                                          │
│ 1. Admin edita em:                                      │
│    https://yumgo.com.br/admin/tenants/xxx/edit          │
│                                                          │
│ 2. Upload de logo → salva em storage CENTRAL            │
│    tenants.logo = "tenants/logos/xxx.png"               │
│                                                          │
│ 3. TenantObserver detecta mudança                       │
│                                                          │
│ 4. Sincroniza PATH (não copia arquivo):                 │
│    settings.logo = tenants.logo                         │
│                                                          │
│ 5. Site acessa via:                                     │
│    https://tenant.yumgo.com.br/storage/tenants/logos/xxx│
└─────────────────────────────────────────────────────────┘
```

---

## 🎯 Por Que Esta Arquitetura?

### ✅ Vantagens

1. **Um Único Storage para Backup**
   - Backup simplificado: `/storage/app/public/`
   - Não precisa backup por tenant

2. **Fácil Migração Futura para S3/CDN**
   - Quando crescer, só mudar `disk` de `public` para `s3`
   - Código não muda

3. **Performance**
   - Um único ponto de acesso
   - Cache mais eficiente
   - Não duplica arquivos

4. **Gerenciamento Simples**
   - Tudo em um lugar
   - Fácil ver espaço usado
   - Fácil limpar arquivos órfãos

5. **Isolamento Lógico**
   - Cada tenant: pasta separada
   - Não há risco de vazamento entre tenants
   - Path no settings garante que cada tenant só vê sua logo

---

## 📁 Estrutura de Pastas

```
/var/www/restaurante/
├── storage/
│   └── app/
│       └── public/                    ← STORAGE CENTRAL
│           └── tenants/
│               ├── logos/             ← Logos dos restaurantes
│               │   ├── marmitaria.png
│               │   └── parker.png
│               └── banners/           ← (futuro) Banners
│                   └── ...
│
└── public/
    └── storage/                       ← Symlink → /storage/app/public
```

---

## 🔄 Fluxo Completo

### Upload de Logo no Admin Central

```
1️⃣ Admin acessa:
   https://yumgo.com.br/admin/tenants/marmitariadagi/edit

2️⃣ Faz upload de logo (logo.png)

3️⃣ Filament FileUpload:
   - Disk: public
   - Directory: tenants/logos
   - Salva em: /storage/app/public/tenants/logos/01KKDE7Q90E3VPTGRPVEWG7MET.png

4️⃣ Salva no banco:
   UPDATE tenants SET logo = 'tenants/logos/01KKDE7Q90E3VPTGRPVEWG7MET.png'

5️⃣ TenantObserver.updated() dispara:
   - Detecta: $tenant->isDirty('logo') = true
   - Chama: syncLogoToSettings($tenant)

6️⃣ syncLogoToSettings():
   - Inicializa tenancy: tenancy()->initialize($tenant)
   - Busca settings do tenant
   - Atualiza: settings.logo = tenants.logo
   - Log: "✅ Logo path sincronizado"

7️⃣ Site acessa:
   - View: $settings->logo
   - HTML: <img src="/storage/tenants/logos/01KKDE7Q90E3VPTGRPVEWG7MET.png">
   - Nginx serve de: /public/storage/... (symlink)
```

---

## 🔗 URLs de Acesso

### Desenvolvimento
```
https://marmitariadagi.yumgo.com.br/storage/tenants/logos/xxx.png
```

### Produção (Futuro com CDN)
```
https://cdn.yumgo.com.br/tenants/logos/xxx.png
```

---

## 📝 Código Implementado

### TenantObserver.php (Sincronização)

```php
protected function syncLogoToSettings(Tenant $tenant): void
{
    try {
        // Inicializar contexto do tenant
        tenancy()->initialize($tenant);

        // Buscar settings
        $settings = \App\Models\Settings::first();

        if ($settings) {
            // Sincronizar apenas o PATH (não copia arquivo físico)
            $settings->update([
                'logo' => $tenant->logo,
            ]);

            Log::info("✅ Logo path sincronizado", [
                'tenant_id' => $tenant->id,
                'logo_path' => $tenant->logo,
                'storage' => 'central',
            ]);
        }

        tenancy()->end();
    } catch (\Exception $e) {
        Log::error("❌ Erro ao sincronizar logo: " . $e->getMessage());
        tenancy()->end();
    }
}
```

### restaurant-home.blade.php (Exibição)

```blade
@if($settings?->logo)
    <img src="{{ asset('storage/' . $settings->logo) }}"
         alt="Logo"
         class="h-20 w-20 rounded-2xl">
@endif
```

### TenantResource.php (Upload)

```php
Forms\Components\FileUpload::make('logo')
    ->label('Logo do Restaurante')
    ->image()
    ->disk('public')                    // Storage central
    ->directory('tenants/logos')        // Pasta isolada
    ->maxSize(2048)                     // 2MB max
    ->dehydrated()
```

---

## 🧪 Como Testar

### 1. Upload no Admin Central

```bash
# 1. Acesse
https://yumgo.com.br/admin/tenants/marmitariadagi/edit

# 2. Vá na aba "Informações Básicas"

# 3. Clique em "Logo do Restaurante"

# 4. Faça upload de uma imagem

# 5. Salve
```

### 2. Verificar Sincronização

```bash
php artisan tinker

$tenant = \App\Models\Tenant::find('marmitariadagi');
tenancy()->initialize($tenant);
$settings = \App\Models\Settings::first();

echo "Tenant logo: " . $tenant->logo . "\n";
echo "Settings logo: " . $settings->logo . "\n";
echo "Match? " . ($tenant->logo === $settings->logo ? 'YES' : 'NO');
```

**Resultado esperado:**
```
Tenant logo: tenants/logos/01KKDE7Q90E3VPTGRPVEWG7MET.png
Settings logo: tenants/logos/01KKDE7Q90E3VPTGRPVEWG7MET.png
Match? YES
```

### 3. Verificar no Site

```bash
# Acesse
https://marmitariadagi.yumgo.com.br/

# Logo deve aparecer no header
```

---

## 🚀 Migração Futura para S3/CDN

Quando o projeto crescer, migração é simples:

### 1. Instalar pacote S3

```bash
composer require league/flysystem-aws-s3-v3
```

### 2. Configurar .env

```env
AWS_ACCESS_KEY_ID=xxx
AWS_SECRET_ACCESS_KEY=xxx
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=yumgo-uploads
AWS_URL=https://cdn.yumgo.com.br
```

### 3. Mudar apenas 1 linha no TenantResource

```php
Forms\Components\FileUpload::make('logo')
    ->disk('s3')  // ← Era 'public', agora 's3'
    ->directory('tenants/logos')
```

### 4. Migrar arquivos existentes

```bash
php artisan storage:migrate-to-s3
```

**Código não muda!** Asset helper já funciona com S3.

---

## 📊 Monitoramento

### Verificar Espaço Usado

```bash
du -sh /var/www/restaurante/storage/app/public/tenants/
```

### Listar Logos por Tenant

```bash
ls -lh /var/www/restaurante/storage/app/public/tenants/logos/
```

### Logs de Sincronização

```bash
tail -f storage/logs/laravel.log | grep "Logo path sincronizado"
```

---

## ⚠️ Importante: Backup

### Backup Diário

```bash
# Cron job
0 2 * * * tar -czf /backup/storage-$(date +\%Y\%m\%d).tar.gz /var/www/restaurante/storage/app/public/
```

### Restore

```bash
tar -xzf /backup/storage-20260311.tar.gz -C /var/www/restaurante/
```

---

## ✅ Checklist de Verificação

- [x] Logo salva em storage central
- [x] Observer sincroniza PATH automaticamente
- [x] Settings.logo = Tenants.logo
- [x] Site exibe logo corretamente
- [x] URL pública funciona
- [x] Symlink /public/storage existe
- [x] Permissões corretas (www-data)
- [x] Logs funcionando

---

**Status:** ✅ ARQUITETURA PROFISSIONAL IMPLEMENTADA!

**Data:** 11/03/2026
**Versão:** 1.0 - Storage Central (Recomendado)
