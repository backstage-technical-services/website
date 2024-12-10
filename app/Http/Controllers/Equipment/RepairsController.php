<?php

namespace App\Http\Controllers\Equipment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Equipment\RepairRequest;
use App\Mail\Equipment\Breakage as BreakageEmail;
use App\Models\Equipment\Breakage;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Intervention\Image\Facades\Image;

class RepairsController extends Controller
{
    /**
     * View the list of outstanding repairs.
     *
     * @return $this
     */
    public function index()
    {
        $this->authorize('index', Breakage::class);

        $breakages = Breakage::where('status', '<>', Breakage::STATUS_RESOLVED)
                             ->where('closed', false)
                             ->orderBy('created_at', 'DESC')
                             ->paginate(20);
        $this->checkPage($breakages);

        return view('equipment.repairs.index')->with('breakages', $breakages);
    }

    /**
     * View the form to create a new repair.
     *
     * @return Factory|View
     */
    public function create()
    {
        $this->authorize('create', Breakage::class);
        return view('equipment.repairs.create');
    }

    /**
     * Process the form and create a new breakage.
     *
     * @param RepairRequest $request
     *
     * @return RedirectResponse
     */
    public function store(RepairRequest $request)
    {
        // Create the breakage
        $breakage = Breakage::create([
            'name'        => clean($request->get('name')),
            'label'       => clean($request->get('label')),
            'location'    => clean($request->get('location')),
            'description' => clean($request->get('description')),
            'status'      => Breakage::STATUS_REPORTED,
            'user_id'     => $request->user()->id,
            'closed'      => false,
        ]); 
        // Save the images
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $i = 0;
            foreach ($images as $image) {
                $filename = $breakage->saveImage($image);
                $image = $breakage->images()->create([
                    'filename' => $filename,
                    'position_id' => $i++,
                    'mime'     => $image->getClientMimeType(),
                ]);                
            }
        }

        // Send the email
        Mail::to(config('bts.emails.equipment.breakage_reports'))
            ->queue(new BreakageEmail($breakage->toArray() + [
                    'user_email'    => $breakage->user->email,
                    'user_name'     => $breakage->user->name,
                    'user_username' => $breakage->user->username,
                ]));

        Log::info("User {$request->user()->id} reported breakage for '{$request->get('name')}'");

        Notify::success('Breakage reported');
        return redirect()->route('equipment.repairs.index');
    }

    /**
     * View the details of a breakage.
     *
     * @param $id
     *
     * @return Factory|View
     */
    public function view($id)
    {
        $breakage = Breakage::findOrFail($id);
        $this->authorize('view', $breakage);
        return view('equipment.repairs.view')->with('breakage', $breakage);
    }

    /**
     * Update a breakage.
     *
     * @param                          $id
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function update($id, Request $request)
    {
        // Get the equipment breakage and authorise
        $breakage = Breakage::findOrFail($id);
        $this->authorize('update', $breakage);

        if ($request->get('action') == 'update') {
            $this->updateBreakageStatus($breakage, $request);

            $statusString = Breakage::$Status[$request->get('status')];
            Log::info("User {$request->user()->id} updated status of breakage $id to '$statusString'");
        } else if ($request->get('action') == 'close') {
            $breakage->update([
                'closed' => true,
            ]);
            Log::info("User {$request->user()->id} closed breakage $id");
            Notify::success('Breakage closed');
        } else if ($request->get('action') == 'reopen') {
            $breakage->update([
                'closed' => false,
            ]);
            Log::info("User {$request->user()->id} reopened breakage $id");
            Notify::success('Breakage re-opened');
        }

        return redirect()->route('equipment.repairs.view', ['id' => $id]);
    }

    /**
     * Update the status of a breakage.
     *
     * @param Breakage $breakage
     * @param Request $request
     */
    private function updateBreakageStatus(Breakage $breakage, Request $request)
    {
        // Validate
        $this->validate($request, [
            'status' => 'required|in:' . implode(',', array_keys(Breakage::$Status)),
        ], [
            'status.required' => 'Please choose a status for the breakage',
            'status.in'       => 'Please choose a valid status',
        ]);

        // Update, message and redirect
        $breakage->update([
            'comment' => clean($request->get('comment')),
            'status'  => clean($request->get('status')),
            'closed'  => (int)$request->get('status') === Breakage::STATUS_RESOLVED,
        ]);
        Notify::success('Breakage updated');
    }

    /**
     * Stream an image.
     * 
     * @param $id
     * @param $image
     * 
     * @return mixed
     */
    public function streamImage($id, $imageId) {
        $breakage = Breakage::findOrFail($id);
        $this->authorize('view', $breakage);

        $image = $breakage->images()->where('position_id', $imageId)->firstOrFail();
        $path = $image->getImagePath(false);

        return response(file_get_contents($path), 200, [
            'Content-Type' => $image->mime,
            'Content-Disposition' => 'inline; filename="break-' . $breakage->id . '-' . $image->position_id . '.' . pathinfo($path, PATHINFO_EXTENSION) . '"',
        ]);
    }

}
