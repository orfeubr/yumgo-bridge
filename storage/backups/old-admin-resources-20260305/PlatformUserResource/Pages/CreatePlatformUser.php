<?php

namespace App\Filament\Admin\Resources\PlatformUserResource\Pages;

use App\Filament\Admin\Resources\PlatformUserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePlatformUser extends CreateRecord
{
    protected static string $resource = PlatformUserResource::class;
}
