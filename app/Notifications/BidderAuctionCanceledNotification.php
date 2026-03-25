<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BidderAuctionCanceledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $auction;
    public $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct($auction, $reason)
    {
        $this->auction = $auction;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
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
        return [
            'auction_id' => $this->auction->id,
            'title' => 'Auction Cancelled',
            'message' => "An auction you bid on, '" . $this->auction->title . "', has been cancelled.",
            'reason' => $this->reason,
            'type' => 'bid_cancelled',
        ];
    }
}
