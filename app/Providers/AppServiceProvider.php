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
        // Auto-finalize ended auctions (fallback so they end seamlessly without Cron)
        try {
            $endedAuctions = \App\Models\Auction::where('status', 'active')
                ->where('end_time', '<=', now())
                ->get();
            foreach ($endedAuctions as $auction) {
                try {
                    $auction->finalize();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to auto-finalize auction {$auction->id}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            // Ignore exception if DB not migrated or missing tables
        }

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
