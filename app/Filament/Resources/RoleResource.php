<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Role Information')->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Select::make('guard_name')
                        ->options([
                            'web' => 'Web',
                            'api' => 'API',
                        ])
                        ->default('web')
                        ->required(),
                ]),

                Section::make('Permissions')->schema([
                    Select::make('permissions')
                        ->label('Permissions')
                        ->relationship('permissions', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->options(Permission::all()->pluck('name', 'name')->toArray())
                        ->getSearchResultsUsing(fn (string $search) => Permission::where('name', 'like', "%{$search}%")->pluck('name', 'name')->toArray()),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('guard_name')->badge(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('admin');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin');
    }
}