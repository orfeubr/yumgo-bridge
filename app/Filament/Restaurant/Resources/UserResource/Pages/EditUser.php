<?php

namespace App\Filament\Restaurant\Resources\UserResource\Pages;

use App\Filament\Restaurant\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalDescription('Tem certeza que deseja deletar este usuário? Esta ação não pode ser desfeita.'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Usuário atualizado com sucesso!';
    }
}
