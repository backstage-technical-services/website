<?php

namespace App\Models\Media;

use Alaouy\Youtube\Facades\Youtube;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateInterval;
use Exception;
use stdClass;

class Video
{
    /**
     * List of supported search types
     *
     * @var array
     */
    private static $SupportedKind = [
        'youtube#searchResult',
        'youtube#video',
    ];

    /**
     * The video ID
     *
     * @var int
     */
    private $videoId;

    /**
     * The video title
     *
     * @var string
     */
    public $title;

    /**
     * The video description
     *
     * @var string
     */
    public $description;

    /**
     * A list of the video's tags
     *
     * @var array
     */
    public $tags = [];

    /**
     * The URL of the video's thumbnail
     *
     * @var string
     */
    public $thumbnail;

    /**
     * The duration of the video
     *
     * @var CarbonInterval
     */
    public $duration;

    /**
     * The date the video was created
     *
     * @var Carbon
     */
    public $created;

    /**
     * The video's source object
     *
     * @var stdClass
     */
    private $src = null;

    /**
     * Get a video from it's ID.
     *
     * @param $videoId
     *
     * @return \App\Models\Media\Video
     */
    public static function find($videoId)
    {
        $video = Youtube::getVideoInfo($videoId);

        return $video === false ? false : new static($video);
    }

    /**
     * Video constructor.
     *
     * @param \stdClass $video
     *
     * @throws \Exception
     */
    public function __construct(stdClass $video)
    {
        // Check the search type is supported
        if (!in_array($video->kind, self::$SupportedKind)) {
            throw new Exception('Result type \'' . $video->kind . '\' is not currently supported');
        }

        // Set up the attributes
        switch ($video->kind) {
            case 'youtube#searchResult':
                $this->videoId = $video->id->videoId;
                $this->src     = Youtube::getVideoInfo($this->videoId);
                break;
            case 'youtube#video':
                $this->videoId = $video->id;
                $this->src     = $video;
                break;
            default:
                throw new Exception('Could not find ID of video');
        }

        // Load the video details
        $this->loadVideoDetails();
    }

    /**
     * Load the video's details from the source object.
     *
     * @return $this
     * @throws \Exception
     */
    private function loadVideoDetails()
    {
        $this->title       = $this->src->snippet->title;
        $this->description = $this->src->snippet->description ?? '';
        $this->tags        = $this->src->snippet->tags ?? [];
        $this->thumbnail   = '';
        $this->duration    = CarbonInterval::instance(new DateInterval($this->src->contentDetails->duration));
        $this->created     = new Carbon($this->src->snippet->publishedAt);

        foreach (['high', 'medium', 'standard', 'default'] as $quality) {
            if (isset($this->src->snippet->thumbnails->$quality)) {
                $this->thumbnail = $this->src->snippet->thumbnails->$quality->url;
                break;
            }
        }

        return $this;
    }

    /**
     * Get the video's embed URL.
     *
     * @return string
     */
    public function embed()
    {
        return '//www.youtube.com/embed/' . $this->videoId . '?rel=0&hd=1';
    }

    /**
     * Magic method to get any attribute.
     *
     * @param $attribute
     *
     * @return int
     */
    public function __get($attribute)
    {
        if ($attribute == 'id') {
            return $this->videoId;
        } else {
            return $this->{$attribute};
        }
    }
}
