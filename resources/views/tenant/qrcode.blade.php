@extends('tenant.layouts.app')

@section('title', 'QR Code do Cardápio')

@section('content')
<div class="bg-gray-50 min-h-screen pb-20">
    <!-- Header -->
    <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white py-8 shadow-lg">
        <div class="max-w-4xl mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold mb-2">📱 QR Code do Cardápio</h1>
            <p class="text-white/90">{{ $tenant->name }}</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Card Principal -->
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <!-- QR Code -->
            <div class="inline-block p-8 bg-white rounded-2xl shadow-inner">
                {!! QrCode::size(300)->margin(2)->errorCorrection('H')->generate($url) !!}
            </div>

            <!-- Informações -->
            <div class="mt-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Escaneie para ver o cardápio!</h2>
                <p class="text-gray-600 mb-1">{{ $url }}</p>
                <p class="text-sm text-gray-500">Aponte a câmera do celular para o QR Code</p>
            </div>

            <!-- Botões -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/qrcode/download"
                   class="px-6 py-3 bg-primary-500 text-white rounded-lg font-bold hover:bg-primary-600 transition inline-flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Baixar PNG
                </a>

                <a href="/qrcode/pdf"
                   target="_blank"
                   class="px-6 py-3 bg-green-500 text-white rounded-lg font-bold hover:bg-green-600 transition inline-flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir
                </a>

                <button onclick="copyLink()"
                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition inline-flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Copiar Link
                </button>
            </div>
        </div>

        <!-- Instruções -->
        <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-lg">
            <h3 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Como usar:
            </h3>
            <ul class="space-y-2 text-sm text-blue-900">
                <li class="flex gap-2">
                    <span class="font-bold">1.</span>
                    <span>Baixe o QR Code ou imprima</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-bold">2.</span>
                    <span>Coloque nas mesas do restaurante</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-bold">3.</span>
                    <span>Clientes escaneiam e fazem pedido direto pelo celular!</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
function copyLink() {
    navigator.clipboard.writeText('{{ $url }}').then(() => {
        alert('✅ Link copiado!');
    });
}
</script>
@endsection
