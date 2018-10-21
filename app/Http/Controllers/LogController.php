<?php

namespace App\Http\Controllers;

use App\Models\Log;

class LogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorizeGate('admin');

        $logs = Log::orderBy('created_at', 'DESC')
                   ->paginate(100);

        return view('logs.index')->with([
            'logs' => $logs,
        ]);
    }
}