<?php

namespace App\Models\Users;

use App\Models\Events\Event;
use App\Models\Training\Category;
use App\Models\Training\Skills\Application;
use App\Models\Training\Skills\AwardedSkill;
use App\Models\Training\Skills\Skill;
use App\Notifications\Auth\ResetPassword;
use App\Notifications\Users\UserAccountCreated;
use bnjns\LaravelNotifications\Facades\Notify;
use bnjns\WebDevTools\Laravel\Traits\CorrectsDistinctPagination;
use bnjns\WebDevTools\Laravel\Traits\ValidatableModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class User extends Authenticatable
{
    use Notifiable, ValidatableModel, CorrectsDistinctPagination;

    /**
     * Define the user account types.
     *
     * @var array
     */
    public static $AccountTypes = [
        'member'      => 'Standard Member',
        'committee'   => 'Committee',
        'associate'   => 'Associate',
        'staff'       => 'SU / Staff',
        'super_admin' => 'Super Admin',
    ];

    /**
     * Define the default validation rules.
     *
     * @var array
     */
    public static $ValidationRules = [
        'name'         => 'required|name',
        'nickname'     => 'nullable|regex:/^[a-zA-Z _\.]+$/',
        'username'     => 'required|regex:/^[a-zA-Z0-9_\.]+$/|unique:users,username',
        'email'        => 'required|email|unique:users,email',
        'phone'        => 'nullable|phone',
        'dob'          => 'nullable|date_format:Y-m-d|regex:/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/',
        'show_email'   => 'sometimes|boolean',
        'show_phone'   => 'sometimes|boolean',
        'show_address' => 'sometimes|boolean',
        'show_age'     => 'sometimes|boolean',
        'type'         => 'required|in:member,committee,associate,staff,super_admin',
    ];

    /**
     * Define the default validation messages.
     *
     * @var array
     */
    public static $ValidationMessages = [
        'name.required'     => 'Please enter your name',
        'name.name'         => 'Please enter your forename and surname',
        'nickname.regex'    => 'Please just use letters',
        'username.required' => 'Please enter their BUCS username',
        'username.regex'    => 'Please use only letters and numbers',
        'username.unique'   => 'A user with that username already exists',
        'email.required'    => 'Please enter your email address',
        'email.email'       => 'Please enter a valid email address',
        'email.unique'      => 'That email address is already in use by another user',
        'phone.phone'       => 'Please enter a valid phone number',
        'dob.date_format'   => 'Please enter your DOB in the format YYYY-MM-DD',
        'dob.regex'         => 'Please enter your DOB in the format YYYY-MM-DD',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     * Some of these are "pseudo-attributes" as they don't exist
     * in the database but are used to add functionality
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'forename',
        'surname',
        'name', // Pseudo
        'nickname',
        'email',
        'password',
        'status',
        'phone',
        'address',
        'tool_colours',
        'dob',
        'show_email',
        'show_phone',
        'show_address',
        'show_age',
        'user_group_id',
        'type', // Pseudo
        'diary_preferences',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Define the attributes which should be provided as Carbon dates.
     *
     * @var array
     */
    protected $dates = [
        'dob',
    ];

    /**
     * Define variable types to cast some attributes to.
     *
     * @var array
     */
    protected $casts = [
        'show_email'        => 'boolean',
        'show_phone'        => 'boolean',
        'show_address'      => 'boolean',
        'show_age'          => 'boolean',
        'diary_preferences' => 'array',
    ];

    /**
     * Override the default ::create method to automatically assign some attributes.
     * This also automatically sets up the role and sends the new user an email.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public static function create(array $attributes = [])
    {
        // Set up the default parameters
        $password                        = Str::random(15);
        $attributes['email']             = $attributes['username'] . '@bath.ac.uk';
        $attributes['password']          = bcrypt($password);
        $attributes['status']            = true;
        $attributes['diary_preferences'] = ['event_types'  => ["event","training","social","meeting","hidden"],
                                            'crewing'      => '*'];

        // Create the new user
        $user = new User($attributes);
        $user->save();
        $user->type = $attributes['type'];

        // Send the email
        $user->notify(new UserAccountCreated($password));

        return $user;
    }

    /**
     * Define the relationship with the user's group.
     *
     * @return BelongsTo
     */
    public function group()
    {
        return $this->belongsTo('App\Models\Users\Group', 'user_group_id', 'id');
    }

    /**
     * Define the pages foreign key link.
     *
     * @return HasMany
     */
    public function pages()
    {
        return $this->hasMany('App\Models\Page');
    }

    /**
     * Define the relationship with the events.
     *
     * @return HasMany
     */
    public function events()
    {
        $relation = $this->hasMany('App\Models\Events\Event', 'id', 'id');

        $query = DB::table('events')
                   ->select('events.*')
                   ->leftJoin('event_crew', 'events.id', '=', 'event_crew.event_id')
                   ->join('event_times', 'events.id', '=', 'event_times.event_id')
                   ->where('events.em_id', $this->id)
                   ->orWhere('event_crew.user_id', $this->id)
                   ->distinct()
                   ->orderBy('event_times.end', 'DESC');

        $relation->getQuery()->setQuery($query);

        return $relation;
    }

    /**
     * Define the relationship with the user's skills.
     *
     * @return HasMany
     */
    public function skills()
    {
        return $this->hasMany(AwardedSkill::class);
    }

    /**
     * Add a scope for only getting active accounts.
     *
     * @param $query
     */
    public function scopeActive($query)
    {
        $query->where('status', true);
    }

    /**
     * Add a scope for only getting archived accounts.
     *
     * @param $query
     */
    public function scopeArchived($query)
    {
        $query->where('status', false);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string $token
     *
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Add a scope for only getting BTS member accounts.
     *
     * @param $query
     */
    public function scopeMember($query)
    {
        $query->select('users.*')
              ->join('user_groups', 'users.user_group_id', '=', 'user_groups.id')
              ->whereIn('user_groups.name', ['member', 'committee', 'associate']);
    }

    /**
     * Add a scope for getting users in a specific group.
     *
     * @param $query
     * @param $groupName
     */
    public function scopeInGroup($query, $groupName)
    {
        $query->select('users.*')
              ->join('user_groups', 'users.user_group_id', '=', 'user_groups.id')
              ->where('user_groups.name', $groupName);
    }

    /**
     * Add a scope for getting users that are signed up to an event.
     *
     * @param                          $query
     * @param Event                    $event
     */
    public function scopeCrewingEvent($query, Event $event)
    {
        $query->select('users.*')
              ->join('event_crew', 'users.id', '=', 'event_crew.user_id')
              ->where('event_crew.event_id', $event->id);
    }

    /**
     * Add a scope for getting users which are not signed up to an event.
     *
     * @param                          $query
     * @param Event                    $event
     */
    public function scopeNotCrewingEvent($query, Event $event)
    {
        $query->select('users.*')
              ->whereNotIn('users.id', self::crewingEvent($event)->lists('id'))
              ->whereNotIn('users.id', self::select('users.*')
                                           ->join('events', 'users.id', '=', 'events.em_id')
                                           ->where('events.id', $event->id)
                                           ->lists('id'));
    }

    /**
     * Add a scope to order the users by their name.
     *
     * @param $query
     */
    public function scopeNameOrder($query)
    {
        $query->orderBy('surname', 'ASC')
              ->orderBy('forename', 'ASC');
    }

    /**
     * Add a scope to get the results and produce an array suitable for <select> elements.
     *
     * @param $query
     *
     * @return array
     */
    public function scopeGetSelect($query)
    {
        $results           = $query->get();
        $results_formatted = [];
        foreach ($results as $result) {
            $results_formatted[$result->id] = sprintf("%s (%s)", $result->name, $result->username);
        }

        return $results_formatted;
    }

    /**
     * Add a scope to allow searching for users by their name, surname, nickname, username and email.
     *
     * @param $query
     * @param $term
     */
    public function scopeSearch($query, $term)
    {
        if (stripos($term, ' ')) {
            $query->where(function ($query) use ($term) {
                $query->where('forename', 'LIKE', '%' . substr($term, 0, stripos($term, ' ')) . '%')
                      ->where('surname', 'LIKE', '%' . substr($term, stripos($term, ' ') + 1) . '%')
                      ->orWhere('nickname', 'LIKE', '%' . $term . '%');
            });
        } else {
            $query->where(function ($query) use ($term) {
                $query->where('username', 'LIKE', '%' . $term . '%')
                      ->orWhere('nickname', 'LIKE', '%' . $term . '%')
                      ->orWhere('forename', 'LIKE', '%' . $term . '%')
                      ->orWhere('surname', 'LIKE', '%' . $term . '%')
                      ->orWhere('email', 'LIKE', '%' . $term . '%');
            });
        }
    }

    /**
     * Check if this user is the same as the active user.
     *
     * @return bool
     */
    public function isActiveUser()
    {
        return Auth::check() && $this->id && $this->id === Auth::id();
    }

    /**
     * Test if the user is a BTS member.
     *
     * @return bool
     */
    public function isMember()
    {
        return in_array($this->group->name, ['member', 'committee', 'associate']);
    }

    /**
     * Test if the user is a committee member.
     *
     * @return bool
     */
    public function isCommittee()
    {
        return $this->group->name == 'committee';
    }

    /**
     * Test if the user is an associate.
     *
     * @return bool
     */
    public function isAssociate()
    {
        return $this->group->name == 'associate';
    }

    /**
     * Test if the user is a staff member.
     *
     * @return bool
     */
    public function isStaff()
    {
        return $this->group->name == 'staff';
    }

    /**
     * Test if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isCommittee() || $this->group->name == 'super_admin';
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->forename . ' ' . $this->surname;
    }

    /**
     * Allow setting the forename and surname as a "name".
     *
     * @param $value
     *
     * @return $this
     */
    public function setNameAttribute($value)
    {
        list($this->forename, $this->surname) = explode(' ', $value);

        return $this;
    }

    /**
     * Get the user's forename.
     *
     * @return string
     */
    public function getForenameAttribute()
    {
        return ucfirst($this->attributes['forename']);
    }

    /**
     * Get the user's surname.
     *
     * @return string
     */
    public function getSurnameAttribute()
    {
        return ucfirst($this->attributes['surname']);
    }

    /**
     * @param string $after
     *
     * @return string
     */
    public function getPossessiveName($after = '')
    {
        return $this->name . "'" . (substr($this->name, -1) == 's' ? '' : 's') . ' ' . $after;
    }

    /**
     * Get the user's role as an "account type".
     *
     * @return int
     */
    public function getTypeAttribute()
    {
        return $this->user_group_id;
    }

    /**
     * Set the user's role from an "account type".
     *
     * @param $value
     *
     * @return bool
     */
    public function setTypeAttribute($value)
    {
        if (!is_int($value)) {
            $value = Group::where('name', (string)$value)->first()->id;
        }
        return $this->update(['user_group_id' => $value]);
    }

    /**
     * Get the user's account type.
     *
     * @return string
     */
    public function getAccountTypeAttribute()
    {
        if (!$this->status) {
            return 'Archived';
        } else if ($this->group->name == 'staff') {
            return 'Staff';
        } else if ($this->group->name == 'associate') {
            return 'Associate';
        } else if ($this->group->name == 'committee') {
            return 'Committee';
        } else if ($this->group->name == 'super_admin') {
            return 'Admin';
        } else {
            return 'Member';
        }
    }

    /**
     * Parse the tool colours into a HTML string.
     *
     * @return string
     */
    public function getToolColoursParsedAttribute()
    {
        // Initialise
        $toolColours = strtolower($this->tool_colours);
        $toolColours = str_replace(['and', ';', '&', ','], ' ', $toolColours);
        $toolColours = trim(preg_replace("/\s+/", ' ', $toolColours));
        $recognised  = ["red", "blue", "green", "yellow", "white", "black", "brown", "pink", "purple", "grey", "orange", "earth", "rainbow", "turquoise"];

        if (!empty($toolColours)) {
            // Look for initials
            $initials = null;
            if (preg_match("/(?:with)?\s*initials(\s*\(?([a-z0-9]+)\)?)?/i", $toolColours, $matches)) {
                $initials    = isset($matches[2]) ? $matches[2] : (substr($this->forename, 0, 1) . substr($this->surname, 0, 1));
                $toolColours = preg_replace("/with initials(\s*\(?[a-z0-9]+\)?)?/i", '', $toolColours);
            }

            // Look for colour entries
            $tool_colours = [];
            $title        = '';
            preg_match_all("/(light|fluorescent)?\s*([a-z]+)(\s*\([a-z+\s]+\))?/i", $toolColours, $matches);
            foreach ($matches[0] as $i => $full_colour) {
                if (!in_array($matches[2][$i], $recognised)) {
                    return $this->tool_colours;
                }

                // Add support for striped colours
                $shape   = 'wrench';
                $colours = [$matches[2][$i]];
                switch ($matches[2][$i]) {
                    case 'earth':
                        $colours = ['yellow', 'green'];
                        break;
                    case 'rainbow':
                        $colours = ['red', 'orange', 'yellow', 'green', 'blue', 'purple'];
                        break;
                    default:
                }

                // Build the html entry
                $title            .= ($matches[1][$i] ? (ucfirst($matches[1][$i] . ' ')) : '') . ucfirst($matches[2][$i]) . ', ';
                $tool_colours[$i] = '<span class="tool-colour' . (count($colours) > 1 ? ' striped' : '') . '">';
                for ($j = count($colours) - 1; $j >= 0; $j--) {
                    $tool_colours[$i] .= '<span class="fa fa-' . $shape . ' ' . trim($colours[$j] . ' ' . ($matches[1][$i] ?: '')) . '"></span>';
                }
                $tool_colours[$i] .= '</span>';
            }

            // End
            $tool_html =
                '<span class="tool-colours" title="' . trim(rtrim($title, ', ') . ' ' . ($initials ? ((!empty($title) ? 'with ' : '') . 'initials') : ''))
                . '">' . implode('', $tool_colours);
            if ($initials) {
                $tool_html .= '<span class="initials">(' . trim($initials) . ')</span>';
            }
            $tool_html .= '</span>';

            return $tool_html;
        } else {
            return '';
        }
    }

    /**
     * Make the user account archived
     *
     * @return bool
     */
    public function archive()
    {
        // Check the selected user isn't the current user
        if (Auth::check() && $this->id == Auth::user()->id) {
            Notify::warning('You cannot archive your own account');
            return false;
        }

        // Change status
        return $this->update(['status' => false]);
    }

    /**
     * Make the user a normal member.
     *
     * @return bool
     */
    public function makeMember()
    {
        return $this->setMembershipType('member');
    }

    /**
     * Make the user a committee member.
     *
     * @return bool
     */
    public function makeCommittee()
    {
        return $this->setMembershipType('committee');
    }

    /**
     * Make the user an associate
     *
     * @return bool
     */
    public function makeAssociate()
    {
        return $this->setMembershipType('associate');
    }

    /**
     * General function for changing a user's membership type.
     *
     * @param string $type
     *
     * @return bool
     */
    private function setMembershipType($type = 'member')
    {
        if ($this->id == Auth::user()->id) {
            Notify::warning('You cannot change your own membership type');
            return false;
        }


        return $this->update([
            'user_group_id' => Group::where('name', $type)->first()->id,
        ]);
    }

    /**
     * Create the greeting string.
     *
     * @return string
     */
    public function greeting()
    {
        return 'Hi ' . $this->forename . ',';
    }

    /**
     * Get the URL of the user's profile picture to be used in img tags.
     *
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->getAvatarPath(false, true);
    }

    /**
     * Check if the user has a custom profile picture.
     *
     * @return bool
     */
    public function hasAvatar()
    {
        return file_exists($this->getAvatarPath(true, false));
    }

    /**
     * Get the URL or path of the user's profile picture.
     *
     * @param bool $absolute
     * @param bool $checkExists
     *
     * @return string
     */
    public function getAvatarPath($absolute = false, $checkExists = false)
    {
        $basePath = '/images/profiles/';
        $imgPath  = $basePath . $this->username . '.jpg';
        $path     = !$checkExists || $this->hasAvatar() ? $imgPath : ($basePath . 'blank.jpg');

        return $absolute ? base_path('public/' . $path) : $path;

    }

    /**
     * Change the user's profile picture and resize to 500x500.
     *
     * @param UploadedFile $image
     *
     * @return $this
     */
    public function setAvatar(UploadedFile $image)
    {
        // Convert, resize and save
        Image::make($image)
             ->fit(500, 500)
             ->save($this->getAvatarPath(true));

        return $this;
    }

    /**
     * Check if the user is an event's TEM.
     *
     * @param Event $event
     *
     * @return bool
     */
    public function isTEM(Event $event)
    {
        return $event->isTEM($this);
    }

    /**
     * Test if a user is a member of crew for a given event.
     *
     * @param Event $event
     *
     * @return bool
     */
    public function isCrew(Event $event)
    {
        if ($event->isTEM($this)) {
            return true;
        } else {
            return $event->crew()
                         ->forUser($this)
                         ->count() > 0;
        }
    }

    /**
     * Test if a user has an EM role for a given event.
     *
     * @param Event $event
     *
     * @return bool
     */
    public function hasEMRole(Event $event)
    {
        if ($this->isTEM($event)) {
            return true;
        } else {
            return $event->crew()
                         ->forUser($this)
                         ->where('em', true)
                         ->count() > 0;
        }
    }

    /**
     * Get a list of a member's crew roles for an event.
     *
     * @param Event $event
     *
     * @return null|string
     */
    public function getCrewRoles(Event $event)
    {
        if (!$this->isCrew($event)) {
            return null;
        }

        // Check if TEM
        $roles = $this->isTEM($event) ? ['TEM'] : [];

        // Get any core roles
        $roles = array_merge($roles, $event->crew()
                                           ->core()
                                           ->forUser($this)
                                           ->pluck('name')
                                           ->toArray());

        // Check if general
        if ($event->crew()->general()->forUser($this)->count()) {
            $roles = array_merge($roles, ['General Crew']);
        }

        return implode(', ', $roles);
    }

    /**
     * Alias for getting a list of a member's crew roles for an event.
     *
     * @param Event $event
     *
     * @return null|string
     */
    public function getCrewRole(Event $event)
    {
        return $this->getCrewRoles($event);
    }

    /**
     * Check if the user has an event export token.
     *
     * @return bool
     */
    public function hasExportToken()
    {
        return $this->isMember() && !is_null($this->export_token);
    }

    /**
     * Get the user's encrypted event export token.
     *
     * @return null|string
     */
    public function getExportToken()
    {
        return $this->hasExportToken() ? hash('sha256', $this->export_token) : null;
    }

    /**
     * Get the value of a diary preference.
     *
     * @param $preference
     *
     * @return null
     */
    public function getDiaryPreference($preference)
    {
        if ($preference == 'event_types') {
            return isset($this->diary_preferences['event_types']) ? $this->diary_preferences['event_types'] : null;
        } else if ($preference == 'crewing') {
            return isset($this->diary_preferences['crewing']) ? $this->diary_preferences['crewing'] : null;
        } else {
            return null;
        }
    }

    /**
     * Check that a diary preference value is correct.
     *
     * @param $preference
     * @param $value
     *
     * @return bool
     */
    public function isDiaryPreference($preference, $value)
    {
        if ($preference == 'event_types') {
            return isset($this->diary_preferences['event_types']) && in_array($value, $this->diary_preferences['event_types']);
        } else if ($preference == 'crewing') {
            return isset($this->diary_preferences['crewing']) && $this->diary_preferences['crewing'] == $value;
        } else {
            return false;
        }
    }

    /**
     * Test whether the user has a given skill.
     *
     * @param Skill $skill
     *
     * @return void
     */
    public function hasSkill(Skill $skill)
    {
        return $this->skills
                   ->where('skill_id', $skill->id)
                   ->count() == 1;
    }

    /**
     * Get the awarded skill object.
     *
     * @param Skill $skill
     *
     * @return mixed
     */
    public function getAwardedSkill(Skill $skill)
    {
        return $this->skills
            ->where('skill_id', $skill->id)
            ->first();
    }

    /**
     * Get the user's awarded skill level.
     *
     * @param Skill $skill
     *
     * @return null
     */
    public function getSkillLevel(Skill $skill)
    {
        if (!$this->hasSkill($skill)) {
            return null;
        }

        return $this->getAwardedSkill($skill)
            ->level;
    }

    /**
     * An alias to get the user's awarded skill level.
     *
     * @param Skill $skill
     *
     * @return null
     */
    public function getAwardedLevel(Skill $skill)
    {
        return $this->getSkillLevel($skill);
    }

    /**
     * Count the number of skills a user has in a given category.
     *
     * @param Category|null $category
     *
     * @return int
     */
    public function countSkills(Category $category = null)
    {
        if ($category === null) {
            return $this->skills()
                        ->join('training_skills', 'training_awarded_skills.skill_id', '=', 'training_skills.id')
                        ->whereNull('training_skills.category_id')
                        ->count();
        } else {
            return $this->skills()
                        ->join('training_skills', 'training_awarded_skills.skill_id', '=', 'training_skills.id')
                        ->where('training_skills.category_id', $category->id)
                        ->count();
        }
    }

    /**
     * Test whether the user has an application pending for a given skill.
     *
     * @param Skill $skill
     *
     * @return bool
     */
    public function hasApplicationPending(Skill $skill)
    {
        return Application::notAwarded()
                          ->where('user_id', $this->id)
                          ->where('skill_id', $skill->id)
                          ->count() > 0;
    }

    /**
     * Set the level of a skill
     *
     * @param $skillId
     * @param $level
     */
    public function setSkillLevel($skillId, $level)
    {
        // Create array of attributes to set
        $data = [
            'skill_id'   => $skillId,
            'level'      => $level,
            'awarded_by' => Auth::user()->id,
        ];

        // Update or create
        if ($this->skills->where('skill_id', $skillId)->count() == 1) {
            $this->skills->where('skill_id', $skillId)->first()->update($data);
        } else {
            $this->skills()->create($data);
        }
    }

    /**
     * Test whether the user can award a skill level to another member.
     *
     * @param Skill $skill
     *
     * @return bool
     */
    public function canAwardSkill(Skill $skill = null)
    {
        if ($this->isAdmin()) {
            return true;
        } else if ($skill !== null && $this->isMember() && $this->getSkillLevel($skill) == 3) {
            return true;
        }

        return false;
    }

    /**
     * Test whether the user can revoke a skill level from another member.
     *
     * @param Skill $skill
     *
     * @return bool
     */
    public function canRevokeSkill(Skill $skill = null)
    {
        return $this->canAwardSkill($skill);
    }
}