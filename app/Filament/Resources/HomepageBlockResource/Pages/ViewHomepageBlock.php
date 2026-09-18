<?php

namespace App\Filament\Resources\HomepageBlockResource\Pages;

use App\Filament\Resources\HomepageBlockResource;
use Filament\Resources\Pages\ViewRecord;

class ViewHomepageBlock extends ViewRecord
{
    protected static string $resource = HomepageBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            HomepageBlockResource\Pages\EditHomepageBlock::make(),
        ];
    }
}
