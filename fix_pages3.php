<?php

$files = glob('app/Filament/Resources/*/Pages/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Skip if already has configurationClass
    if (strpos($content, 'configurationClass') !== false) {
        continue;
    }
    
    // Check if it's a page class that extends Record or Page
    if (!preg_match('/extends\s+(EditRecord|CreateRecord|ViewRecord|ListRecords|Page)/', $content)) {
        continue;
    }
    
    // Add import after the last use statement - find the last use line
    $lines = explode("\n", $content);
    $newLines = [];
    $lastUseIndex = -1;
    
    foreach ($lines as $i => $line) {
        $newLines[] = $line;
        if (strpos($line, 'use ') === 0 && strpos($line, ';') !== false) {
            $lastUseIndex = $i;
        }
    }
    
    if ($lastUseIndex >= 0) {
        // Insert after the last use statement
        array_splice($newLines, $lastUseIndex + 1, 0, 'use Filament\Pages\PageConfiguration;');
        $content = implode("\n", $newLines);
    }
    
    // Add configurationClass property after the $resource property
    $content = preg_replace(
        '/(protected static string \$resource = .+;)/',
        "$1\n\n    protected static ?string \$configurationClass = PageConfiguration::class;",
        $content
    );
    
    file_put_contents($file, $content);
    echo "Fixed: $file\n";
}

echo "Done!\n";