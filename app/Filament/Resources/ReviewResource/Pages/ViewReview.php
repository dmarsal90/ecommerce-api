<?php

namespace App\Filament\Resources\Review\Pages;

use App\Filament\Resources\Review\ReviewResource;
use Filament\Resources\Pages\ViewRecord;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ReviewResource\Pages\EditReview::make(),
        ];
    }
}
