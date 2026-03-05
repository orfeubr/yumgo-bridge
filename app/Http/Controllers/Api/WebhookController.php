<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated Asaas foi substituído por Pagar.me como gateway principal
 * Mantido apenas para compatibilidade com tenants antigos que ainda usam Asaas
 * Use PagarMeWebhookController para novos pagamentos
 */
class WebhookController extends Controller
{
    /**
     * Webhook do Asaas para notificações de pagamento
     *
     * @deprecated Usar webhook do Pagar.me (/api/webhooks/pagarme)
     */
    public function asaas(Request $request)
    {
        Log::warning('⚠️ Webhook Asaas recebido mas GATEWAY DESABILITADO. Migrar para Pagar.me!', [
            'data' => $request->all(),
        ]);

        return response()->json([
            'message' => 'Asaas webhook desabilitado. Este restaurante foi migrado para Pagar.me.'
        ], 410); // 410 Gone - recurso não está mais disponível
    }
}
