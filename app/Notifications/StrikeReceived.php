<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Auction;

class StrikeReceived extends Notification
{
    use Queueable;

    public $auction;

    /**
     * Create a new notification instance.
     */
    public function __construct(Auction $auction)
    {
        $this->auction = $auction;
    }

    /**
     * Get the notification's delivery channels.
     * We strictly use 'database' as requested (no email).
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $strikes = $notifiable->unpaid_strikes_count;
        $max = \App\Models\User::MAX_GLOBAL_STRIKES;

        return [
            'type'    => 'strike_received',
            'title'   => 'Unpaid Item Strike (' . $strikes . '/' . $max . ')',
            'message' => 'You received an unpaid item strike for "' . $this->auction->title . '". If you reach ' . $max . ' strikes, your account will be suspended. Contact Support if this is an error.',
            'url'     => route('contact') // Using standard contact route
        ];
    }
}
