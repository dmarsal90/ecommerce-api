<?php

// Parse corrupted resource files and rebuild them correctly for Filament v5

$files = glob('/home/david/Proyectos/ecommerce-api/app/Filament/Resources/*.php');

foreach ($files as $file) {
    $basename = basename($file, '.php');
    if ($basename === 'CategoryResource') {
        continue;
    } // Already fixed manually

    $content = file_get_contents($file);

    // Extract namespace and class name
    if (! preg_match('/namespace (.*?);/', $content, $m)) {
        continue;
    }
    $namespace = $m[1];

    if (! preg_match('/class (\w+) extends Resource/', $content, $m)) {
        continue;
    }
    $className = $m[1];

    // Extract model
    if (! preg_match('/protected static \?string \$model = (\w+)::class;/', $content, $m)) {
        continue;
    }
    $model = $m[1];

    // Extract navigationIcon
    if (! preg_match('/protected static string\\|\\\\BackedEnum\\|null \$navigationIcon = \'(.*?)\'/', $content, $m)) {
        continue;
    }
    $icon = $m[1];

    // Extract navigationGroup
    if (! preg_match('/protected static string\\|\\\\UnitEnum\\|null \$navigationGroup = \'(.*?)\'/', $content, $m)) {
        continue;
    }
    $group = $m[1];

    // Extract navigationSort if exists
    $sort = null;
    if (preg_match('/protected static \?int \$navigationSort = (\d+);/', $content, $m)) {
        $sort = $m[1];
    }

    // Extract form schema content (between ->schema([ and ]);)
    $formSchema = '';
    if (preg_match('/return \$schema\s*->\s*schema\((\[.*?\])\);/s', $content, $m)) {
        $formSchema = $m[1];
    } elseif (preg_match('/return \$form\s*->\s*schema\((\[.*?\])\);/s', $content, $m)) {
        $formSchema = $m[1];
    }

    // Extract table columns content
    $tableColumns = '';
    if (preg_match('/->columns\((\[.*?\])\)\s*->/s', $content, $m)) {
        $tableColumns = $m[1];
    }

    // Extract filters, actions, bulkActions if present
    $filters = '';
    if (preg_match('/->filters\((\[.*?\])\)\s*->/s', $content, $m)) {
        $filters = $m[1];
    }

    $actions = '';
    if (preg_match('/->actions\((\[.*?\])\)\s*->/s', $content, $m)) {
        $actions = $m[1];
    }

    $bulkActions = '';
    if (preg_match('/->bulkActions\((\[.*?\])\)\s*;/s', $content, $m)) {
        $bulkActions = $m[1];
    }

    // Extract defaultSort
    $defaultSort = '';
    if (preg_match('/->defaultSort\((.*?)\)\s*->/s', $content, $m)) {
        $defaultSort = $m[1];
    }

    // Extract getRelations
    $getRelations = '';
    if (preg_match('/public static function getRelations\(\): array\s*{([^}]*)}/s', $content, $m)) {
        $getRelations = trim($m[1]);
    }

    // Extract getPages
    $getPages = '';
    if (preg_match('/public static function getPages\(\): array\s*{([^}]*)}/s', $content, $m)) {
        $getPages = trim($m[1]);
    }

    // Determine which components are used in form schema
    $components = [];
    $componentClasses = [
        'TextInput', 'Textarea', 'Select', 'FileUpload', 'Toggle',
        'Grid', 'DatePicker', 'DateTimePicker', 'ColorPicker', 'KeyValue',
        'RichEditor', 'Placeholder', 'Section', 'Repeater', 'Hidden',
        'Checkbox', 'CheckboxList', 'Radio', 'TagsInput', 'MarkdownEditor',
        'CodeEditor', 'KeyValue', 'Wizard', 'Tabs', 'Fieldset', 'Group',
    ];

    foreach ($componentClasses as $comp) {
        if (strpos($formSchema, $comp.'::make') !== false ||
            strpos($formSchema, $comp.'::') !== false) {
            $components[] = $comp;
        }
    }

    // Determine which table columns are used
    $tableColumnClasses = [
        'TextColumn', 'ImageColumn', 'IconColumn', 'BadgeColumn',
        'ColorColumn', 'ProgressColumn', 'SummarizeColumn', 'ViewColumn',
    ];

    $tableColumnsUsed = [];
    foreach ($tableColumnClasses as $col) {
        if (strpos($tableColumns, $col.'::make') !== false) {
            $tableColumnsUsed[] = $col;
        }
    }

    // Check for other table features
    $usesFilters = ! empty($filters);
    $usesActions = ! empty($actions);
    $usesBulkActions = ! empty($bulkActions);
    $usesBuilder = strpos($content, 'Builder') !== false;
    $usesSet = strpos($content, 'Set') !== false && strpos($content, 'Utilities\\Set') === false;

    // Build imports
    $imports = [
        "use App\\Filament\\Resources\\{$className}\\Pages;",
        "use App\\Models\\{$model};",
        'use Filament\\Schemas\\Schema;',
    ];

    foreach ($components as $comp) {
        $imports[] = "use Filament\\Schemas\\Components\\{$comp};";
    }

    if ($usesSet) {
        $imports[] = 'use Filament\\Schemas\\Components\\Utilities\\Set;';
    }

    $imports[] = 'use Filament\\Resources\\Resource;';
    $imports[] = 'use Filament\\Tables;';

    foreach ($tableColumnsUsed as $col) {
        $imports[] = "use Filament\\Tables\\Columns\\{$col};";
    }

    $imports[] = 'use Filament\\Tables\\Table;';

    if ($usesBuilder) {
        $imports[] = 'use Illuminate\\Database\\Eloquent\\Builder;';
    }

    $importsStr = implode("\n", $imports);

    // Build form method
    $formMethod = "    public static function form(Schema \$schema): Schema\n    {\n        return \$schema\n            ->schema({$formSchema});\n    }";

    // Build table method
    $tableMethod = "    public static function table(Table \$table): Table\n    {\n        return \$table\n            ->columns({$tableColumns})";

    if ($defaultSort) {
        $tableMethod .= "\n            ->defaultSort({$defaultSort})";
    }

    if ($usesFilters) {
        $tableMethod .= "\n            ->filters({$filters})";
    }

    if ($usesActions) {
        $tableMethod .= "\n            ->actions({$actions})";
    }

    if ($usesBulkActions) {
        $tableMethod .= "\n            ->bulkActions({$bulkActions})";
    }

    $tableMethod .= ";\n    }";

    // Build getRelations
    $getRelationsMethod = "    public static function getRelations(): array\n    {\n        return [\n            {$getRelations}\n        ];\n    }";

    // Build getPages
    $getPagesMethod = "    public static function getPages(): array\n    {\n        return [\n            {$getPages}\n        ];\n    }";

    // Build full class
    $class = <<<PHP
<?php

namespace {$namespace};

{$importsStr}

class {$className} extends Resource
{
    protected static ?string \$model = {$model}::class;

    protected static string|\\BackedEnum|null \$navigationIcon = '{$icon}';

    protected static string|\\UnitEnum|null \$navigationGroup = '{$group}';
PHP;

    if ($sort !== null) {
        $class .= "\n    protected static ?int \$navigationSort = {$sort};\n";
    }

    $class .= "\n{$formMethod}\n\n{$tableMethod}\n\n{$getRelationsMethod}\n\n{$getPagesMethod}\n}";

    file_put_contents($file, $class);
    echo "Fixed: {$basename}\n";
}

echo "Done!\n";
