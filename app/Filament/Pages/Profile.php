<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use MatondoJK\FilamentAvatarPicker\Components\AvatarPicker;

class Profile extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.profile';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'avatar_url' => Auth::user()->avatar_url,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255),
                AvatarPicker::make('avatar_url')
                    ->label('Avatar')
                    ->disk('public'),
            ])
            ->statePath('data')
            ->model(Auth::user());
    }

    public function save(): void
    {
        $data = $this->form->getState();
        Auth::user()->update($data);

        Notification::make()
            ->title('Profile updated successfully')
            ->success()
            ->send();
    }
}
