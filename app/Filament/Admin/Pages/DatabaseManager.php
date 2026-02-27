<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class DatabaseManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static string $view = 'filament.admin.pages.database-manager';

    protected static ?string $navigationLabel = 'Banco de Dados';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 99;

    public static function canAccess(): bool
    {
        // Apenas super admins
        return auth()->guard('platform')->check();
    }

    public function getHeading(): string
    {
        return 'Gerenciador de Banco de Dados';
    }
}
