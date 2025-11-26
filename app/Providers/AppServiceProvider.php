<?php

namespace App\Providers;

use App\Models\Qdb\QdbQuestion;
use App\Models\Appt\Appointment;
use App\Models\Edm\EdmVideoLike;
use App\Models\Gallery\GalImage;
use App\Models\Edm\EdmVideoComment;
use App\Observers\GalImageObserver;
use App\Models\Edm\EdmVideoFavorite;
use App\Observers\AppointmentObserver;
use App\Observers\QdbQuestionObserver;
use App\Observers\EdmVideoLikeObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\EdmVideoCommentObserver;
use App\Observers\EdmVideoFavoriteObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       EdmVideoLike::observe(EdmVideoLikeObserver::class);
        EdmVideoFavorite::observe(EdmVideoFavoriteObserver::class);
        EdmVideoComment::observe(EdmVideoCommentObserver::class);
         GalImage::observe(GalImageObserver::class);
         QdbQuestion::observe(QdbQuestionObserver::class);
         Appointment::observe(AppointmentObserver::class);
    }
}
