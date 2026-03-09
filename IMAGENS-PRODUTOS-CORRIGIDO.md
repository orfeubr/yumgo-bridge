# ✅ Correção de Imagens de Produtos - 07/03/2026

## 🎯 Problema

As imagens dos produtos estavam cadastradas no painel Filament, mas não apareciam na home do restaurante.

## 🔍 Causa Raiz

O sistema multi-tenant salva arquivos em diretórios separados por tenant:
```
/storage/tenant{id}/app/public/products/arquivo.jpg
```

Mas o symlink padrão do Laravel aponta para:
```
/public/storage -> /storage/app/public/
```

Isso causava 404 porque o path não incluía o tenant ID.

## ✅ Solução Implementada

### 1. Rota Customizada para Servir Imagens

**Arquivo:** `routes/tenant.php`

```php
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path("app/public/{$path}");

    if (!file_exists($filePath)) {
        abort(404);
    }

    return response()->file($filePath, [
        'Content-Type' => mime_content_type($filePath),
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*')->name('tenant.storage');
```

**Por quê funciona:**
- `storage_path()` com tenancy ativo já retorna o path correto do tenant
- A rota serve os arquivos diretamente do storage do tenant
- Cache de 1 ano para performance

### 2. Corrigir URLs na API

**Arquivo:** `app/Http/Controllers/Api/ProductController.php`

**Método `formatProduct()`:**
```php
'image' => $product->image
    ? (str_starts_with($product->image, 'http')
        ? $product->image
        : url('/storage/' . $product->image))
    : null,
```

**Método `pizzaFlavors()`:**
```php
'image' => $product->image
    ? (str_starts_with($product->image, 'http')
        ? $product->image
        : url('/storage/' . $product->image))
    : null,
```

**Mudança:**
- ANTES: `asset('storage/' . $product->image)` → Gerava URL errada
- DEPOIS: `url('/storage/' . $product->image)` → Gera URL relativa ao tenant

### 3. URLs Geradas

**Exemplo:**

**Produto:** Feijoada Completa
**Path no banco:** `products/01KJ90KR3W14N2RAGG11JW2HBP.png`
**URL gerada:** `https://marmitariadagi.yumgo.com.br/storage/products/01KJ90KR3W14N2RAGG11JW2HBP.png`
**Path físico:** `/var/www/restaurante/storage/tenant144c5973-f985-4309-8f9a-c404dd11feae/app/public/products/01KJ90KR3W14N2RAGG11JW2HBP.png`

## 📝 Arquivos Modificados

1. ✅ `routes/tenant.php` - Nova rota `/storage/{path}`
2. ✅ `app/Http/Controllers/Api/ProductController.php` - URLs corrigidas

## 🧪 Testes Realizados

```bash
# Testar URL da imagem
curl -I https://marmitariadagi.yumgo.com.br/storage/products/01KJ90KR3W14N2RAGG11JW2HBP.png

# Resultado: HTTP/2 200 ✅
```

**API retornando URLs corretas:**
```json
{
  "id": 3,
  "name": "Feijoada Completa",
  "image": "https://marmitariadagi.yumgo.com.br/storage/products/01KJ90KR3W14N2RAGG11JW2HBP.png"
}
```

## ⚠️ Notas Importantes

### Multi-Tenant Storage

Cada tenant tem seu próprio diretório de storage:
```
/storage/tenant{id}/app/public/
```

A rota `/storage/` criada funciona porque:
1. É executada no contexto do tenant (middleware tenancy)
2. `storage_path()` automaticamente retorna o path do tenant ativo
3. Isolamento total entre restaurantes

### Cache

As imagens são servidas com cache de **1 ano**:
```
Cache-Control: public, max-age=31536000
```

Para forçar atualização de uma imagem:
- Troque o nome do arquivo, OU
- Adicione query string: `?v=2`

### Performance

A rota serve arquivos via PHP, o que é menos eficiente que Nginx direto. Para produção com alto tráfego, considerar:
- Nginx location blocks por tenant
- CDN (Cloudflare Images, AWS CloudFront)
- S3 com URLs assinadas

Mas para o volume atual, a solução atual é suficiente e mantém a simplicidade.

## 🎉 Resultado Final

✅ Imagens aparecendo na home
✅ Imagens aparecendo no painel
✅ URLs corretas por tenant
✅ Isolamento mantido
✅ Cache otimizado

---

**Data:** 07/03/2026
**Status:** ✅ Resolvido e testado
