<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomepageBlockResource\Pages;
use App\Models\HomepageBlock;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;

class HomepageBlockResource extends Resource
{
    protected static ?string $model = HomepageBlock::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

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
                ViewAction::make()->iconButton()->color('sky-500'),
                EditAction::make()->iconButton()->color('yellow-500'),
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('red-600')
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
            'index' => Pages\ListHomepageBlocks::route('/'),
            'create' => Pages\CreateHomepageBlock::route('/create'),
            'view' => Pages\ViewHomepageBlock::route('/{record}'),
            'edit' => Pages\EditHomepageBlock::route('/{record}/edit'),
        ];
    }
}