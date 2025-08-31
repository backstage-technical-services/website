<?php

namespace App\Models\Resources;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class Resource extends Model
{
    /**
     * Define the resource type constants.
     */
    const TYPE_FILE = 1;
    const TYPE_GDOC = 2;

    /**
     * Define the access constants
     */
    const ACCESS_PUBLIC = 1;
    const ACCESS_REGISTERED = 3;
    const ACCESS_MEMBER = 4;
    const ACCESS_COMMITTEE = 5;

    /**
     * Define the resource types.
     *
     * @var array
     */
    const TYPES = [
        self::TYPE_FILE => 'Uploaded File',
    ];

    /**
     * Define the access types.
     *
     * @var array
     */
    const ACCESS = [
        self::ACCESS_PUBLIC => 'Everyone',
        self::ACCESS_REGISTERED => 'Registered Users',
        self::ACCESS_MEMBER => 'BTS Members',
        self::ACCESS_COMMITTEE => 'Committee Only',
    ];

    /**
     * Set table name.
     *
     * @var string
     */
    protected $table = 'resources';

    /**
     * Define the attributes that are fillable by mass assignment.
     *
     * @var array
     */
    public $fillable = ['title', 'description', 'category_id', 'event_id', 'author_id', 'type', 'href', 'access'];

    /**
     * Sanitised a string to be suitable for a file name.
     *
     * @param string $string
     *
     * @return string
     */
    public static function sanitised($string)
    {
        return preg_replace('/[^0-9A-Za-z_\- .]/', '-', $string);
    }

    /**
     * Define the relationship with the resource's category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Resources\Category', 'category_id');
    }

    /**
     * Define the relationship with the resource's tags.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(
            'App\Models\Resources\Tag',
            'resource_tag',
            'resource_id',
            'resource_tag_id',
        )->orderBy('resource_tags.name', 'ASC');
    }

    /**
     * Define the relationship with the resource's event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo('App\Models\Events\Event', 'event_id', 'id');
    }

    /**
     * Define the relationship with the issues.
     *
     * @return mixed
     */
    public function issues()
    {
        return $this->hasMany('App\Models\Resources\Issue')->orderBy('resource_issues.issue', 'ASC');
    }

    /**
     * Scope to perform a FULLTEXT search on the title and description.
     * This automatically encloses words in wildcards to allow substrings to match.
     *
     * @param $query
     * @param $searchTerm
     *
     * @return mixed
     */
    public function scopeSearch($query, $searchTerm)
    {
        $searchTerm = preg_replace(
            '/(\s{2,})/',
            ' ',
            preg_replace('/(^|\s)(\w+)(\s|$)/', '$1*$2*$3', preg_replace('/\s/', '  ', $searchTerm)),
        );
        return $query->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$searchTerm]);
    }

    /**
     * Scope to specify a tag the resource(s) should have.
     *
     * @param $query
     * @param $tags
     *
     * @return mixed
     */
    public function scopeWithTags($query, $tags)
    {
        return $query
            ->leftJoin('resource_tag', 'resources.id', '=', 'resource_tag.resource_id')
            ->leftJoin('resource_tags', 'resource_tag.resource_tag_id', '=', 'resource_tags.id')
            ->whereIn('resource_tags.slug', $tags)
            ->groupBy('resources.id')
            ->havingRaw('COUNT(DISTINCT resource_tags.slug) = ' . count($tags));
    }

    /**
     * Scope to specify the category the resource(s) should be in.
     *
     * @param $query
     * @param $category
     *
     * @return mixed
     */
    public function scopeInCategory($query, $category)
    {
        return $query
            ->leftJoin('resource_categories', 'resources.category_id', '=', 'resource_categories.id')
            ->where('resource_categories.slug', is_object($category) ? $category->slug : $category);
    }

    /**
     * Scope to only get resources the active user can access.
     *
     * @param                             $query
     * @param \App\Models\Users\User|null $user
     *
     * @return mixed
     */
    public function scopeAccessible($query, User $user = null)
    {
        $user = $user === null ? Auth::user() : $user;

        $query->whereNull('access')->orWhere('access', self::ACCESS_PUBLIC);

        if ($user !== null) {
            $query->orWhere('access', self::ACCESS_REGISTERED);

            if ($user->isMember()) {
                $query->orWhere('access', self::ACCESS_MEMBER);
            }
            if ($user->isCommittee()) {
                $query->orWhere('access', self::ACCESS_COMMITTEE);
            }
        }
    }

    /**
     * Test if the resource is attached to an event.
     *
     * @return bool
     */
    public function isAttachedToEvent()
    {
        return $this->event_id !== null;
    }

    /**
     * Test if the resource is categorised.
     *
     * @return bool
     */
    public function isCategorised()
    {
        return $this->category_id !== null;
    }

    /**
     * Test if the resource is a file.
     *
     * @return bool
     */
    public function isFile()
    {
        return $this->type == self::TYPE_FILE;
    }

    /**
     * Test if the resource is a Google Doc.
     *
     * @return bool
     */
    public function isGDoc()
    {
        return $this->type == self::TYPE_GDOC;
    }

    /**
     * Test if the resource can be re-issued.
     *
     * @return bool
     */
    public function isIssuable()
    {
        return $this->isFile();
    }

    /**
     * Test if the resource is flagged as a risk assessment.
     *
     * @return bool
     */
    public function isRiskAssessment()
    {
        return $this->isCategorised() && $this->category->flag === Category::FLAG_RISK_ASSESSMENT;
    }

    /**
     * Test if the resource is flagged as an event report.
     *
     * @return bool
     */
    public function isEventReport()
    {
        return $this->isCategorised() && $this->category->flag === Category::FLAG_EVENT_REPORT;
    }

    /**
     * Test if the resource is flagged as a meeting agenda.
     *
     * @return bool
     */
    public function isMeetingAgenda()
    {
        return $this->isCategorised() && $this->category->flag === Category::FLAG_MEETING_AGENDA;
    }

    /**
     * Test if the resource is flagged as meeting minutes.
     *
     * @return bool
     */
    public function isMeetingMinutes()
    {
        return $this->isCategorised() && $this->category->flag === Category::FLAG_MEETING_MINUTES;
    }

    /**
     * Test is a user can view the resource.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function canAccess(User $user = null)
    {
        if ($this->access === null || $this->access === self::ACCESS_PUBLIC) {
            return true;
        }

        switch ($this->access) {
            case self::ACCESS_REGISTERED:
                return !is_null($user);
            case self::ACCESS_MEMBER:
                return !is_null($user) && $user->isMember();
            case self::ACCESS_COMMITTEE:
                return !is_null($user) && $user->isCommittee();
            default:
                return false;
        }
    }

    /**
     * Get all the resource's tags as an array of IDs.
     *
     * @return mixed
     */
    public function getTagsAttribute()
    {
        return $this->tags()->pluck('id')->toArray();
    }

    /**
     * Define a shortcut for getting the name of the resource's category.
     *
     * @return string
     */
    public function getCategoryNameAttribute()
    {
        return $this->isCategorised() ? $this->category->name : 'Uncategorised';
    }

    /**
     * Define a shortcut for getting the title of the resource's access.
     *
     * @return string
     */
    public function getAccessNameAttribute()
    {
        return isset(self::ACCESS[$this->access]) ? self::ACCESS[$this->access] : self::ACCESS[self::ACCESS_PUBLIC];
    }

    /**
     * Get the author of the latest issue.
     *
     * @return \App\Models\Users\User
     */
    public function getAuthorAttribute()
    {
        return $this->issue()->author;
    }

    /**
     * Get the current issue number.
     *
     * @return int
     */
    public function getIssueAttribute()
    {
        return $this->issue() ? $this->issue()->issue : null;
    }

    /**
     * Get the current issue object.
     *
     * @return \App\Models\Resources\Issue
     */
    public function issue()
    {
        return Issue::where('resource_id', $this->id)->orderBy('issue', 'DESC')->first();
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     * @param string                        $reason
     *
     * @return \App\Models\Resources\Issue
     */
    public function reissue(UploadedFile $file, $reason)
    {
        // Create the new issue
        $issue = $this->issues()->create([
            'issue' => (int) $this->issue + 1,
            'author_id' => Auth::user()->id,
            'reason' => clean($reason),
        ]);

        // Save the file
        $file->move($this->getPath(), $issue->getFileName());

        // Send the issue back
        return $issue;
    }

    /**
     * Check the issue number is valid.
     *
     * @param null $issueNum
     *
     * @return \App\Models\Resources\Resource
     */
    public function checkIssueNum($issueNum = null)
    {
        $issueNum = $issueNum === null ? (request()->get('issue') ?: 1) : $issueNum;

        if (!$this->issues()->where('issue', $issueNum)->count()) {
            app()->abort(404);
        }

        return $this;
    }

    /**
     * Get the path of the resource folder.
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->isFile()) {
            return resource_path('resources/' . $this->id);
        } else {
            return '';
        }
    }

    /**
     * Get the full path of the file.
     *
     * @return string
     */
    public function getFullPath()
    {
        if ($this->isFile()) {
            return $this->issue()->getPath();
        } elseif ($this->isGDoc()) {
            return 'https://drive.google.com/open?id=' . $this->href;
        } else {
            return '';
        }
    }

    /**
     * A shortcut for getting the current issue's file extension.
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->issue()->getFileExtension();
    }

    /**
     * Get an associative array of headers to send
     * when streaming or downloading the resource.
     *
     * @return array
     */
    public function getHeaders()
    {
        if ($this->isFile()) {
            return [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' =>
                    'inline; filename="' . (static::sanitised($this->title) . '.' . $this->getFileExtension()) . '"',
                'Content-Length' => filesize($this->getFullPath()),
            ];
        } else {
            return [];
        }
    }

    /**
     * Stream the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function stream()
    {
        if ($this->isFile()) {
            return response(file_get_contents($this->getFullPath()), 200, $this->getHeaders());
        } elseif ($this->isGDoc()) {
            return $this->getFullPath();
        } else {
            app()->abort(404);
        }
    }

    /**
     * Download the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function download()
    {
        if ($this->isFile()) {
            return response()->download(
                $this->getFullPath(),
                static::sanitised($this->title) . '.' . $this->getFileExtension(),
                $this->getHeaders(),
            );
        } elseif ($this->isGDoc()) {
            return $this->getFullPath();
        } else {
            redirect()->route('resource.search');
        }
    }
}
