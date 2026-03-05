<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\Payment;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated Job específico para Asaas - gateway descontinuado
 * Pagar.me usa webhooks automáticos e não precisa de polling
 * Manter apenas para compatibilidade com tenants antigos
 */
class CheckPendingPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    /**
     * Verifica todos os pagamentos pendentes em todos os tenants
     *
     * @deprecated Asaas descontinuado - Pagar.me usa webhooks
     */
    public function handle(): void
    {
        Log::info('⚠️ CheckPendingPayments desabilitado - Asaas foi descontinuado. Pagar.me usa webhooks automáticos.');
        return; // Job desabilitado

        /* CÓDIGO ORIGINAL COMENTADO - MANTER PARA REFERÊNCIA
        Log::info('🔍 Iniciando verificação de pagamentos pendentes');

        $tenants = Tenant::where('status', 'active')->get();
        $checkedCount = 0;
        $confirmedCount = 0;

        foreach ($tenants as $tenant) {
            try {
                tenancy()->initialize($tenant);

                // Buscar pagamentos pendentes criados há mais de 1 minuto (para não verificar imediatamente)
                $pendingPayments = Payment::where('status', 'pending')
                    ->where('gateway', 'asaas')
                    ->where('created_at', '<', now()->subMinute())
                    ->where('created_at', '>', now()->subHours(24)) // Apenas últimas 24h
                    ->get();

                foreach ($pendingPayments as $payment) {
                    $checkedCount++;

                    if ($this->checkPaymentStatus($payment, $tenant)) {
                        $confirmedCount++;
                    }
                }

                tenancy()->end();
            } catch (\Exception $e) {
                Log::error('❌ Erro ao verificar pagamentos do tenant: ' . $tenant->id, [
                    'error' => $e->getMessage()
                ]);
                tenancy()->end();
                continue;
            }
        }

        Log::info('✅ Verificação concluída', [
            'tenants' => $tenants->count(),
            'payments_checked' => $checkedCount,
            'payments_confirmed' => $confirmedCount,
        ]);
        */ // FIM DO CÓDIGO COMENTADO
    }

    /**
     * Verifica status de um pagamento específico no Asaas
     */
    private function checkPaymentStatus(Payment $payment, Tenant $tenant): bool
    {
        try {
            $asaasService = app(AsaasService::class);

            // Buscar status atual no Asaas
            $asaasPayment = $asaasService->getPaymentStatus($payment->transaction_id);

            if (!$asaasPayment) {
                Log::warning('⚠️ Não foi possível consultar pagamento no Asaas', [
                    'tenant' => $tenant->id,
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                ]);
                return false;
            }

            $asaasStatus = $asaasPayment['status'] ?? null;

            // Se foi confirmado no Asaas
            if (in_array($asaasStatus, ['CONFIRMED', 'RECEIVED'])) {
                Log::info('🎉 Pagamento confirmado via polling', [
                    'tenant' => $tenant->id,
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'asaas_status' => $asaasStatus,
                ]);

                // Atualizar pagamento
                $payment->update([
                    'status' => 'confirmed',
                    'paid_at' => now(),
                ]);

                // Confirmar pedido (processar cashback, etc)
                $order = $payment->order;
                if ($order && $order->payment_status !== 'paid') {
                    $orderService = app(OrderService::class);
                    $orderService->confirmPayment($order);

                    Log::info('✅ Pedido confirmado automaticamente', [
                        'tenant' => $tenant->id,
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                    ]);
                }

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao verificar status do pagamento', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
