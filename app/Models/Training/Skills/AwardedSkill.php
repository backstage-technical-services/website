<?php

namespace App\Models\Training\Skills;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;

class AwardedSkill extends Model
{
    /**
     * Define the table name.
     *
     * @var string
     */
    protected $table = 'training_awarded_skills';

    /**
     * The attributes fillable by mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'skill_id',
        'user_id',
        'level',
        'awarded_by',
    ];

    /**
     * Define the relationship with the skill's details.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function skill()
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    /**
     * Define the relationship with the user the skill is awarded to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship with the user who awarded the skill.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function awarder()
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    /**
     * Get the category using the related skill.
     *
     * @return \App\Models\Training\Category
     */
    public function getCategoryAttribute()
    {
        return $this->skill->category;
    }
}
