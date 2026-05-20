<?php

namespace App\Filament\Resources\PoolParticipantResource\Pages;

use App\Filament\Resources\PoolParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPoolParticipant extends EditRecord
{
    protected static string $resource = PoolParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
