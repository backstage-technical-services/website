<?php

namespace App\Models\Resources;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    /**
     * Set the table name.
     *
     * @var string
     */
    protected $table = 'resource_issues';

    /**
     * Define the attributes that are fillable by mass assignment.
     *
     * @var array
     */
    public $fillable = ['issue', 'resource_id', 'author_id', 'reason'];

    /**
     * Define the relationship with the resource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function resource()
    {
        return $this->belongsTo('App\Models\Resources\Resource');
    }

    /**
     * Define the relationship with the author.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('App\Models\Users\User', 'author_id');
    }

    /**
     * Get the file's extension. As PDFs are the only file type
     * currently supported there is no logic here; however it
     * does provide flexibility for the future.
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->resource->isFile() ? 'pdf' : '';
    }

    /**
     * Get the file name of the issue.
     *
     * @return string
     */
    public function getFileName()
    {
        return sprintf('iss%02d.%s', $this->issue, $this->getFileExtension());
    }

    /**
     * Get the full path of the file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->resource->getPath() . '/' . $this->getFileName();
    }
}
