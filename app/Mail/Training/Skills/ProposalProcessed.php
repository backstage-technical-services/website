<?php

namespace App\Mail\Training\Skills;

use App\Mail\Mailable;
use App\Models\Training\Skills\Proposal;
use App\Models\Training\Skills\Skill;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ProposalProcessed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Variable to store the proposal data.
     *
     * @var array
     */
    private $proposal;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Training\Skills\Proposal $proposal
     */
    public function __construct(Proposal $proposal)
    {
        $this->proposal = [
            'user'               => $proposal->user->name,
            'user_email'         => $proposal->user->email,
            'level'              => Skill::LEVEL_NAMES[$proposal->proposed_level],
            'skill'              => $proposal->skill->name,
            'awarder'            => $proposal->awarder->name,
            'awarder_forename'   => $proposal->awarder->forename,
            'awarded_level'      => $proposal->awarded_level,
            'awarded_level_text' => $proposal->awarded_level > 0 ? Skill::LEVEL_NAMES[$proposal->awarded_level] : null,
            'awarded_comment'    => $proposal->awarded_comment,
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
                    ->subject('Your Skill Proposal')
                    ->markdown('emails.training.skills.proposal.processed')
                    ->with($this->proposal);
    }
}
