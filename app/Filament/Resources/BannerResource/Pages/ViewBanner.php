<?php

namespace App\Filament\Resources\Banner\Pages;

use App\Filament\Resources\Banner\BannerResource;
use Filament\Resources\Pages\ViewRecord;

class ViewBanner extends ViewRecord
{
    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            BannerResource\Pages\EditBanner::make(),
        ];
    }
}
