<?php

namespace App\Mail\Training\Skills;

use App\Mail\Mailable;
use App\Models\Training\Skills\Application;
use App\Models\Training\Skills\Skill;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ApplicationProcessed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Variable to store the application data.
     *
     * @var array
     */
    private $application;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Training\Skills\Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = [
            'user'               => $application->user->name,
            'user_email'         => $application->user->email,
            'level'              => Skill::LEVEL_NAMES[$application->applied_level],
            'skill'              => $application->skill->name,
            'awarder'            => $application->awarder->name,
            'awarder_forename'   => $application->awarder->forename,
            'awarded_level'      => $application->awarded_level,
            'awarded_level_text' => $application->awarded_level > 0 ? Skill::LEVEL_NAMES[$application->awarded_level] : null,
            'awarded_comment'    => $application->awarded_comment,
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->replyTo('training@bts-crew.com')
                    ->subject('Your Skill Application')
                    ->markdown('emails.training.skills.application.processed')
                    ->with($this->application);
    }
}
