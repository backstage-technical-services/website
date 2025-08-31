<?php

namespace App\Models;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Package\WebDevTools\Traits\AccountsForTimezones;

class Log extends Model
{
    use AccountsForTimezones;

    protected $table = 'logs';

    protected $fillable = ['user_id', 'ip_address', 'action', 'payload', 'status'];

    protected $correct_tz = ['created_at'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Test whether the log entry was created by a guest.
     *
     * @return bool
     */
    public function isGuest()
    {
        return $this->user_id === null;
    }

    /**
     * Get the payload as a decoded value.
     *
     * @return mixed
     */
    public function getPayloadAttribute()
    {
        return json_decode($this->attributes['payload'], true);
    }

    /**
     * Get the date as a string.
     *
     * @return mixed
     */
    public function getDateAttribute()
    {
        return $this->created_at->format('d/m/Y');
    }

    /**
     * Get the time as a string.
     *
     * @return mixed
     */
    public function getTimeAttribute()
    {
        return $this->created_at->format('g:i:s a');
    }
}
