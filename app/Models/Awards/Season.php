<?php

namespace App\Models\Awards;

use bnjns\WebDevTools\Laravel\Traits\ValidatableModel;
use bnjns\WebDevTools\Traits\AccountsForTimezones;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Season extends Model
{
    use ValidatableModel, AccountsForTimezones {
        getValidationRules as traitValidationRules;
    }

    /**
     * Define the status constants.
     */
    const STATUS_NOMINATIONS = 1;
    const STATUS_VOTING      = 2;
    const STATUS_RESULTS     = 3;
    const STATUSES           = [
        self::STATUS_NOMINATIONS => 'Nominations Open',
        self::STATUS_VOTING      => 'Voting Open',
        self::STATUS_RESULTS     => 'Results Released',
    ];

    /**
     * Define the validation rules.
     *
     * @var array
     */
    public static $ValidationRules = [
        'name'   => 'required',
        'status' => 'nullable',
        'awards' => 'required|array|exists:awards,id',
    ];

    /**
     * Define the validation messages.
     *
     * @var array
     */
    public static $ValidationMessages = [
        'name.required'   => 'Please enter the award season name',
        'awards.required' => 'Please select which awards to include in this season',
        'awards.array'    => 'Please select which awards to include in this season',
        'awards.exists'   => 'Please select a valid award',
    ];

    /**
     * Set the table name.
     *
     * @var string
     */
    public $table = 'award_seasons';

    /**
     * Define the attributes that are fillable by mass assignment.
     *
     * @var array
     */
    public $fillable = [
        'name',
        'status',
    ];

    /**
     * @var array
     */
    public $appends = [
        'awards',
    ];

    /**
     * Override the boot method to hook into events.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::updating(function (Season $season) {
            // If changing to results released, determine award winners
            if ($season->original['status'] != self::STATUS_RESULTS && $season->attributes['status'] == self::STATUS_RESULTS) {
                $season->calculateWinners();
            }
            // If changing from voting to nominations or closed, wipe any votes
            if ($season->original['status'] == self::STATUS_VOTING &&
                ($season->attributes['status'] === null || $season->attributes['status'] == self::STATUS_NOMINATIONS)) {
                Vote::where('award_season_id', $season->id)
                    ->delete();
            }
        });
    }

    /**
     * Define the relationship with the awards.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function awards()
    {
        return $this->belongsToMany(Award::class, 'award_award_season', 'award_season_id', 'award_id');
    }

    /**
     * Define the relationship with the award nominations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function nominations()
    {
        return $this->hasMany(Nomination::class, 'award_season_id', 'id');
    }

    /**
     * Get the validation rules for the provided fields.
     *
     * @return array
     */
    public static function getValidationRules()
    {
        static::$ValidationRules['status'] .= '|in:' . implode(',', array_keys(self::STATUSES));
        return call_user_func_array('self::traitValidationRules', func_get_args());
    }

    /**
     * Check if nominations are open.
     *
     * @return bool
     */
    public function areNominationsOpen()
    {
        return $this->status === self::STATUS_NOMINATIONS;
    }

    /**
     * Check if voting is open.
     *
     * @return bool
     */
    public function isVotingOpen()
    {
        return $this->status === self::STATUS_VOTING;
    }

    /**
     * Check if results are released.
     *
     * @return bool
     */
    public function areResultsReleased()
    {
        return $this->status === self::STATUS_RESULTS;
    }

    /**
     * Get the status of the award season.
     *
     * @return string
     */
    public function getStatusTextAttribute()
    {
        if ($this->areNominationsOpen()) {
            return 'Nominations Open';
        } else if ($this->isVotingOpen()) {
            return 'Voting Open';
        } else if ($this->areResultsReleased()) {
            return 'Results Released';
        } else {
            return '';
        }
    }

    /**
     * Get a list of award IDs.
     *
     * @return array
     */
    public function getAwardsAttribute()
    {
        return $this->awards()->pluck('id')->toArray();
    }

    /**
     * Calculate the nominations that have won.
     *
     * @return void
     */
    private function calculateWinners()
    {
        // Set all nominations to have not won
        $this->nominations()
             ->update(['won' => false]);

        // Get all nominations, grouped by award
        $nominations = $this->nominations()
                            ->get()
                            ->groupBy(function ($nomination) {
                                return $nomination->award_id;
                            });

        // Get the total number of votes for each nomination
        $votes = DB::table('award_votes')
                   ->select(DB::raw('nomination_id, COUNT(*)'))
                   ->where('award_season_id', $this->id)
                   ->groupBy('nomination_id')
                   ->get()
                   ->mapWithKeys(function ($item) {
                       return [$item->nomination_id => $item->{'COUNT(*)'}];
                   });

        // Get the list of awards that have nominations
        $awards = $this->awards()
                       ->whereIn('id', $nominations->keys())
                       ->get();

        // For each award, create a list of nominations and their number of votes, and update the ones with the highest to have won.
        foreach ($awards as $award) {
            $award_votes = [];
            foreach ($nominations[$award->id] as $nomination) {
                $award_votes[$nomination->id] = isset($votes[$nomination->id]) ? $votes[$nomination->id] : 0;
            }

            $winners = array_filter($award_votes, function ($votes) use ($award_votes) {
                return $votes == max($award_votes) && $votes > 0;
            });
            Nomination::whereIn('id', array_keys($winners))
                      ->update(['won' => true]);
        }
    }
}
