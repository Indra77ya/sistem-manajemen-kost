<?php

namespace App\Filament\Resources\MaintenanceCategoryResource\Pages;

use App\Filament\Resources\MaintenanceCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaintenanceCategory extends EditRecord
{
    protected static string $resource = MaintenanceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
