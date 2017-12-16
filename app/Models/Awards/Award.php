<?php

namespace App\Models\Awards;

use App\Models\Users\User;
use bnjns\WebDevTools\Laravel\Traits\ValidatableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    use ValidatableModel;

    /**
     * Define the validation rules.
     *
     * @var array
     */
    public static $ValidationRules = [
        'name'        => 'required',
        'description' => 'required',
        'recurring'   => 'required|boolean',
    ];

    /**
     * Define the validation messages.
     *
     * @var array
     */
    public static $ValidationMessages = [
        'name.required'        => 'Please enter the award name',
        'description.required' => 'Please enter a description',
        'recurring.required'   => 'Please select whether this should recur every year',
        'recurring.boolean'    => 'Please select a valid option',
    ];

    /**
     * Set the table name.
     *
     * @var string
     */
    public $table = 'awards';

    /**
     * Define the attributes that are fillable by mass assignment.
     *
     * @var array
     */
    public $fillable = [
        'name',
        'description',
        'suggested_by',
        'recurring',
    ];

    /**
     * Define the relationship with the user who suggested the award.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function suggestor()
    {
        return $this->belongsTo(User::class, 'suggested_by', 'id');
    }

    /**
     * Define the relationship with any nominations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function nominations()
    {
        return $this->hasMany(Nomination::class, 'award_id', 'id');
    }

    /**
     * The scope for getting approved awards.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeApproved(Builder $query)
    {
        $query->whereNull('suggested_by');
    }

    /**
     * The scope for getting un-approved (have been suggested) awards.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeSuggested(Builder $query)
    {
        $query->whereNotNull('suggested_by');
    }

    /**
     * Define the relationship with approved award nominations.
     *
     * @param $seasonId
     *
     * @return mixed
     */
    public function approvedSeasonNominations($seasonId)
    {
        return $this->nominations()
                    ->approved()
                    ->where('award_nominations.award_season_id', $seasonId);
    }

    /**
     * Check if the award has any approved nominations for a season
     *
     * @param $seasonId
     *
     * @return bool
     */
    public function hasApprovedNominations($seasonId)
    {
        return $this->approvedSeasonNominations($seasonId)
                    ->count() > 0;
    }

    /**
     * Get the list of approved nominations for an award / award season.
     *
     * @param $seasonId
     *
     * @return mixed
     */
    public function getApprovedNominations($seasonId)
    {
        return $this->approvedSeasonNominations($seasonId)
                    ->get();
    }

    /**
     * Check if the award has been approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->suggested_by === null;
    }

    /**
     * Check if the award is recurring.
     *
     * @return bool
     */
    public function isRecurring()
    {
        return $this->recurring;
    }

    /**
     * Get any votes for this award in a given season.
     *
     * @param \App\Models\Awards\Season $season
     *
     * @return $this
     */
    public function votes(Season $season)
    {
        return Vote::select('award_votes.*')
                   ->where('award_votes.award_season_id', $season->id)
                   ->join('award_nominations', 'award_votes.nomination_id', '=', 'award_nominations.id')
                   ->where('award_id', $this->id);
    }

    /**
     * Test whether a user has already voted for this award in a given season.
     *
     * @param \App\Models\Awards\Season $season
     * @param \App\Models\Users\User    $user
     *
     * @return bool
     */
    public function userHasVoted(Season $season, User $user)
    {
        return $this->votes($season)
                    ->where('award_votes.user_id', $user->id)
                    ->count() > 0;
    }
}
