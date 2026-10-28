<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Resources\CouponResource;
use Filament\Actions\EditAction;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\ViewRecord;

class ViewCoupon extends ViewRecord
{
    protected static string $resource = CouponResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
