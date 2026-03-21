<?php

namespace App\Filament\Restaurant\Pages;

use Filament\Pages\Page;

class PendingOrders extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = '📱 Pedidos Pendentes';
    protected static ?string $title = 'Pedidos Pendentes - QR Code PIX';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.restaurant.pages.pending-orders';

    // Configurações da página
    protected static bool $shouldRegisterNavigation = true;
    
    public function getHeading(): string
    {
        return '📱 Pedidos Pendentes - QR Code PIX';
    }

    public function getSubheading(): ?string
    {
        return 'Clique no pedido para mostrar QR Code gigante ao cliente';
    }
}
