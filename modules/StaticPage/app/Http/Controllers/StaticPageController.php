<?php

namespace Modules\StaticPage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function landing(): View
    {
        return view('staticpage::landing');
    }

    public function about(): View
    {
        return view('staticpage::about');
    }

    public function faq(): View
    {
        return view('staticpage::faq');
    }

    public function links(): View
    {
        return view('staticpage::links');
    }

    public function privacyPolicy(): View
    {
        return view('staticpage::privacy-policy');
    }
}
