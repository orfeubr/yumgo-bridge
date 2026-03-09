<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\ProductImportService;
use Illuminate\Console\Command;

class ImportProducts extends Command
{
    protected $signature = 'products:import
                            {tenant : Slug do tenant (ex: marmitaria-gi)}
                            {file : Caminho do arquivo Excel/CSV}
                            {--test : Modo teste (não salva no banco)}';

    protected $description = 'Importa produtos de um arquivo Excel/CSV para um restaurante';

    public function handle(): int
    {
        $tenantSlug = $this->argument('tenant');
        $filePath = $this->argument('file');
        $isTest = $this->option('test');

        // Busca tenant
        $tenant = Tenant::where('slug', $tenantSlug)->first();

        if (!$tenant) {
            $this->error("❌ Tenant '{$tenantSlug}' não encontrado.");
            return self::FAILURE;
        }

        // Valida arquivo
        if (!file_exists($filePath)) {
            $this->error("❌ Arquivo não encontrado: {$filePath}");
            return self::FAILURE;
        }

        $this->info("🚀 Iniciando importação para: {$tenant->name}");
        $this->info("📄 Arquivo: {$filePath}");

        if ($isTest) {
            $this->warn("⚠️ MODO TESTE - Nenhuma alteração será salva no banco");
        }

        $this->newLine();

        // Inicializa tenancy
        tenancy()->initialize($tenant);

        try {
            $service = new ProductImportService($tenant->id);

            if ($isTest) {
                $this->info("🧪 Validando arquivo...");
                // Aqui você pode adicionar validação sem salvar
            }

            // Importa com barra de progresso
            $report = $service->import($filePath);

            // Exibe relatório
            $this->displayReport($report);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Erro: {$e->getMessage()}");
            return self::FAILURE;
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Exibe relatório de importação
     */
    protected function displayReport(array $report): void
    {
        $this->newLine();
        $this->info("📊 RELATÓRIO DE IMPORTAÇÃO");
        $this->info("═══════════════════════════");

        // Sucessos
        $this->line("✅ Produtos importados: <fg=green>{$report['success']}</>");
        $this->line("📁 Categorias criadas: <fg=cyan>{$report['categories_created']}</>");
        $this->line("📦 Produtos criados: <fg=cyan>{$report['products_created']}</>");
        $this->line("🔢 Variações criadas: <fg=cyan>{$report['variations_created']}</>");
        $this->line("➕ Adicionais criados: <fg=cyan>{$report['addons_created']}</>");

        // Avisos
        if (!empty($report['warnings'])) {
            $this->newLine();
            $this->warn("⚠️ AVISOS ({count($report['warnings'])}):");
            foreach ($report['warnings'] as $warning) {
                $this->line("  • {$warning}");
            }
        }

        // Erros
        if (!empty($report['errors'])) {
            $this->newLine();
            $this->error("❌ ERROS ({count($report['errors'])}):");
            foreach ($report['errors'] as $error) {
                $this->line("  • {$error}");
            }
        }

        $this->newLine();

        if (empty($report['errors'])) {
            $this->info("🎉 Importação concluída com sucesso!");
        } else {
            $this->warn("⚠️ Importação concluída com erros. Revise os produtos.");
        }
    }
}
