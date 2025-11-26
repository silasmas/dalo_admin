<?php

namespace App\Observers;

use App\Models\Edm\EdmVideoLike;

class EdmVideoLikeObserver
{
    public function created(EdmVideoLike $like): void
    {
        $like->video()->increment('likes_count');
    }

    public function deleted(EdmVideoLike $like): void
    {
        // Pour soft delete on décrémente seulement si le like est encore compté
        $like->video()->decrement('likes_count');
    }
}
