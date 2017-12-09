<?php

namespace App\Models\Events;

use App\Models\Users\User;
use bnjns\WebDevTools\Laravel\Traits\ChecksIfJoined;
use bnjns\WebDevTools\Laravel\Traits\CorrectsDistinctPagination;
use bnjns\WebDevTools\Laravel\Traits\ValidatableModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Event extends Model
{
    use ValidatableModel, ChecksIfJoined, CorrectsDistinctPagination {
        getValidationRules as traitValidationRules;
    }

    /**
     * Define the constants for the event type codes.
     */
    const TYPE_EVENT    = 1;
    const TYPE_TRAINING = 2;
    const TYPE_SOCIAL   = 3;
    const TYPE_MEETING  = 4;
    const TYPE_HIDDEN   = 5;
    const TYPE_BOOKING  = 6;

    const CREW_LIST_HIDDEN = -1;
    const CREW_LIST_CLOSED = 0;
    const CREW_LIST_OPEN   = 1;


    /**
     * Define the types of events.
     *
     * @var array
     */
    public static $Types = [
        self::TYPE_EVENT    => 'Event',
        self::TYPE_TRAINING => 'Training Session',
        self::TYPE_SOCIAL   => 'Social',
        self::TYPE_MEETING  => 'Meeting',
        self::TYPE_HIDDEN   => 'Hidden / General Info',
    ];

    /**
     * Define the shortened names for the event types.
     *
     * @var array
     */
    public static $TypesShort = [
        self::TYPE_EVENT    => 'event',
        self::TYPE_TRAINING => 'training',
        self::TYPE_SOCIAL   => 'social',
        self::TYPE_MEETING  => 'meeting',
        self::TYPE_HIDDEN   => 'hidden',
    ];

    /**
     * Define the HTML classes for each event type.
     *
     * @var array
     */
    public static $TypeClasses = [
        self::TYPE_EVENT    => 'event',
        self::TYPE_TRAINING => 'training',
        self::TYPE_SOCIAL   => 'social',
        self::TYPE_MEETING  => 'meeting',
        self::TYPE_HIDDEN   => 'bts',
        self::TYPE_BOOKING  => 'booking',
    ];

    /**
     * Define the client types.
     *
     * @var array
     */
    public static $Clients = [
        1 => 'Students\' Union',
        2 => 'University',
        3 => 'External',
    ];

    /**
     * Define the venue types.
     *
     * @var array
     */
    public static $VenueTypes = [
        1 => 'On-campus',
        2 => 'Off-campus',
    ];

    /**
     * Define the crew list statuses.
     *
     * @var array
     */
    public static $CrewListStatus = [
        self::CREW_LIST_HIDDEN => 'Hidden',
        self::CREW_LIST_CLOSED => 'Closed',
        self::CREW_LIST_OPEN   => 'Open',
    ];

    /**
     * Define the types of paperwork
     *
     * @var array
     */
    public static $Paperwork = [
        'risk_assessment' => 'Risk Assessment',
        'insurance'       => 'Insurance',
        'finance_em'      => 'EM Finance',
        'finance_treas'   => 'Treasurer Finance',
        'event_report'    => 'Event Report',
    ];

    /**
     * The validation rules for an event's attributes.
     *
     * @var array
     */
    protected static $ValidationRules = [
        'name'             => 'required',
        'em_id'            => 'nullable|exists:users,id',
        'description'      => 'required',
        'venue'            => 'required',
        'crew_list_status' => 'in:' . self::CREW_LIST_HIDDEN . ',' . self::CREW_LIST_CLOSED . ',' . self::CREW_LIST_OPEN,
        'date_start'       => 'required|date',
        'date_end'         => 'required|date|after:date_start',
        'time_start'       => 'required|date_format:H:i',
        'time_end'         => 'required|date_format:H:i|after:time_start',
    ];

    /**
     * The messages for the above validation rules.
     *
     * @var array
     */
    protected static $ValidationMessages = [
        'name.required'          => 'Please enter the event\'s name',
        'em_id.exists'           => 'Please select a valid user',
        'type.required'          => 'Please select an event type',
        'type.in'                => 'Please select a valid event type',
        'description.required'   => 'Please enter the event description',
        'venue.required'         => 'Please enter the venue',
        'venue_type.required'    => 'Please select the venue type',
        'venue_type.in'          => 'Please select a valid venue type',
        'client_type.required'   => 'Please select a client type',
        'client_type.in'         => 'Please select a valid client type',
        'crew_list_status.in'    => 'Please select a status for the crew list',
        'date_start.required'    => 'Please enter when this event starts',
        'date_start.date'        => 'Please enter a valid date',
        'date_end.required'      => 'Please enter when this event ends',
        'date_end.date'          => 'Please enter a valid date',
        'date_end.after'         => 'This must be after the start date',
        'time_start.required'    => 'Please enter the start time',
        'time_start.date_format' => 'Please enter a valid time',
        'time_end.required'      => 'Please enter the end time',
        'time_end.date_format'   => 'Please enter a valid time',
        'time_end.after'         => 'This must be after the start time',
    ];

    /**
     * Set the table name.
     *
     * @var string
     */
    protected $table = 'events';

    /**
     * The attributes fillable by mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'venue',
        'em_id',
        'description',
        'description_public',
        'type',
        'crew_list_status',
        'client_type',
        'venue_type',
        'paperwork',
    ];

    /**
     * Define any type-casting.
     *
     * @var array
     */
    protected $casts = [
        'paperwork' => 'array',
    ];

    /**
     * Variable to store the crew list count.
     *
     * @var array
     */
    private $crewListCount = null;

    /**
     * Override the default method to set some dynamic rules.
     *
     * @return array
     */
    public static function getValidationRules()
    {
        static::$ValidationRules['type']        = 'required|in:' . implode(',', array_keys(static::$Types));
        static::$ValidationRules['venue_type']  = 'required|in:' . implode(',', array_keys(static::$VenueTypes));
        static::$ValidationRules['client_type'] = 'required|in:' . implode(',', array_keys(static::$Clients));

        return call_user_func_array('self::traitValidationRules', func_get_args());
    }

    /**
     * Define the foreign key relationship with the TEM.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function em()
    {
        return $this->belongsTo('App\Models\Users\User', 'em_id');
    }

    /**
     * Define the foreign key relationship with the event times.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function times()
    {
        return $this->hasMany('App\Models\Events\Time')
                    ->orderBy('start', 'ASC');
    }

    /**
     * Define the foreign key relationship with the event crew.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function crew()
    {
        return $this->hasMany('App\Models\Events\Crew');
    }

    /**
     * Define the foreign key relationship with the event emails.
     *
     * @return mixed
     */
    public function emails()
    {
        return $this->hasMany('App\Models\Events\Email')
                    ->orderBy('created_at', 'DESC');
    }

    /**
     * Order the events by when they start, ascending (soonest first)
     *
     * @param $query
     */
    public function scopeOldestFirst(Builder $query)
    {
        $this->joinEventTimes($query)
             ->orderBy('event_times.end', 'ASC');
    }

    /**
     * Order the events by when they end, descending
     *
     * @param $query
     */
    public function scopeNewestFirst(Builder $query)
    {
        $this->joinEventTimes($query)
             ->orderBy('event_times.end', 'DESC');
    }

    /**
     * Only get events which lie on a particular date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon                        $date
     */
    public function scopeOnDate(Builder $query, Carbon $date)
    {
        // Set the start and end times
        $day_start = $date->setTime(0, 0, 0)->toDateTimeString();
        $day_end   = $date->setTime(23, 59, 59)->toDateTimeString();

        // Join the table
        $query = $this->joinEventTimes($query);

        // Set the query
        $query->where('event_times.end', '>=', $day_start)
              ->where('event_times.start', '<', $day_end)
              ->distinct();
    }

    /**
     * Get events that a user is crewing.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\Users\User                $user
     */
    public function scopeUserOnCrew(Builder $query, User $user)
    {
        $this->joinEventCrew($query)
             ->where('events.em_id', $user->id)
             ->orWhere('event_crew.user_id', $user->id)
             ->distinct();
    }

    /**
     * Get events that are in the past.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopePast(Builder $query)
    {
        $this->joinEventTimes($query)
             ->where('event_times.end', '<', Carbon::now()->setTime(0, 0, 0)->toDateTimeString())
             ->distinct();
    }

    /**
     * Get events that are in the future.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return void
     */
    public function scopeFuture(Builder $query)
    {
        $this->joinEventTimes($query)
             ->where('event_times.start', '>=', Carbon::now()->setTime(0, 0, 0)->toDateTimeString())
             ->distinct();
    }

    /**
     * Get events that are of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|array                             $type
     */
    public function scopeType(Builder $query, $type)
    {
        if (is_array($type)) {
            $query->whereIn('events.type', $type);
        } else {
            $query->where('events.type', $type);
        }
    }

    /**
     * Join a query with the event_times table.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function joinEventTimes(Builder $query)
    {
        if (!$this->alreadyJoined($query, 'event_times')) {
            $query->select('events.*')
                  ->join('event_times', 'events.id', '=', 'event_times.event_id');
        }
        return $query;
    }

    /**
     * Join a query with the event_crew table.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function joinEventCrew(Builder $query)
    {
        if (!$this->alreadyJoined($query, 'event_crew')) {
            $query->select('events.*')
                  ->join('event_crew', 'events.id', '=', 'event_crew.event_id');
        }
        return $query;
    }

    /**
     * Fix the issue of no EM not being set to null.
     *
     * @param $id
     */
    public function setEmIdAttribute($id)
    {
        $this->attributes['em_id'] = empty($id) ? null : (int)$id;
    }

    /**
     * Get the type as a human-readable string.
     *
     * @return string
     */
    public function getTypeStringAttribute()
    {
        return isset(self::$Types[$this->type]) ? self::$Types[$this->type] : self::$Types[self::TYPE_EVENT];
    }

    /**
     * Get the shortened name for the event type.
     *
     * @return mixed
     */
    public function getTypeShortAttribute()
    {
        return isset(self::$TypesShort[$this->type]) ? self::$TypesShort[$this->type] : self::$TypesShort[self::TYPE_EVENT];
    }

    /**
     * Get the HTML class of the type.
     *
     * @return string
     */
    public function getTypeClassAttribute()
    {
        return isset(self::$TypeClasses[$this->type]) ? self::$TypeClasses[$this->type] : self::$TypeClasses[self::TYPE_EVENT];
    }

    /**
     * Get the client type as a human-readable string.
     *
     * @return mixed
     */
    public function getClientAttribute()
    {
        return isset(self::$Clients[$this->client_type]) ? self::$Clients[$this->client_type] : self::$Clients[1];
    }

    /**
     * Get the event's start date.
     *
     * @return mixed
     */
    public function getStartAttribute()
    {
        return $this->times->first()->start;
    }

    /**
     * Get the event's start date as a string.
     *
     * @return mixed
     */
    public function getStartDateAttribute()
    {
        return $this->start->format('d/m/Y');
    }

    /**
     * Get the event's end date.
     *
     * @return mixed
     */
    public function getEndAttribute()
    {
        return $this->times->last()->end;
    }

    /**
     * Get the event's end date as a string.
     *
     * @return mixed
     */
    public function getEndDateAttribute()
    {
        return $this->end->format('d/m/Y');
    }

    /**
     * Get the crew list status as text.
     *
     * @return string
     */
    public function getCrewListStatusTextAttribute()
    {
        return static::$CrewListStatus[$this->crew_list_status];
    }

    /**
     * Get the core crew list.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCoreCrewAttribute()
    {
        return $this->crew()
                    ->core()
                    ->order()
                    ->get()
                    ->groupBy(function ($item) {
                        return $item->name;
                    });
    }

    /**
     * Get the general crew list.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getGeneralCrewAttribute()
    {
        return $this->crew()
                    ->general()
                    ->order()
                    ->get();
    }

    /**
     * Get the guest crew list.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getGuestsAttribute()
    {
        if (!$this->isSocial()) {
            return [];
        }

        return $this->crew()
                    ->guest()
                    ->orderBy('guest_name', 'ASC')
                    ->get();
    }

    /**
     * Get the full crew list
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCrewAttribute()
    {
        $crew = $this->core_crew;

        if ($this->countCrew('general') > 0) {
            $crew->put('General Crew', $this->general_crew);
        }

        if ($this->isSocial() && $this->countCrew('guests') > 0) {
            $crew->put('Guests', $this->guests);
        }

        return $crew;
    }

    /**
     * Set the value of a paperwork's status.
     *
     * @param $key
     * @param $value
     *
     * @return bool|void
     */
    public function setPaperwork($key, $value)
    {
        if (!isset(static::$Paperwork[$key])) {
            return;
        }

        return $this->update([
            'paperwork' => array_merge($this->paperwork, [$key => $value]),
        ]);
    }

    /**
     * Check if the entry is an event.
     *
     * @return bool
     */
    public function isEvent()
    {
        return $this->type == self::TYPE_EVENT;
    }

    /**
     * Check if the event is a training session.
     *
     * @return bool
     */
    public function isTraining()
    {
        return $this->type == self::TYPE_TRAINING;
    }

    /**
     * Check if the event is a social.
     *
     * @return bool
     */
    public function isSocial()
    {
        return $this->type == self::TYPE_SOCIAL;
    }

    /**
     * Check if crew 'attendance' needs to be tracked.
     *
     * @return bool
     */
    public function isTracked()
    {
        return $this->isTraining() || $this->isSocial();
    }

    /**
     * Test if the event's crew list is open.
     *
     * @return bool
     */
    public function isCrewListOpen()
    {
        return $this->crew_list_status == self::CREW_LIST_OPEN;
    }

    /**
     * Test if the event's crew list is closed.
     *
     * @return bool
     */
    public function isCrewListClosed()
    {
        return $this->crew_list_status == self::CREW_LIST_CLOSED;
    }

    /**
     * Test if the event's crew list is hidden.
     *
     * @return bool
     */
    public function isCrewListHidden()
    {
        return $this->crew_list_status == self::CREW_LIST_HIDDEN;
    }

    /**
     * Test if the event has an EM.
     *
     * @return bool
     */
    public function hasEM()
    {
        return $this->em_id !== null;
    }

    /**
     * Check if a given user is the EM.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function isTEM(User $user = null)
    {
        if ($user === null) {
            return false;
        }

        return $this->hasEM() && $this->em_id === $user->id;
    }

    /**
     * Check if a user is on the event crew.
     *
     * @param \App\Models\Users\User|null $user
     *
     * @return bool
     */
    public function userIsCrew(User $user = null)
    {
        return $user === null ? false : $user->isCrew($this);
    }

    /**
     * Check if a user has an EM role.
     *
     * @param \App\Models\Users\User|null $user
     *
     * @return bool|void
     */
    public function userHasEMRole(User $user = null)
    {
        return $user === null ? false : $user->hasEMRole($this);
    }

    /**
     * Count the amount of paperwork that is incomplete.
     *
     * @param null $complete
     *
     * @return int
     */
    public function countPaperwork($complete = null)
    {
        if ($complete === null) {
            return count($this->paperwork);
        } else {
            return count(array_filter($this->paperwork, function ($value) use ($complete) {
                return $complete ? $value : !$value;
            }));
        }
    }

    /**
     * Count the number of crew.
     *
     * @param null $key
     *
     * @return int
     */
    public function countCrew($key = null)
    {
        // Count the crew list if it hasn't already been counted
        if ($this->crewListCount === null) {
            $this->crewListCount = [
                'total'     => $this->crew()
                                    ->count(),
                'general'   => $this->crew()
                                    ->general()
                                    ->count(),
                'core'      => $this->crew()
                                    ->core()
                                    ->count(),
                'members'   => $this->crew()
                                    ->member()
                                    ->count(),
                'guests'    => $this->crew()
                                    ->guest()
                                    ->count(),
                'em'        => $this->crew()
                                    ->em()
                                    ->count(),
                'confirmed' => $this->crew()
                                    ->where('confirmed', true)
                                    ->count(),
            ];
        }

        // Return the count
        if ($key === null) {
            return $this->crewListCount['total'];
        } else if (isset($this->crewListCount[$key])) {
            return $this->crewListCount[$key];
        } else {
            return 0;
        }
    }

    /**
     * Send the request to add the event to the finance database.
     */
    public function addToFinanceDb()
    {
        if ($this->type == static::TYPE_EVENT && app()->environment('production')) {
            $fields       = [
                'data[Event][event_name]'  => $this->name,
                'data[Event][start_date]'  => Carbon::createFromFormat('d/m/Y', $this->start_date)->format('d-m-Y'),
                'data[Event][end_date]'    => Carbon::createFromFormat('d/m/Y', $this->end_date)->format('d-m-Y'),
                'data[Event][verified]'    => 0,
                'data[Event][bts_crew_id]' => $this->id,
            ];
            $field_string = http_build_query($fields);

            // Send the data using cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, config('bts.finance_db.url'));
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    /**
     * Test whether an event should be visible in the diary.
     *
     * @return bool
     */
    public function visibleInDiary()
    {
        $user = Auth::user();

        // Check the user permissions
        if (is_null($user) || !$user->isMember()) {
            return $this->isEvent();
        }

        // Check that the event type is allowed
        if (!in_array($this->type_short, $user->getDiaryPreference('event_types'))) {
            return false;
        }

        // Check that the crewing status is allowed
        if ($user->getDiaryPreference('crewing') == '*') {
            return true;
        } else {
            $crewing = $user->getDiaryPreference('crewing') == 'true';
            return $crewing === $this->userIsCrew($user);
        }
    }
}