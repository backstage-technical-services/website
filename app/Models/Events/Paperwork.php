<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Model;

class Paperwork extends Model
{
    /**
     * The validation rules for a paperwork's attributes.
     *
     * @var array
     */
    protected static $ValidationRules = [
        'name'          => 'required',
        'template_link' => 'nullable',
    ];

    /**
     * The validation messages for a paperwork's attributes.
     *
     * @var array
     */
    protected static $ValidationMessages = [
        'name.required'     => 'Please enter a title for the paperwork',
    ];

    /**
     * Set the table name.
     *
     * @var string
     */
    protected $table = 'event_paperwork';

    /**
     * The attributes fillable by mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'template_link',
    ];

    /**
     * Define the foreign key relationship with the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo('App\Models\Events\Event')
                    ->withTimestamps();
    }
}
