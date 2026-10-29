<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\CouponResource;
use Filament\Actions\DeleteAction;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditCoupon extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = CouponResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
