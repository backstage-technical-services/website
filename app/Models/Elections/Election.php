<?php

namespace App\Models\Elections;

use App\Collection;
use bnjns\WebDevTools\Traits\AccountsForTimezones;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Election extends Model
{
    use AccountsForTimezones;

    /**
     * Define the types of elections.
     *
     * @var array
     */
    public static $Types = [
        1 => 'Full',
        2 => 'By-Election',
    ];

    /**
     * Define the database table name.
     *
     * @var string
     */
    protected $table = 'elections';

    /**
     * Define the attributes that should be Carbon instances.
     *
     * @var array
     */
    protected $dates = [
        'nominations_start',
        'nominations_end',
        'voting_start',
        'voting_end',
        'hustings_time',
    ];

    /**
     * Define the attributes that are mass-assignable.
     *
     * @var array
     */
    public $fillable = [
        'type',
        'bathstudent_id',
        'positions',
        'nominations_start',
        'nominations_end',
        'voting_start',
        'voting_end',
        'hustings_time',
        'hustings_location',
    ];

    /**
     * Define the attributes to correct the timezone for.
     *
     * @var array
     */
    protected $correct_tz = [
        'nominations_start',
        'nominations_end',
        'voting_start',
        'voting_end',
        'hustings_time',
    ];

    /**
     * Define the relationship with the election's nominations.
     *
     * @return HasMany
     */
    public function nominations()
    {
        return $this->hasMany('App\Models\Elections\Nomination', 'election_id', 'id');
    }

    /**
     * Create a string to use as the election's title.
     *
     * @return string
     */
    public function getTitleAttribute()
    {
        return ($this->voting_end->format('Y') . ' ' . ($this->isFull() ? 'Election' : 'By-Election'));
    }

    /**
     * Set the list of positions for the election,
     * allowing the parameter to be an array
     *
     * @param $positions
     */
    public function setPositionsAttribute($positions)
    {
        if (!is_string($positions)) {
            $positions = json_encode($positions);
        }
        $this->attributes['positions'] = $positions;
    }

    /**
     * When getting the positions, convert to an array
     *
     * @return array
     */
    public function getPositionsAttribute()
    {
        return json_decode($this->attributes['positions'], true);
    }

    /**
     * Get the base path for all manifestos for this election.
     *
     * @return string
     */
    public function getManifestoPath()
    {
        return base_path('resources/elections/' . $this->id);
    }

    /**
     * Get the string name of a position
     *
     * @param $index
     *
     * @return string
     */
    public function getPosition($index)
    {
        $positions = $this->positions;

        return isset($positions[$index]) ? $positions[$index] : null;
    }

    /**
     * Get a slugged string of an election position
     *
     * @param $index
     *
     * @return string
     */
    public function getPositionSlug($index)
    {
        $position = $this->getPosition($index);
    
        return $position ? Str::slug(strtolower($position)) : null;
    }

    /**
     * Get all of the nominations for a specified position.
     *
     * @param $positionIndex
     *
     * @return Collection
     */
    public function getNominations($positionIndex)
    {
        return $this->nominations()
                    ->select('election_nominations.*')
                    ->where('position', $positionIndex)
                    ->join('users', 'election_nominations.user_id', '=', 'users.id')
                    ->orderBy('users.surname', 'ASC')
                    ->orderBy('users.forename', 'ASC')
                    ->get();
    }

    /**
     * Test if this election is a full election.
     *
     * @return bool
     */
    public function isFull()
    {
        return $this->type == 1;
    }

    /**
     * Test if nominations are currently open.
     *
     * @return bool
     */
    public function isNominationsOpen()
    {
        $now = Carbon::now();

        return $now->gte($this->nominations_start)
               && $now->lt($this->voting_start);
    }

    /**
     * Test if voting is currently open.
     *
     * @return bool
     */
    public function isVotingOpen()
    {
        $now = Carbon::now();

        return $now->gte($this->voting_start)
               && $now->lte($this->voting_end);
    }

    /**
     * Test if the voting has been closed.
     * This is different to negating isVotingOpen()
     * as it also requires that the voting has
     * opened and then closed.
     *
     * @return bool
     */
    public function hasVotingClosed()
    {
        $now = Carbon::now();

        return $now->gt($this->voting_end);
    }
}
