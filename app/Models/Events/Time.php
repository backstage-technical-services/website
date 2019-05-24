<?php

namespace App\Models\Events;

use bnjns\WebDevTools\Laravel\Traits\ValidatableModel;
use Illuminate\Database\Eloquent\Model;

class Time extends Model
{
    use ValidatableModel;

    /**
     * The validation rules for a event time's attributes.
     *
     * @var array
     */
    protected static $ValidationRules = [
        'name'  => 'required',
        'start' => 'required|date_format:Y-m-d H:i',
        'end'   => 'required|date_format:Y-m-d H:i|after:start',
    ];

    /**
     * The validation rules for a event time's attributes.
     *
     * @var array
     */
    protected static $ValidationMessages = [
        'name.required'     => 'Please enter a title for the time',
        'date.required'     => 'Please enter the date',
        'start.required'    => 'Please enter the start time',
        'start.date_format' => 'Please enter a valid time',
        'end.required'      => 'Please enter the end time',
        'end.date_format'   => 'Please enter a valid time',
        'end.after'         => 'It cannot end before it\'s begun!',
    ];

    /**
     * Set the table name.
     *
     * @var string
     */
    protected $table = 'event_times';

    /**
     * The attributes fillable by mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'name',
        'start',
        'end',
    ];

    /**
     * Define the additional fields that should be Carbon instances.
     *
     * @var array
     */
    protected $dates = [
        'start',
        'end',
    ];

    /**
     * The attributes that should be visible when converted to an array.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'name',
        'start',
        'end',
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
}