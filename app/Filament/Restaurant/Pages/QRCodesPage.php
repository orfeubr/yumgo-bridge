<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\Table;
use Filament\Pages\Page;
use Filament\Actions\Action;

class QRCodesPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = '📱 QR Codes';
    protected static ?string $title = 'QR Codes para Pedidos Presenciais';
    protected static ?string $slug = 'qr-codes';
    protected static ?string $navigationGroup = '🏪 Pedidos Presenciais';
    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.restaurant.pages.q-r-codes-page';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('counterQR')
                ->label('🧍 Ver QR do Balcão')
                ->color('success')
                ->url(route('restaurant.counter.qr-code'))
                ->openUrlInNewTab(),
        ];
    }

    public function getTables()
    {
        return Table::where('is_active', true)->orderBy('number')->get();
    }
}
