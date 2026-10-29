<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrders extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->weight('medium')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->limit(30),

                TextColumn::make('total')
                    ->money('USD')
                    ->weight('medium'),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'info' => 'processing',
                        'primary' => 'shipped',
                        'gray' => 'delivered',
                        'danger' => 'cancelled',
                        'purple' => 'refunded',
                    ]),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->since(),
            ])
            ->paginated(false);
    }
}
