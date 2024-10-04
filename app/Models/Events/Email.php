<?php

namespace App\Models\Events;

use App\Mail\Events\CrewEmail;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Package\Notifications\Facades\Notify;
use Package\WebDevTools\Laravel\Traits\ValidatableModel;
use Package\WebDevTools\Traits\AccountsForTimezones;

class Email extends Model
{
    use ValidatableModel, AccountsForTimezones;

    /**
     * The validation rules for an event's attributes.
     *
     * @var array
     */
    protected static $ValidationRules = [
        'header' => 'required',
        'body'   => 'required',
        'crew'   => 'required|in:core,all',
    ];

    /**
     * The messages for the above validation rules.
     *
     * @var array
     */
    protected static $ValidationMessages = [
        'header.required' => 'Please enter the email subject',
        'body.required'   => 'Please enter the email message',
        'crew.required'   => 'Please select who to send the email to',
        'crew.in'         => 'Please select who to send the email to',
    ];

    /**
     * Set the table name.
     *
     * @var string
     */
    protected $table = 'event_emails';

    /**
     * The attributes fillable by mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'sender_id',
        'header',
        'body',
    ];

    /**
     * Define the attributes to correct the timezone for.
     *
     * @var array
     */
    protected $correct_tz = [
        'created_at',
    ];

    /**
     * Define the foreign key relationship with the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo('App\Models\Events\Event');
    }

    /**
     * Define the foreign key relationship with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo('App\Models\Users\User', 'sender_id');
    }

    /**
     * Send the email to the crew.
     *
     * @param string                 $crew
     * @param \App\Models\Users\User $sentFrom
     *
     * @return mixed
     */
    public function send(string $crew, User $sentFrom)
    {
        // Get the crew
        $crew_list = $this->event->crew();
        if ($crew == 'core') {
            $crew_list = $crew_list->core();
        }

        // Convert to collection of users
        $crew_list = $crew_list->get()->map(function ($c) {
            return $c->isGuest() ? null : $c->user;
        });

        if ($crew_list->count() > 0) {
            Mail::to($crew_list)->send(new CrewEmail($this, $sentFrom));

            Log::info("User {$sentFrom->id} emailed $crew crew for event {$this->event->id}");
            Notify::success('Email sent');
            return response()->json(['response' => 'Email sent']);
        } else {
            return response()->json(['error' => 'No crew to send email to', '__error' => true], 422);
        }
    }
}
