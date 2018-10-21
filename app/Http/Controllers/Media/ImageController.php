<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;

class ImageController extends Controller
{
    /**
     * Display an index of all image albums on the facebook page.
     *
     * @return $this
     */
    public function index()
    {
        // Temporarily disable
        return redirect()->route('home')->setStatusCode(307);
    }

    /**
     * View an album
     *
     * @param                                               $albumId
     *
     * @return $this
     */
    public function album($albumId)
    {
        // Temporarily disable
        return redirect()->route('home')->setStatusCode(307);
    }
}
