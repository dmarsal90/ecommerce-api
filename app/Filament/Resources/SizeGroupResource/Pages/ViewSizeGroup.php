<?php

namespace App\Filament\Resources\SizeGroupResource\Pages;

use App\Filament\Resources\SizeGroupResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSizeGroup extends ViewRecord
{
    protected static string $resource = SizeGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SizeGroupResource\Pages\EditSizeGroup::make(),
        ];
    }
}
