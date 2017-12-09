<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use bnjns\FlashNotifications\Facades\Notifications;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;

class ImageController extends Controller
{
    /**
     * Display an index of all image albums on the facebook page.
     * @param \SammyK\LaravelFacebookSdk\LaravelFacebookSdk $fb
     * @return $this
     */
    public function index(LaravelFacebookSdk $fb)
    {
        // Make the request for all the albums
        $token    = $fb->getApp()->getAccessToken()->getValue();
        $response = $fb->get('/BackstageTechnicalServices/albums?fields=id,count,name,type,created_time&limit=500', $token);
        
        // Get the list of albums
        $albums = $response->getDecodedBody()['data'];
        
        // Remove any albums which aren't 'normal'
        foreach($albums as $key => $album) {
            if($album['type'] != 'normal') {
                unset($albums[$key]);
            }
        }
        array_filter($albums);
        
        // Sort the results
        usort($albums, function ($a, $b) {
            return strcmp($b['created_time'], $a['created_time']);
        });
        
        // Render
        return view('media.images.index')->with('albums', $albums);
    }
    
    /**
     * View an album
     * @param                                               $albumId
     * @param \SammyK\LaravelFacebookSdk\LaravelFacebookSdk $fb
     * @return $this
     */
    public function album($albumId, LaravelFacebookSdk $fb)
    {
        // Get the album details
        $fb->setDefaultAccessToken($fb->getApp()->getAccessToken()->getValue());
        $response = $fb->get("/{$albumId}?fields=id,count,name");
        $album    = $response->getDecodedBody();
        
        // Check the album has photos
        if($album['count'] == 0) {
            Notifications::warning('That album doesn\'t have any photos');
            return redirect()->route('media.images.index');
        }
        
        // Get the album photos
        $response = $fb->get("/{$albumId}/photos?fields=images,link,name,source&limit=500");
        $photos   = $response->getDecodedBody()['data'];
        
        return view('media.images.album')->with('album', $album)
                                         ->with('photos', $photos);
    }
}
