<?php

namespace App\Filament\Resources\GalImageResource\Pages;

use Filament\Actions;
use App\Models\Gallery\GalImage;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\GalImageResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class CreateGalImage extends CreateRecord
{
    protected static string $resource = GalImageResource::class;

    /**
     * On override la création pour créer UNE LIGNE PAR IMAGE uploadée.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $images = $data['images'] ?? [];
        unset($data['images']); // on enlève le champ virtuel

        $records = [];

        foreach ($images as $path) {
            // Récupère les métadonnées depuis S3
            $meta = $this->getImageMetaFromS3($path);

            $records[] = GalImage::create([
                'status'      => $data['status'] ?? 1,
                'gallery_id'  => $data['gallery_id'],
                'title'       => $data['title'] ?? null,
                'caption'     => $data['caption'] ?? null,
                'alt_text'    => $data['alt_text'] ?? null,
                'taken_at'    => $data['taken_at'] ?? null,
                'file_url'    => $path,                // clé S3
                'bytes'       => $meta['bytes'] ?? null,
                'width'       => $meta['width'] ?? null,
                'height'      => $meta['height'] ?? null,
                'created_by'  => Auth::id(),
            ]);
        }

        // On renvoie le dernier créé pour que Filament soit content
        return end($records);
    }

    /**
     * Récupère taille, largeur, hauteur à partir du fichier sur S3.
     */
    protected function getImageMetaFromS3(string $path): array
    {
        $disk = Storage::disk('s3');

        $bytes = null;
        $width = null;
        $height = null;

        try {
            // taille brute
            $bytes = $disk->size($path);

            // on télécharge temporairement le fichier pour lire les dimensions
            $tmp = tempnam(sys_get_temp_dir(), 'gal_');
            file_put_contents($tmp, $disk->get($path));

            if ($info = @getimagesize($tmp)) {
                $width  = $info[0] ?? null;
                $height = $info[1] ?? null;
            }

            @unlink($tmp);
        } catch (\Throwable $e) {
            // en cas d'erreur, on laisse les valeurs à null
        }

        return compact('bytes', 'width', 'height');
    }
}
