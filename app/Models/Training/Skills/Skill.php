<?php

namespace App\Models\Training\Skills;

use App\Models\Training\Category;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Package\WebDevTools\Laravel\Traits\ValidatableModel;

class Skill extends Model
{
    use ValidatableModel;

    /**
     * Define the names of the skill levels.
     *
     * @var array
     */
    const LEVEL_NAMES = [
        1 => 'Level 1',
        2 => 'Level 2',
        3 => 'Level 3',
    ];

    /**
     * Define the validation rules.
     *
     * @var array
     */
    protected static $ValidationRules = [
        'name' => 'required',
        'category_id' => 'exists:training_categories,id',
        'description' => 'required',
        'requirements_level1' => 'required',
        'requirements_level2' => 'required',
        'requirements_level3' => 'required',
    ];

    /**
     * Define the validation messages.
     *
     * @var array
     */
    protected static $ValidationMessages = [
        'name.required' => 'Please enter the skill name',
        'category_id.exists' => 'Please choose a valid category',
        'description.required' => 'Please enter a brief description of the skill',
        'requirements_level1.required' => 'Please enter the requirements for Level 1',
        'requirements_level2.required' => 'Please enter the requirements for Level 2',
        'requirements_level3.required' => 'Please enter the requirements for Level 3',
    ];

    /**
     * Set the table name.
     *
     * @var string
     */
    protected $table = 'training_skills';

    /**
     * The attributes fillable by mass assignment.
     *
     * @var array
     */
    protected $fillable = ['name', 'category_id', 'description', 'level1', 'level2', 'level3'];

    /**
     * Define the relationship with the training category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the list of levels that are available.
     *
     * @return array
     */
    public function getAvailableAttribute()
    {
        return [
            'level1' => $this->isLevelAvailable(1),
            'level2' => $this->isLevelAvailable(2),
            'level3' => $this->isLevelAvailable(3),
        ];
    }

    /**
     * Get the list of users with this skill.
     *
     * @return mixed
     */
    public function getUsersAttribute()
    {
        // Get all the users
        $users = User::join('training_awarded_skills', 'users.id', '=', 'training_awarded_skills.user_id')
            ->where('training_awarded_skills.skill_id', $this->id)
            ->active()
            ->member()
            ->nameOrder()
            ->select('users.*', 'training_awarded_skills.level')
            ->get();

        // Sort the users by level
        $levels = [1 => [], 2 => [], 3 => []];
        foreach ($users as $user) {
            $levels[$user->level][] = $user;
        }

        return $levels;
    }

    /**
     * Get the name of the skill's category.
     *
     * @return string
     */
    public function getCategoryNameAttribute()
    {
        return $this->isCategorised() ? $this->category->name : 'Uncategorised';
    }

    /**
     * Test whether the skill is categorised.
     *
     * @return bool
     */
    public function isCategorised()
    {
        return $this->category_id !== null;
    }

    /**
     * Test whether a particular level is available.
     *
     * @param $level
     *
     * @return bool
     */
    public function isLevelAvailable($level)
    {
        return $this->{'level' . (int) $level} !== null;
    }
}
