<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class S3Path
{
    /**
     * Transforme une valeur venant de la BDD (URL complète ou key) en "key S3" (ex: products/aspirine.jpg)
     */
    public static function keyFromDb(?string $value, string $disk = 's3'): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Si ce n'est PAS une URL => on suppose que c'est déjà une key
        if (! preg_match('#^https?://#i', $value)) {
            return ltrim($value, '/');
        }

        // Si c'est une URL complète => on extrait seulement le "path"
        $path = parse_url($value, PHP_URL_PATH) ?? '';
        $key  = ltrim($path, '/');

        // Certains providers mettent le bucket dans le path : /proxydocfiles/products/aspirine.jpg
        $bucket = config("filesystems.disks.$disk.bucket");
        if ($bucket && str_starts_with($key, $bucket . '/')) {
            $key = substr($key, strlen($bucket) + 1);
        }

        return $key ?: null;
    }

    /**
     * Transforme une key S3 en URL complète (en s'appuyant sur Storage::url())
     */
    public static function urlFromKey(?string $key, string $disk = 's3'): ?string
    {
        if (empty($key)) {
            return null;
        }

        $key = ltrim($key, '/');

        return Storage::disk($disk)->url($key);
    }
}
