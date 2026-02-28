<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Services\PagarMeService;
use Illuminate\Support\Facades\Log;

class TenantRecipientObserver
{
    /**
     * Handle the Tenant "created" event.
     * Cria recebedor no Pagar.me quando um tenant é criado com dados bancários
     */
    public function created(Tenant $tenant): void
    {
        $this->createRecipientIfNeeded($tenant);
    }

    /**
     * Handle the Tenant "updated" event.
     * Cria recebedor no Pagar.me quando dados bancários são adicionados
     */
    public function updated(Tenant $tenant): void
    {
        // Se o tenant não tem recipient_id mas agora tem dados bancários completos
        if (!$tenant->pagarme_recipient_id && $this->hasCompleteBankData($tenant)) {
            $this->createRecipientIfNeeded($tenant);
        }
    }

    /**
     * Cria recebedor no Pagar.me se necessário
     */
    private function createRecipientIfNeeded(Tenant $tenant): void
    {
        // Só tenta criar se:
        // 1. Não tem recipient_id ainda
        // 2. Tem dados bancários completos
        // 3. Gateway ativo é Pagar.me
        if ($tenant->pagarme_recipient_id) {
            return;
        }

        if (!$this->hasCompleteBankData($tenant)) {
            Log::info('Tenant sem dados bancários completos, pulando criação de recebedor', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
            ]);
            return;
        }

        // Só cria se o gateway ativo for Pagar.me
        if ($tenant->payment_gateway !== 'pagarme') {
            Log::info('Gateway não é Pagar.me, pulando criação de recebedor', [
                'tenant_id' => $tenant->id,
                'gateway' => $tenant->payment_gateway,
            ]);
            return;
        }

        try {
            Log::info('Criando recebedor Pagar.me para tenant', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
            ]);

            $pagarmeService = new PagarMeService();

            // Determinar tipo (individual ou company)
            $type = 'company'; // Padrão
            if (in_array($tenant->company_type, ['individual', 'mei'])) {
                $type = 'individual';
            }

            // Preparar dados do recebedor
            $recipientData = [
                'name' => $tenant->company_name ?: $tenant->name,
                'email' => $tenant->email,
                'document' => $this->cleanDocument($tenant->cpf_cnpj),
                'type' => $type,
                'phone' => $this->cleanPhone($tenant->phone ?: $tenant->mobile_phone),
                'bank_account' => [
                    'holder_name' => $tenant->company_name ?: $tenant->name,
                    'holder_type' => $type,
                    'holder_document' => $this->cleanDocument($tenant->cpf_cnpj),
                    'bank' => $tenant->bank_code,
                    'branch_number' => $tenant->bank_agency,
                    'branch_check_digit' => $tenant->bank_branch_digit ?: '0',
                    'account_number' => $tenant->bank_account,
                    'account_check_digit' => $tenant->bank_account_digit,
                    'type' => $tenant->bank_account_type === 'savings' ? 'savings' : 'checking',
                ],
            ];

            // Criar recebedor
            $result = $pagarmeService->createRecipient($recipientData);

            if ($result && isset($result['id'])) {
                // Salvar recipient_id no banco SEM disparar eventos novamente
                $tenant->updateQuietly([
                    'pagarme_recipient_id' => $result['id'],
                ]);

                Log::info('✅ Recebedor Pagar.me criado com sucesso', [
                    'tenant_id' => $tenant->id,
                    'recipient_id' => $result['id'],
                    'status' => $result['status'] ?? 'unknown',
                ]);

                // Notificar sucesso (opcional - pode usar Notifications)
                // Notification::send($tenant, new RecipientCreatedNotification($result));
            } else {
                Log::error('❌ Erro ao criar recebedor Pagar.me - resposta inválida', [
                    'tenant_id' => $tenant->id,
                    'result' => $result,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('❌ Exceção ao criar recebedor Pagar.me', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Verifica se o tenant tem todos os dados bancários necessários
     */
    private function hasCompleteBankData(Tenant $tenant): bool
    {
        return !empty($tenant->cpf_cnpj)
            && !empty($tenant->bank_code)
            && !empty($tenant->bank_agency)
            && !empty($tenant->bank_account)
            && !empty($tenant->bank_account_digit)
            && !empty($tenant->bank_account_type);
    }

    /**
     * Remove formatação de documento (CPF/CNPJ)
     */
    private function cleanDocument(?string $document): string
    {
        if (!$document) {
            return '';
        }

        return preg_replace('/[^0-9]/', '', $document);
    }

    /**
     * Remove formatação de telefone
     */
    private function cleanPhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Remove tudo exceto números
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Garante formato +55XXXXXXXXXXX
        if (strlen($cleaned) === 11) {
            return '+55' . $cleaned;
        }

        if (strlen($cleaned) === 10) {
            return '+55' . $cleaned;
        }

        // Se já tem código do país, retorna como está
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        return '+55' . $cleaned;
    }
}
