<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductVariantAttributeValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class VariantAttributeValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'variantAttributeValues';

    protected static ?string $recordTitleAttribute = 'attribute_value_id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('attribute_value_id')
                    ->label('Attribute Value')
                    ->relationship('attributeValue', 'value')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->value} ({$record->attribute->name})")
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('attributeValue.value')->label('Value')->searchable(),
                Tables\Columns\TextColumn::make('attributeValue.attribute.name')->label('Attribute')->sortable(),
                Tables\Columns\ColorColumn::make('attributeValue.color_code')->label('Color'),
                Tables\Columns\TextColumn::make('attributeValue.color.name')->label('Color Name'),
                Tables\Columns\TextColumn::make('attributeValue.size.name')->label('Size'),
                Tables\Columns\IconColumn::make('attributeValue.is_active')->boolean()->label('Active'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}