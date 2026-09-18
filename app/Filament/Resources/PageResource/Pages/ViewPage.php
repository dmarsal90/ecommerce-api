<?php

namespace App\Filament\Resources\Page\Pages;

use App\Filament\Resources\Page\PageResource;
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
