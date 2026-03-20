<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\CashMovement;
use App\Models\CashRegister;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Caixa extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = '💰 Caixa';
    protected static ?string $title = 'Controle de Caixa';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.restaurant.pages.caixa';

    // Estado do caixa
    public ?CashRegister $currentCashRegister = null;
    public bool $isOpen = false;

    public function mount(): void
    {
        $this->loadCurrentCashRegister();
    }

    protected function loadCurrentCashRegister(): void
    {
        $this->currentCashRegister = CashRegister::currentOpen(Auth::id());
        $this->isOpen = $this->currentCashRegister !== null;

        if ($this->isOpen) {
            // Recalcular totais em tempo real
            $this->currentCashRegister->calculateTotals();
        }
    }

    // ============================================
    // AÇÕES DO CABEÇALHO
    // ============================================

    protected function getHeaderActions(): array
    {
        return [
            Action::make('abrir_caixa')
                ->label('🔓 Abrir Caixa')
                ->color('success')
                ->icon('heroicon-o-lock-open')
                ->visible(fn () => !$this->isOpen)
                ->form([
                    TextInput::make('opening_balance')
                        ->label('Fundo de Troco (R$)')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->minValue(0)
                        ->suffix('R$')
                        ->helperText('Valor inicial em dinheiro no caixa'),

                    Textarea::make('opening_notes')
                        ->label('Observações (Opcional)')
                        ->rows(3)
                        ->placeholder('Ex: Notas de abertura, pendências do turno anterior...'),
                ])
                ->action(function (array $data) {
                    try {
                        $cashRegister = CashRegister::openNew(
                            userId: Auth::id(),
                            openingBalance: $data['opening_balance'],
                            notes: $data['opening_notes'] ?? null
                        );

                        $this->loadCurrentCashRegister();

                        Notification::make()
                            ->success()
                            ->title('Caixa Aberto!')
                            ->body("Fundo de troco: R$ " . number_format($data['opening_balance'], 2, ',', '.'))
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Erro ao abrir caixa')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Action::make('fazer_sangria')
                ->label('💸 Sangria')
                ->color('warning')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn () => $this->isOpen)
                ->form([
                    TextInput::make('amount')
                        ->label('Valor da Sangria (R$)')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->suffix('R$')
                        ->helperText('Valor a ser retirado do caixa'),

                    Select::make('reason')
                        ->label('Motivo')
                        ->options([
                            'Depósito bancário' => 'Depósito bancário',
                            'Pagamento fornecedor' => 'Pagamento fornecedor',
                            'Despesa operacional' => 'Despesa operacional',
                            'Troco para outros caixas' => 'Troco para outros caixas',
                            'Outro' => 'Outro',
                        ])
                        ->required(),

                    Textarea::make('notes')
                        ->label('Observações (Opcional)')
                        ->rows(3),

                    FileUpload::make('receipt')
                        ->label('Comprovante (Opcional)')
                        ->image()
                        ->maxSize(2048)
                        ->directory('cash-movements'),
                ])
                ->action(function (array $data) {
                    try {
                        CashMovement::withdraw(
                            cashRegisterId: $this->currentCashRegister->id,
                            userId: Auth::id(),
                            amount: $data['amount'],
                            reason: $data['reason'],
                            notes: $data['notes'] ?? null,
                            receiptPath: $data['receipt'] ?? null
                        );

                        $this->loadCurrentCashRegister();

                        Notification::make()
                            ->success()
                            ->title('Sangria Registrada!')
                            ->body("R$ " . number_format($data['amount'], 2, ',', '.') . " retirado do caixa")
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Erro ao registrar sangria')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Action::make('fazer_reforco')
                ->label('💵 Reforço')
                ->color('info')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn () => $this->isOpen)
                ->form([
                    TextInput::make('amount')
                        ->label('Valor do Reforço (R$)')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->suffix('R$')
                        ->helperText('Valor a ser adicionado ao caixa'),

                    Select::make('reason')
                        ->label('Motivo')
                        ->options([
                            'Troco adicional' => 'Troco adicional',
                            'Reforço de fundo' => 'Reforço de fundo',
                            'Correção de erro' => 'Correção de erro',
                            'Outro' => 'Outro',
                        ])
                        ->required(),

                    Textarea::make('notes')
                        ->label('Observações (Opcional)')
                        ->rows(3),

                    FileUpload::make('receipt')
                        ->label('Comprovante (Opcional)')
                        ->image()
                        ->maxSize(2048)
                        ->directory('cash-movements'),
                ])
                ->action(function (array $data) {
                    try {
                        CashMovement::deposit(
                            cashRegisterId: $this->currentCashRegister->id,
                            userId: Auth::id(),
                            amount: $data['amount'],
                            reason: $data['reason'],
                            notes: $data['notes'] ?? null,
                            receiptPath: $data['receipt'] ?? null
                        );

                        $this->loadCurrentCashRegister();

                        Notification::make()
                            ->success()
                            ->title('Reforço Registrado!')
                            ->body("R$ " . number_format($data['amount'], 2, ',', '.') . " adicionado ao caixa")
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Erro ao registrar reforço')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Action::make('fechar_caixa')
                ->label('🔒 Fechar Caixa')
                ->color('danger')
                ->icon('heroicon-o-lock-closed')
                ->visible(fn () => $this->isOpen)
                ->requiresConfirmation()
                ->modalHeading('Fechar Caixa')
                ->modalDescription('Confira os valores e informe o total de dinheiro em caixa.')
                ->form([
                    TextInput::make('closing_balance')
                        ->label('Total em Dinheiro (Conferência)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->suffix('R$')
                        ->helperText(fn () =>
                            'Esperado: R$ ' . number_format($this->currentCashRegister->final_balance, 2, ',', '.')
                        ),

                    Textarea::make('closing_notes')
                        ->label('Observações de Fechamento (Opcional)')
                        ->rows(3)
                        ->placeholder('Ex: Diferenças, pendências, observações gerais...'),
                ])
                ->action(function (array $data) {
                    try {
                        $success = $this->currentCashRegister->close(
                            closingBalance: $data['closing_balance'],
                            notes: $data['closing_notes'] ?? null
                        );

                        if ($success) {
                            $difference = $this->currentCashRegister->difference;
                            $this->loadCurrentCashRegister();

                            $message = "Caixa fechado com sucesso!";
                            if (abs($difference) > 0.01) {
                                $tipo = $difference > 0 ? 'SOBRA' : 'FALTA';
                                $message .= " | {$tipo}: R$ " . number_format(abs($difference), 2, ',', '.');
                            } else {
                                $message .= " | ✅ Caixa bateu perfeitamente!";
                            }

                            Notification::make()
                                ->success()
                                ->title($message)
                                ->duration(5000)
                                ->send();

                            // Redirecionar para relatório (opcional)
                            // $this->redirect(route('filament.restaurant.resources.cash-registers.view', $this->currentCashRegister));
                        }

                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Erro ao fechar caixa')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }

    // ============================================
    // GETTERS PARA A VIEW
    // ============================================

    public function getCashRegisterProperty(): ?CashRegister
    {
        return $this->currentCashRegister;
    }
}
