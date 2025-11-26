<?php

namespace App\Observers;

use App\Models\Edm\EdmVideoFavorite;

class EdmVideoFavoriteObserver
{
    public function created(EdmVideoFavorite $favorite): void
    {
        $favorite->video()->increment('favorites_count');
    }

    public function deleted(EdmVideoFavorite $favorite): void
    {
        $favorite->video()->decrement('favorites_count');
    }
}
