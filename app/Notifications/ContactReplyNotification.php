<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

// ✅ Models
use App\Models\Contact;

// ✅ Helpers
use Illuminate\Support\Str;

class ContactReplyNotification extends Notification
{
    use Queueable;

    protected Contact $contact;

    /**
     * Create a new notification instance.
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'contact_reply',
            'title'   => 'Update on your inquiry',

            'message' => $this->contact->admin_notes
                ?? 'We received your query regarding "'
                    . Str::limit($this->contact->subject, 20)
                    . '". Our team has reviewed it and will contact you shortly.',

            'link'    => route('user.message.show', $this->contact->id),
        ];
    }
}
