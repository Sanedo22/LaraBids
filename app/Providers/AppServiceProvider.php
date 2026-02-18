<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Listeners\PruneOldSessions;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Implicitly grant "Super Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super admin') ? true : null;
        });

        // Pagination styling
        Paginator::useBootstrapFive();

        // Share notifications globally (user dashboard & layout)
        View::composer(
            [
                'website.layouts.dashboard',
                'website.user.dashboard',
                'website.user.notifications',
            ],
            function ($view) {
                if (auth()->check()) {
                    $view->with(
                        'sharedNotifications',
                        auth()->user()->notifications()->latest()->take(5)->get()
                    );

                    $view->with(
                        'unreadNotificationsCount',
                        auth()->user()->unreadNotifications()->count()
                    );
                }
            }
        );

        // Prune old sessions on login
        Event::listen(
            Login::class,
            PruneOldSessions::class
        );
    }
}
