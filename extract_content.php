<?php

// Extract form schema and table content from corrupted files using bracket matching

function extractBracketedContent($content, $startPattern)
{
    $pos = strpos($content, $startPattern);
    if ($pos === false) {
        return '';
    }

    $pos += strlen($startPattern);
    $depth = 1;
    $start = $pos;
    $len = strlen($content);

    for ($i = $pos; $i < $len; $i++) {
        $char = $content[$i];
        if ($char === '[') {
            $depth++;
        } elseif ($char === ']') {
            $depth--;
        }
        if ($depth === 0) {
            return substr($content, $start, $i - $start + 1);
        }
    }

    return '';
}

$files = glob('/home/david/Proyectos/ecommerce-api/app/Filament/Resources/*.php');

foreach ($files as $file) {
    $basename = basename($file, '.php');
    if ($basename === 'CategoryResource') {
        continue;
    }

    $content = file_get_contents($file);

    // The corrupted file has the form/table content but with $form-> and wrong imports
    // Let me try to extract from the original corrupted version
    // Actually, the current file was already rewritten with empty content
    // I need to find a backup or the original

    // Let me check if there's a .bak or if I can recover from PHP's opcache
    // For now, let me manually define the content for each resource based on what we know
}

echo "Need to manually restore content\n";
