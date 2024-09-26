<?php

namespace App\Http\Controllers\Awards;

use App\Http\Controllers\Controller;
use App\Models\Awards\Award;
use App\Models\Awards\Season;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeasonController extends Controller
{
    /**
     * View all the award seasons.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('index', Season::class);

        return view('awards.seasons.index')->with('seasons', Season::orderBy('created_at', 'DESC')->get())
                                           ->with('awards', Award::approved()->get());
    }

    /**
     * Store a new award season.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Authorise
        $this->requireAjax();
        $this->authorize('create', Season::class);

        // Validate
        $fields = ['name', 'status', 'awards'];
        $this->validate($request, Season::getValidationRules($fields), Season::getValidationMessages($fields));

        // Create the season
        $season = Season::create(clean($request->only('name')) + ['status' => $request->get('status') ?: null]);
        $season->awards()->sync($request->get('awards') ?: []);

        Log::info("User {$request->user()->id} created award season {$season->id} ({$season->name})");

        Notify::success('Award season created');
        return $this->ajaxResponse('Award season created');
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function view($id)
    {
        // Authorise
        $season = Season::findOrFail($id);
        $this->authorize('view', $season);
        if (!Auth::user()->isAdmin() && $season->status === null) {
            return redirect()->route('award.season.index');
        }

        return view('awards.seasons.view')->with('season', $season);
    }

    /**
     * Update an award season.
     *
     * @param                          $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        // Authorise
        $this->requireAjax();
        $season = Season::findOrFail($id);
        $this->authorize('update', $season);

        // Validate
        $fields = ['name', 'status', 'awards'];
        $this->validate($request, Season::getValidationRules($fields), Season::getValidationMessages($fields));

        // Update
        $season->update(clean($request->only('name')) + ['status' => $request->get('status') ?: null]);

        Log::info("User {$request->user()->id} updated award season $id");

        Notify::success('Award season updated');
        return $this->ajaxResponse('Award season updated');
    }

    /**
     * Update a season's status.
     *
     * @param                          $id
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function updateStatus($id, Request $request)
    {
        // Authorise
        $this->requireAjax();
        $season = Season::findOrFail($id);
        $this->authorize('update', $season);

        // Validate the status
        $status = $request->get('status') ? (int)$request->get('status') : null;
        if ($status !== null && !in_array($status, array_keys(Season::STATUSES))) {
            return $this->ajaxError(0, 422, 'Please select a valid status');
        }

        // Update
        $season->update([
            'status' => $status === $season->status ? null : $status,
        ]);

        Log::info("User {$request->user()->id} updated the status of award season $id to '{$season->status}'");

        Notify::success('Status updated');
        return $this->ajaxResponse(true);
    }

    /**
     * Record a user's votes.
     *
     * @param                          $id
     * @param \Illuminate\Http\Request $request
     *
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function vote($id, Request $request)
    {
        // Authorise
        $season = Season::findOrFail($id);
        $this->authorize('vote', $season);

        // Check the number of awards that have been voted for
        $awards = $request->get('awards') ?: [];
        if (count($awards) == 0) {
            Notify::warning('Please select some nominations to vote for');
            return redirect()->back();
        }

        // Process each award in turn
        $successful = 0;
        foreach ($awards as $awardId => $nominationId) {
            Log::withContext(['award' => $awardId, 'nomination' => $nominationId, 'season' => $id]);
            Log::debug("Processing vote for user {$request->user()->id}");
            $nomination = $season->nominations()->find($nominationId);
            $award      = Award::find($awardId);

            if (!$award) {
                Log::warning("Cannot process vote for user {$request->user()->id} - award $awardId does not exist");
                continue;
            }

            if (!$nomination) {
                Log::warning("Cannot process vote for user {$request->user()->id} - nomination $nominationId does not exist for season $id");
                continue;
            }

            if ($award->userHasVoted($season, $request->user())) {
                Log::debug("User {$request->user()->id} has already voted for award season $id");
                continue;
            }

            if ($nomination->votes()->create(['award_season_id' => $id, 'user_id' => $request->user()->id])) {
                Log::info("Recorded vote for user {$request->user()->id} on award season $id");
                $successful++;
            } else {
                Log::warning("Failed to record vote for user {$request->user()->id}");
            }
        }

        if ($successful == count($awards)) {
            Notify::success('Votes recorded');
        } else if ($successful > 0) {
            Notify::warning('Only some votes were recorded');
        } else {
            Notify::error('Uh oh! Something went wrong.');
        }

        return redirect()->back()->withInput($request->only('awards'));
    }

    /**
     * Delete an award season.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Authorise
        $this->requireAjax();
        $season = Season::findOrFail($id);
        $this->authorize('delete', $season);

        $season->delete();

        Log::info("User " . request()->user()->id . " deleted award season $id");

        Notify::success('Award season deleted');
        return $this->ajaxResponse('Award season deleted');
    }
}
