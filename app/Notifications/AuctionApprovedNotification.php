<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Auction;

class AuctionApprovedNotification extends Notification
{
    use Queueable;

    protected $auction;

    /**
     * Create a new notification instance.
     */
    public function __construct(Auction $auction)
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
            'type' => 'auction_approved',
            'title' => 'Auction Approved',
            'message' => 'Your auction "' . $this->auction->title . '" has been approved and is now live.',
            'link' => route('auctions.show', $this->auction->id),
            'auction_id' => $this->auction->id,
        ];
    }
}
