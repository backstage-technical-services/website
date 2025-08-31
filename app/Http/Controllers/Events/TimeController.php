<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use App\Models\Events\Time;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeController extends Controller
{
    /**
     * Set the basic authentication requirements.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Process the form and create the new crew role.
     *
     * @param                          $eventId
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($eventId, Request $request)
    {
        // Authorise
        $this->requireAjax();
        $event = Event::findOrFail($eventId);
        $this->authorize('update', $event);

        // Validate
        $fields = ['name', 'start', 'end'];
        $this->validate($request, Time::getValidationRules($fields), Time::getValidationMessages($fields));

        // Create the time
        $time = $event->times()->create([
            'name' => clean($request->get('name')),
            'start' => Carbon::createFromFormat('Y-m-d H:i', $request->get('start')),
            'end' => Carbon::createFromFormat('Y-m-d H:i', $request->get('end')),
        ]);

        Log::info("User {$request->user()->id} created event time {$time->id} for event $eventId");
        Notify::success('Event time created');
        return $this->ajaxResponse('Event time created');
    }

    /**
     * Update an event time.
     *
     * @param                          $eventId
     * @param                          $timeId
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($eventId, $timeId, Request $request)
    {
        // Authorise
        $this->requireAjax();
        $event = Event::findOrFail($eventId);
        $time = $event->times()->where('id', $timeId)->firstOrFail();
        $this->authorize('update', $time);

        // Validate
        $fields = ['name', 'start', 'end'];
        $this->validate($request, Time::getValidationRules($fields), Time::getValidationMessages($fields));

        // Update
        $time->update([
            'name' => clean($request->get('name')),
            'start' => Carbon::createFromFormat('Y-m-d H:i', $request->get('start')),
            'end' => Carbon::createFromFormat('Y-m-d H:i', $request->get('end')),
        ]);

        Log::info("User {$request->user()->id} updated event time $timeId for event $eventId");
        Notify::success('Event time updated');
        return $this->ajaxResponse('Event time updated');
    }

    /**
     * Delete an event time.
     *
     * @param                          $eventId
     * @param                          $timeId
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($eventId, $timeId, Request $request)
    {
        // Authorise
        $event = Event::findOrFail($eventId);
        $time = $event->times()->where('id', $timeId)->firstOrFail();
        $this->authorize('delete', $time);

        // Check that it isn't the last event time
        if ($event->times()->count() == 1) {
            Log::warning(
                "User {$request->user()->id} tried to delete event time $timeId but event $eventId has no other event times",
            );
            return $this->ajaxError(0, 422, 'An event needs at least 1 event time.');
        }

        // Delete
        $time->delete();

        Log::info("User {$request->user()->id} deleted event time $timeId for event $eventId");
        Notify::success('Event time deleted');
        return $this->ajaxResponse('Event time deleted');
    }
}
