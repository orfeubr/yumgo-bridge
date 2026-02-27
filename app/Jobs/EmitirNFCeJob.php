<?php

namespace App\Jobs;

use App\Models\FiscalNote;
use App\Models\Order;
use App\Services\SefazService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EmitirNFCeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Tentar 3 vezes em caso de falha
    public $timeout = 120; // Timeout de 2 minutos
    public $backoff = [30, 60, 120]; // Esperar 30s, 60s, 120s entre tentativas
    public $failOnTimeout = true;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $orderId,
        public string $tenantId
    ) {
        // Definir fila específica para NFC-e
        $this->onQueue('nfce');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $lockKey = "nfce:order:{$this->orderId}";

        // Lock para evitar emissão duplicada (5 minutos)
        $lock = Cache::lock($lockKey, 300);

        try {
            if (!$lock->get()) {
                Log::warning('⚠️ NFC-e já está sendo processada', [
                    'order_id' => $this->orderId,
                    'tenant_id' => $this->tenantId,
                ]);
                return;
            }

            // Inicializar contexto do tenant
            $tenant = \App\Models\Tenant::find($this->tenantId);
            if (!$tenant) {
                throw new \Exception('Tenant não encontrado');
            }

            tenancy()->initialize($tenant);

            // Buscar pedido
            $order = Order::find($this->orderId);
            if (!$order) {
                throw new \Exception('Pedido não encontrado');
            }

            // Verificar se já existe nota fiscal
            if ($order->fiscalNote) {
                Log::info('ℹ️ Nota fiscal já existe, ignorando', [
                    'order_id' => $this->orderId,
                    'fiscal_note_id' => $order->fiscalNote->id,
                ]);
                return;
            }

            // Verificar rate limiting (máximo 10 requisições por minuto para este tenant)
            $rateLimitKey = "nfce:ratelimit:{$this->tenantId}";
            $requests = Cache::get($rateLimitKey, 0);

            if ($requests >= 10) {
                Log::warning('⚠️ Rate limit excedido, tentando novamente em 1 minuto', [
                    'tenant_id' => $this->tenantId,
                    'requests' => $requests,
                ]);

                // Recolocar na fila com delay de 1 minuto
                $this->release(60);
                return;
            }

            // Incrementar contador de rate limit
            Cache::put($rateLimitKey, $requests + 1, 60);

            Log::info('🚀 Iniciando emissão NFC-e (Job)', [
                'order_id' => $this->orderId,
                'order_number' => $order->order_number,
                'tenant_id' => $this->tenantId,
                'attempt' => $this->attempts(),
            ]);

            // Criar registro da nota fiscal com status pending
            $fiscalNote = FiscalNote::create([
                'order_id' => $order->id,
                'note_number' => $tenant->nfce_numero,
                'serie' => $tenant->nfce_serie,
                'status' => 'pending',
                'total_value' => $order->total,
                'emission_date' => now(),
            ]);

            // Emitir nota via SEFAZ
            $sefazService = app(SefazService::class);
            $result = $sefazService->emitNFCe($order);

            // Atualizar registro com dados da SEFAZ
            $fiscalNote->update([
                'status' => 'authorized',
                'chave_acesso' => $result['chave_acesso'] ?? null,
                'protocolo' => $result['protocolo'] ?? null,
                'xml_url' => $result['xml'] ?? null,
                'authorization_date' => isset($result['data_autorizacao'])
                    ? now()->parse($result['data_autorizacao'])
                    : now(),
                'raw_response' => $result,
            ]);

            // Incrementar número da nota
            $tenant->increment('nfce_numero');

            Log::info('✅ NFC-e emitida com sucesso (Job)', [
                'order_id' => $this->orderId,
                'fiscal_note_id' => $fiscalNote->id,
                'chave_acesso' => $fiscalNote->chave_acesso,
                'protocolo' => $fiscalNote->protocolo,
                'attempt' => $this->attempts(),
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao emitir NFC-e (Job)', [
                'order_id' => $this->orderId,
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Atualizar nota com erro (se existir)
            if (isset($fiscalNote)) {
                $fiscalNote->update([
                    'status' => 'error',
                    'error_message' => $e->getMessage(),
                ]);
            }

            // Liberar lock antes de retentar
            $lock->release();

            // Lançar exceção para que o Laravel tente novamente
            throw $e;

        } finally {
            // Liberar lock
            optional($lock)->release();

            // Finalizar tenancy
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('💀 NFC-e falhou após todas as tentativas', [
            'order_id' => $this->orderId,
            'tenant_id' => $this->tenantId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Inicializar tenant para atualizar nota
        try {
            $tenant = \App\Models\Tenant::find($this->tenantId);
            if ($tenant) {
                tenancy()->initialize($tenant);

                $order = Order::find($this->orderId);
                if ($order && $order->fiscalNote) {
                    $order->fiscalNote->update([
                        'status' => 'error',
                        'error_message' => 'Falha após ' . $this->tries . ' tentativas: ' . $exception->getMessage(),
                    ]);
                }

                tenancy()->end();
            }
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status da nota após falha', [
                'error' => $e->getMessage(),
            ]);
        }

        // TODO: Notificar administrador do restaurante
    }
}
