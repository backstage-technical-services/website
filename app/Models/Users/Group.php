<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'name',
    ];
    
    public $timestamps = false;

    /**
     * Define the relationship with the group's users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany('App\Models\Users\User', 'user_group_id', 'id');
    }
}
