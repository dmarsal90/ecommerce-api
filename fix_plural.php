<?php

$resources = [
    'CategoryResource' => 'Categories',
    'BannerResource' => 'Banners',
    'BrandResource' => 'Brands',
    'CollectionResource' => 'Collections',
    'ColorResource' => 'Colors',
    'CustomerResource' => 'Customers',
    'HomepageBlockResource' => 'HomepageBlocks',
    'InventoryResource' => 'Inventories',
    'MenuItemResource' => 'MenuItems',
    'MenuResource' => 'Menus',
    'OrderResource' => 'Orders',
    'PageResource' => 'Pages',
    'ProductAttributeResource' => 'ProductAttributes',
    'ProductResource' => 'Products',
    'ReviewResource' => 'Reviews',
    'SizeGroupResource' => 'SizeGroups',
    'SizeResource' => 'Sizes',
    'StockMovementResource' => 'StockMovements',
    'TagResource' => 'Tags',
];

$basePath = '/home/david/Proyectos/ecommerce-api/app/Filament/Resources/';

foreach ($resources as $resource => $plural) {
    $file = $basePath . $resource . '.php';
    $content = file_get_contents($file);
    
    // Fix getPages pluralization
    $modelName = str_replace('Resource', '', $resource);
    
    $content = str_replace(
        "Pages\\List{$modelName}s::route('/')",
        "Pages\\List{$plural}::route('/')",
        $content
    );
    
    file_put_contents($file, $content);
    echo "Fixed pluralization: $resource -> $plural\n";
}

echo "Done!\n";