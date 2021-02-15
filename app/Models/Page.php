<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Page extends Model
{
    use HasFactory;
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'published',
        'user_id',
    ];

    /**
     * Allow retrieving a page by its slug.
     *
     * @param $slug
     *
     * @return
     */
    public static function findBySlug($slug)
    {
        return static::where(['slug' => $slug])
                     ->get()
                     ->first();
    }

    /**
     * Method automatically throw a 404 if the page isn't found, or isn't published.
     *
     * @param $slug
     *
     * @return \App\Page
     */
    public static function findBySlugOrFail($slug)
    {
        if (!($page = static::findBySlug($slug))) {
            throw new NotFoundHttpException;
        }

        return $page;
    }

    /**
     * Create the foreign key link.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('App\Models\Users\User', 'user_id');
    }

    /**
     * The scope that allows filtering pages by whether they're published or not.
     *
     * @param $query
     */
    public function scopePublished($query)
    {
        $query->where('published', 1);
    }
}
