<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\PageConfiguration;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $configurationClass = PageConfiguration::class;
}
