<?php

namespace App\Filament\Resources\Tag\Pages;

use App\Filament\Resources\Tag\TagResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTag extends ViewRecord
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            TagResource\Pages\EditTag::make(),
        ];
    }
}
