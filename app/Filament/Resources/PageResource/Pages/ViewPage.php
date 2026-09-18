<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPage extends ViewRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PageResource\Pages\EditPage::make(),
        ];
    }
}
