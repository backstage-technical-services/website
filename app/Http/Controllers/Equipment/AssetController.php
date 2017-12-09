<?php

namespace App\Http\Controllers\Equipment;

use App\Http\Controllers\Controller;

class AssetController extends Controller
{
    /**
     * View the asset register.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function view()
    {
        $this->authorizeGate('member');
        return view('equipment.assets.view');
    }
}
