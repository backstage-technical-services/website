<?php

namespace App\Models;

use Carbon\Carbon;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    /**
     * @var int
     */
    public $num;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'quotes';

    /**
     * The attributes that are fillable by mass assignment.
     *
     * @var array
     */
    protected $fillable = ['culprit', 'quote', 'date', 'added_by'];

    /**
     * Define variable types to cast some attributes to.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Quote constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $attributes = array_merge(['date' => new Carbon()], $attributes);

        parent::__construct($attributes);
    }

    /**
     * Define the creator foreign key link
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo('\App\Models\Users\User', 'added_by');
    }

    /**
     * Get the quote, with the markdown content converted to html.
     *
     * @return string
     */
    public function getHtmlAttribute()
    {
        return str_replace(PHP_EOL, '</p><p>', Markdown::convertToHtml($this->attributes['quote']));
    }
}
