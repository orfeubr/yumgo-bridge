<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected AsaasService $asaasService
    ) {}

    /**
     * Webhook do Asaas para notificações de pagamento
     */
    public function asaas(Request $request)
    {
        Log::info('Webhook Asaas recebido', $request->all());

        try {
            // Valida token do webhook (se configurado)
            $webhookToken = config('services.asaas.webhook_token');
            if ($webhookToken && $request->header('asaas-access-token') !== $webhookToken) {
                Log::warning('Webhook Asaas - Token inválido');
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Processa webhook
            $success = $this->asaasService->handleWebhook($request->all());

            if ($success) {
                return response()->json(['message' => 'Webhook processado com sucesso']);
            }

            return response()->json(['message' => 'Erro ao processar webhook'], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Asaas: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Erro interno'], 500);
        }
    }
}
