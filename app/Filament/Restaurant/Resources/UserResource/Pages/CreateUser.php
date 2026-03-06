<?php

namespace App\Filament\Restaurant\Resources\UserResource\Pages;

use App\Filament\Restaurant\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Garantir que email_verified_at seja preenchido
        $data['email_verified_at'] = now();

        // Se não tiver permissões definidas, inicializar como array vazio
        if (!isset($data['permissions'])) {
            $data['permissions'] = [];
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Usuário criado com sucesso!';
    }
}
