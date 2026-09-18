<?php

namespace App\Filament\Resources\Permission\Pages;

use App\Filament\Resources\Permission\PermissionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPermission extends ViewRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PermissionResource\Pages\EditPermission::make(),
        ];
    }
}
