<?php

// Fix all resource files by rewriting them with correct v5 syntax

$resources = [
    'BannerResource' => [
        'model' => 'Banner',
        'icon' => 'heroicon-o-photo',
        'group' => 'Content',
        'sort' => 2,
        'components' => ['DatePicker', 'FileUpload', 'Grid', 'Select', 'TextInput', 'Toggle', 'Textarea'],
        'tableColumns' => ['IconColumn', 'ImageColumn', 'TextColumn'],
        'usesSet' => true,
    ],
    'BrandResource' => [
        'model' => 'Brand',
        'icon' => 'heroicon-o-shield-check',
        'group' => 'Catalog',
        'sort' => null,
        'components' => ['FileUpload', 'Grid', 'Textarea', 'TextInput', 'Toggle'],
        'tableColumns' => ['ImageColumn', 'TextColumn'],
        'usesSet' => true,
    ],
    'CategoryResource' => [
        'model' => 'Category',
        'icon' => 'heroicon-o-tag',
        'group' => 'Catalog',
        'sort' => 1,
        'components' => ['FileUpload', 'Select', 'Textarea', 'TextInput', 'Toggle'],
        'tableColumns' => ['IconColumn', 'ImageColumn', 'TextColumn'],
        'usesSet' => true,
    ],
    'CollectionResource' => [
        'model' => 'Collection',
        'icon' => 'heroicon-o-rectangle-stack',
        'group' => 'Catalog',
        'sort' => null,
        'components' => ['DatePicker', 'FileUpload', 'Grid', 'Select', 'Textarea', 'TextInput', 'Toggle'],
        'tableColumns' => ['ImageColumn', 'TextColumn'],
        'usesSet' => true,
    ],
    'ColorResource' => [
        'model' => 'Color',
        'icon' => 'heroicon-o-paint-brush',
        'group' => 'Catalog',
        'sort' => null,
        'components' => ['ColorPicker', 'FileUpload', 'Grid', 'TextInput', 'Toggle'],
        'tableColumns' => ['TextColumn'],
        'usesSet' => true,
    ],
    'CustomerResource' => [
        'model' => 'Customer',
        'icon' => 'heroicon-o-users',
        'group' => 'Sales',
        'sort' => null,
        'components' => ['Grid', 'TextInput', 'Toggle'],
        'tableColumns' => ['TextColumn', 'IconColumn'],
        'usesSet' => false,
    ],
    'HomepageBlockResource' => [
        'model' => 'HomepageBlock',
        'icon' => 'heroicon-o-squares-2x2',
        'group' => 'Content',
        'sort' => null,
        'components' => ['DatePicker', 'Grid', 'KeyValue', 'Select', 'TextInput', 'Toggle'],
        'tableColumns' => ['TextColumn'],
        'usesSet' => false,
    ],
    'InventoryResource' => [
        'model' => 'Inventory',
        'icon' => 'heroicon-o-cube-transparent',
        'group' => 'Inventory',
        'sort' => null,
        'components' => ['Grid', 'Select', 'TextInput', 'Toggle'],
        'tableColumns' => ['TextColumn', 'IconColumn'],
        'usesSet' => false,
    ],
    'MenuItemResource' => [
        'model' => 'MenuItem',
        'icon' => 'heroicon-o-link',
        'group' => 'Content',
        'sort' => null,
        'components' => ['Grid', 'Select', 'TextInput', 'Toggle'],
        'tableColumns' => ['TextColumn', 'IconColumn'],
        'usesSet' => false,
    ],
    'MenuResource' => [
        'model' => 'Menu',
        'icon' => 'heroicon-o-bars-3',
        'group' => 'Content',
        'sort' => null,
        'components' => ['Grid', 'TextInput', 'Toggle'],
        'tableColumns' => ['TextColumn', 'IconColumn'],
        'usesSet' => true,
    ],
    'OrderResource' => [
        'model' => 'Order',
        'icon' => 'heroicon-o-shopping-cart',
        'group' => 'Sales',
        'sort' => null,
        'components' => ['Grid', 'Select', 'Textarea', 'TextInput'],
        'tableColumns' => ['TextColumn', 'IconColumn', 'BadgeColumn'],
        'usesSet' => false,
    ],
    'PageResource' => [
        'model' => 'Page',
        'icon' => 'heroicon-o-document-text',
        'group' => 'Content',
        'sort' => null,
        'components' => ['DateTimePicker', 'FileUpload', 'Grid', 'RichEditor', 'Select', 'TextInput', 'Toggle'],
        'tableColumns' => ['TextColumn', 'IconColumn', 'ImageColumn'],
        'usesSet' => true,
    ],
    'ProductAttributeResource' => [
        'model' => 'ProductAttribute',
        'icon' => 'heroicon-o-swatch',
        'group' => 'Catalog',
        'sort' => null,
        'components' => ['Grid', 'Select', 'TextInput', 'Toggle'],
        'tableColumns' => ['TextColumn', 'IconColumn'],
        'usesSet' => true,
    ],
    'ProductResource' => [
        'model' => 'Product',
        'icon' => 'heroicon-o-shopping-bag',
        'group' => 'Catalog',
        'sort' => null,
        'components' => ['FileUpload', 'Grid', 'Placeholder', 'Select', 'Section', 'Textarea', 'TextInput', 'Toggle', 'Repeater', 'Hidden'],
        'tableColumns' => ['TextColumn', 'ImageColumn', 'IconColumn', 'BadgeColumn'],
        'usesSet' => true,
    ],
    'ReviewResource' => [
        'model' => 'Review',
        'icon' => 'heroicon-o-star',
        'group' => 'Sales',
        'sort' => null,
        'components' => ['Grid', 'Select', 'Textarea', 'TextInput', 'Toggle'],
        'tableColumns' => ['TextColumn', 'IconColumn', 'StarColumn'],
        'usesSet' => false,
    ],
    'SizeGroupResource' => [
        'model' => 'SizeGroup',
        'icon' => 'heroicon-o-scale',
        'group' => 'Catalog',
        'sort' => null,
        'components' => ['Grid', 'Textarea', 'TextInput', 'Toggle'],
        'tableColumns' => ['TextColumn', 'IconColumn'],
        'usesSet' => true,
    ],
    'SizeResource' => [
        'model' => 'Size',
        'icon' => 'heroicon-o-scale',
        'group' => 'Catalog',
        'sort' => null,
        'components' => ['Grid', 'Select', 'TextInput', 'Toggle'],
        'tableColumns' => ['TextColumn', 'IconColumn'],
        'usesSet' => true,
    ],
    'StockMovementResource' => [
        'model' => 'StockMovement',
        'icon' => 'heroicon-o-arrow-path',
        'group' => 'Inventory',
        'sort' => null,
        'components' => ['Grid', 'Select', 'Textarea', 'TextInput'],
        'tableColumns' => ['TextColumn', 'IconColumn', 'BadgeColumn'],
        'usesSet' => false,
    ],
    'TagResource' => [
        'model' => 'Tag',
        'icon' => 'heroicon-o-tag',
        'group' => 'Catalog',
        'sort' => null,
        'components' => ['ColorPicker', 'Grid', 'TextInput', 'Toggle'],
        'tableColumns' => ['TextColumn', 'IconColumn'],
        'usesSet' => true,
    ],
];

$basePath = '/home/david/Proyectos/ecommerce-api/app/Filament/Resources/';

foreach ($resources as $name => $config) {
    $file = $basePath . $name . '.php';
    
    // Read original to get form/table content
    $original = file_get_contents($file);
    
    // Extract form schema content (between ->schema([ and ]);)
    // Extract table content
    
    // Build imports
    $imports = [
        "use App\\Filament\\Resources\\{$name}\\Pages;",
        "use App\\Models\\{$config['model']};",
        "use Filament\\Schemas\\Schema;",
    ];
    
    // Add component imports
    $componentImports = [];
    foreach ($config['components'] as $comp) {
        $componentImports[] = "use Filament\\Schemas\\Components\\{$comp};";
    }
    $imports = array_merge($imports, $componentImports);
    
    // Add Set if needed
    if ($config['usesSet']) {
        $imports[] = "use Filament\\Schemas\\Components\\Utilities\\Set;";
    }
    
    // Add Resource and Tables imports
    $imports[] = "use Filament\\Resources\\Resource;";
    $imports[] = "use Filament\\Tables;";
    
    // Add table column imports
    foreach ($config['tableColumns'] as $col) {
        $imports[] = "use Filament\\Tables\\Columns\\{$col};";
    }
    $imports[] = "use Filament\\Tables\\Table;";
    
    // Check if uses Builder
    if (strpos($original, 'Illuminate\\Database\\Eloquent\\Builder') !== false) {
        $imports[] = "use Illuminate\\Database\\Eloquent\\Builder;";
    }
    
    $importsStr = implode("\n", $imports);
    
    // Build the class
    $class = <<<PHP
<?php

namespace App\Filament\Resources;

{$importsStr}

class {$name} extends Resource
{
    protected static ?string \$model = {$config['model']}::class;

    protected static string|\BackedEnum|null \$navigationIcon = '{$config['icon']}';

    protected static string|\UnitEnum|null \$navigationGroup = '{$config['group']}';
PHP;
    
    if ($config['sort'] !== null) {
        $class .= "\n    protected static ?int \$navigationSort = {$config['sort']};\n";
    }
    
    $class .= "\n    public static function form(Schema \$schema): Schema\n    {\n        return \$schema\n            ->schema([\n                // TODO: form schema\n            ]);\n    }\n\n    public static function table(Table \$table): Table\n    {\n        return \$table\n            ->columns([\n                // TODO: table columns\n            ]);\n    }\n\n    public static function getRelations(): array\n    {\n        return [\n            //\n        ];\n    }\n\n    public static function getPages(): array\n    {\n        return [\n            'index' => Pages\\List{$config['model']}s::route('/'),\n            'create' => Pages\\Create{$config['model']}::route('/create'),\n            'edit' => Pages\\Edit{$config['model']}::route('/{record}/edit'),\n        ];\n    }\n}";

    file_put_contents($file, $class);
    echo "Generated: $file\n";
}

echo "Done! Now need to manually add form/table content for each.\n";