<?php

namespace App\Models\Equipment;

use Illuminate\Database\Eloquent\Model;

class BreakageImage extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['mime', 'equipment_breakage_id', 'extension'];

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
        $path = resource_path('breakages/') . $this->id . '.' . $this->extension;

        return $absolute ? base_path('/' . $path) : $path;
    }

    /**
     * Get the public route to the image.
     *
     * @return string
     */
    public function getImageRoute()
    {
        return route('equipment.repairs.images.stream', ['id' => $this->equipment_breakage_id, 'imageId' => $this->id]);
    }
}
