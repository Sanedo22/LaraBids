<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class PruneOldSessions
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $currentSessionId = session()->getId();

        // Get all other ACTIVE sessions for this user
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->where('is_kicked_out', false)
            ->orderBy('last_activity', 'desc')
            ->get();

        // If we already have 3 or more OTHER active sessions, we need to prune
        if ($sessions->count() >= 3) {
            $sessionsToKick = $sessions->slice(2);
            
            DB::table('sessions')
                ->whereIn('id', $sessionsToKick->pluck('id'))
                ->update(['is_kicked_out' => true]);
        }
    }
}
