# Filtro de Cardápio Semanal na Homepage

## 📋 Como Funciona

A homepage da loja agora mostra **apenas os produtos do cardápio semanal** quando há um cardápio ativo.

### Lógica de Exibição

1. ✅ **Se há cardápio ativo para hoje** → Mostra APENAS produtos do dia
2. ⚠️ **Se não há cardápio ativo** → Mostra TODOS os produtos ativos (comportamento padrão)

## 🔍 Implementação

### RestaurantHomeController

```php
// 1. Busca cardápio ativo
$activeMenu = WeeklyMenu::getActive();

// 2. Se houver, pega IDs dos produtos de hoje
if ($activeMenu) {
    $today = WeeklyMenu::getCurrentDayOfWeek(); // 'monday', 'tuesday', etc
    $todayProductIds = $activeMenu->items()
        ->where('day_of_week', $today)
        ->where('is_available', true)
        ->pluck('product_id')
        ->toArray();
}

// 3. Filtra produtos usando whereIn()
$allProducts = Product::where('is_active', true)
    ->when($todayProductIds !== null, function ($query) use ($todayProductIds) {
        $query->whereIn('id', $todayProductIds);
    })
    ->get();
```

### Scope Reutilizável

Foi criado um scope `inTodaysMenu()` no modelo Product:

```php
Product::active()
    ->inStock()
    ->inTodaysMenu() // ⭐ Novo scope
    ->get();
```

## 📅 Exemplo Prático

### Configuração no Painel

**Cardápio da Semana**
- Segunda: Frango Grelhado, Bife Acebolado, Lasanha
- Terça: Peixe Assado, Strogonoff, Feijoada
- Quarta: Frango Grelhado, Picadinho, Nhoque

### Resultado na Loja

- **Segunda-feira**: Mostra apenas 3 produtos
- **Terça-feira**: Mostra apenas 3 produtos (diferentes)
- **Quarta-feira**: Mostra apenas 3 produtos
- **Sem cardápio ativo**: Mostra TODOS os produtos do catálogo

## 🎯 Benefícios

1. **Clientes veem apenas o disponível hoje** - Evita pedidos de produtos indisponíveis
2. **Cardápio rotativo** - Renova a loja automaticamente a cada dia
3. **Gestão simplificada** - Configure uma vez, funciona a semana toda
4. **Flexibilidade** - Se desativar o cardápio, volta ao catálogo completo

## 🔧 Arquivos Modificados

- `app/Http/Controllers/RestaurantHomeController.php` - Lógica de filtro
- `app/Models/Product.php` - Scope `inTodaysMenu()`
- `app/Models/WeeklyMenu.php` - Métodos `getActive()` e `getCurrentDayOfWeek()`

## 🧪 Testando

### 1. Criar Cardápio Ativo

```bash
# No painel do restaurante
Cardápio Semanal → Criar Novo
- Nome: Cardápio da Semana
- Status: Ativo ✅
- Selecionar produtos por dia
```

### 2. Verificar Homepage

```
https://seu-restaurante.eliseus.com.br/
```

Deve mostrar apenas os produtos configurados para o dia atual.

### 3. Desativar Cardápio

```bash
# No painel
Editar Cardápio → Status: Inativo ❌
```

Homepage volta a mostrar todos os produtos.

## ⚙️ Configurações Importantes

### WeeklyMenu::getActive()

Retorna cardápio se:
- `is_active = true`
- `starts_at <= hoje` (ou null)
- `ends_at >= hoje` (ou null)

### WeeklyMenu::getCurrentDayOfWeek()

Retorna o dia atual em inglês minúsculo:
- `'monday'`, `'tuesday'`, `'wednesday'`, etc.

### WeeklyMenuItem

Cada item precisa:
- `product_id` - Produto vinculado
- `day_of_week` - Dia da semana ('monday', 'tuesday', etc)
- `is_available = true` - Disponível

## 📊 Casos de Uso

### Marmitaria
- Cardápio fixo semanal
- Produtos diferentes a cada dia
- Clientes sabem o que esperar

### Pizzaria
- Promoções diárias
- "Terça é dia de 4 Queijos"
- Pizzas especiais por dia

### Restaurante
- Pratos do dia
- Menu executivo semanal
- Almoço vs Jantar (múltiplos cardápios)

## 🚨 Notas Importantes

1. **Produtos fora do estoque** - Mesmo no cardápio, não aparecem se `stock_quantity = 0`
2. **Produtos inativos** - Não aparecem mesmo que estejam no cardápio
3. **Múltiplos cardápios** - Se criar vários, apenas o primeiro ativo é usado
4. **Horário** - Usa timezone configurado em `config/app.php`

---

**Data:** 22/02/2026
**Implementado por:** Claude Code
