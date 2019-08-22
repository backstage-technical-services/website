<?php

namespace App\Notifications\Events;

use App\Models\Events\Event;
use App\Notifications\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class HasBeenVolunteered extends Notification
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
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = [
            'id'          => $event->id,
            'name'        => $event->name,
            'has_em'      => $event->hasEM(),
            'em_name'     => $event->hasEM() ? $event->em->name : null,
            'em_forename' => $event->hasEM() ? $event->em->forename : null,
            'em_email'    => $event->hasEM() ? $event->em->email : null,
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
        $msg = (new MailMessage)->subject('Volunteered to crew event \'' . $this->event['name'] . '\'')
                                ->greeting($notifiable->greeting())
                                ->line('This is just to let you know that you\'ve been volunteered to crew the event ** ' . $this->event['name'] . ' **.');

        if($this->event['has_em']) {
            $msg = $msg->line('The Event Manager, ' . $this->event['em_name'] . ', should get in touch with the event details soon.')
                       ->action('View the Event', route('event.view', ['id' => $this->event['id']]))
                       ->line('If you have any questions, you can get in contact with ' . $this->event['em_forename'] . ' by replying to this email.')
                       ->replyTo($this->event['em_email'], $this->event['em_name']);
        } else {
            $msg = $msg->line('There isn\'t currently an Event Manager assigned, but when they are they should get in touch with the details.')
                       ->action('View the Event', route('event.view', ['id' => $this->event['id']]))
                       ->line('If you have any questions, you can get in contact with the Production Manager by replying to this email.')
                ->replyTo(config('bts.emails.events.volunteered'));
        }


        return $msg;


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
