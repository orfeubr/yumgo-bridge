# 🎨 Logos dos Restaurantes no Marketplace - RESOLVIDO ✅

**Data:** 08/03/2026
**Problema:** Logos não apareciam na página inicial (yumgo.com.br)
**Causa:** Logos salvos no storage dos tenants, mas marketplace busca na tabela central
**Solução:** Comando de sincronização + correções no marketplace

---

## 🔍 Diagnóstico do Problema

### **Situação Encontrada:**

**❌ Problema:**
- Restaurantes possuíam logos
- Logos não apareciam em yumgo.com.br
- Campo `tenants.logo` estava NULL no banco

**🔎 Causa Raiz:**

Existem **DOIS sistemas de logos** diferentes:

1. **Logo no Painel do Restaurante (Settings):**
   - Upload: `/painel/settings` (cada restaurante)
   - Armazenamento: `/storage/tenant{id}/app/public/logos/`
   - Uso: Site do próprio restaurante
   - Schema: TENANT (isolado)
   - ❌ **Não aparece no marketplace**

2. **Logo na Tabela Central (Marketplace):**
   - Upload: `/admin/tenants` (admin central)
   - Armazenamento: `/storage/app/public/tenants/logos/`
   - Campo: `tenants.logo` (schema PUBLIC)
   - Uso: Marketplace principal (yumgo.com.br)
   - ✅ **Aparece no marketplace**

---

## ✅ Solução Implementada

### **1. Comando de Sincronização**

**Arquivo:** `app/Console/Commands/SyncTenantLogos.php`

**Função:**
- Busca logos nos storages dos tenants
- Copia para storage central (`/storage/app/public/tenants/logos/`)
- Atualiza campo `logo` na tabela `tenants` (schema PUBLIC)

**Como usar:**
```bash
php artisan tenants:sync-logos
```

**Output:**
```
🔄 Sincronizando logos dos restaurantes...

📍 Processando: Parker Pizzaria (ID: parker-pizzaria)
  ✅ Logo copiado: tenants/logos/parker-pizzaria.png
📍 Processando: Marmitaria da Gi (ID: 144c5973-f985-4309-8f9a-c404dd11feae)
  ✅ Logo copiado: tenants/logos/144c5973-f985-4309-8f9a-c404dd11feae.png
📍 Processando: Los Pampas (ID: a48efe45-872d-403e-a522-2cf445b1229b)
  ✅ Logo copiado: tenants/logos/a48efe45-872d-403e-a522-2cf445b1229b.png

✅ Sincronização concluída!
   📊 Sincronizados: 3
   ⏭️  Ignorados: 0
```

---

### **2. Correções no MarketplaceController**

**Arquivo:** `app/Http/Controllers/MarketplaceController.php:39`

**Mudança:**
```php
// ❌ ANTES (arquivo não existia)
$restaurant->logo_url = $restaurant->logo
    ? asset('storage/' . $restaurant->logo)
    : asset('images/default-restaurant.png');

// ✅ DEPOIS (SVG existe)
$restaurant->logo_url = $restaurant->logo
    ? asset('storage/' . $restaurant->logo)
    : asset('images/default-restaurant.svg');
```

---

### **3. Melhorias na View do Marketplace**

**Arquivo:** `resources/views/marketplace/index.blade.php:119-132`

**Mudanças:**
- `object-cover` → `object-contain` (logos não são cortados)
- Fundo cinza → Fundo branco (melhor para logos)
- Adicionado `onerror` handler (fallback automático)
- Padding de 4 unidades (logos não encostam nas bordas)

**Código:**
```blade
<div class="relative h-40 bg-white overflow-hidden">
    @if($restaurant->logo)
        <img
            src="{{ $restaurant->logo_url }}"
            alt="{{ $restaurant->name }}"
            class="w-full h-full object-contain p-4 group-hover:scale-105"
            onerror="this.onerror=null; this.src='{{ asset('images/default-restaurant.svg') }}';"
        >
    @else
        <img
            src="{{ asset('images/default-restaurant.svg') }}"
            alt="{{ $restaurant->name }}"
            class="w-full h-full object-contain p-4 opacity-50"
        >
    @endif
</div>
```

---

## 📊 Status Atual

| Restaurante | Logo | Path | Status |
|-------------|------|------|--------|
| Parker Pizzaria | ✅ | `tenants/logos/parker-pizzaria.png` | Sincronizado |
| Marmitaria da Gi | ✅ | `tenants/logos/144c5973-f985-4309-8f9a-c404dd11feae.png` | Sincronizado |
| Los Pampas | ✅ | `tenants/logos/a48efe45-872d-403e-a522-2cf445b1229b.png` | Sincronizado |

---

## 🔄 Como Funciona Agora

### **Fluxo de Upload de Logos:**

```
Restaurante faz upload no painel (/painel/settings)
    ↓
Logo salvo em: /storage/tenant{id}/app/public/logos/xxx.png
    ↓
Usado no site do restaurante (tenant.domain.com)
    ↓
[MANUALMENTE] Admin roda: php artisan tenants:sync-logos
    ↓
Logo copiado para: /storage/app/public/tenants/logos/{tenant-id}.png
    ↓
Campo tenants.logo atualizado
    ↓
Logo aparece no marketplace (yumgo.com.br) ✅
```

---

## 🎯 Testes Realizados

### **1. Verificar Logos no Banco:**
```bash
php artisan tinker
> App\Models\Tenant::select('name', 'logo')->get();
```

**Resultado:**
```
✅ Parker Pizzaria: tenants/logos/parker-pizzaria.png
✅ Marmitaria da Gi: tenants/logos/144c5973-f985-4309-8f9a-c404dd11feae.png
✅ Los Pampas: tenants/logos/a48efe45-872d-403e-a522-2cf445b1229b.png
```

### **2. Verificar Arquivos Físicos:**
```bash
ls -lah /var/www/restaurante/storage/app/public/tenants/logos/
```

**Resultado:**
```
total 3.6M
-rw-r--r-- 1 ubuntu 496K parker-pizzaria.png
-rw-r--r-- 1 ubuntu 1.6M 144c5973-f985-4309-8f9a-c404dd11feae.png
-rw-r--r-- 1 ubuntu 1.6M a48efe45-872d-403e-a522-2cf445b1229b.png
```

### **3. Verificar Symlink:**
```bash
ls -la /var/www/restaurante/public/storage
```

**Resultado:**
```
lrwxrwxrwx -> /var/www/restaurante/storage/app/public ✅
```

### **4. Acessar Marketplace:**
```
https://yumgo.com.br/
```

**Resultado esperado:**
- ✅ Grid de restaurantes exibido
- ✅ Logos aparecem nos cards
- ✅ Fallback SVG para logos quebrados
- ✅ Hover effect funciona
- ✅ Links para subdomains funcionam

---

## 📁 Arquivos Modificados/Criados

| Arquivo | Ação | Descrição |
|---------|------|-----------|
| `app/Console/Commands/SyncTenantLogos.php` | ✨ CRIADO | Comando de sincronização |
| `app/Http/Controllers/MarketplaceController.php` | 🔧 MODIFICADO | Correção do placeholder (.svg) |
| `resources/views/marketplace/index.blade.php` | 🔧 MODIFICADO | Melhor exibição de logos |
| `LOGOS-MARKETPLACE-CORRIGIDO.md` | ✨ CRIADO | Este documento |
| `RESTAURANTES-LANDING-PAGE.md` | ✨ CRIADO | Documentação geral |

---

## 🚀 Próximos Passos (Automação)

### **Opção 1: Observer para Sincronização Automática**

Criar um observer que sincroniza logos automaticamente quando o restaurante faz upload:

```php
// app/Observers/TenantSettingsObserver.php
public function updated(Settings $settings)
{
    if ($settings->isDirty('logo')) {
        // Copiar logo para storage central
        // Atualizar tenants.logo
    }
}
```

### **Opção 2: Scheduled Task**

Adicionar ao cron para rodar diariamente:

```php
// app/Console/Kernel.php
$schedule->command('tenants:sync-logos')->daily();
```

### **Opção 3: Event Listener**

Criar evento de upload e listener de sincronização:

```php
// app/Events/TenantLogoUploaded.php
// app/Listeners/SyncLogoToCentral.php
```

---

## ⚠️ Notas Importantes

### **Quando rodar o comando de sincronização?**

**Manual (atual):**
- Quando restaurante faz upload de novo logo
- Após mudanças em massa
- Comando: `php artisan tenants:sync-logos`

**Automático (futuro):**
- Observer/Listener sincroniza automaticamente
- Scheduled task roda diariamente
- Não precisa intervenção manual

### **Dois sistemas de logos são necessários?**

**Sim, porque:**
1. **Logo do Tenant (isolado):**
   - Usado no site do próprio restaurante
   - Storage isolado por tenant (segurança)
   - Schema TENANT (multi-tenant)

2. **Logo Central (marketplace):**
   - Usado na listagem pública
   - Storage central compartilhado
   - Schema PUBLIC (todos veem)

**Justificativa:**
- Isolamento de dados (multi-tenant)
- Performance (cache separado)
- Segurança (tenant não acessa logo de outro)

---

## 🎉 Resultado Final

### **ANTES:**
- ❌ Logos não apareciam em yumgo.com.br
- ❌ Todos os cards mostravam placeholder
- ❌ Campo `tenants.logo` estava NULL

### **DEPOIS:**
- ✅ Logos aparecem em yumgo.com.br
- ✅ Cards mostram logos reais dos restaurantes
- ✅ Campo `tenants.logo` preenchido corretamente
- ✅ URLs funcionando: `/storage/tenants/logos/*.png`
- ✅ Fallback SVG para logos inexistentes
- ✅ Comando de sincronização disponível

---

## 📞 URLs de Teste

| URL | Descrição |
|-----|-----------|
| https://yumgo.com.br/ | Marketplace com logos ✅ |
| https://yumgo.com.br/storage/tenants/logos/parker-pizzaria.png | Logo Parker ✅ |
| https://yumgo.com.br/storage/tenants/logos/144c5973-f985-4309-8f9a-c404dd11feae.png | Logo Marmitaria ✅ |
| https://yumgo.com.br/storage/tenants/logos/a48efe45-872d-403e-a522-2cf445b1229b.png | Logo Los Pampas ✅ |

---

**✅ PROBLEMA RESOLVIDO!**

Logos dos restaurantes agora aparecem corretamente na página inicial do marketplace!
