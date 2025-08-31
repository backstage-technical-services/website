<?php

namespace App\Http\Controllers\Elections;

use App\Http\Controllers\Controller;
use App\Models\Elections\Election;
use App\Models\Elections\Nomination;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class NominationController extends Controller
{
    /**
     * Add a nomination and upload the manifesto.
     *
     * @param                          $electionId
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($electionId, Request $request)
    {
        // Make sure requested over AJAX
        $this->requireAjax();

        // Authorise
        $this->authorize('create', Nomination::class);

        // Get the election
        $election = Election::find($electionId); /* @var Election $election */
        if (!$election) {
            return $this->ajaxError('404', 404, 'Couldn\'t find that election.');
        }

        // Check that nominations are open
        if (!$election->canModifyNominations()) {
            Log::debug(
                "User {$request->user()->id} tried to add nomination for election $electionId but nominations are closed",
            );
            return $this->ajaxError('nominations_closed', 405, 'Nominations are closed and so cannot be deleted.');
        }

        // Validate the input
        $this->validate(
            $request,
            [
                'user_id' => 'required|exists:users,id',
                'position' =>
                    'required|in:' .
                    implode(',', array_keys($election->positions)) .
                    '|unique:election_nominations,position,NULL,id,election_id,' .
                    $election->id .
                    ',user_id,' .
                    $request->get('user_id'),
                'manifesto' => 'required|mimes:pdf',
            ],
            [
                'user_id.required' => 'Please select a member',
                'user_id.exists' => 'Please select a valid member',
                'position.required' => 'Please select a position they are running for',
                'position.in' => 'Please select a valid position',
                'position.unique' => 'They are already running for this position',
                'manifesto.required' =>
                    'Please provide their manifesto. If you had, it might be too big to upload (max of 2MB).',
                'manifesto.mimes' => 'Only PDFs are currently supported',
            ],
        );

        // Create the nomination and upload manifesto
        $nomination = $election->nominations()->create($request->only('user_id', 'position'));
        $request->file('manifesto')->move($election->getManifestoPath(), $nomination->getManifestoName());

        Log::info("User {$request->user()->id} created nomination {$nomination->id} for election $electionId");
        Notify::success('Nomination created');
        return $this->ajaxResponse(true);
    }

    /**
     * View a nomination's manifesto.
     *
     * @param $id
     * @param $nominationId
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function manifesto($id, $nominationId)
    {
        // Get the election and nomination
        $election = Election::findOrFail($id); /* @var Election $election */
        $nomination = $election->nominations()->where('id', $nominationId)->first(); /* @var Nomination $nomination */

        // Authorise
        $this->authorize('manifesto', $nomination);

        // Get the manifesto path
        $path = $nomination->getManifestoPath();
        if (!$nomination || !file_exists($path)) {
            Log::error("Could not find manifesto for nomination $nominationId on election $id");
            app()->abort(404);
        }

        // Return the PDF
        return response(file_get_contents($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $nomination->getManifestoName() . '"',
            'Content-Length' => filesize($path),
        ]);
    }

    /**
     * Delete a nomination.
     *
     * @param $id
     * @param $nominationId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, $nominationId)
    {
        // Make sure requested over AJAX
        $this->requireAjax();

        // Get the election
        $election = Election::find($id); /* @var Election $election */
        if (!$election) {
            return $this->ajaxError('404', 404, 'Couldn\'t find that election.');
        }

        // Check that nominations are open
        if (!$election->canModifyNominations()) {
            Log::debug(
                'User ' .
                    request()->user()->id .
                    " tried to delete nomination $nominationId for election $id but nominations are closed",
            );
            return $this->ajaxError('nominations_closed', 405, 'Nominations are closed and so cannot be deleted.');
        }

        // Get the nomination
        $nomination = $election->nominations()->where('id', $nominationId)->first();
        if (!$nomination) {
            return $this->ajaxError('404', 404, 'Couldn\'t find that nomination.');
        }

        // Authorise
        $this->authorize('delete', $nomination);

        // Delete
        $nomination->delete();
        File::delete($nomination->getManifestoPath());

        Log::info('User ' . request()->user()->id . " deleted nomination $nominationId for election $id");

        Notify::success('Nomination deleted');
        return $this->ajaxResponse(true);
    }
}
