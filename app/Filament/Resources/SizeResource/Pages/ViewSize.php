<?php

namespace App\Filament\Resources\Size\Pages;

use App\Filament\Resources\Size\SizeResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSize extends ViewRecord
{
    protected static string $resource = SizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SizeResource\Pages\EditSize::make(),
        ];
    }
}
