<?php

namespace App\Models\Awards;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Nomination extends Model
{
    /**
     * Set the table name.
     *
     * @var string
     */
    public $table = 'award_nominations';

    /**
     * Define the attributes that are fillable by mass assignment.
     *
     * @var array
     */
    public $fillable = ['award_id', 'award_season_id', 'nominee', 'reason', 'approved', 'suggested_by'];

    /**
     * Define the relationship with the award.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function award()
    {
        return $this->belongsTo(Award::class, 'award_id', 'id');
    }

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
     * Define the relationship with the user who suggested the nomination.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function suggestor()
    {
        return $this->belongsTo(User::class, 'suggested_by', 'id');
    }

    /**
     * Define the relationship with the nomination's votes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes()
    {
        return $this->hasMany(Vote::class, 'nomination_id', 'id');
    }

    /**
     * A scope for getting approved nominations only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return void
     */
    public function scopeApproved(Builder $query)
    {
        $query->where('approved', true);
    }

    /**
     * Add a scope to order nominations by the award they're for.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return void
     */
    public function scopeOrdered(Builder $query)
    {
        $query
            ->select('award_nominations.*')
            ->join('awards', 'award_nominations.award_id', '=', 'awards.id')
            ->orderBy('awards.name', 'ASC')
            ->orderBy('award_nominations.nominee', 'ASC');
    }

    /**
     * Check whether the nomination is approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->approved;
    }

    /**
     * Check whether the nomination has won.
     *
     * @return bool
     */
    public function hasWon()
    {
        return $this->won;
    }

    /**
     * Test whether a user has voted for this nomination.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function userVotedFor(User $user)
    {
        return $this->votes()->byUser($user)->count() > 0;
    }
}
