<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-semibold text-gray-700">Nome do Restaurante</label>
            <p class="text-sm text-gray-900">{{ $tenant->name }}</p>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700">URL</label>
            <p class="text-sm text-gray-900">{{ $tenant->slug }}.yumgo.com.br</p>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700">Email</label>
            <p class="text-sm text-gray-900">{{ $tenant->email }}</p>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700">Telefone</label>
            <p class="text-sm text-gray-900">{{ $tenant->phone }}</p>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700">Cadastrado em</label>
            <p class="text-sm text-gray-900">{{ $tenant->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700">Plano Escolhido</label>
            <p class="text-sm text-gray-900">{{ $tenant->plan->name ?? 'N/A' }}</p>
        </div>
    </div>

    @if($tenant->rejection_reason)
        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
            <label class="text-sm font-semibold text-red-900">Motivo da Rejeição Anterior</label>
            <p class="text-sm text-red-700 mt-1">{{ $tenant->rejection_reason }}</p>
        </div>
    @endif

    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
        <label class="text-sm font-semibold text-gray-700">Ações Recomendadas</label>
        <ul class="text-sm text-gray-600 mt-2 space-y-1 list-disc list-inside">
            <li>Verificar se o nome do restaurante é legítimo</li>
            <li>Validar se o telefone é real (pode ligar/mandar mensagem)</li>
            <li>Verificar se o email é profissional (evitar emails temporários)</li>
            <li>Confirmar se a URL (slug) não é ofensiva ou genérica demais</li>
        </ul>
    </div>
</div>
