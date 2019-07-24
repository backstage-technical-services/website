<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Events\Paperwork;
use App\Http\Requests\Events\PaperworkRequest;

use bnjns\LaravelNotifications\Facades\Notify;

class PaperworkController extends Controller
{
    /**
     * Set the basic authentication requirements.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', Paperwork::class);

        $paperwork = Paperwork::orderBy('name', 'ASC')->get();

        return view('events.paperwork')->with('paperwork_list', $paperwork);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Paperwork::class);

        return view('events.paperwork.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Events\PaperworkRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PaperworkRequest $request)
    {
        Paperwork::create([
            'name'          => clean($request->get('name')),
            'template_link' => clean($request->get('template_link')),
        ]);


        Notify::success("Paperwork Created");
        return $this->ajaxResponse('Paperwork created');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Events\PaperworkRequest   $request
     * @param  integer                                      $paperworkID
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(PaperworkRequest $request, $paperworkID)
    {
        $this->authorize('update', Paperwork::class);

        $paperwork = Paperwork::findOrFail($paperworkID);

        $paperwork::update([
            'title'         => clean($request->get('title')),
            'template_link' => clean($request->get('template_link')),
        ]);

        return $this->ajaxResponse('Paperwork updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Requests\Events\PaperworkRequest  $paperwork
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(PaperworkRequest $paperwork)
    {
        $this->authorize('delete', Paperwork::class);

        return $this->ajaxResponse('Paperwork updated');
    }
}
