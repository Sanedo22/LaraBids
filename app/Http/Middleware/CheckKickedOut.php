<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckKickedOut
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $sessionId = session()->getId();
            $session = \DB::table('sessions')
                ->where('id', $sessionId)
                ->first();

            if ($session && $session->is_kicked_out) {
                // Delete the session record so it doesn't trigger again
                \DB::table('sessions')->where('id', $sessionId)->delete();

                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('kicked_out', 'You have been logged out because your session was terminated by another device.');
            }
        }

        return $next($request);
    }
}
