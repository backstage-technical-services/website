<?php

namespace App\Models\Resources;

use bnjns\WebDevTools\Laravel\Traits\ValidatableModel;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use ValidatableModel;

    /**
     * Define the static rules for validating tags.
     *
     * @var array
     */
    protected static $ValidationRules = [
        'name' => 'required',
        'slug' => 'required|regex:/^[a-zA-Z0-9-]+$/|unique:resource_tags,slug',
    ];

    /**
     * Define the validation messages.
     *
     * @var array
     */
    protected static $ValidationMessages = [
        'name.required' => 'Please enter the tag name',
        'slug.required' => 'Please enter a slug',
        'slug.regex'    => 'The slug can only include letters, numbers and hyphens',
        'slug.unique'   => 'That slug is already in use',
    ];

    /**
     * Set the table name.
     *
     * @var string
     */
    protected $table = 'resource_tags';

    /**
     * Define the attributes that are fillable by mass assignment.
     *
     * @var array
     */
    public $fillable = [
        'name',
        'slug',
    ];

    /**
     * Disable timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Define the relationship through the pivot table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function resources()
    {
        return $this->belongsToMany('App\Models\Resources\Resource', 'resource_tag', 'resource_tag_id', 'resource_id');
    }
}
