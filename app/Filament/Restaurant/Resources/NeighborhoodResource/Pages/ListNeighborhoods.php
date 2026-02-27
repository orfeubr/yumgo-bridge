<?php

namespace App\Filament\Restaurant\Resources\NeighborhoodResource\Pages;

use App\Filament\Restaurant\Resources\NeighborhoodResource;
use App\Services\LocationService;
use App\Models\Settings;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Filament\Forms;

class ListNeighborhoods extends ListRecords
{
    protected static string $resource = NeighborhoodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import_neighborhoods')
                ->label('Cadastrar Bairros')
                ->icon('heroicon-o-map-pin')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('city')
                        ->label('Cidade')
                        ->required()
                        ->searchable()
                        ->options([
                            'Jundiaí' => 'Jundiaí',
                            'Louveira' => 'Louveira',
                            'Vinhedo' => 'Vinhedo',
                            'Itupeva' => 'Itupeva',
                            'Campinas' => 'Campinas',
                            'São Paulo' => 'São Paulo',
                        ])
                        ->helperText('Escolha sua cidade para carregar os bairros'),
                ])
                ->action(function (array $data) {
                    try {
                        $city = $data['city'];
                        $service = app(LocationService::class);

                        $count = $service->importNeighborhoodsToDatabase($city);

                        if ($count > 0) {
                            // Salvar cidade nas settings
                            $settings = Settings::current();
                            $settings->update([
                                'delivery_city' => $city,
                                'delivery_state' => 'SP',
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Bairros cadastrados!')
                                ->body("{$count} bairros de {$city} foram cadastrados como INATIVOS. Agora ative os bairros que você atende e configure as taxas de entrega.")
                                ->duration(8000)
                                ->send();
                        } else {
                            Notification::make()
                                ->warning()
                                ->title('Nenhum bairro encontrado')
                                ->body("Não encontramos bairros cadastrados para {$city}. Use o botão 'Criar' para adicionar manualmente.")
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Erro ao cadastrar')
                            ->body('Ocorreu um erro: ' . $e->getMessage())
                            ->send();
                    }
                })
                ->modalHeading('Cadastrar Bairros da sua Cidade')
                ->modalDescription('Os bairros serão cadastrados como INATIVOS. Depois você deve ativar apenas os que você atende e configurar a taxa de entrega de cada um.')
                ->modalSubmitActionLabel('Cadastrar Bairros')
                ->modalWidth('md'),

            Actions\CreateAction::make()
                ->label('Criar Bairro'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Restaurant\Widgets\NeighborhoodStatsWidget::class,
        ];
    }
}
