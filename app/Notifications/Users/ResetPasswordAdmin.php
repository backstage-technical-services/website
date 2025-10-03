<?php

namespace App\Notifications\Users;

use App\Notifications\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordAdmin extends Notification
{
    use Queueable;

    /**
     * Variable to store the user's password.
     * @var string
     */
    private $password;

    /**
     * Create a new notification instance.
     * @param $password
     */
    public function __construct($password)
    {
        $this->password = $password;
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
            ->subject('Your new password')
            ->line('Your password has been reset by an administrator to: ')
            ->line($this->password)
            ->line('You\'ll be asked to update this to something more memorable when you next log in.')
            ->action('Log in', route('auth.login'));
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
