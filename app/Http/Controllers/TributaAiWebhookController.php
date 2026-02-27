<?php

namespace App\Http\Controllers;

use App\Models\FiscalNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TributaAiWebhookController extends Controller
{
    /**
     * Handle Tributa AI webhook notifications
     *
     * Endpoint: POST /api/webhook/tributaai/{tenant_id}
     */
    public function handle(Request $request, string $tenantId)
    {
        try {
            Log::info('🔔 Webhook Tributa AI recebido', [
                'tenant_id' => $tenantId,
                'payload' => $request->all(),
            ]);

            // Validar payload
            $data = $request->validate([
                'event' => 'required|string',
                'nfce' => 'required|array',
                'nfce.id' => 'required|string',
                'nfce.status' => 'sometimes|string',
            ]);

            // Encontrar nota fiscal pelo ID do Tributa AI
            $fiscalNote = FiscalNote::where('tributaai_note_id', $data['nfce']['id'])->first();

            if (!$fiscalNote) {
                Log::warning('Nota fiscal não encontrada', [
                    'tributaai_note_id' => $data['nfce']['id'],
                ]);

                return response()->json([
                    'message' => 'Nota fiscal não encontrada',
                ], 404);
            }

            // Processar evento
            switch ($data['event']) {
                case 'nfce.authorized':
                case 'nfce.autorizada':
                    $this->handleAuthorized($fiscalNote, $data['nfce']);
                    break;

                case 'nfce.rejected':
                case 'nfce.rejeitada':
                    $this->handleRejected($fiscalNote, $data['nfce']);
                    break;

                case 'nfce.cancelled':
                case 'nfce.cancelada':
                    $this->handleCancelled($fiscalNote, $data['nfce']);
                    break;

                case 'nfce.error':
                case 'nfce.erro':
                    $this->handleError($fiscalNote, $data['nfce']);
                    break;

                default:
                    Log::info('Evento desconhecido ignorado', ['event' => $data['event']]);
            }

            return response()->json([
                'message' => 'Webhook processado com sucesso',
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao processar webhook Tributa AI', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erro ao processar webhook',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * NFC-e autorizada pela SEFAZ
     */
    private function handleAuthorized(FiscalNote $fiscalNote, array $data): void
    {
        $fiscalNote->update([
            'status' => 'authorized',
            'chave_acesso' => $data['chave_acesso'] ?? $fiscalNote->chave_acesso,
            'protocolo' => $data['protocolo'] ?? $fiscalNote->protocolo,
            'pdf_url' => $data['pdf_url'] ?? $fiscalNote->pdf_url,
            'xml_url' => $data['xml_url'] ?? $fiscalNote->xml_url,
            'authorization_date' => now(),
        ]);

        Log::info('✅ NFC-e autorizada', [
            'fiscal_note_id' => $fiscalNote->id,
            'chave_acesso' => $fiscalNote->chave_acesso,
        ]);
    }

    /**
     * NFC-e rejeitada pela SEFAZ
     */
    private function handleRejected(FiscalNote $fiscalNote, array $data): void
    {
        $fiscalNote->update([
            'status' => 'rejected',
            'error_message' => $data['mensagem'] ?? $data['message'] ?? 'Nota rejeitada pela SEFAZ',
        ]);

        Log::error('❌ NFC-e rejeitada', [
            'fiscal_note_id' => $fiscalNote->id,
            'error' => $fiscalNote->error_message,
        ]);

        // TODO: Notificar restaurante sobre rejeição
    }

    /**
     * NFC-e cancelada
     */
    private function handleCancelled(FiscalNote $fiscalNote, array $data): void
    {
        $fiscalNote->update([
            'status' => 'cancelled',
            'cancellation_date' => now(),
            'cancellation_reason' => $data['motivo'] ?? $fiscalNote->cancellation_reason,
        ]);

        Log::info('🗑️ NFC-e cancelada', [
            'fiscal_note_id' => $fiscalNote->id,
        ]);
    }

    /**
     * Erro ao processar NFC-e
     */
    private function handleError(FiscalNote $fiscalNote, array $data): void
    {
        $fiscalNote->update([
            'status' => 'error',
            'error_message' => $data['mensagem'] ?? $data['message'] ?? 'Erro ao processar nota',
        ]);

        Log::error('❌ Erro ao processar NFC-e', [
            'fiscal_note_id' => $fiscalNote->id,
            'error' => $fiscalNote->error_message,
        ]);

        // TODO: Notificar restaurante sobre erro
    }
}
