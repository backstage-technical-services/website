<?php

namespace App\Models;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';

    protected $fillable = [
        'user_id',
        'ip_address',
        'action',
        'payload',
        'status',
    ];

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
        return $this->created_at->tzUser()->format('d/m/Y');
    }

    /**
     * Get the time as a string.
     *
     * @return mixed
     */
    public function getTimeAttribute()
    {
        return $this->created_at->tzUser()->format('g:i:s a');
    }
}