<?php

namespace App\Models\Training\Skills;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    /**
     * Disable the created/updated timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Define the table name.
     *
     * @var string
     */
    protected $table = 'training_skill_proposals';

    /**
     * The attributes fillable by mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'skill_id',
        'user_id',
        'proposed_level',
        'reasoning',
        'date',
        'awarded_level',
        'awarded_by',
        'awarded_comment',
        'awarded_date',
    ];

    /**
     * The attributes that should be Carbon instances.
     *
     * @var array
     */
    protected $dates = [
        'date',
        'awarded_date',
    ];

    /**
     * Define the relationship with the skill.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function skill()
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    /**
     * Define the relationship with the user who made the application.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship with the user who awarded the application.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function awarder()
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    /**
     * Add a scope for getting applications which haven't been awarded.
     *
     * @param $query
     */
    public function scopeNotAwarded($query)
    {
        return $query->whereNull('awarded_date');
    }

    /**
     * Add a scope for getting applications that have been awarded.
     *
     * @param $query
     */
    public function scopeAwarded($query)
    {
        return $query->whereNotNull('awarded_date');
    }

    /**
     * Check if a application has been awarded.
     *
     * @return bool
     */
    public function isAwarded()
    {
        return $this->awarded_date !== null;
    }
}
