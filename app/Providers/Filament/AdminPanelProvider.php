<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // 1) Filament doit utiliser le guard "admin"
           ->authGuard('admin')
            // Option 1 : garde l’accès via closure
        //    ->auth(fn () => auth('admin')->check())
             // 2) Laisse Filament gérer l'auth avec SON middleware
           // ->authMiddleware([
           //     \Filament\Http\Middleware\Authenticate::class, // ✅ pas d'argument ":admin"
           // ])
                 ->colors([
               // Marque (or du texte et du ruban)
            'primary' => Color::hex('#EED242'),

            // Sémantiques adaptées au logo
            'warning' => Color::hex('#EED242'), // or (avertissements)
            'danger'  => Color::hex('#6A2712'), // acajou/bois sombre (marteau/plaquette)
            'info'    => Color::hex('#CBC5BF'), // acier/argent (lame)
             'gray'    => Color::Zinc, // ✅ palette complète 50..950
                ])
                // optionnel : variantes dark (un or plus “mat” + acajou plus sombre)
                // ->darkMode(function ($panel) {
                //     $panel->colors([
                //         'primary' => Color::hex('#BCA82E'), // or assombri
                //         'danger'  => Color::hex('#4A170D'), // acajou sombre
                //         'info'    => Color::hex('#A7A5A2'), // acier plus neutre
                //     ]);
                // })
             ->passwordReset()
            ->emailVerification()
        // ->profile(EditProfile::class)
        // ->profile(isSimple: false)
        // ->pages([
        //     EditProfile::class, // ✅ ta page profil sur-mesure
        // ])
            // ->colors([
            //     'primary' => "#13A4D3",
            //     // 'primary' => Color::Amber,
            // ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->unsavedChangesAlerts()
            ->brandName('Dashboard Dalo Ministries')
            // ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandLogo(asset('assets/images/log.jpg'))
            ->brandLogoHeight(fn() => Auth::check() ? '3rem' : '5rem')
            ->favicon(asset('assets/images/logo.png'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])

            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                \App\Filament\Resources\ShopOrderResource\Widgets\ShopOrderStats::class,
        \App\Filament\Resources\QuestionResource\Widgets\QuestionStats::class,
        \App\Filament\Resources\GalImageResource\Widgets\GalleryStats::class,
        \App\Filament\Resources\EdmVideoResource\Widgets\EdmVideoStats::class,
        \App\Filament\Resources\DonDonationResource\Widgets\DonDonationStats::class,
        \App\Filament\Resources\AppointmentResource\Widgets\AppointmentStats::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->plugins([
            EasyFooterPlugin::make()->withFooterPosition('footer')
            // ->withLogo(asset('assets/images/logo.png'), 'https://daloministries.com')
            ->withLoadTime('Cette page a été chargée dans')
                ->withLinks([
                    ['title' => 'A propos', 'url' => 'https://daloministries.com/about'],
                    ['title' => 'Privacy Policy', 'url' => 'https://daloministries.com/privacy-policy'],
                    ['title' => 'Design by SDev', 'url' => 'https://silasmas.com'],
                ])->withBorder(),
                  ]);
                }
}
