<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Filament\Resources\ProductResource\RelationManagers\VariantAttributeValuesRelationManager;
use App\Models\ProductVariant;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'sku';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('sku')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                TextInput::make('price')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->required(),

                TextInput::make('compare_at_price')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->label('Compare At Price'),

                TextInput::make('cost_price')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->label('Cost Price'),

                TextInput::make('quantity')
                    ->numeric()
                    ->default(0),

                TextInput::make('low_stock_threshold')
                    ->numeric()
                    ->default(5)
                    ->label('Low Stock Threshold'),

                TextInput::make('weight')
                    ->numeric()
                    ->step(0.01)
                    ->label('Weight (kg)'),

                TextInput::make('barcode')
                    ->maxLength(100),

                FileUpload::make('image')
                    ->image()
                    ->directory('variants'),

                Toggle::make('is_active')
                    ->default(true),

                Toggle::make('track_quantity')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')->searchable(),
                Tables\Columns\TextColumn::make('price')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('compare_at_price')->money('USD')->sortable()->label('Compare At'),
                Tables\Columns\TextColumn::make('quantity')->numeric()->sortable()->label('Stock'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\IconColumn::make('track_quantity')->boolean()->label('Track Qty'),
                Tables\Columns\ImageColumn::make('image')->circular(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            VariantAttributeValuesRelationManager::class,
        ];
    }
}