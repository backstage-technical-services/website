<?php

namespace Tests\Support\Fakes;

use Exception;
use InvalidArgumentException;
use stdClass;

/**
 * A fake of the YouTube API wrapper (alaouy/youtube) used by the videos pages. It returns the same shapes as the real
 * wrapper, including `false` when there are no results.
 */
class FakeYoutube
{
    /**
     * The videos on the "channel", newest first.
     *
     * @var list<stdClass>
     */
    private array $videos = [];

    /**
     * The parameters of each search, in order.
     *
     * @var list<array<string, mixed>>
     */
    public array $searches = [];

    private ?Exception $failure = null;

    /**
     * Add a video to the channel. The attributes override the defaults.
     *
     * @param array{title?: string, description?: string, tags?: list<string>, publishedAt?: string, duration?: string, thumbnail?: string} $attributes
     */
    public function addVideo(string $id, array $attributes = []): stdClass
    {
        $attributes += [
            'title' => "Video {$id}",
            'description' => "The description of video {$id}",
            'tags' => [],
            'publishedAt' => '2026-01-01T12:00:00Z',
            'duration' => 'PT3M20S',
            'thumbnail' => "https://i.ytimg.com/vi/{$id}/hqdefault.jpg",
        ];

        $video = (object) [
            'kind' => 'youtube#video',
            'id' => $id,
            'snippet' => (object) [
                'title' => $attributes['title'],
                'description' => $attributes['description'],
                'tags' => $attributes['tags'],
                'publishedAt' => $attributes['publishedAt'],
                'thumbnails' => (object) ['high' => (object) ['url' => $attributes['thumbnail']]],
            ],
            'contentDetails' => (object) ['duration' => $attributes['duration']],
        ];
        array_unshift($this->videos, $video);

        return $video;
    }

    /**
     * Make every call throw, in the same way as the real wrapper does when the API returns an error.
     */
    public function failWith(
        string $message = 'Error 403 The request cannot be completed because you have exceeded your quota. : quotaExceeded',
    ): void {
        $this->failure = new Exception($message);
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return list<stdClass>|false
     */
    public function searchAdvanced($params, bool $pageInfo = false): array|false
    {
        if (
            empty($params) ||
            (!isset($params['q']) && !isset($params['channelId']) && !isset($params['videoCategoryId']))
        ) {
            throw new InvalidArgumentException(
                'at least the Search query or Channel ID or videoCategoryId must be supplied',
            );
        }
        $this->searches[] = $params;
        $this->throwIfFailing();

        $results = array_map(
            fn(stdClass $video) => (object) [
                'kind' => 'youtube#searchResult',
                'id' => (object) ['kind' => 'youtube#video', 'videoId' => $video->id],
                'snippet' => $video->snippet,
            ],
            array_slice($this->videos, 0, (int) ($params['maxResults'] ?? 5)),
        );

        return count($results) === 0 ? false : $results;
    }

    /**
     * @param string $vId
     * @param list<string> $part
     */
    public function getVideoInfo($vId, $part = []): stdClass|false
    {
        $this->throwIfFailing();

        foreach ($this->videos as $video) {
            if ($video->id === $vId) {
                return $video;
            }
        }

        return false;
    }

    private function throwIfFailing(): void
    {
        if ($this->failure !== null) {
            throw $this->failure;
        }
    }
}
