<?php

namespace App\Filament\Restaurant\Resources\UserResource\Pages;

use App\Filament\Restaurant\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
