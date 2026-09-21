<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\PageConfiguration;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}