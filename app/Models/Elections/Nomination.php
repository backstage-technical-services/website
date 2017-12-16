<?php

namespace App\Models\Elections;

use Illuminate\Database\Eloquent\Model;

class Nomination extends Model
{
    /**
     * Define the database table name.
     *
     * @var string
     */
    protected $table = 'election_nominations';

    /**
     * Disable timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Define the attributes that are mass-assignable.
     *
     * @var array
     */
    public $fillable = [
        'user_id',
        'position',
        'elected',
    ];

    /**
     * Define the relationship with the election.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function election()
    {
        return $this->belongsTo('App\Models\Elections\Election', 'election_id', 'id');
    }

    /**
     * Define the relationship with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Users\User', 'user_id', 'id');
    }

    /**
     * Get the URL of a nominee's manifesto.
     *
     * @return string
     */
    public function getManifestoPath()
    {
        return $this->election->getManifestoPath() . '/' . $this->getManifestoName();
    }

    /**
     * Get the name of the nominee's manifesto file.
     */
    public function getManifestoName()
    {
        return $this->id . '.pdf';
    }
}
