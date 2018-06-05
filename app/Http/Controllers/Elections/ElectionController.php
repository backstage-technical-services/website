<?php

namespace App\Http\Controllers\Elections;

use App\Http\Controllers\Controller;
use App\Http\Requests\ElectionRequest;
use App\Models\Committee\Role;
use App\Models\Elections\Election;
use bnjns\LaravelNotifications\Facades\Notify;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ElectionController extends Controller
{
    /**
     * View the list of elections.
     *
     * @return $this
     */
    public function index()
    {
        $this->authorize('index', Election::class);

        $elections = Election::orderBy('voting_start', 'DESC')
                             ->paginate(10);
        $this->checkPage($elections);

        return view('elections.index')->with('elections', $elections);
    }

    /**
     * View an election.
     *
     * @param $id
     *
     * @return $this
     */
    public function view($id)
    {
        $election = Election::findOrFail($id);
        $this->authorize('view', $election);

        return view('elections.view')->with('election', $election);
    }

    /**
     * View the form to create the election.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return $this
     */
    public function create(Request $request)
    {
        $this->authorize('create', Election::class);

        // Determine the positions
        $positions = Role::orderBy('order', 'ASC')
                         ->pluck('name', 'id');

        return view('elections.create')->with('positions', $positions)
                                       ->with('election', new Election([
                                           'type' => $request->old('type') ?: 1,
                                       ]))
                                       ->with('route', route('election.store'));
    }

    /**
     * Process the form and create the new election.
     *
     * @param \App\Http\Requests\ElectionRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ElectionRequest $request)
    {
        // Determine the positions
        $positions = $this->determineElectionPositions($request);

        // Create the election
        $request->merge([
            'positions'         => $positions,
            'nominations_start' => Carbon::createFromFormat('Y-m-d H:i:s', $request->get('nominations_start')),
            'nominations_end'   => Carbon::createFromFormat('Y-m-d H:i:s', $request->get('nominations_end')),
            'voting_start'      => Carbon::createFromFormat('Y-m-d H:i:s', $request->get('voting_start')),
            'voting_end'        => Carbon::createFromFormat('Y-m-d H:i:s', $request->get('voting_end')),
        ]);
        $election = Election::create(clean($request->all()));
        File::makeDirectory($election->getManifestoPath(), 0775, true);
        Notify::success('Election created');

        return redirect()->route('election.view', ['id' => $election->id]);
    }

    /**
     * Show the form for editing the election.
     *
     * @param $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $election = Election::findOrFail($id);
        $this->authorize('update', $election);

        return view('elections.edit')->with('election', $election)
                                     ->with('positions', $election->positions)
                                     ->with('route', route('election.update', ['id' => $election->id]));

    }

    /**
     * Process the form and update the election.
     *
     * @param                                    $id
     * @param \App\Http\Requests\ElectionRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id, ElectionRequest $request)
    {
        $election  = Election::findOrFail($id);
        $positions = $this->determineElectionPositions($request);

        $request->merge(['positions' => $positions]);
        $election->update(clean($request->all()));

        Notify::success('Election updated');
        return redirect()->route('election.view', ['id' => $id]);
    }

    /**
     * Delete an election.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Make sure requested over AJAX
        $this->requireAjax();

        // Get the election
        $election = Election::find($id);
        if (!$election) {
            return $this->ajaxError('404', 404, 'Couldn\'t find that election.');
        }

        // Delete
        $election->delete();
        File::delete($election->getManifestoPath());

        Notify::success('Election deleted');
        return $this->ajaxResponse(true);
    }

    /**
     * Set the elected committee members.
     *
     * @param                          $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function elect($id, Request $request)
    {
        // Make sure requested over AJAX
        $this->requireAjax();

        // Get the election
        $election = Election::find($id);
        if (!$election) {
            return $this->ajaxError('404', 404, 'Couldn\'t find that election.');
        }

        // Check that voting has closed
        if (!$election->hasVotingClosed()) {
            return $this->ajaxError('voting_open', 405, 'Voting has not yet closed.');
        }

        // Validate the request
        $this->validate($request, [
            'elected'   => 'array',
            'elected.*' => 'required',
        ], [
            'elected.array'      => 'Please select the elected members',
            'elected.*.required' => 'Please select the elected members',
        ]);

        // Set those elected
        $elected = $request->get('elected') ?: [];
        foreach ($election->nominations as $nomination) {
            $nomination->update(['elected' => in_array($nomination->id, $elected)]);
        }

        Notify::success('Committee saved');
        return $this->ajaxResponse(true);
    }

    /**
     * Determine the positions available in an election.
     *
     * @param \App\Http\Requests\ElectionRequest $request
     *
     * @return array
     */
    private function determineElectionPositions(ElectionRequest $request)
    {
        if ($request->get('type') == 2) {
            $positions_checked = $request->get('positions_checked');
            $positions         = array_values(array_filter($request->get('positions'), function ($index) use ($positions_checked) {
                return in_array($index, $positions_checked);
            }, ARRAY_FILTER_USE_KEY));
        } else {
            $positions = Role::orderBy('order', 'ASC')
                             ->pluck('name', 'id')
                             ->toArray();
        }

        return $positions;
    }
}
