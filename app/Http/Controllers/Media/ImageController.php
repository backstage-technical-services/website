<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use bnjns\LaravelNotifications\Facades\Notify;

class ImageController extends Controller
{
    /**
     * Display an index of all image albums.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Notify::info('The image gallery is currently disabled');
        return redirect()->route('home');
    }

    /**
     * View an individual image.
     *
     * @return \Illuminate\Http\Response
     */
    public function view()
    {
        Notify::info('The image gallery is currently disabled');
        return redirect()->route('home');
    }

    /**
     * View an album.
     *
     * @return \Illuminate\Http\Response
     */
    public function album()
    {
        Notify::info('The image gallery is currently disabled');
        return redirect()->route('home');
    }
}
