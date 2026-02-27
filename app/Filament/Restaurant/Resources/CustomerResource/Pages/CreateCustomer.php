<?php

namespace App\Filament\Restaurant\Resources\CustomerResource\Pages;

use App\Filament\Restaurant\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
