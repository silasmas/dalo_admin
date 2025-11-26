<?php

namespace App\Observers;

use App\Models\Gallery\GalImage;
use App\Models\Gallery\GalGallery;

class GalImageObserver
{
    public function created(GalImage $image): void
    {
        $this->recount($image->gallery_id);
    }

    public function deleted(GalImage $image): void
    {
        $this->recount($image->gallery_id);
    }

    public function restored(GalImage $image): void
    {
        $this->recount($image->gallery_id);
    }

    protected function recount(?int $galleryId): void
    {
        if (! $galleryId) {
            return;
        }

        $count = GalImage::where('gallery_id', $galleryId)->whereNull('deleted_at')->count();

        GalGallery::where('id', $galleryId)->update([
            'images_count' => $count,
        ]);
    }
}
