<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Storage;

/** Demo photos are local-only files in storage/app/public/demo (see CREDITS.md there); records simply have no image without them. */
class DemoPhoto
{
    public static function path(string $name, string $ext = 'jpg'): ?string
    {
        return Storage::disk('public')->exists("demo/$name.$ext") ? "demo/$name.$ext" : null;
    }
}
