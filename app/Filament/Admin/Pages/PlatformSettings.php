<?php

namespace App\Filament\Admin\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;

class PlatformSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Configurações da Plataforma';
    protected static ?string $title = 'Configurações da Plataforma';
    protected static ?int $navigationSort = 100;
    protected static ?string $navigationGroup = 'Sistema';

    protected static string $view = 'filament.admin.pages.platform-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'tributaai_platform_token' => config('services.tributaai.platform_token'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tributa AI - Classificação Fiscal com IA')
                    ->description('Configure o token da plataforma para oferecer classificação automática de produtos para TODOS os restaurantes')
                    ->schema([
                        Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="space-y-4">
                                    <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700">
                                        <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">
                                            🤖 Token Compartilhado da Plataforma
                                        </h3>
                                        <div class="text-sm text-blue-700 dark:text-blue-300 space-y-2">
                                            <p><strong>Como funciona:</strong></p>
                                            <p>✅ Você compra 1 token no Tributa AI (~R$ 29-99/mês)</p>
                                            <p>✅ TODOS os restaurantes usam este token gratuitamente</p>
                                            <p>✅ Restaurantes veem "Classificação com IA disponível GRÁTIS!"</p>
                                            <p>✅ Custo fixo independente do número de restaurantes</p>
                                        </div>
                                    </div>

                                    <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700">
                                        <h3 class="text-sm font-semibold text-green-800 dark:text-green-200 mb-2">
                                            💰 ROI (Retorno sobre Investimento)
                                        </h3>
                                        <div class="text-sm text-green-700 dark:text-green-300 space-y-1">
                                            <p><strong>Custo:</strong> R$ 29-99/mês (1 token para TODOS)</p>
                                            <p><strong>10 restaurantes:</strong> R$ 2,90-9,90 por restaurante</p>
                                            <p><strong>50 restaurantes:</strong> R$ 0,58-1,98 por restaurante</p>
                                            <p><strong>100 restaurantes:</strong> R$ 0,29-0,99 por restaurante</p>
                                            <p class="pt-2 border-t border-green-200 dark:border-green-700 font-semibold">
                                                🚀 Diferencial competitivo: "IA de classificação fiscal GRÁTIS"
                                            </p>
                                        </div>
                                    </div>

                                    <div class="p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700">
                                        <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2">
                                            ⚠️ Importante
                                        </h3>
                                        <div class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                                            <p>• Obtenha seu token em: <a href="https://tributa.ai" target="_blank" class="underline">tributa.ai</a></p>
                                            <p>• Token é armazenado no arquivo .env do servidor</p>
                                            <p>• Restaurantes podem ter token próprio (planos Enterprise)</p>
                                            <p>• Cache de 30 dias economiza requisições da API</p>
                                        </div>
                                    </div>
                                </div>'
                            ))
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('tributaai_platform_token')
                            ->label('Token da Plataforma')
                            ->password()
                            ->revealable()
                            ->placeholder('seu-token-tributa-ai-aqui')
                            ->helperText('Configure o token que será compartilhado entre todos os restaurantes')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('status')
                            ->label('Status')
                            ->content(function ($get) {
                                $token = $get('tributaai_platform_token') ?? config('services.tributaai.platform_token');

                                if (empty($token)) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="font-medium">Token NÃO configurado - Classificação com IA indisponível</span>
                                        </div>'
                                    );
                                }

                                return new \Illuminate\Support\HtmlString(
                                    '<div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-medium">Token CONFIGURADO - Classificação com IA disponível para todos!</span>
                                    </div>'
                                );
                            })
                            ->columnSpanFull(),
                    ])->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            // Atualizar arquivo .env
            $envPath = base_path('.env');
            $envContent = File::get($envPath);

            $token = $data['tributaai_platform_token'] ?? '';

            // Atualizar ou adicionar TRIBUTAAI_PLATFORM_TOKEN
            if (preg_match('/^TRIBUTAAI_PLATFORM_TOKEN=.*/m', $envContent)) {
                $envContent = preg_replace(
                    '/^TRIBUTAAI_PLATFORM_TOKEN=.*/m',
                    'TRIBUTAAI_PLATFORM_TOKEN=' . $token,
                    $envContent
                );
            } else {
                $envContent .= "\nTRIBUTAAI_PLATFORM_TOKEN=" . $token;
            }

            File::put($envPath, $envContent);

            // Limpar cache de config
            \Artisan::call('config:clear');

            Notification::make()
                ->success()
                ->title('Configurações salvas com sucesso!')
                ->body('O token da plataforma foi atualizado. Todos os restaurantes já podem usar a classificação com IA.')
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erro ao salvar configurações')
                ->body($e->getMessage())
                ->send();
        }
    }
}
