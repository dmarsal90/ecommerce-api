<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStock extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('track_quantity', true)
                    ->whereRaw('quantity <= low_stock_threshold')
                    ->orderBy('quantity')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->weight('medium')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('sku')
                    ->label('SKU'),

                TextColumn::make('quantity')
                    ->label('Stock')
                    ->color('danger')
                    ->weight('bold'),

                TextColumn::make('low_stock_threshold')
                    ->label('Threshold'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->paginated(false)
            ->emptyStateHeading('No low stock products')
            ->emptyStateDescription('All products have sufficient inventory.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
