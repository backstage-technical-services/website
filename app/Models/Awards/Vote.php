<?php

namespace App\Models\Awards;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    /**
     * Set the table name.
     *
     * @var string
     */
    public $table = 'award_votes';

    /**
     * Define the attributes that are fillable by mass assignment.
     *
     * @var array
     */
    public $fillable = [
        'award_season_id',
        'nomination_id',
        'user_id',
    ];

    /**
     * Define the relationship with the award season.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function season()
    {
        return $this->belongsTo(Season::class, 'award_season_id', 'id');
    }

    /**
     * Define the relationship with the nomination.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nomination()
    {
        return $this->belongsTo(Nomination::class, 'nomination_id', 'id');
    }

    /**
     * Define the relationship with the user who cast the vote.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Add a scope for filtering by user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\Users\User                $user
     *
     * @return void
     */
    public function scopeByUser(Builder $query, User $user)
    {
        $query->where('user_id', $user->id);
    }

    /**
     * Add a scope for filtering by award season.
     *
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\Awards\Season             $season
     *
     * @return void
     */
    public function scopeForSeason(Builder $query, Season $season)
    {
        $query->where('award_season_id', $season->id);
    }
}
