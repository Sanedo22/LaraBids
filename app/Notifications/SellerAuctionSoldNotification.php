<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerAuctionSoldNotification extends Notification
{
    protected $auction;

    /**
     * Create a new notification instance.
     */
    public function __construct($auction)
    {
        $this->auction = $auction;
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
            'title' => 'Your item was sold!',
            'message' => 'Your item "' . $this->auction->title . '" was sold for ₹' . number_format($this->auction->current_price, 2) . '. A 5% platform commission (₹' . number_format($this->auction->current_price * 0.05, 2) . ') will be deducted from your final payout.',
            'amount' => $this->auction->current_price,
            'commission' => $this->auction->current_price * 0.05,
            'net_amount' => $this->auction->current_price * 0.95,
            'link' => route('user.my-auctions'),
        ];
    }
}
