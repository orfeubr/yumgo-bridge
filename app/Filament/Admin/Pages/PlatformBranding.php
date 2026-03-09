<?php

namespace App\Filament\Admin\Pages;

use App\Models\PlatformSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;

class PlatformBranding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationLabel = 'Identidade Visual';

    protected static ?string $title = 'Identidade Visual da Plataforma';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.admin.pages.platform-branding';

    public ?array $data = [];

    public function mount(): void
    {
        // Carrega configurações do banco de dados
        $this->form->fill([
            'platform_name' => PlatformSetting::get('platform_name', 'YumGo'),
            'primary_color' => PlatformSetting::get('primary_color', '#EA1D2C'),
            'logo' => PlatformSetting::get('logo'),
            'favicon' => PlatformSetting::get('favicon'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações da Plataforma')
                    ->description('Configure o nome e slogan da sua plataforma')
                    ->schema([
                        TextInput::make('platform_name')
                            ->label('Nome da Plataforma')
                            ->placeholder('YumGo')
                            ->maxLength(255)
                            ->helperText('Este nome aparecerá no header e no título das páginas'),
                    ]),

                Section::make('Logotipo')
                    ->description('Faça upload da logo da sua plataforma')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo Principal')
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '4:1',
                                '16:9',
                            ])
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('400')
                            ->imageResizeTargetHeight('100')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                            ->downloadable()
                            ->openable()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    \Log::info('📸 Logo uploaded', ['path' => $state]);
                                }
                            })
                            ->helperText('Formatos: PNG, JPG, WEBP. Tamanho máximo: 2MB. Ideal: 400x100px'),

                        FileUpload::make('favicon')
                            ->label('Favicon')
                            ->disk('public')
                            ->directory('branding')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('64')
                            ->imageResizeTargetHeight('64')
                            ->maxSize(512)
                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon'])
                            ->helperText('Ícone da aba do navegador. Ideal: 64x64px'),
                    ]),

                Section::make('Cores')
                    ->description('Personalize as cores da plataforma')
                    ->schema([
                        ColorPicker::make('primary_color')
                            ->label('Cor Primária')
                            ->helperText('Cor principal usada em botões, links e destaques')
                            ->default('#EA1D2C'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            \Log::info('💾 [PlatformBranding] Save iniciado', ['logo_value' => $data['logo'] ?? 'null']);
            $savedItems = [];

            // Processar logo
            if (isset($data['logo'])) {
                $uploadedFile = $data['logo'];

                // Se for null ou vazio, deletar logo
                if (empty($uploadedFile)) {
                    \Log::info('🗑️ [PlatformBranding] Deletando logo');

                    // Deletar do banco
                    PlatformSetting::where('key', 'logo')->delete();

                    // Deletar de public
                    $publicLogo = public_path('logo.png');
                    if (file_exists($publicLogo)) {
                        unlink($publicLogo);
                        \Log::info('✅ Logo deletado de public');
                    }

                    $savedItems[] = 'Logo (removido)';
                }
                // Se for TemporaryUploadedFile (novo upload)
                elseif (is_object($uploadedFile) && method_exists($uploadedFile, 'store')) {
                    \Log::info('📸 [PlatformBranding] Novo upload detectado');

                    // Deletar logo antigo do storage
                    $oldLogo = PlatformSetting::get('logo');
                    if ($oldLogo) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
                    }

                    // Salvar novo arquivo
                    $storedPath = $uploadedFile->store('branding', 'public');
                    \Log::info('💾 [PlatformBranding] Arquivo salvo no storage', ['path' => $storedPath]);

                    // Salvar path no banco
                    PlatformSetting::set('logo', $storedPath);
                    \Log::info('💾 [PlatformBranding] Path salvo no banco', [
                        'path' => $storedPath,
                        'verificacao' => PlatformSetting::get('logo')
                    ]);

                    // Copiar para public/logo.png
                    $disk = \Illuminate\Support\Facades\Storage::disk('public');
                    if ($disk->exists($storedPath)) {
                        $content = $disk->get($storedPath);
                        $destination = public_path('logo.png');
                        File::put($destination, $content);
                        chmod($destination, 0644);
                        $savedItems[] = 'Logo';
                        \Log::info('✅ [PlatformBranding] Logo copiado para public', [
                            'destination' => $destination,
                            'size' => strlen($content)
                        ]);
                    } else {
                        \Log::error('❌ [PlatformBranding] Arquivo não encontrado no storage após salvar!', [
                            'path' => $storedPath
                        ]);
                    }
                }
                // Se for string (logo já existente, não modificado)
                elseif (is_string($uploadedFile)) {
                    \Log::info('📁 [PlatformBranding] Logo existente mantido', ['path' => $uploadedFile]);
                    // Garantir que está em public
                    $disk = \Illuminate\Support\Facades\Storage::disk('public');
                    if ($disk->exists($uploadedFile)) {
                        $content = $disk->get($uploadedFile);
                        $destination = public_path('logo.png');
                        if (!file_exists($destination) || md5_file($destination) !== md5($content)) {
                            File::put($destination, $content);
                            chmod($destination, 0644);
                            $savedItems[] = 'Logo (sincronizado)';
                        }
                    }
                }
            }

            // Salvar favicon no banco E copiar para public
            if (!empty($data['favicon'])) {
                $faviconPath = $data['favicon'];
                PlatformSetting::set('favicon', $faviconPath);

                // Copiar do storage para public usando Storage
                $disk = \Illuminate\Support\Facades\Storage::disk('public');
                if ($disk->exists($faviconPath)) {
                    $content = $disk->get($faviconPath);
                    $destination = public_path('favicon.ico');
                    File::put($destination, $content);
                    chmod($destination, 0644);
                    $savedItems[] = 'Favicon';
                    \Log::info('✅ Favicon salvo', ['path' => $destination]);
                } else {
                    \Log::warning('⚠️ Favicon não encontrado', ['path' => $faviconPath]);
                }
            }

            // Salvar nome da plataforma
            if (!empty($data['platform_name'])) {
                PlatformSetting::set('platform_name', $data['platform_name']);
                $savedItems[] = 'Nome';
            }

            // Salvar cor primária
            if (!empty($data['primary_color'])) {
                PlatformSetting::set('primary_color', $data['primary_color']);
                $savedItems[] = 'Cor';
            }

            $message = empty($savedItems)
                ? 'Nenhuma alteração foi feita.'
                : 'Salvos: ' . implode(', ', $savedItems);

            Notification::make()
                ->title('✅ Identidade visual atualizada!')
                ->success()
                ->body($message)
                ->duration(5000)
                ->send();

        } catch (\Exception $e) {
            \Log::error('❌ [PlatformBranding] Erro', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title('❌ Erro ao salvar')
                ->danger()
                ->body($e->getMessage())
                ->persistent()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar Alterações')
                ->action('save')
                ->color('primary')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->modalHeading('Salvar alterações?')
                ->modalDescription('As alterações na identidade visual serão aplicadas.')
                ->modalSubmitActionLabel('Sim, salvar'),
        ];
    }
}
