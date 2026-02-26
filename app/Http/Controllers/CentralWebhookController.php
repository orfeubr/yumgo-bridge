<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CentralWebhookController extends Controller
{
    /**
     * Webhook GLOBAL do Asaas para eventos de pagamento
     * Identifica o tenant e processa o webhook
     */
    public function asaas(Request $request)
    {
        // LOG COMPLETO PARA DEBUG
        Log::info('🔔 Webhook Asaas GLOBAL recebido', [
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        $data = $request->all();
        $event = $data['event'] ?? null;

        Log::info('Webhook Asaas GLOBAL - Processando', [
            'event' => $event,
            'payment_id' => $data['payment']['id'] ?? null,
        ]);

        try {
            // Validar token (se configurado)
            // Asaas envia token no corpo da requisição como 'access_token' ou no header
            $webhookToken = config('services.asaas.webhook_token');
            if ($webhookToken) {
                $receivedToken = $request->header('asaas-access-token')
                    ?? $request->input('access_token')
                    ?? null;

                // Se token configurado mas não recebido, apenas loga warning (não bloqueia)
                if (!$receivedToken) {
                    Log::warning('Webhook Asaas - Token não enviado pelo Asaas (ignorando validação)');
                } elseif ($receivedToken !== $webhookToken) {
                    Log::warning('Webhook Asaas - Token inválido', [
                        'expected' => substr($webhookToken, 0, 10) . '...',
                        'received' => substr($receivedToken, 0, 10) . '...',
                    ]);
                    return response()->json(['message' => 'Unauthorized'], 401);
                }
            }

            // Identificar tenant pelo payment
            $paymentId = $data['payment']['id'] ?? null;
            $externalReference = $data['payment']['externalReference'] ?? null;

            if (!$paymentId) {
                Log::error('Webhook: Payment ID ausente', $data);
                return response()->json(['error' => 'Payment ID ausente'], 400);
            }

            // Buscar em qual tenant está esse pagamento
            $tenant = $this->findTenantByPayment($paymentId, $externalReference);

            if (!$tenant) {
                Log::error('Webhook: Tenant não encontrado para payment', [
                    'payment_id' => $paymentId,
                ]);
                return response()->json(['error' => 'Tenant não encontrado'], 404);
            }

            // Inicializar tenancy
            tenancy()->initialize($tenant);

            // Processar webhook diretamente
            $order = \App\Models\Order::whereHas('payments', function($query) use ($paymentId) {
                $query->where('transaction_id', $paymentId);
            })->first();

            if (!$order) {
                Log::error('Webhook: Order não encontrado para payment', [
                    'payment_id' => $paymentId,
                    'tenant' => $tenant->id,
                ]);
                return response()->json(['error' => 'Order não encontrado'], 404);
            }

            // Atualizar payment
            $payment = $order->payments()->where('transaction_id', $paymentId)->first();

            if ($payment) {
                switch ($event) {
                    case 'PAYMENT_CONFIRMED':
                    case 'PAYMENT_RECEIVED':
                        $payment->update([
                            'status' => 'confirmed',
                            'paid_at' => now(),
                        ]);

                        // Atualizar order
                        $order->update([
                            'payment_status' => 'paid',
                            'status' => 'confirmed',
                        ]);

                        Log::info('✅ Webhook: Pagamento confirmado e order atualizado', [
                            'tenant' => $tenant->id,
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'payment_id' => $paymentId,
                            'old_status' => $order->getOriginal('status'),
                            'new_status' => $order->status,
                        ]);
                        break;

                    case 'PAYMENT_OVERDUE':
                    case 'PAYMENT_DELETED':
                        $payment->update(['status' => 'failed']);
                        Log::info('Webhook: Pagamento falhou', [
                            'tenant' => $tenant->id,
                            'order_id' => $order->id,
                            'event' => $event,
                        ]);
                        break;
                }

                return response()->json(['message' => 'Webhook processado com sucesso']);
            }

            return response()->json(['message' => 'Payment não encontrado'], 404);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Asaas GLOBAL: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data ?? null,
            ]);

            return response()->json(['message' => 'Erro interno'], 500);
        }
    }

    /**
     * Encontra o tenant pelo ID do pagamento ou externalReference
     */
    protected function findTenantByPayment(string $paymentId, ?string $externalReference = null): ?Tenant
    {
        // Tentar extrair tenant do externalReference (formato: "tenant-slug:order-id")
        if ($externalReference && str_contains($externalReference, ':')) {
            [$tenantSlug, $orderId] = explode(':', $externalReference, 2);

            $tenant = Tenant::where('id', $tenantSlug)
                ->orWhere('id', str_replace('-', '', $tenantSlug)) // sem hífens
                ->first();

            if ($tenant) {
                Log::info('Tenant encontrado via externalReference', [
                    'tenant' => $tenant->id,
                    'externalReference' => $externalReference,
                ]);
                return $tenant;
            }
        }

        // Fallback: percorrer todos os tenants ativos e buscar o pagamento
        $tenants = Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            try {
                tenancy()->initialize($tenant);

                $payment = \App\Models\Payment::where('transaction_id', $paymentId)->first();

                if ($payment) {
                    Log::info('Tenant encontrado via payment_id no banco', [
                        'tenant' => $tenant->id,
                        'payment_id' => $paymentId,
                    ]);
                    return $tenant;
                }
            } catch (\Exception $e) {
                Log::warning("Erro ao buscar pagamento no tenant {$tenant->id}: " . $e->getMessage());
                continue;
            } finally {
                tenancy()->end();
            }
        }

        return null;
    }

    /**
     * Webhook do Asaas para eventos de conta (aprovação/rejeição)
     */
    public function asaasAccountWebhook(Request $request)
    {
        $data = $request->all();
        $event = $data['event'] ?? null;

        Log::info('Webhook Asaas Account recebido', [
            'event' => $event,
            'data' => $data,
        ]);

        switch ($event) {
            case 'ACCOUNT_STATUS_UPDATED':
                return $this->handleAccountStatusUpdate($data);

            default:
                Log::warning('Evento de conta não tratado', ['event' => $event]);
                return response()->json(['message' => 'Evento não tratado'], 200);
        }
    }

    /**
     * Trata atualização de status de conta (aprovação/rejeição)
     */
    protected function handleAccountStatusUpdate(array $data): \Illuminate\Http\JsonResponse
    {
        $accountId = $data['account']['id'] ?? null;
        $status = $data['account']['status'] ?? null;

        if (!$accountId) {
            Log::error('Webhook: Account ID ausente', $data);
            return response()->json(['error' => 'Account ID ausente'], 400);
        }

        // Buscar tenant pelo account_id
        $tenant = Tenant::where('asaas_account_id', $accountId)->first();

        if (!$tenant) {
            Log::error('Webhook: Tenant não encontrado', [
                'account_id' => $accountId,
            ]);
            return response()->json(['error' => 'Tenant não encontrado'], 404);
        }

        // Mapear status do Asaas para nosso sistema
        $statusMap = [
            'ACTIVE' => 'APPROVED',
            'REJECTED' => 'REJECTED',
            'AWAITING_APPROVAL' => 'PENDING_APPROVAL',
        ];

        $newStatus = $statusMap[$status] ?? $status;

        // Atualizar status do tenant
        $tenant->update([
            'asaas_status' => $newStatus,
        ]);

        Log::info('Status da conta atualizado via webhook', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'old_status' => $tenant->asaas_status,
            'new_status' => $newStatus,
            'asaas_status' => $status,
        ]);

        // Enviar notificação para o tenant
        if ($newStatus === 'APPROVED') {
            $this->notifyAccountApproved($tenant);
        } elseif ($newStatus === 'REJECTED') {
            $this->notifyAccountRejected($tenant);
        }

        return response()->json(['message' => 'Webhook processado com sucesso'], 200);
    }

    /**
     * Notifica o tenant que a conta foi aprovada
     */
    protected function notifyAccountApproved(Tenant $tenant): void
    {
        // TODO: Enviar email ou notificação no painel
        Log::info('Conta aprovada', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
        ]);

        // Aqui você pode adicionar:
        // - Envio de email
        // - Notificação no banco de dados
        // - SMS, etc
    }

    /**
     * Notifica o tenant que a conta foi rejeitada
     */
    protected function notifyAccountRejected(Tenant $tenant): void
    {
        // TODO: Enviar email ou notificação no painel
        Log::info('Conta rejeitada', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
        ]);

        // Aqui você pode adicionar:
        // - Envio de email com motivo da rejeição
        // - Notificação no banco de dados
        // - SMS, etc
    }
}
