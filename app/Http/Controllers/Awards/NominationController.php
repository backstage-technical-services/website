<?php

namespace App\Http\Controllers\Awards;

use App\Http\Controllers\Controller;
use App\Http\Requests\Awards\Nominate;
use App\Models\Awards\Season;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;

class NominationController extends Controller
{
    /**
     * Set up the middleware.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * View the list of nominations.
     *
     * @param $seasonId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($seasonId)
    {
        // Authorise
        $season = Season::findOrFail($seasonId);
        $this->authorize('update', $season);

        return view('awards.seasons.nominations.view', [
            'season' => $season,
        ]);
    }

    /**
     * Create a new nomination.
     *
     * @param                                    $seasonId
     * @param \App\Http\Requests\Awards\Nominate $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store($seasonId, Nominate $request)
    {
        $season = Season::findOrFail($seasonId);
        $nomination = $season->nominations()->create([
            'award_id' => $request->get('award_id'),
            'nominee' => clean($request->get('nominee')),
            'reason' => clean($request->get('reason')),
            'approved' => false,
            'suggested_by' => $request->user()->id,
        ]);

        Log::info(
            "User {$request->user()->id} created nomination {$nomination->id} for award {$nomination->award_id} and season $seasonId",
        );

        Notify::success('Nomination created');
        return redirect()->route('award.season.view', ['id' => $seasonId]);
    }

    /**
     * Toggle the approved status of a nomination.
     *
     * @param $seasonId
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve($seasonId, $id)
    {
        $nomination = $this->getNomination($seasonId, $id);
        $nomination->update([
            'approved' => !$nomination->isApproved(),
        ]);

        Log::info(
            'User ' .
                request()->user()->id .
                " approved nomination $id for award {$nomination->award_id} and season $seasonId",
        );

        Notify::success('Nomination ' . ($nomination->isApproved() ? 'approved' : 'unapproved'));
        return $this->ajaxResponse(true);
    }

    /**
     * Delete a nomination.
     *
     * @param $seasonId
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($seasonId, $id)
    {
        $nomination = $this->getNomination($seasonId, $id);
        $nomination->delete();

        Log::info(
            'User ' .
                request()->user()->id .
                " deleted nomination $id for award {$nomination->award_id} and season $seasonId",
        );

        Notify::success('Nomination deleted');
        return $this->ajaxResponse(true);
    }

    /**
     * Get a nomination for the season.
     *
     * @param $seasonId
     * @param $id
     *
     * @return mixed
     */
    private function getNomination($seasonId, $id)
    {
        $this->requireAjax();
        $season = Season::findOrFail($seasonId);
        $nomination = $season->nominations()->where('id', $id)->firstOrFail();
        $this->authorize('edit', $nomination);

        return $nomination;
    }
}
