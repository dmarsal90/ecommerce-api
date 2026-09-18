<?php

namespace App\Filament\Resources\HomepageBlock\Pages;

use App\Filament\Resources\HomepageBlock\HomepageBlockResource;
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
