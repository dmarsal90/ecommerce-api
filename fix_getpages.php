<?php

// Fix getRelations and getPages in all resource files

$files = glob('/home/david/Proyectos/ecommerce-api/app/Filament/Resources/*.php');

foreach ($files as $file) {
    $basename = basename($file, '.php');
    if ($basename === 'CategoryResource' || $basename === 'BannerResource') {
        continue;
    }

    $content = file_get_contents($file);

    // Fix getRelations - replace the broken double return
    $content = preg_replace(
        '/public static function getRelations\(\): array\s*{\s*return \[\s*return \[\s*\/\/\];\s*\];\s*}/s',
        "public static function getRelations(): array\n    {\n        return [\n            //\n        ];\n    }",
        $content
    );

    // Fix getPages - replace the broken double return
    $content = preg_replace(
        '/public static function getPages\(\): array\s*{\s*return \[\s*return \[\s*(.*?)\s*\];\s*}/s',
        "public static function getPages(): array\n    {\n        return [\n            \$1\n        ];\n    }",
        $content
    );

    // Also fix the case where getPages has the model name pattern
    $modelName = str_replace('Resource', '', $basename);
    $content = preg_replace(
        '/public static function getPages\(\): array\s*{\s*return \[\s*return \[\s*(.*?)\s*\];\s*}/s',
        "public static function getPages(): array\n    {\n        return [\n            'index' => Pages\\List{$modelName}s::route('/'),\n            'create' => Pages\\Create{$modelName}::route('/create'),\n            'edit' => Pages\\Edit{$modelName}::route('/{record}/edit'),\n        ];\n    }",
        $content
    );

    file_put_contents($file, $content);
    echo "Fixed getRelations/getPages: $basename\n";
}

echo "Done!\n";
