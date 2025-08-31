<?php

namespace App\Models\Events;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Package\WebDevTools\Laravel\Traits\ChecksIfJoined;
use Package\WebDevTools\Laravel\Traits\ValidatableModel;

class Crew extends Model
{
    use ValidatableModel, ChecksIfJoined;

    /**
     * The validation rules for a crew role's attributes.
     *
     * @var array
     */
    protected static $ValidationRules = [
        'user_id' => 'required|exists:users,id',
        'name' => 'nullable|required_if:core,1',
        'guest_name' => 'required',
    ];

    /**
     * The validation rules for a crew role's attributes.
     *
     * @var array
     */
    protected static $ValidationMessages = [
        'user_id.required' => 'Please select a member',
        'user_id.exists' => 'Please select a member',
        'name.required_if' => 'Please enter a role title',
        'guest_name.required' => 'Please enter the guest\'s name',
    ];

    /**
     * Disable timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'event_crew';

    /**
     * The attributes fillable by mass assignment.
     *
     * @var array
     */
    protected $fillable = ['event_id', 'user_id', 'name', 'em', 'confirmed', 'guest_name'];

    /**
     * Define the attributes that need type-casting.
     *
     * @var array
     */
    protected $casts = [
        'em' => 'boolean',
        'confirmed' => 'boolean',
    ];

    /**
     * The attributes that should be visible when converted to an array.
     *
     * @var array
     */
    protected $visible = ['id', 'user_id', 'name', 'em', 'confirmed', 'guest_name', 'core'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['core'];

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
    public function user()
    {
        return $this->belongsTo('App\Models\Users\User');
    }

    /**
     * Get event manager crew roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeEm(Builder $query)
    {
        $query->where('em', true);
    }

    /**
     * Get core crew roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeCore(Builder $query)
    {
        $query->member()->where('name', '<>', null);
    }

    /**
     * Get general crew roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeGeneral(Builder $query)
    {
        $query->member()->where('name', null);
    }

    /**
     * Get member crew roles (not guests).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeMember(Builder $query)
    {
        $query->where('user_id', '<>', null);
    }

    /**
     * Get guests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeGuest(Builder $query)
    {
        $query->whereNull('user_id');
    }

    /**
     * Filter to crew roles for a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\Users\User                $user
     */
    public function scopeForUser(Builder $query, User $user)
    {
        $query->where('user_id', $user->id);
    }

    /**
     * Order the crew by role name, the user name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeOrder(Builder $query)
    {
        if (!$this->alreadyJoined($query, 'users')) {
            $query->select('event_crew.*')->join('users', 'event_crew.user_id', '=', 'users.id');
        }
        $query->orderBy('event_crew.name', 'ASC')->orderBy('users.surname', 'ASC')->orderBy('users.forename', 'ASC');
    }

    /**
     * Test whether the crew role is for a guest user (non-registered).
     *
     * @return bool
     */
    public function isGuest()
    {
        return $this->user_id === null;
    }

    /**
     * Get the crew member's name.
     *
     * @return mixed
     */
    public function getCrewNameAttribute()
    {
        return $this->isGuest() ? $this->guest_name : $this->user->name;
    }

    /**
     * Get whether the crew role is a core crew.
     *
     * @return bool
     */
    public function getCoreAttribute()
    {
        return (int) ($this->name !== null);
    }
}
