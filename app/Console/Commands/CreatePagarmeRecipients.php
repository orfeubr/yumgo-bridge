<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\PagarMeService;
use Illuminate\Console\Command;

class CreatePagarmeRecipients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pagarme:create-recipients
                            {--tenant= : ID específico do tenant para criar recebedor}
                            {--force : Recriar recebedor mesmo se já existir}
                            {--dry-run : Simular sem criar de fato}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criar recebedores Pagar.me para restaurantes que ainda não têm';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando criação de recebedores Pagar.me...');
        $this->newLine();

        $pagarmeService = new PagarMeService();

        // Se foi especificado um tenant
        if ($tenantId = $this->option('tenant')) {
            $tenant = Tenant::find($tenantId);

            if (!$tenant) {
                $this->error("❌ Tenant '{$tenantId}' não encontrado!");
                return 1;
            }

            $this->processTenant($tenant, $pagarmeService);
            return 0;
        }

        // Processar todos os tenants
        $query = Tenant::query();

        // Se não for force, pegar apenas os que não têm recipient_id
        if (!$this->option('force')) {
            $query->whereNull('pagarme_recipient_id')
                  ->orWhere('pagarme_recipient_id', '');
        }

        // Filtrar apenas os que têm dados bancários completos
        $query->whereNotNull('cpf_cnpj')
              ->whereNotNull('bank_code')
              ->whereNotNull('bank_agency')
              ->whereNotNull('bank_account')
              ->whereNotNull('bank_account_digit')
              ->whereNotNull('bank_account_type');

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->info('✅ Nenhum tenant precisa de recebedor!');
            $this->newLine();
            $this->line('Todos os tenants com dados bancários já têm recebedores criados.');
            return 0;
        }

        $this->info("📋 Encontrados {$tenants->count()} tenant(s) para processar");
        $this->newLine();

        $success = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($tenants as $tenant) {
            $result = $this->processTenant($tenant, $pagarmeService);

            if ($result === 'success') {
                $success++;
            } elseif ($result === 'error') {
                $errors++;
            } else {
                $skipped++;
            }

            $this->newLine();
        }

        // Resumo
        $this->info('📊 RESUMO:');
        $this->line("  ✅ Sucesso: {$success}");
        $this->line("  ❌ Erros: {$errors}");
        $this->line("  ⏭️  Pulados: {$skipped}");

        return $errors > 0 ? 1 : 0;
    }

    /**
     * Processa um tenant específico
     */
    private function processTenant(Tenant $tenant, PagarMeService $pagarmeService): string
    {
        $this->line("🔄 Processando: {$tenant->name} (ID: {$tenant->id})");

        // Verificar se já tem recipient_id
        if ($tenant->pagarme_recipient_id && !$this->option('force')) {
            $this->line("  ⏭️  Já possui recebedor: {$tenant->pagarme_recipient_id}");
            return 'skipped';
        }

        // Verificar se gateway é Pagar.me
        if ($tenant->payment_gateway && $tenant->payment_gateway !== 'pagarme') {
            $this->line("  ⏭️  Gateway ativo não é Pagar.me: {$tenant->payment_gateway}");
            return 'skipped';
        }

        // Verificar dados completos
        if (!$this->hasCompleteBankData($tenant)) {
            $this->line('  ❌ Dados bancários incompletos!');
            $this->showMissingFields($tenant);
            return 'error';
        }

        // Modo dry-run
        if ($this->option('dry-run')) {
            $this->line('  🧪 [DRY-RUN] Recebedor seria criado com:');
            $this->showRecipientData($tenant);
            return 'success';
        }

        // Criar recebedor
        try {
            $type = in_array($tenant->company_type, ['individual', 'mei']) ? 'individual' : 'company';

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

            $result = $pagarmeService->createRecipient($recipientData);

            if ($result && isset($result['id'])) {
                // Salvar recipient_id
                $tenant->update([
                    'pagarme_recipient_id' => $result['id'],
                ]);

                $this->line("  ✅ Recebedor criado: {$result['id']}");
                $this->line("  📊 Status: {$result['status']}");

                return 'success';
            } else {
                $this->line('  ❌ Erro ao criar recebedor: resposta inválida');
                return 'error';
            }
        } catch (\Exception $e) {
            $this->line("  ❌ Erro: {$e->getMessage()}");
            return 'error';
        }
    }

    /**
     * Verifica se tem dados bancários completos
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
     * Mostra campos faltando
     */
    private function showMissingFields(Tenant $tenant): void
    {
        $missing = [];

        if (empty($tenant->cpf_cnpj)) $missing[] = 'CPF/CNPJ';
        if (empty($tenant->bank_code)) $missing[] = 'Código do Banco';
        if (empty($tenant->bank_agency)) $missing[] = 'Agência';
        if (empty($tenant->bank_account)) $missing[] = 'Conta';
        if (empty($tenant->bank_account_digit)) $missing[] = 'Dígito da Conta';
        if (empty($tenant->bank_account_type)) $missing[] = 'Tipo de Conta';

        $this->line('     Faltando: ' . implode(', ', $missing));
    }

    /**
     * Mostra dados do recebedor que seria criado
     */
    private function showRecipientData(Tenant $tenant): void
    {
        $type = in_array($tenant->company_type, ['individual', 'mei']) ? 'individual' : 'company';

        $this->line('     Nome: ' . ($tenant->company_name ?: $tenant->name));
        $this->line('     Email: ' . $tenant->email);
        $this->line('     Documento: ' . $this->cleanDocument($tenant->cpf_cnpj));
        $this->line('     Tipo: ' . $type);
        $this->line('     Banco: ' . $tenant->bank_code);
        $this->line('     Agência: ' . $tenant->bank_agency . '-' . ($tenant->bank_branch_digit ?: '0'));
        $this->line('     Conta: ' . $tenant->bank_account . '-' . $tenant->bank_account_digit);
        $this->line('     Tipo Conta: ' . $tenant->bank_account_type);
    }

    /**
     * Remove formatação de documento
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

        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($cleaned) === 11 || strlen($cleaned) === 10) {
            return '+55' . $cleaned;
        }

        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        return '+55' . $cleaned;
    }
}
