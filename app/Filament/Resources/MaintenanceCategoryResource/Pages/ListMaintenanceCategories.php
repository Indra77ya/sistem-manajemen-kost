<?php

namespace App\Filament\Resources\MaintenanceCategoryResource\Pages;

use App\Filament\Resources\MaintenanceCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceCategories extends ListRecords
{
    protected static string $resource = MaintenanceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
