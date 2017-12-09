<?php

namespace App\Models\Equipment;

use Illuminate\Database\Eloquent\Model;

class Breakage extends Model
{
    /**
     * Define the 'resolved' and 'reported' statuses
     */
    const STATUS_RESOLVED = 0;
    const STATUS_REPORTED = 1;

    /**
     * Define the acceptable breakage statuses.
     *
     * @var array
     */
    public static $Status = [
        self::STATUS_REPORTED => 'Reported',
        2                     => 'Diagnosed',
        3                     => 'Awaiting Parts',
        4                     => 'Usable (Issue still exists)',
        5                     => 'Unrepairable',
        self::STATUS_RESOLVED => 'Resolved',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'location',
        'label',
        'description',
        'comment',
        'status',
        'user_id',
        'closed',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'equipment_breakages';

    /**
     * Define the foreign-key relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Users\User');
    }
}
