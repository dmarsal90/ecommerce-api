<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomepageBlockResource\Pages;
use App\Models\HomepageBlock;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
            ->columns();
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
            'edit' => Pages\EditHomepageBlock::route('/{record}/edit'),
        ];
    }
}