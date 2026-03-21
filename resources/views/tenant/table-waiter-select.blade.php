<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Selecione seu Garçom - Mesa {{ $table->number }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            -webkit-tap-highlight-color: transparent;
        }

        .waiter-card {
            @apply bg-white rounded-lg p-4 shadow-md border-2 border-transparent transition-all duration-200 cursor-pointer;
        }

        .waiter-card:hover {
            @apply border-blue-500 shadow-lg;
        }

        .waiter-card.selected {
            @apply border-green-500 bg-green-50;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <div class="container mx-auto px-4 py-8 max-w-2xl">

        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 rounded-lg shadow-lg mb-6 text-center">
            <h1 class="text-2xl font-bold mb-2">🪑 Mesa {{ $table->number }}</h1>
            <p class="text-blue-100">Selecione quem irá atendê-lo</p>
        </div>

        <!-- Lista de Garçons -->
        <form method="POST" action="{{ route('table.select-waiter') }}" id="waiterForm">
            @csrf

            <input type="hidden" name="waiter_id" id="waiterId">

            <div class="space-y-4 mb-6">
                @forelse($waiters as $waiter)
                    <div class="waiter-card" onclick="selectWaiter({{ $waiter->id }}, this)">
                        <div class="flex items-center space-x-4">
                            @if($waiter->photo)
                                <img src="{{ Storage::url($waiter->photo) }}" alt="{{ $waiter->name }}" class="w-16 h-16 rounded-full border-2 border-gray-200">
                            @else
                                <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center border-2 border-gray-200">
                                    <span class="text-2xl font-bold text-blue-600">{{ substr($waiter->name, 0, 1) }}</span>
                                </div>
                            @endif

                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-gray-800">{{ $waiter->name }}</h3>
                                @if($waiter->phone)
                                    <p class="text-sm text-gray-500">{{ $waiter->phone }}</p>
                                @endif
                            </div>

                            <div class="checkmark hidden text-green-500 text-2xl">✓</div>
                        </div>
                    </div>
                @empty
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <p class="text-yellow-800 font-semibold mb-2">⚠️ Nenhum garçom disponível</p>
                        <p class="text-yellow-600 text-sm">Por favor, chame um atendente.</p>
                    </div>
                @endforelse
            </div>

            <!-- Botão Confirmar -->
            <button
                type="submit"
                id="confirmButton"
                disabled
                class="w-full py-4 bg-gray-300 text-gray-500 rounded-lg font-bold text-lg transition-all disabled:cursor-not-allowed">
                Selecione um garçom
            </button>

        </form>

        <!-- Informações -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-800 text-center">
                ℹ️ Após selecionar, você será direcionado ao cardápio
            </p>
        </div>

    </div>

    <script>
        let selectedWaiterId = null;

        function selectWaiter(waiterId, element) {
            // Remove seleção anterior
            document.querySelectorAll('.waiter-card').forEach(card => {
                card.classList.remove('selected');
                card.querySelector('.checkmark').classList.add('hidden');
            });

            // Adiciona seleção
            element.classList.add('selected');
            element.querySelector('.checkmark').classList.remove('hidden');

            // Armazena ID
            selectedWaiterId = waiterId;
            document.getElementById('waiterId').value = waiterId;

            // Ativa botão
            const button = document.getElementById('confirmButton');
            button.disabled = false;
            button.className = 'w-full py-4 bg-green-500 hover:bg-green-600 text-white rounded-lg font-bold text-lg transition-all cursor-pointer active:scale-95';
            button.textContent = 'Confirmar e Ver Cardápio';
        }
    </script>
</body>
</html>
