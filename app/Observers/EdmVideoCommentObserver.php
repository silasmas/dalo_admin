<?php

namespace App\Observers;

use App\Models\Edm\EdmVideoComment;

class EdmVideoCommentObserver
{
    public function created(EdmVideoComment $comment): void
    {
        $comment->video()->increment('comments_count');
    }

    public function deleted(EdmVideoComment $comment): void
    {
        $comment->video()->decrement('comments_count');
    }
}
