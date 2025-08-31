<?php

namespace App\Notifications\Events;

use App\Models\Events\Crew;
use App\Notifications\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserHasVolunteered extends Notification
{
    use Queueable;

    /**
     * Store the details of the event.
     * @var array
     */
    private $event;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Events\Crew $crew
     */
    public function __construct(Crew $crew)
    {
        $this->event = [
            'id' => $crew->event->id,
            'name' => $crew->event->name,
            'user_name' => $crew->user->name,
            'user_forename' => $crew->user->forename,
            'user_email' => $crew->user->email,
        ];
    }

    /**
     * Get the notification's delivery channels.
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return new MailMessage()
            ->subject('Crew volunteered for \'' . $this->event['name'] . '\'')
            ->replyTo($this->event['user_email'], $this->event['user_name'])
            ->greeting($notifiable->greeting())
            ->line(
                'This is to let you know that **' .
                    $this->event['user_name'] .
                    '** has volunteered to crew your event **' .
                    $this->event['name'] .
                    '**.',
            )
            ->line(
                'You can get in contact with ' .
                    $this->event['user_forename'] .
                    ' by replying to this email, or you can send an email to all your crew
        using the [event details page](' .
                    route('event.view', ['id' => $this->event['id'], 'tab' => 'emails']) .
                    ').',
            );
    }

    /**
     * Get the array representation of the notification.
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
                //
            ];
    }
}
