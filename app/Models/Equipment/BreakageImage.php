<?php

namespace App\Models\Equipment;

use Illuminate\Database\Eloquent\Model;

class BreakageImage extends Model
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
        6                     => 'Awaiting Repair',
        self::STATUS_RESOLVED => 'Resolved',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'filename',
        'mime',
        'report_id',
        'position_id'
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'equipment_breakages_images';

    /**
     * Define the foreign-key relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function report()
    {
        return $this->belongsTo('App\Models\Equipment\Breakage');
    }

    /**
     * Get the internal path to the image.
     *
     * @param bool $absolute
     *
     * @return string
     */
    public function getImagePath($absolute = false)
    {
        $path  = resource_path("breakages/") . $this->filename;

        return $absolute ? base_path('/' . $path) : $path;
    }

    /**
     * Get the public route to the image.
     *
     * @return string
     */
    public function getImageRoute()
    {
        return route('equipment.repairs.images.stream', ['id' => $this->report_id, 'imageId' => $this->position_id]);

    }
}
