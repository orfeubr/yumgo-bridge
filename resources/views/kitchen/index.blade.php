<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👨‍🍳 Cozinha - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta http-equiv="refresh" content="30"> <!-- Auto-refresh a cada 30s -->
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white p-6 shadow-lg">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-5xl">👨‍🍳</span>
                    <div>
                        <h1 class="text-3xl font-black">COZINHA</h1>
                        <p class="text-orange-100 text-sm">Atualiza automaticamente a cada 30 segundos</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-black">{{ $orders->count() }}</div>
                    <div class="text-sm text-orange-100">Pedidos Ativos</div>
                </div>
            </div>
        </div>

        <!-- Pedidos -->
        <div class="max-w-7xl mx-auto p-6">
            @if($orders->isEmpty())
                <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                    <div class="text-8xl mb-4">🎉</div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Nenhum pedido pendente!</h2>
                    <p class="text-gray-600">Todos os pedidos foram preparados.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($orders as $order)
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden border-4
                            {{ $order->status === 'pending' ? 'border-gray-300' : '' }}
                            {{ $order->status === 'confirmed' ? 'border-yellow-400' : '' }}
                            {{ $order->status === 'preparing' ? 'border-orange-500' : '' }}
                            {{ $order->status === 'ready' ? 'border-green-500' : '' }}">

                            <!-- Header do Card -->
                            <div class="p-4
                                {{ $order->status === 'pending' ? 'bg-gray-100' : '' }}
                                {{ $order->status === 'confirmed' ? 'bg-yellow-50' : '' }}
                                {{ $order->status === 'preparing' ? 'bg-orange-50' : '' }}
                                {{ $order->status === 'ready' ? 'bg-green-50' : '' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-3xl font-black text-gray-800">#{{ $order->order_number }}</span>
                                    <span class="px-3 py-1 rounded-full text-sm font-bold
                                        {{ $order->status === 'pending' ? 'bg-gray-200 text-gray-800' : '' }}
                                        {{ $order->status === 'confirmed' ? 'bg-yellow-200 text-yellow-800' : '' }}
                                        {{ $order->status === 'preparing' ? 'bg-orange-200 text-orange-800' : '' }}
                                        {{ $order->status === 'ready' ? 'bg-green-200 text-green-800' : '' }}">
                                        @switch($order->status)
                                            @case('pending') ⏳ PENDENTE @break
                                            @case('confirmed') ✅ CONFIRMADO @break
                                            @case('preparing') 👨‍🍳 PREPARANDO @break
                                            @case('ready') 📦 PRONTO @break
                                        @endswitch
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <span>👤</span>
                                    <span class="font-semibold">{{ $order->customer->name }}</span>
                                </div>

                                <div class="flex items-center gap-2 text-sm text-gray-600 mt-1">
                                    <span>🕐</span>
                                    <span>{{ $order->created_at->format('H:i') }}</span>
                                    <span class="ml-auto px-2 py-1 rounded-full text-xs font-bold
                                        {{ $order->created_at->diffInMinutes(now()) > 30 ? 'bg-red-500 text-white' : 'bg-green-500 text-white' }}">
                                        {{ $order->created_at->diffInMinutes(now()) }} min
                                    </span>
                                </div>
                            </div>

                            <!-- Itens do Pedido -->
                            <div class="p-4 space-y-2">
                                @foreach($order->items as $item)
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                        <span class="text-2xl font-black text-orange-600">{{ $item->quantity }}x</span>
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-800">{{ $item->product_name }}</p>
                                            @if($item->notes)
                                                <p class="text-sm text-gray-600 mt-1">📝 {{ $item->notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Ações -->
                            <div class="p-4 bg-gray-50 space-y-2">
                                @if($order->status === 'pending')
                                    <button onclick="updateStatus({{ $order->id }}, 'confirmed')"
                                        class="w-full py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded-lg transition">
                                        ✅ CONFIRMAR PEDIDO
                                    </button>
                                @endif

                                @if($order->status === 'confirmed')
                                    <button onclick="updateStatus({{ $order->id }}, 'preparing')"
                                        class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-lg transition">
                                        👨‍🍳 COMEÇAR PREPARO
                                    </button>
                                @endif

                                @if($order->status === 'preparing')
                                    <button onclick="updateStatus({{ $order->id }}, 'ready')"
                                        class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition">
                                        📦 MARCAR PRONTO
                                    </button>
                                @endif

                                @if($order->status === 'ready')
                                    <button onclick="updateStatus({{ $order->id }}, 'out_for_delivery')"
                                        class="w-full py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg transition text-lg">
                                        🚗 SAIU PARA ENTREGA
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
        function updateStatus(orderId, status) {
            if (!confirm('Confirmar mudança de status?')) return;

            fetch(`/api/kitchen/${orderId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                alert('Erro ao atualizar status');
                console.error(error);
            });
        }

        // Som de notificação (opcional)
        @if($orders->where('status', 'pending')->count() > 0)
            // Pode adicionar um beep ou notificação sonora aqui
        @endif
    </script>
</body>
</html>
