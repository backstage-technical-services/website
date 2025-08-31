<?php

namespace App\Mail\Training\Skills;

use App\Mail\Mailable;
use App\Models\Training\Skills\Application;
use App\Models\Training\Skills\Skill;
use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Variable to store the application details.
     *
     * @var array
     */
    private $application;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Training\Skills\Skill       $skill
     * @param \App\Models\Training\Skills\Application $application
     * @param \App\Models\Users\User                  $user
     */
    public function __construct(Skill $skill, Application $application, User $user)
    {
        $this->application = [
            'skill' => $skill->name,
            'user' => $user->name,
            'user_email' => $user->email,
            'url' => route('training.skill.application.view', ['id' => $application->id]),
            'level' => Skill::LEVEL_NAMES[$application->applied_level],
            'reasoning' => $application->reasoning,
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->replyTo($this->application['user_email'], $this->application['user'])
            ->subject('Training Skill Application')
            ->markdown('emails.training.skills.application.submitted')
            ->with($this->application);
    }
}
