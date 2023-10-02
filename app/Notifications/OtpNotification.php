<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OtpNotification extends Notification
{
    protected $subject;
    protected $recipient;
    protected $message;

    /**
     * Create a new notification instance.
     *
     * @param string $subject
     * @param string $recipient
     * @param string $message
     * @return void
     */
    public function __construct($subject, $recipient, $message)
    {
        $this->subject = $subject;
        $this->recipient = $recipient;
        $this->message = $message;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->to($this->recipient)
            ->line($this->message); // You can add more lines or formatting here if needed
    }

    // Other notification channels (e.g., toDatabase, toBroadcast) can be defined here if needed
}
