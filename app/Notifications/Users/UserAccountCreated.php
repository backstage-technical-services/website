<?php

namespace App\Notifications\Users;

use App\Notifications\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserAccountCreated extends Notification
{
    use Queueable;

    private ?string $password;

    /**
     * Create a new notification instance.
     * @param $password
     */
    public function __construct(?string $password)
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
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return new MailMessage()
            ->subject('Your new Backstage account')
            ->replyTo(config('bts.emails.account.created'))
            ->greeting($notifiable->greeting())
            ->line(
                'This email is just to let you know that an account has just been created for you on the [Backstage Website](' .
                    route('home') .
                    ').',
            )
            ->line(
                match ($this->password) {
                    null => 'You can log in using your existing Backstage identity.',
                    default
                        => "Your temporary password is `{$this->password}`. You will be prompted to update this to something more memorable the first time you log in.",
                },
            )
            ->action('Log in', route('auth.login'))
            ->line('If you have any questions you can get in contact with the Secretary by replying to this email.');
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
