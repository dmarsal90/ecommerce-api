<?php

$files = glob('/home/david/Proyectos/ecommerce-api/app/Filament/Resources/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    // Replace use Filament\Forms\Form; with use Filament\Schemas\Schema;
    $content = str_replace(
        'use Filament\\Forms\\Form;',
        'use Filament\\Schemas\\Schema;',
        $content
    );

    // Replace use Filament\Forms; with use Filament\Schemas;
    $content = str_replace(
        'use Filament\\Forms;',
        'use Filament\\Schemas;',
        $content
    );

    // Replace use Filament\Forms\Components\ClassName; with use Filament\Schemas\Components\ClassName;
    $content = preg_replace(
        '/use Filament\\\\Forms\\\\Components\\\\(\\w+);/',
        'use Filament\\Schemas\\Components\\$1;',
        $content
    );

    // Replace Forms\Set with Schemas\Components\Utilities\Set
    $content = str_replace(
        'Forms\\Set',
        'Schemas\\Components\\Utilities\\Set',
        $content
    );

    // Replace form method signature
    $content = preg_replace(
        '/public static function form\(Form \$form\): Form/',
        'public static function form(Schema $schema): Schema',
        $content
    );

    // Replace $form-> with $schema->
    $content = str_replace('$form->', '$schema->', $content);

    file_put_contents($file, $content);
    echo "Fixed: $file\n";
}

echo "Done!\n";
