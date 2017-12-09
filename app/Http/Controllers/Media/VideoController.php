<?php

    namespace App\Http\Controllers\Media;

    use Alaouy\Youtube\Facades\Youtube;
    use App\Http\Controllers\Controller;
    use App\Models\Media\Video;

    class VideoController extends Controller
    {
        /**
         * Define the BTS YouTube channel ID.
         */
        const ChannelId = 'UCz6b0CQClaUH9poF83PNRCw';

        /**
         * View all videos on the YouTube channel
         * @return $this
         */
        public function index()
        {
            $params = [
                'type'       => 'video',
                'channelId'  => self::ChannelId,
                'part'       => 'id, snippet',
                'maxResults' => 10,
                'order'      => 'date',
            ];

            $videos = Youtube::searchAdvanced($params);
            foreach($videos as &$video) {
                $video = new Video($video);
            }

            return view('media.videos.index')->with('videos', $videos);
        }

        /**
         * View a video.
         * @param $videoId
         * @return $this
         */
        public function show($videoId)
        {
            $video = Video::find($videoId);

            if(!$video) {
                app()->abort(404);
            }

            return view('media.videos.view')->with('video', $video);
        }
    }
