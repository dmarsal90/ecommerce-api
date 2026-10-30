<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class MediaUrl
{
    /** Image columns store a path on the public disk (or an absolute URL); clients need a URL they can load. */
    public static function public(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return preg_match('#^https?://#', $path) ? $path : Storage::disk('public')->url($path);
    }
}
