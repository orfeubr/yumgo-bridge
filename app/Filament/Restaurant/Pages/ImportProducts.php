<?php

namespace App\Filament\Restaurant\Pages;

use App\Services\ProductImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ImportProducts extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $slug = 'importar-produtos'; // URL em português

    protected static ?string $navigationLabel = 'Importar Produtos';

    protected static ?string $title = 'Importar Produtos';

    protected static ?string $navigationGroup = '🍕 Cardápio';

    protected static ?int $navigationSort = 22;

    protected static string $view = 'filament.restaurant.pages.import-products';

    public ?array $data = [];

    /**
     * Verifica se deve exibir no menu (só Pro/Enterprise)
     */
    public static function shouldRegisterNavigation(): bool
    {
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            return false;
        }

        $subscription = $tenant->activeSubscription();
        $planName = $subscription?->plan->name ?? '';

        // Importação CSV disponível apenas para Pro e Enterprise
        return in_array($planName, ['Pro', 'Enterprise']);
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Importar Produtos em Lote')
                    ->description('Faça upload de uma planilha Excel (.xlsx) para importar múltiplos produtos de uma só vez.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Arquivo Excel')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->maxSize(10240) // 10MB
                            ->required()
                            ->helperText('Formatos aceitos: .xlsx, .xls, .csv (máximo 10MB)')
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private'),
                    ])
                    ->collapsible(),

                Section::make('Instruções')
                    ->description('Como usar a importação de produtos')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('instructions')
                            ->label('')
                            ->content(fn () => view('filament.restaurant.components.import-instructions')->render()),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Download Template')
                    ->description('Baixe o modelo de planilha para preencher seus produtos')
                    ->schema([
                        \Filament\Forms\Components\Actions::make([
                            \Filament\Forms\Components\Actions\Action::make('download_template')
                                ->label('Baixar Modelo Excel')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->color('success')
                                ->url(route('download.template.products'))
                                ->openUrlInNewTab(),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Processa a importação
     */
    public function import(): void
    {
        $data = $this->form->getState();

        if (empty($data['file'])) {
            Notification::make()
                ->title('Erro')
                ->body('Selecione um arquivo para importar.')
                ->danger()
                ->send();
            return;
        }

        try {
            // Caminho do arquivo
            $filePath = Storage::disk('local')->path($data['file']);

            // Tenant atual
            $tenant = tenant();

            // Importa
            $service = new ProductImportService($tenant->id);
            $report = $service->import($filePath);

            // Remove arquivo temporário
            Storage::disk('local')->delete($data['file']);

            // Notificação de sucesso
            if (empty($report['errors'])) {
                Notification::make()
                    ->title('Importação Concluída!')
                    ->body("✅ {$report['success']} produtos importados com sucesso.")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Importação Concluída com Avisos')
                    ->body("⚠️ {$report['success']} produtos importados, mas houve " . count($report['errors']) . " erros.")
                    ->warning()
                    ->send();
            }

            // Exibe relatório detalhado
            $this->displayReportModal($report);

            // Limpa form
            $this->form->fill();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao Importar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Exibe modal com relatório detalhado
     */
    protected function displayReportModal(array $report): void
    {
        $message = "📊 **Relatório de Importação**\n\n";
        $message .= "✅ Produtos: {$report['products_created']}\n";
        $message .= "📁 Categorias: {$report['categories_created']}\n";
        $message .= "🔢 Variações: {$report['variations_created']}\n";
        $message .= "➕ Adicionais: {$report['addons_created']}\n";

        if (!empty($report['warnings'])) {
            $message .= "\n⚠️ **Avisos:**\n";
            foreach (array_slice($report['warnings'], 0, 5) as $warning) {
                $message .= "• {$warning}\n";
            }
            if (count($report['warnings']) > 5) {
                $message .= "• ... e mais " . (count($report['warnings']) - 5) . " avisos\n";
            }
        }

        if (!empty($report['errors'])) {
            $message .= "\n❌ **Erros:**\n";
            foreach (array_slice($report['errors'], 0, 5) as $error) {
                $message .= "• {$error}\n";
            }
            if (count($report['errors']) > 5) {
                $message .= "• ... e mais " . (count($report['errors']) - 5) . " erros\n";
            }
        }

        Notification::make()
            ->title('Relatório Detalhado')
            ->body($message)
            ->info()
            ->persistent()
            ->send();
    }

    /**
     * Header actions da página
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Importar Produtos')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Confirmar Importação')
                ->modalDescription('Tem certeza que deseja importar os produtos do arquivo selecionado?')
                ->modalSubmitActionLabel('Sim, Importar')
                ->action('import'),
        ];
    }
}
