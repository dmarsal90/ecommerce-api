<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // TODO: form schema
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Add your columns here
            ])
            ->actions([
                ViewAction::make()->iconButton()->color('info'),
                EditAction::make()->iconButton()->color('warning'),
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->iconButton(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'view' => Pages\ViewMenuItem::route('/{record}'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}