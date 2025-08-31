<?php

namespace App\Models\Training;

use App\Models\Training\Skills\Skill;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * The database table name.
     *
     * @var string
     */
    protected $table = 'training_categories';

    /**
     * The attributes fillable by mass assignment.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Define the relationship with the training skills.
     *
     * @return $this
     */
    public function skills()
    {
        return $this->hasMany(Skill::class, 'category_id')->orderBy('name', 'ASC');
    }
}
