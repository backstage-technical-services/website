<?php

namespace App\Mail\Training\Skills;

use App\Mail\Mailable;
use App\Models\Training\Skills\Proposal;
use App\Models\Training\Skills\Skill;
use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ProposalSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Variable to store the proposal details.
     *
     * @var array
     */
    private $proposal;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Training\Skills\Skill    $skill
     * @param \App\Models\Training\Skills\Proposal $proposal
     * @param \App\Models\Users\User               $user
     */
    public function __construct(Skill $skill, Proposal $proposal, User $user)
    {
        $this->proposal = [
            'skill'      => $skill->name,
            'user'       => $user->name,
            'user_email' => $user->email,
            'url'        => route('training.skill.proposal.view', ['id' => $proposal->id]),
            'level'      => Skill::LEVEL_NAMES[$proposal->proposed_level],
            'reasoning'  => $proposal->reasoning,
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->replyTo($this->proposal['user_email'], $this->proposal['user'])
                    ->subject('Training Skill Application')
                    ->markdown('emails.training.skills.proposal.submitted')
                    ->with($this->proposal);
    }
}
