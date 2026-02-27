<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\PagarMeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PagarMeWebhookController extends Controller
{
    /**
     * Webhook GLOBAL do Pagar.me para eventos de pagamento
     * Identifica o tenant e processa o webhook
     *
     * Eventos processados:
     * - order.paid - Pedido pago com sucesso
     * - charge.paid - Cobrança paga (similar a order.paid)
     * - order.payment_failed - Pagamento falhou
     * - charge.payment_failed - Cobrança falhou
     */
    public function handle(Request $request)
    {
        // LOG SEGURO (sem dados sensíveis - LGPD compliant)
        Log::info('🔔 Webhook Pagar.me GLOBAL recebido', [
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'event' => $request->input('type'),
        ]);

        $data = $request->all();
        $event = $data['type'] ?? null;

        Log::info('Webhook Pagar.me - Processando', [
            'event' => $event,
            'order_id' => $data['data']['id'] ?? null,
        ]);

        try {
            // 🔐 VALIDAÇÃO DE ASSINATURA (SEGURANÇA)
            if (!$this->validateSignature($request)) {
                Log::error('🚨 WEBHOOK: Assinatura inválida');
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            // Processar webhook via PagarMeService
            $pagarmeService = app(PagarMeService::class);
            $success = $pagarmeService->handleWebhook($data);

            if ($success) {
                Log::info('✅ Webhook Pagar.me processado com sucesso', [
                    'event' => $event,
                ]);

                return response()->json([
                    'message' => 'Webhook processed successfully',
                ], 200);
            } else {
                Log::warning('⚠️ Webhook Pagar.me não processado', [
                    'event' => $event,
                ]);

                return response()->json([
                    'message' => 'Webhook received but not processed',
                ], 200); // Retorna 200 para não reenviar
            }
        } catch (\Exception $e) {
            Log::error('❌ Erro ao processar webhook Pagar.me', [
                'event' => $event,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Retorna 200 para evitar reenvio infinito
            return response()->json([
                'message' => 'Error processing webhook',
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Valida assinatura do webhook Pagar.me
     *
     * O Pagar.me envia um header X-Hub-Signature com a assinatura
     * Formato: sha256=<hash>
     */
    private function validateSignature(Request $request): bool
    {
        $signature = $request->header('X-Hub-Signature');

        if (!$signature) {
            Log::warning('Webhook sem assinatura - permitindo em ambiente de desenvolvimento');

            // Em desenvolvimento, permite webhook sem assinatura
            if (app()->environment(['local', 'development'])) {
                return true;
            }

            return false;
        }

        $webhookToken = config('services.pagarme.webhook_token');

        if (!$webhookToken) {
            Log::error('🚨 WEBHOOK: Token não configurado no .env (PAGARME_WEBHOOK_TOKEN)');
            return false;
        }

        // Remove prefixo "sha256="
        $signature = str_replace('sha256=', '', $signature);

        // Calcula hash esperado
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $webhookToken);

        // Compara assinaturas (timing attack safe)
        $isValid = hash_equals($expectedSignature, $signature);

        if (!$isValid) {
            Log::error('🚨 WEBHOOK: Assinatura não confere', [
                'received' => substr($signature, 0, 10) . '...',
                'expected' => substr($expectedSignature, 0, 10) . '...',
            ]);
        }

        return $isValid;
    }

    /**
     * Webhook específico para PIX
     * Endpoint: /webhooks/pagarme/pix
     */
    public function pix(Request $request)
    {
        Log::info('🔔 Webhook Pagar.me PIX recebido', [
            'event' => $request->input('type'),
        ]);

        return $this->handle($request);
    }

    /**
     * Webhook específico para Cartão
     * Endpoint: /webhooks/pagarme/card
     */
    public function card(Request $request)
    {
        Log::info('🔔 Webhook Pagar.me CARD recebido', [
            'event' => $request->input('type'),
        ]);

        return $this->handle($request);
    }
}
