<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\CashbackSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class CashbackConfig extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'Cashback';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.cashback-config';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = CashbackSettings::firstOrCreate([]);
        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Status do Cashback')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Cashback Ativo')
                            ->helperText('Ative ou desative o sistema de cashback')
                            ->inline(false)
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Bronze (Iniciante)')
                    ->description('Clientes novos começam neste nível')
                    ->schema([
                        Forms\Components\TextInput::make('bronze_percentage')
                            ->label('% de Cashback')
                            ->numeric()
                            ->suffix('%')
                            ->default(2.00)
                            ->required(),
                        Forms\Components\TextInput::make('bronze_min_orders')
                            ->label('Pedidos Mínimos')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('bronze_min_spent')
                            ->label('Gasto Mínimo (R$)')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0.00)
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Prata')
                    ->description('2º nível de fidelidade')
                    ->schema([
                        Forms\Components\TextInput::make('silver_percentage')
                            ->label('% de Cashback')
                            ->numeric()
                            ->suffix('%')
                            ->default(3.50)
                            ->required(),
                        Forms\Components\TextInput::make('silver_min_orders')
                            ->label('Pedidos Mínimos')
                            ->numeric()
                            ->default(5)
                            ->required(),
                        Forms\Components\TextInput::make('silver_min_spent')
                            ->label('Gasto Mínimo (R$)')
                            ->numeric()
                            ->prefix('R$')
                            ->default(200.00)
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Ouro')
                    ->description('3º nível de fidelidade')
                    ->schema([
                        Forms\Components\TextInput::make('gold_percentage')
                            ->label('% de Cashback')
                            ->numeric()
                            ->suffix('%')
                            ->default(5.00)
                            ->required(),
                        Forms\Components\TextInput::make('gold_min_orders')
                            ->label('Pedidos Mínimos')
                            ->numeric()
                            ->default(15)
                            ->required(),
                        Forms\Components\TextInput::make('gold_min_spent')
                            ->label('Gasto Mínimo (R$)')
                            ->numeric()
                            ->prefix('R$')
                            ->default(500.00)
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Platina (VIP)')
                    ->description('Nível máximo de fidelidade')
                    ->schema([
                        Forms\Components\TextInput::make('platinum_percentage')
                            ->label('% de Cashback')
                            ->numeric()
                            ->suffix('%')
                            ->default(7.00)
                            ->required(),
                        Forms\Components\TextInput::make('platinum_min_orders')
                            ->label('Pedidos Mínimos')
                            ->numeric()
                            ->default(30)
                            ->required(),
                        Forms\Components\TextInput::make('platinum_min_spent')
                            ->label('Gasto Mínimo (R$)')
                            ->numeric()
                            ->prefix('R$')
                            ->default(1000.00)
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Bônus Especiais')
                    ->schema([
                        Forms\Components\Toggle::make('birthday_bonus_enabled')
                            ->label('Bônus de Aniversário')
                            ->helperText('Cashback dobrado no dia do aniversário')
                            ->inline(false)
                            ->default(true),
                        Forms\Components\TextInput::make('birthday_multiplier')
                            ->label('Multiplicador de Aniversário')
                            ->numeric()
                            ->suffix('x')
                            ->default(2.00)
                            ->visible(fn (Forms\Get $get) => $get('birthday_bonus_enabled')),

                        Forms\Components\Toggle::make('referral_enabled')
                            ->label('Programa de Indicação')
                            ->helperText('Cliente ganha ao indicar amigos')
                            ->inline(false)
                            ->default(true),
                        Forms\Components\TextInput::make('referral_bonus_referrer')
                            ->label('Bônus para quem Indica (R$)')
                            ->numeric()
                            ->prefix('R$')
                            ->default(10.00)
                            ->visible(fn (Forms\Get $get) => $get('referral_enabled')),
                        Forms\Components\TextInput::make('referral_bonus_referred')
                            ->label('Bônus para quem foi Indicado (R$)')
                            ->numeric()
                            ->prefix('R$')
                            ->default(5.00)
                            ->visible(fn (Forms\Get $get) => $get('referral_enabled')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Regras de Uso')
                    ->schema([
                        Forms\Components\TextInput::make('min_order_value_to_earn')
                            ->label('Valor Mínimo do Pedido para Ganhar (R$)')
                            ->helperText('Pedidos abaixo deste valor não geram cashback')
                            ->numeric()
                            ->prefix('R$')
                            ->default(10.00)
                            ->required(),
                        Forms\Components\TextInput::make('min_cashback_to_use')
                            ->label('Saldo Mínimo para Usar (R$)')
                            ->helperText('Cliente só pode usar cashback se tiver pelo menos este valor')
                            ->numeric()
                            ->prefix('R$')
                            ->default(5.00)
                            ->required(),
                        Forms\Components\TextInput::make('expiration_days')
                            ->label('Validade do Cashback (dias)')
                            ->helperText('Após quantos dias o cashback expira')
                            ->numeric()
                            ->suffix('dias')
                            ->default(180)
                            ->required(),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = CashbackSettings::firstOrCreate([]);
        $settings->update($data);

        Notification::make()
            ->success()
            ->title('Configurações salvas!')
            ->body('As configurações de cashback foram atualizadas.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Salvar Configurações')
                ->action('save'),
        ];
    }
}
