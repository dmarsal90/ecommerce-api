<?php

$files = glob('/home/david/Proyectos/ecommerce-api/app/Filament/Resources/*.php');

foreach ($files as $file) {
    $basename = basename($file, '.php');
    if ($basename === 'CategoryResource' || $basename === 'BannerResource') continue;
    
    $content = file_get_contents($file);
    
    // Fix getRelations
    $content = str_replace(
        "public static function getRelations(): array\n    {\n        return [\n            return [\n            //\n        ];\n        ];\n    }",
        "public static function getRelations(): array\n    {\n        return [\n            //\n        ];\n    }",
        $content
    );
    
    // Fix getPages - extract model name
    $modelName = str_replace('Resource', '', $basename);
    $content = str_replace(
        "public static function getPages(): array\n    {\n        return [\n            'index' => Pages\\List{$modelName}s::route('/'),\n            'create' => Pages\\Create{$modelName}::route('/create'),\n            'edit' => Pages\\Edit{$modelName}::route('/{record\n        ];\n    }",
        "public static function getPages(): array\n    {\n        return [\n            'index' => Pages\\List{$modelName}s::route('/'),\n            'create' => Pages\\Create{$modelName}::route('/create'),\n            'edit' => Pages\\Edit{$modelName}::route('/{record}/edit'),\n        ];\n    }",
        $content
    );
    
    file_put_contents($file, $content);
    echo "Fixed: $basename\n";
}

echo "Done!\n";