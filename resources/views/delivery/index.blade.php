<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚗 Entregas - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta http-equiv="refresh" content="30">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-cyan-600 text-white p-6 shadow-lg">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-5xl">🚗</span>
                    <div>
                        <h1 class="text-3xl font-black">ENTREGAS</h1>
                        <p class="text-blue-100 text-sm">Atualiza automaticamente a cada 30 segundos</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-black">{{ $orders->where('status', 'out_for_delivery')->count() }}</div>
                    <div class="text-sm text-blue-100">Em Entrega Agora</div>
                </div>
            </div>
        </div>

        <!-- Pedidos -->
        <div class="max-w-7xl mx-auto p-6">
            @if($orders->isEmpty())
                <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                    <div class="text-8xl mb-4">🎉</div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Nenhuma entrega pendente!</h2>
                    <p class="text-gray-600">Aguardando novos pedidos.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($orders as $order)
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden border-4
                            {{ $order->status === 'ready' ? 'border-yellow-400' : 'border-blue-500' }}">

                            <!-- Header -->
                            <div class="p-4 {{ $order->status === 'ready' ? 'bg-yellow-50' : 'bg-blue-50' }}">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-3xl font-black text-gray-800">#{{ $order->order_number }}</span>
                                    <span class="px-4 py-2 rounded-full text-sm font-bold
                                        {{ $order->status === 'ready' ? 'bg-yellow-200 text-yellow-800' : 'bg-blue-200 text-blue-800' }}">
                                        {{ $order->status === 'ready' ? '📦 PRONTO' : '🚗 EM ENTREGA' }}
                                    </span>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xl">👤</span>
                                        <span class="font-bold text-lg">{{ $order->customer->name }}</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-xl">📞</span>
                                        <a href="tel:{{ $order->customer->phone }}" class="font-semibold text-blue-600 hover:underline">
                                            {{ $order->customer->phone }}
                                        </a>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-xl">📍</span>
                                        <span class="text-sm">{{ $order->delivery_address }}</span>
                                    </div>

                                    @if($order->delivery_neighborhood)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl">🏘️</span>
                                            <span class="px-3 py-1 bg-gray-200 rounded-full text-sm font-semibold">
                                                {{ $order->delivery_neighborhood }}
                                            </span>
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-2 mt-3 pt-3 border-t">
                                        <span class="text-xl">💰</span>
                                        <span class="text-2xl font-black text-green-600">
                                            R$ {{ number_format($order->total, 2, ',', '.') }}
                                        </span>
                                        <span class="ml-auto px-3 py-1 rounded-full text-xs font-bold
                                            {{ $order->payment_method === 'pix' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ strtoupper($order->payment_method) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Itens -->
                            <div class="p-4 border-t">
                                <p class="text-xs font-semibold text-gray-500 mb-2">ITENS DO PEDIDO:</p>
                                <div class="space-y-1">
                                    @foreach($order->items as $item)
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="font-bold text-orange-600">{{ $item->quantity }}x</span>
                                            <span>{{ $item->product_name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Ações -->
                            <div class="p-4 bg-gray-50 space-y-2">
                                @if($order->status === 'ready')
                                    <button onclick="updateStatus({{ $order->id }}, 'out_for_delivery')"
                                        class="w-full py-4 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg transition text-lg">
                                        🚗 INICIAR ENTREGA
                                    </button>
                                @endif

                                @if($order->status === 'out_for_delivery')
                                    <button onclick="updateStatus({{ $order->id }}, 'delivered')"
                                        class="w-full py-4 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition text-lg">
                                        ✅ MARCAR COMO ENTREGUE
                                    </button>
                                @endif

                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($order->delivery_address . ', ' . $order->delivery_neighborhood) }}"
                                    target="_blank"
                                    class="block w-full py-3 bg-gray-700 hover:bg-gray-800 text-white font-bold rounded-lg transition text-center">
                                    🗺️ ABRIR NO GOOGLE MAPS
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
        function updateStatus(orderId, status) {
            const confirmMsg = status === 'delivered'
                ? 'Confirmar que o pedido foi ENTREGUE ao cliente?'
                : 'Confirmar início da entrega?';

            if (!confirm(confirmMsg)) return;

            fetch(`/api/delivery/${orderId}/status`, {
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
    </script>
</body>
</html>
