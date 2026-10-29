<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Concerns\SavesAndCloses;
use App\Filament\Resources\CustomerResource;
use Filament\Pages\PageConfiguration;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    use SavesAndCloses;

    protected static string $resource = CustomerResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
