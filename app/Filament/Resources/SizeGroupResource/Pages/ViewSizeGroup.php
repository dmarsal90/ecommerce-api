<?php

namespace App\Filament\Resources\SizeGroupResource\Pages;

use App\Filament\Resources\SizeGroupResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\PageConfiguration;

class ViewSizeGroup extends ViewRecord
{
    protected static string $resource = SizeGroupResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;

    protected function getHeaderActions(): array
    {
        return [
            SizeGroupResource\Pages\EditSizeGroup::make(),
        ];
    }
}
