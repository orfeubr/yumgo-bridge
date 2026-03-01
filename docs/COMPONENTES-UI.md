# 🎨 Componentes UI - YumGo

Biblioteca completa de componentes Blade reutilizáveis com Tailwind CSS + Heroicons.

---

## 📦 Componentes Disponíveis

1. [Loading Spinner](#loading-spinner)
2. [Skeleton Card](#skeleton-card)
3. [Progress Bar](#progress-bar)
4. [Loading Dots](#loading-dots)
5. [Empty State](#empty-state)
6. [Heroicons](#heroicons)

---

## 1. 🔄 Loading Spinner

**Arquivo:** `resources/views/components/loading-spinner.blade.php`

Spinner animado com círculos pulsantes e rotação. Ideal para loading de páginas ou ações.

### Uso Básico

```blade
{{-- Tamanho padrão (md) --}}
<x-loading-spinner />

{{-- Diferentes tamanhos --}}
<x-loading-spinner size="sm" />  <!-- Pequeno: 16px -->
<x-loading-spinner size="md" />  <!-- Médio: 32px (padrão) -->
<x-loading-spinner size="lg" />  <!-- Grande: 48px -->
<x-loading-spinner size="xl" />  <!-- Extra Grande: 64px -->
```

### Exemplo em Loading State

```blade
<div id="content" x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 2000)">
    <div x-show="loading" class="flex items-center justify-center py-20">
        <div class="text-center space-y-4">
            <x-loading-spinner size="lg" />
            <p class="text-gray-600">Carregando cardápio...</p>
        </div>
    </div>

    <div x-show="!loading" x-cloak>
        <!-- Conteúdo aqui -->
    </div>
</div>
```

### Exemplo com Botão

```blade
<button
    type="submit"
    class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
    wire:loading.attr="disabled"
>
    <span wire:loading.remove>Finalizar Pedido</span>
    <span wire:loading class="flex items-center gap-2">
        <x-loading-spinner size="sm" />
        Processando...
    </span>
</button>
```

---

## 2. 💀 Skeleton Card

**Arquivo:** `resources/views/components/skeleton-card.blade.php`

Card de loading com efeito shimmer. Simula estrutura de produto enquanto carrega.

### Uso Básico

```blade
{{-- Card único --}}
<x-skeleton-card />

{{-- Grid de cards (loading de produtos) --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <x-skeleton-card />
    <x-skeleton-card />
    <x-skeleton-card />
    <x-skeleton-card />
    <x-skeleton-card />
    <x-skeleton-card />
</div>
```

### Exemplo com Alpine.js

```blade
<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 1500)">
    {{-- Skeleton enquanto carrega --}}
    <div x-show="!loaded" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @for($i = 0; $i < 6; $i++)
            <x-skeleton-card />
        @endfor
    </div>

    {{-- Produtos reais --}}
    <div x-show="loaded" x-cloak class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($products as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
</div>
```

### Exemplo com Livewire

```blade
<div>
    @if($loading)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @for($i = 0; $i < 9; $i++)
                <x-skeleton-card />
            @endfor
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($products as $product)
                {{-- Seus cards de produto --}}
            @endforeach
        </div>
    @endif
</div>
```

---

## 3. 📊 Progress Bar

**Arquivo:** `resources/views/components/progress-bar.blade.php`

Barra de progresso animada com gradiente e efeito de brilho deslizante.

### Uso Básico

```blade
{{-- Barra simples com porcentagem --}}
<x-progress-bar :percent="75" />

{{-- Com label descritivo --}}
<x-progress-bar :percent="50" label="Preparando seu pedido" />

{{-- Diferentes valores --}}
<x-progress-bar :percent="25" label="Pedido recebido" />
<x-progress-bar :percent="50" label="Em preparação" />
<x-progress-bar :percent="75" label="Saiu para entrega" />
<x-progress-bar :percent="100" label="Entregue!" />
```

### Exemplo com Status do Pedido

```blade
<div class="space-y-4">
    <h3 class="font-bold text-lg">Status do Pedido #{{ $order->id }}</h3>

    @php
        $progressMap = [
            'pending' => ['percent' => 25, 'label' => 'Pedido recebido'],
            'preparing' => ['percent' => 50, 'label' => 'Preparando na cozinha'],
            'ready' => ['percent' => 75, 'label' => 'Pronto para entrega'],
            'delivering' => ['percent' => 90, 'label' => 'Saiu para entrega'],
            'delivered' => ['percent' => 100, 'label' => 'Entregue com sucesso!'],
        ];
        $progress = $progressMap[$order->status] ?? ['percent' => 0, 'label' => 'Aguardando'];
    @endphp

    <x-progress-bar
        :percent="$progress['percent']"
        :label="$progress['label']"
    />
</div>
```

### Exemplo Dinâmico (Alpine.js)

```blade
<div x-data="{ percent: 0 }" x-init="
    let interval = setInterval(() => {
        percent += 10;
        if (percent >= 100) clearInterval(interval);
    }, 500)
">
    <x-progress-bar x-bind:percent="percent" label="Upload em andamento..." />
</div>
```

---

## 4. ⏳ Loading Dots

**Arquivo:** `resources/views/components/loading-dots.blade.php`

Três pontos animados estilo WhatsApp. Ideal para indicar "digitando" ou processamento leve.

### Uso Básico

```blade
<x-loading-dots />
```

### Exemplo com Mensagem

```blade
<div class="flex items-center gap-2 text-gray-600">
    <span>Processando</span>
    <x-loading-dots />
</div>
```

### Exemplo Chat/Bot

```blade
<div class="chat-message bg-gray-100 rounded-lg px-4 py-3 max-w-sm">
    <div class="flex items-center gap-2">
        <img src="/images/bot-avatar.png" class="w-8 h-8 rounded-full">
        <div>
            <p class="text-sm font-medium">Assistente YumGo</p>
            <div class="flex items-center gap-2 text-gray-500 text-sm">
                Digitando
                <x-loading-dots />
            </div>
        </div>
    </div>
</div>
```

### Exemplo em Botão

```blade
<button
    class="px-4 py-2 bg-red-600 text-white rounded-lg"
    wire:loading.attr="disabled"
>
    <span wire:loading.remove>Enviar</span>
    <span wire:loading class="flex items-center gap-2">
        Enviando
        <x-loading-dots />
    </span>
</button>
```

---

## 5. 📭 Empty State

**Arquivo:** `resources/views/components/empty-state.blade.php`

Componente para estados vazios com ícone, título, mensagem e ação opcional.

### Props Disponíveis

| Prop | Tipo | Padrão | Descrição |
|------|------|--------|-----------|
| `title` | string | **obrigatório** | Título do estado vazio |
| `message` | string | **obrigatório** | Mensagem explicativa |
| `icon` | string | `'inbox'` | Ícone: `shopping-cart`, `inbox`, `search` |
| `actionText` | string | `''` | Texto do botão de ação (opcional) |
| `actionUrl` | string | `''` | URL do botão (opcional) |

### Uso Básico

```blade
{{-- Carrinho vazio --}}
<x-empty-state
    title="Carrinho vazio"
    message="Adicione produtos ao carrinho para continuar com o pedido"
    icon="shopping-cart"
/>

{{-- Sem resultados de busca --}}
<x-empty-state
    title="Nenhum produto encontrado"
    message="Tente ajustar os filtros ou fazer uma nova busca"
    icon="search"
/>

{{-- Inbox vazio --}}
<x-empty-state
    title="Nenhuma notificação"
    message="Você está em dia! Não há notificações pendentes"
    icon="inbox"
/>
```

### Com Botão de Ação

```blade
{{-- Carrinho vazio com botão para cardápio --}}
<x-empty-state
    title="Carrinho vazio"
    message="Que tal começar escolhendo algo delicioso?"
    icon="shopping-cart"
    actionText="Ver Cardápio"
    actionUrl="/cardapio"
/>

{{-- Sem pedidos com botão --}}
<x-empty-state
    title="Você ainda não fez pedidos"
    message="Peça agora e receba no conforto da sua casa!"
    icon="inbox"
    actionText="Fazer Primeiro Pedido"
    actionUrl="/cardapio"
/>
```

### Exemplo com Lógica Blade

```blade
@if($cart->isEmpty())
    <x-empty-state
        title="Carrinho vazio"
        message="Adicione produtos para continuar"
        icon="shopping-cart"
        actionText="Explorar Cardápio"
        actionUrl="{{ route('menu') }}"
    />
@else
    {{-- Lista de produtos do carrinho --}}
    @foreach($cart->items as $item)
        <!-- ... -->
    @endforeach
@endif
```

### Exemplo com Alpine.js

```blade
<div x-data="{ products: [] }">
    <div x-show="products.length === 0">
        <x-empty-state
            title="Nenhum favorito ainda"
            message="Marque produtos como favoritos para vê-los aqui"
            icon="inbox"
        />
    </div>

    <div x-show="products.length > 0" x-cloak>
        <!-- Lista de favoritos -->
    </div>
</div>
```

---

## 6. 🎯 Heroicons

**Pacote:** `blade-ui-kit/blade-heroicons`

Biblioteca completa com **300+ ícones** prontos para usar como componentes Blade.

### Instalação

```bash
composer require blade-ui-kit/blade-heroicons
```

### Uso Básico

```blade
{{-- Ícone sólido (preenchido) --}}
<x-heroicon-s-shopping-cart class="w-6 h-6 text-red-600" />

{{-- Ícone outline (contorno) --}}
<x-heroicon-o-shopping-cart class="w-6 h-6 text-gray-600" />

{{-- Ícone mini (16px) --}}
<x-heroicon-m-shopping-cart class="w-4 h-4" />
```

### Prefixos de Estilo

| Prefixo | Estilo | Uso |
|---------|--------|-----|
| `s-` | Solid (sólido) | Ícones preenchidos, botões primários |
| `o-` | Outline (contorno) | Ícones de linha, menu, navegação |
| `m-` | Mini (pequeno) | Ícones 16px, badges, tags |

### Ícones Comuns para Delivery

```blade
{{-- Navegação --}}
<x-heroicon-o-home class="w-6 h-6" />
<x-heroicon-o-shopping-bag class="w-6 h-6" />
<x-heroicon-o-user class="w-6 h-6" />
<x-heroicon-o-heart class="w-6 h-6" />
<x-heroicon-o-bell class="w-6 h-6" />

{{-- Ações --}}
<x-heroicon-s-plus class="w-5 h-5" />
<x-heroicon-s-minus class="w-5 h-5" />
<x-heroicon-s-trash class="w-5 h-5 text-red-600" />
<x-heroicon-s-pencil class="w-5 h-5 text-blue-600" />
<x-heroicon-s-check class="w-5 h-5 text-green-600" />

{{-- Status Pedido --}}
<x-heroicon-o-clock class="w-5 h-5 text-yellow-500" />
<x-heroicon-o-fire class="w-5 h-5 text-orange-500" />
<x-heroicon-o-truck class="w-5 h-5 text-blue-500" />
<x-heroicon-s-check-circle class="w-5 h-5 text-green-500" />

{{-- Comida/Delivery --}}
<x-heroicon-o-shopping-cart class="w-6 h-6" />
<x-heroicon-o-map-pin class="w-5 h-5" />
<x-heroicon-o-credit-card class="w-5 h-5" />
<x-heroicon-o-receipt-percent class="w-5 h-5" />
<x-heroicon-o-gift class="w-5 h-5" />

{{-- UI/UX --}}
<x-heroicon-o-x-mark class="w-5 h-5" />
<x-heroicon-o-magnifying-glass class="w-5 h-5" />
<x-heroicon-o-funnel class="w-5 h-5" />
<x-heroicon-o-bars-3 class="w-6 h-6" />
<x-heroicon-o-chevron-right class="w-5 h-5" />
```

### Exemplo: Botão com Ícone

```blade
<button class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
    <x-heroicon-s-shopping-cart class="w-5 h-5" />
    Adicionar ao Carrinho
</button>

<button class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
    <x-heroicon-o-heart class="w-5 h-5" />
    Favoritar
</button>
```

### Exemplo: Badge de Status

```blade
@php
$statusConfig = [
    'pending' => ['icon' => 'o-clock', 'color' => 'yellow', 'label' => 'Pendente'],
    'preparing' => ['icon' => 'o-fire', 'color' => 'orange', 'label' => 'Preparando'],
    'delivering' => ['icon' => 'o-truck', 'color' => 'blue', 'label' => 'Em entrega'],
    'delivered' => ['icon' => 's-check-circle', 'color' => 'green', 'label' => 'Entregue'],
];
$config = $statusConfig[$order->status];
@endphp

<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-700">
    <x-dynamic-component :component="'heroicon-' . $config['icon']" class="w-4 h-4" />
    {{ $config['label'] }}
</span>
```

### Exemplo: Menu de Navegação

```blade
<nav class="flex items-center gap-6">
    <a href="/" class="flex items-center gap-2 text-gray-700 hover:text-red-600 transition">
        <x-heroicon-o-home class="w-5 h-5" />
        <span>Início</span>
    </a>

    <a href="/cardapio" class="flex items-center gap-2 text-gray-700 hover:text-red-600 transition">
        <x-heroicon-o-shopping-bag class="w-5 h-5" />
        <span>Cardápio</span>
    </a>

    <a href="/pedidos" class="flex items-center gap-2 text-gray-700 hover:text-red-600 transition">
        <x-heroicon-o-receipt-percent class="w-5 h-5" />
        <span>Pedidos</span>
    </a>

    <a href="/perfil" class="flex items-center gap-2 text-gray-700 hover:text-red-600 transition">
        <x-heroicon-o-user class="w-5 h-5" />
        <span>Perfil</span>
    </a>
</nav>
```

### Exemplo: Card de Produto

```blade
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">

    <div class="p-4 space-y-3">
        <div class="flex items-start justify-between">
            <h3 class="font-bold text-lg">{{ $product->name }}</h3>
            <button class="text-gray-400 hover:text-red-600 transition">
                <x-heroicon-o-heart class="w-6 h-6" />
            </button>
        </div>

        <p class="text-gray-600 text-sm line-clamp-2">{{ $product->description }}</p>

        <div class="flex items-center gap-1 text-sm text-gray-500">
            <x-heroicon-m-star class="w-4 h-4 text-yellow-400" />
            <span class="font-medium">4.8</span>
            <span>(120 avaliações)</span>
        </div>

        <div class="flex items-center justify-between pt-2">
            <span class="text-2xl font-bold text-red-600">R$ {{ number_format($product->price, 2, ',', '.') }}</span>
            <button class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-full hover:bg-red-700">
                <x-heroicon-s-plus class="w-5 h-5" />
            </button>
        </div>
    </div>
</div>
```

### Buscar Ícones

**Explorar todos ícones:** https://heroicons.com

**Sintaxe do nome:**
```
x-heroicon-[estilo]-[nome-do-icone]

Exemplos:
x-heroicon-s-home          (solid home)
x-heroicon-o-shopping-cart (outline shopping cart)
x-heroicon-m-bell          (mini bell)
```

---

## 🎨 Paleta de Cores YumGo

```css
/* Vermelho Principal */
bg-red-600   → #EA1D2C (primária)
bg-red-700   → #C41C2A (hover)
bg-red-100   → #FEE2E2 (background leve)
text-red-600 → Texto vermelho

/* Cinza Neutro */
bg-gray-50   → Background muito leve
bg-gray-100  → Cards, bordas
bg-gray-600  → Texto secundário
bg-gray-900  → Texto principal

/* Cores de Status */
text-yellow-500 → Pendente
text-orange-500 → Preparando
text-blue-500   → Em entrega
text-green-500  → Entregue/Sucesso
text-red-500    → Cancelado/Erro
```

---

## 🚀 Exemplos Completos

### Página de Checkout com Loading

```blade
<div x-data="{ loading: true, submitting: false }" x-init="setTimeout(() => loading = false, 1000)">
    {{-- Loading inicial --}}
    <div x-show="loading" class="space-y-6">
        <x-skeleton-card />
        <x-skeleton-card />
        <x-skeleton-card />
    </div>

    {{-- Conteúdo --}}
    <div x-show="!loading" x-cloak class="space-y-6">
        {{-- Carrinho vazio --}}
        @if($cart->isEmpty())
            <x-empty-state
                title="Carrinho vazio"
                message="Adicione produtos para continuar"
                icon="shopping-cart"
                actionText="Ver Cardápio"
                actionUrl="/cardapio"
            />
        @else
            {{-- Lista de produtos --}}
            <div class="space-y-4">
                @foreach($cart->items as $item)
                    <div class="flex items-center gap-4 bg-white p-4 rounded-lg border">
                        <img src="{{ $item->product->image }}" class="w-20 h-20 object-cover rounded">
                        <div class="flex-1">
                            <h3 class="font-bold">{{ $item->product->name }}</h3>
                            <p class="text-sm text-gray-600">R$ {{ number_format($item->price, 2, ',', '.') }}</p>
                        </div>
                        <button wire:click="remove({{ $item->id }})" class="text-red-600 hover:text-red-700">
                            <x-heroicon-o-trash class="w-5 h-5" />
                        </button>
                    </div>
                @endforeach
            </div>

            {{-- Botão finalizar --}}
            <button
                @click="submitting = true"
                wire:click="checkout"
                :disabled="submitting"
                class="w-full py-4 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 disabled:opacity-50"
            >
                <span x-show="!submitting">Finalizar Pedido</span>
                <span x-show="submitting" class="flex items-center justify-center gap-2">
                    <x-loading-spinner size="sm" />
                    Processando
                </span>
            </button>
        @endif
    </div>
</div>
```

### Status do Pedido Animado

```blade
<div class="bg-white rounded-xl shadow-lg p-6 space-y-6">
    <h2 class="text-2xl font-bold flex items-center gap-2">
        <x-heroicon-s-receipt-percent class="w-7 h-7 text-red-600" />
        Pedido #{{ $order->id }}
    </h2>

    {{-- Progress bar --}}
    @php
        $progressMap = [
            'pending' => 25,
            'preparing' => 50,
            'ready' => 75,
            'delivering' => 90,
            'delivered' => 100,
        ];
        $percent = $progressMap[$order->status] ?? 0;
    @endphp

    <x-progress-bar :percent="$percent" :label="$order->status_label" />

    {{-- Timeline --}}
    <div class="space-y-4">
        <div class="flex items-start gap-3">
            <x-heroicon-s-check-circle class="w-6 h-6 text-green-500" />
            <div>
                <p class="font-medium">Pedido recebido</p>
                <p class="text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        @if($order->status !== 'pending')
            <div class="flex items-start gap-3">
                <x-heroicon-s-fire class="w-6 h-6 text-orange-500" />
                <div>
                    <p class="font-medium">Em preparação</p>
                    <p class="text-sm text-gray-500">Estimativa: 30-40 min</p>
                </div>
            </div>
        @endif

        @if(in_array($order->status, ['delivering', 'delivered']))
            <div class="flex items-start gap-3">
                <x-heroicon-s-truck class="w-6 h-6 text-blue-500" />
                <div>
                    <p class="font-medium">Saiu para entrega</p>
                    <p class="text-sm text-gray-500">Motorista: João Silva</p>
                </div>
            </div>
        @endif
    </div>
</div>
```

---

## 📚 Recursos

- **Heroicons:** https://heroicons.com
- **Tailwind CSS:** https://tailwindcss.com/docs
- **Alpine.js:** https://alpinejs.dev
- **Blade Components:** https://laravel.com/docs/blade#components

---

**Criado para YumGo 🚀**
*Delivery que respeita o restaurante e o cliente!*
