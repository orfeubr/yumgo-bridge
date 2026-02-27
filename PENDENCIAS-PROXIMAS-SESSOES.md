# 📝 Pendências para Próximas Sessões - DeliveryPro

**Data:** 22/02/2026
**Status:** Documentado para continuação futura

---

## 🎯 PRIORIDADE 1 - Implementar Primeiro (Recomendado)

Estas funcionalidades são **essenciais** para operação completa dos restaurantes:

### 1. **Tags nos Produtos** 🏷️
**Status:** 50% completo (backend pronto)
**Tempo estimado:** 2 dias
**Prioridade:** ALTA

**O que já tem:**
- ✅ Campo `tags` (JSON) na tabela products
- ✅ Migration executada em todos os tenants
- ✅ Model Product com cast para array

**O que falta implementar:**

#### Backend (5 minutos):
- Nenhum backend necessário, já está pronto!

#### Filament UI (ProductResource):
```php
// Adicionar no formulário:
Forms\Components\TagsInput::make('tags')
    ->label('Tags')
    ->helperText('Ex: Vegano, Sem Glúten, Apimentado, Fit, Light')
    ->suggestions([
        'Vegano',
        'Vegetariano',
        'Sem Glúten',
        'Sem Lactose',
        'Apimentado',
        'Fit',
        'Light',
        'Orgânico',
        'Zero Açúcar',
        'Proteico',
    ])
    ->placeholder('Digite e pressione Enter')
    ->columnSpanFull(),

// Adicionar na tabela:
Tables\Columns\TextColumn::make('tags')
    ->label('Tags')
    ->badge()
    ->separator(',')
    ->color('success'),
```

#### Catálogo Público (catalog.blade.php):
```php
// Exibir badges no card do produto:
@if($product->tags)
    <div class="flex flex-wrap gap-1 mt-2">
        @foreach($product->tags as $tag)
            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                @if($tag === 'Vegano') 🌱
                @elseif($tag === 'Sem Glúten') 🚫
                @elseif($tag === 'Apimentado') 🌶️
                @elseif($tag === 'Fit') 💪
                @endif
                {{ $tag }}
            </span>
        @endforeach
    </div>
@endif
```

#### Filtros (opcional):
- Filtro por tag no ProductResource
- Filtro por tag no catálogo público

---

### 2. **Display para Cozinha (KDS - Kitchen Display System)** 👨‍🍳
**Status:** 0% completo
**Tempo estimado:** 3 dias
**Prioridade:** CRÍTICA

**Descrição:**
Tela dedicada para a cozinha acompanhar pedidos em tempo real.

**Arquivos a criar:**

#### 1. Rota (routes/tenant.php):
```php
Route::get('/painel/cozinha', function () {
    return view('tenant.kds');
})->name('kds.display');
```

#### 2. View (resources/views/tenant/kds.blade.php):
```blade
<!DOCTYPE html>
<html>
<head>
    <title>Cozinha - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-900" x-data="kds()">
    <div class="p-4">
        <div class="grid grid-cols-3 gap-4">
            <!-- Coluna 1: Pendentes -->
            <div>
                <h2 class="text-white text-2xl mb-4">⏳ PENDENTES</h2>
                <template x-for="order in pendingOrders" :key="order.id">
                    <div class="bg-red-500 text-white p-4 rounded-lg mb-4" :class="getColorByTime(order.created_at)">
                        <div class="text-3xl font-bold">#<span x-text="order.order_number"></span></div>
                        <div class="text-sm opacity-75" x-text="timeAgo(order.created_at)"></div>
                        <div class="mt-2" x-html="formatItems(order.items)"></div>
                        <button @click="startPreparing(order.id)" class="mt-4 w-full bg-white text-red-500 py-2 rounded font-bold">
                            INICIAR PREPARO
                        </button>
                    </div>
                </template>
            </div>

            <!-- Coluna 2: Preparando -->
            <div>
                <h2 class="text-white text-2xl mb-4">👨‍🍳 PREPARANDO</h2>
                <!-- Similar estrutura -->
            </div>

            <!-- Coluna 3: Prontos -->
            <div>
                <h2 class="text-white text-2xl mb-4">✅ PRONTOS</h2>
                <!-- Similar estrutura -->
            </div>
        </div>
    </div>

    <script>
        function kds() {
            return {
                pendingOrders: [],
                preparingOrders: [],
                readyOrders: [],

                init() {
                    this.loadOrders();
                    setInterval(() => this.loadOrders(), 10000); // Auto-refresh 10s
                    this.listenForNewOrders();
                },

                loadOrders() {
                    // Fetch API
                },

                startPreparing(orderId) {
                    // PUT /api/v1/orders/{id}/status (status: preparing)
                },

                markAsReady(orderId) {
                    // PUT /api/v1/orders/{id}/status (status: ready)
                },

                playSound() {
                    const audio = new Audio('/sounds/new-order.mp3');
                    audio.play();
                },

                listenForNewOrders() {
                    // WebSocket ou Long Polling
                }
            }
        }
    </script>
</body>
</html>
```

#### 3. API Endpoint (OrderController):
```php
public function updateStatus(Request $request, $id)
{
    $order = Order::findOrFail($id);

    $order->update([
        'status' => $request->status
    ]);

    // Broadcast evento (Pusher/WebSocket)
    broadcast(new OrderStatusUpdated($order));

    return response()->json(['success' => true]);
}
```

#### 4. Recursos necessários:
- Som de notificação (`public/sounds/new-order.mp3`)
- WebSocket ou Long Polling para updates em tempo real
- Responsivo para tablets

---

### 3. **Cadastro de Entregadores** 🚗
**Status:** 0% completo
**Tempo estimado:** 5 dias
**Prioridade:** ALTA

**Arquivos a criar:**

#### 1. Migration:
```bash
php artisan make:migration create_delivery_drivers_table --path=database/migrations/tenant
```

```php
Schema::create('delivery_drivers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('cpf', 11)->unique();
    $table->string('cnh')->nullable();
    $table->string('phone');
    $table->string('email')->nullable();
    $table->enum('vehicle_type', ['moto', 'carro', 'bicicleta', 'a_pe'])->default('moto');
    $table->string('vehicle_plate')->nullable();
    $table->enum('status', ['disponivel', 'em_entrega', 'offline'])->default('offline');
    $table->decimal('rating', 3, 2)->default(5.00);
    $table->integer('total_deliveries')->default(0);
    $table->json('bank_account')->nullable(); // Para pagamento
    $table->boolean('active')->default(true);
    $table->timestamps();
});
```

#### 2. Model:
```php
php artisan make:model DeliveryDriver
```

#### 3. Filament Resource:
```php
php artisan make:filament-resource DeliveryDriver --panel=restaurant
```

Formulário com:
- Nome, CPF, CNH
- Telefone (com botão WhatsApp)
- Veículo (tipo, placa)
- Status (badge colorido)
- Dados bancários (para pagamento)

#### 4. Atribuir entregador ao pedido:
Adicionar na Order migration:
```php
$table->foreignId('delivery_driver_id')->nullable()->constrained();
```

Adicionar select no OrderResource para escolher entregador disponível.

#### 5. Dashboard do entregador:
- Total de entregas
- Avaliação média
- Ganhos do dia/mês
- Histórico

---

## 📊 PRIORIDADE 2 - Gestão e Relatórios

### 4. **Gestão de Estoque** 📦
**Status:** 40% completo (backend pronto)
**Tempo estimado:** 3 dias

**O que falta:**

#### Observer em Order (decrementar estoque):
```php
// app/Observers/OrderObserver.php

public function updated(Order $order)
{
    // Quando status mudar de 'pending' para 'confirmed'
    if ($order->isDirty('status') && $order->status === 'confirmed') {
        foreach ($order->items as $item) {
            $product = $item->product;

            if ($product->stock_enabled) {
                // Decrementar estoque
                $product->decrement('stock_quantity', $item->quantity);

                // Verificar se atingiu mínimo
                if ($product->stock_quantity <= $product->stock_min_alert && !$product->stock_alert_sent) {
                    // Enviar notificação
                    Notification::make()
                        ->title("⚠️ Estoque baixo: {$product->name}")
                        ->body("Restam apenas {$product->stock_quantity} unidades")
                        ->warning()
                        ->sendToDatabase(auth()->user());

                    $product->update(['stock_alert_sent' => true]);
                }
            }
        }
    }
}
```

#### Bloquear pedido se estoque zerado:
```php
// No checkout, validar antes de criar pedido
if ($product->stock_enabled && $product->stock_quantity < $quantity) {
    throw new \Exception("Produto {$product->name} sem estoque");
}
```

---

### 5. **Relatórios XLSX Exportáveis** 📊
**Status:** 0% completo
**Tempo estimado:** 5 dias

**Pacote necessário:**
```bash
composer require maatwebsite/excel
```

**Relatórios a criar:**
1. Vendas por período
2. Produtos mais vendidos
3. Clientes frequentes
4. Faturamento por categoria
5. Relatório financeiro
6. Relatório de estoque
7. Relatório de cashback

**Exemplo de implementação:**
```php
// app/Exports/SalesReport.php
namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Order::whereBetween('created_at', [request('start'), request('end')])
            ->select('order_number', 'customer_id', 'total', 'status', 'created_at')
            ->get();
    }

    public function headings(): array
    {
        return ['Pedido', 'Cliente', 'Total', 'Status', 'Data'];
    }
}

// Controller
public function exportSales()
{
    return Excel::download(new SalesReport(), 'vendas.xlsx');
}
```

---

## 💰 PRIORIDADE 3 - Funcionalidades Avançadas

### 6. **Gestão Financeira** 💰
**Tempo estimado:** 10 dias
**Complexidade:** Alta

Ver detalhes em `docs/ROADMAP-FUNCIONALIDADES.md`

### 7. **Robô IA WhatsApp/Facebook/Instagram** 🤖
**Tempo estimado:** 15 dias
**Complexidade:** Muito Alta

Ver detalhes em `docs/ROADMAP-FUNCIONALIDADES.md`

### 8. **Agendamento de Pedidos** 📅
**Tempo estimado:** 4 dias
**Complexidade:** Média

Ver detalhes em `docs/ROADMAP-FUNCIONALIDADES.md`

---

## 🎨 PRIORIDADE 4 - Melhorias Visuais

### 9. **Modo Escuro** 🌙
**Tempo estimado:** 2 dias
**Prioridade:** Baixa (cosmético)

### 10. **Reformular Outras Páginas** 🎨
Aplicar o design novo (gradiente, gray background) em:
- Profile (tenant.profile)
- Orders (tenant.my-orders)
- Order Tracking (tenant.order-tracking)
- Payment (tenant.payment)

---

## 🔔 PRIORIDADE 5 - Comunicação

### 11. **Notificações Push** 🔔
**Tempo estimado:** 4 dias

### 12. **Chat em Tempo Real** 💬
**Tempo estimado:** 6 dias

---

## 📱 PRIORIDADE 6 - Longo Prazo

### 13. **App Mobile Nativo (Flutter)** 📱
**Tempo estimado:** 60 dias (2 meses)
**Prioridade:** Baixa (PWA funciona bem)

**Nota:** Deixar para última fase, pois PWA já está funcional.

---

## 🐛 BUGS A CORRIGIR

### Prioridade Alta:
1. ✅ ~~Asaas Account ID não preenche~~ - **CORRIGIDO!**
2. ✅ ~~Usuário admin não criado automaticamente~~ - **CORRIGIDO!**
3. ❌ Recuperação de senha não implementada
4. ❌ Verificação de e-mail não implementada

### Prioridade Média:
5. ❌ NFCe estrutura criada mas não funcional
6. ❌ Impressora térmica estrutura criada mas não testada
7. ❌ Reformular páginas com design novo

---

## 📅 CRONOGRAMA SUGERIDO

### Semana 1 (5 dias úteis):
- **Dia 1-2:** Tags nos Produtos (UI)
- **Dia 3-5:** KDS - Display Cozinha

### Semana 2 (5 dias úteis):
- **Dia 1-3:** Gestão de Estoque (lógica completa)
- **Dia 4-5:** Cadastro Entregadores (início)

### Semana 3 (5 dias úteis):
- **Dia 1-3:** Cadastro Entregadores (finalizar)
- **Dia 4-5:** Agendamento de Pedidos (início)

### Semana 4 (5 dias úteis):
- **Dia 1-2:** Agendamento de Pedidos (finalizar)
- **Dia 3-5:** Relatórios XLSX

### Mês 2:
- Gestão Financeira (10 dias)
- Robô IA WhatsApp (15 dias)

### Mês 3+:
- Melhorias visuais
- Notificações e Chat
- App Flutter (se necessário)

---

## ✅ CHECKLIST DE PRIORIDADES

**Fazer primeiro (Essencial):**
- [ ] Tags nos Produtos (UI)
- [ ] KDS - Display Cozinha
- [ ] Cadastro de Entregadores
- [ ] Gestão de Estoque (lógica)

**Fazer depois (Importante):**
- [ ] Agendamento de Pedidos
- [ ] Relatórios XLSX
- [ ] Gestão Financeira

**Deixar por último (Opcional):**
- [ ] Modo Escuro
- [ ] Chat Tempo Real
- [ ] App Flutter

---

## 📝 NOTAS IMPORTANTES

1. **API REST já está pronta** - A maioria das funcionalidades já tem endpoints
2. **Dashboard funcional** - 8 widgets implementados
3. **Multi-tenant OK** - Sistema robusto e escalável
4. **Asaas integrado** - Pagamentos funcionando
5. **Cashback funcionando** - Sistema completo

**Sistema já está MUITO funcional! Focar nas funcionalidades operacionais (KDS, Entregadores, Estoque)** 🎯

---

**Documentado em:** 22/02/2026
**Próxima sessão:** Implementar Tags + KDS + Entregadores
