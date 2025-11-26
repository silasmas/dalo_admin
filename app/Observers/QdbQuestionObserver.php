<?php

namespace App\Observers;

use App\Models\Qdb\QdbQuestion;
use Illuminate\Support\Str;

class QdbQuestionObserver
{
    public function creating(QdbQuestion $q): void
    {
        if (blank($q->slug)) {
            $q->slug = Str::slug(substr($q->title, 0, 190));
        }
    }

    public function updating(QdbQuestion $q): void
    {
        if ($q->isDirty('title') && blank($q->slug)) {
            $q->slug = Str::slug(substr($q->title, 0, 190));
        }
    }
}
