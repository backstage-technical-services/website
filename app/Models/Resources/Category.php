<?php

namespace App\Models\Resources;

use bnjns\WebDevTools\Laravel\Traits\ValidatableModel;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use ValidatableModel {
        getValidationRules as traitValidationRules;
    }

    /**
     * Define the category flag constants.
     */
    const FLAG_NONE            = 0;
    const FLAG_RISK_ASSESSMENT = 1;
    const FLAG_EVENT_REPORT    = 2;
    const FLAG_MEETING_AGENDA  = 3;
    const FLAG_MEETING_MINUTES = 4;

    /**
     * Define the category flags
     *
     * @var array
     */
    const FLAGS = [
        self::FLAG_NONE            => 'None',
        self::FLAG_RISK_ASSESSMENT => 'Risk Assessment',
        self::FLAG_EVENT_REPORT    => 'Event Report',
        self::FLAG_MEETING_AGENDA  => 'Meeting Agenda',
        self::FLAG_MEETING_MINUTES => 'Meeting Minutes',
    ];

    /**
     * Define the static rules for validating categories.
     *
     * @var array
     */
    protected static $ValidationRules = [
        'name' => 'required',
        'slug' => 'required|regex:/^[a-zA-Z0-9-]+$/|unique:resource_categories,slug',
    ];

    /**
     * Define the validation messages.
     *
     * @var array
     */
    protected static $ValidationMessages = [
        'name.required' => 'Please enter the category name',
        'slug.required' => 'Please enter a slug',
        'slug.regex'    => 'The slug can only include letters, numbers and hyphens',
        'slug.unique'   => 'That slug is already in use',
        'flag.in'       => 'Please choose a valid type',
    ];

    /**
     * Define the attributes that are fillable by mass assignment.
     *
     * @var array
     */
    public $fillable = [
        'name',
        'slug',
        'flag',
    ];

    /**
     * Set the correct table name.
     *
     * @var string
     */
    public $table = 'resource_categories';

    /**
     * Disable timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Define the relationship with the category's resources.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function resources()
    {
        return $this->hasMany('App\Models\Resources\Resource', 'category_id', 'id');
    }

    /**
     * Override the default method to set some dynamic rules.
     *
     * @param array $args
     *
     * @return mixed
     */
    public static function getValidationRules(...$args)
    {
        static::$ValidationRules['flag'] = 'nullable|in:' . implode(',', array_keys(static::FLAGS));
        return self::traitValidationRules(...$args);
    }

    /**
     * Set the category's flag attribute.
     *
     * @param $flag
     */
    public function setFlagAttribute($flag)
    {
        $flag                     = isset(self::FLAGS[$flag]) ? $flag : self::FLAG_NONE;
        $this->attributes['flag'] = $flag === self::FLAG_NONE ? null : $flag;
    }

    /**
     * Get the category's flag attribute, ensuring it's an integer.
     *
     * @return int
     */
    public function getFlagAttribute()
    {
        return $this->attributes['flag'] === null ? self::FLAG_NONE : $this->attributes['flag'];
    }

    /**
     * Get the flag as a string.
     *
     * @return mixed|string
     */
    public function flag()
    {
        return isset(self::FLAGS[$this->flag]) ? self::FLAGS[$this->flag] : self::FLAGS[self::FLAG_NONE];
    }
}
