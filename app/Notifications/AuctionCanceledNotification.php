<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionCanceledNotification extends Notification implements ShouldQueue
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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auction Cancelled')
            ->line("Your auction '{$this->auction->title}' has been cancelled by the admin.")
            ->line('Reason: ' . $this->reason)
            ->action('View My Auctions', route('user.my-auctions'))
            ->line('Thank you for using our application!');
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
            'message' => "Your auction '" . $this->auction->title . "' has been cancelled.",
            'reason' => $this->reason,
            'type' => 'auction_cancelled',
        ];
    }
}
