<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Logger;
use App\Models\Events\Crew;
use App\Models\Events\Event;
use App\Models\Users\User;
use App\Notifications\Events\HasBeenVolunteered;
use App\Notifications\Events\UserHasVolunteered;
use App\Notifications\Events\VolunteeredToCrew;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;
use Illuminate\Http\Request;

class CrewController extends Controller
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

        return $this->isGuestAction($event, $request) ? $this->storeGuest($event, $request) : $this->storeMember($event, $request);
    }

    /**
     * Update a crew role.
     *
     * @param                          $eventId
     * @param                          $crewId
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($eventId, $crewId, Request $request)
    {
        // Authorise all updates
        $this->requireAjax();
        $event = Event::findOrFail($eventId);
        $crew  = $event->crew()
                       ->where('event_crew.id', $crewId)
                       ->firstOrFail();
        $this->authorize('update', $crew);

        if ($request->has('action')) {
            $action = $request->get('action');

            if ($action == 'update-field') {
                return $this->updateField($crew, $request);
            }
        } else {
            if ($this->isGuestAction($event, $request)) {
                return $this->updateGuest($crew, $request);
            } else {
                return $this->updateMember($crew, $request);
            }
        }
    }

    /**
     * @param $eventId
     * @param $crewId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($eventId, $crewId)
    {
        // Authorise
        $event = Event::findOrFail($eventId);
        $crew  = $event->crew()
                       ->where('event_crew.id', $crewId)
                       ->firstOrFail();
        $this->authorize('delete', $crew);

        // Delete
        $crew->delete();

        Log::info("User " . request()->user()->id . " removed crew $crewId from event $eventId");
        Notify::success($crew->isGuest() ? 'Guest removed' : 'Crew role deleted');
        return $this->ajaxResponse($crew->isGuest() ? 'Guest removed' : 'Crew role deleted');
    }

    /**
     * Toggle the volunteering status for the current user.
     *
     * @param                          $eventId
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleVolunteer($eventId, Request $request)
    {
        $this->requireAjax();

        $event = Event::findOrFail($eventId);
        $this->authorize('volunteer', $event);

        if (!$request->user()->isCrew($event)) {
            return $this->volunteer($event, $request);
        } else {
            return $this->unvolunteer($event, $request);
        }
    }

    /**
     * Add a guest to the crew list.
     *
     * @param \App\Models\Events\Event $event
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function storeGuest(Event $event, Request $request)
    {
        // Validate
        $fields = ['guest_name'];
        $this->validate($request, Crew::getValidationRules($fields), Crew::getValidationMessages($fields));

        // Create
        $crew = $event->crew()
                      ->create([
                          'user_id'    => null,
                          'name'       => null,
                          'em'         => false,
                          'confirmed'  => $request->has('confirmed'),
                          'guest_name' => clean($request->get('guest_name')),
                      ]);

        Logger::log('event-crew.create', true, $crew->getAttributes());

        Log::info("User {$request->user()->id} added guest {$crew->id} to event {$event->id}");
        Notify::success('Guest added');
        return $this->ajaxResponse('Guest added');
    }

    /**
     * Add a member to the crew list.
     *
     * @param \App\Models\Events\Event $event
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function storeMember(Event $event, Request $request)
    {
        $user = User::find($request->get('user_id'));

        // Validate
        $fields = ['user_id', 'name'];
        $this->validate($request, Crew::getValidationRules($fields), Crew::getValidationMessages($fields));

        // Check the member doesn't already have the same crew role
        if ($event->crew()->where('user_id', $request->get('user_id'))->where('name', $request->get('name') ?: null)->count()) {
            Log::debug("User {$request->user()->id} tried to add user {$request->get('user_id')} to event {$event->id} but they're already on the crew");
            return $this->ajaxError(0, 422, $user->forename . ' already has that crew role.');
        }

        // Create
        $crew = $event->crew()
                      ->create([
                          'user_id'   => $request->get('user_id'),
                          'name'      => $request->get('core') ? clean($request->get('name')) : null,
                          'em'        => $request->get('core') ? $request->has('em') : false,
                          'confirmed' => $event->isTracked() ? $request->has('confirmed') : false,
                      ]);

        Logger::log('event-crew.create', true, $crew->getAttributes());

        // Send an email to the user
        Log::debug("Sending email to notify user {$request->get('user_id')} they have been added to event {$event->id}");
        User::find($request->get('user_id'))->notify(new HasBeenVolunteered($event));

        Log::info("User {$request->user()->id} added crew {$crew->id} to event {$event->id}");
        Notify::success('Member added to crew');
        return $this->ajaxResponse('Member added to crew');
    }

    /**
     * @param \App\Models\Events\Crew  $crew
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function updateField(Crew $crew, Request $request)
    {
        $field = $request->get('field');
        $value = $request->has('value') ? $request->get('value') : false;

        // Validate the request
        $this->validate($request, [
            'field' => [
                'required',
                'in:confirmed',
            ],
        ], [
            'field.required' => 'No field specified',
            'field.in'       => 'No valid field specified',
        ]);

        $crew->update([
            $field => $value,
        ]);
        Log::info("User {$request->user()->id} updated field $field of crew {$crew->id} for event {$crew->event()->id}");
        return $this->ajaxResponse('Field updated');
    }

    /**
     * Update a guest's details.
     *
     * @param \App\Models\Events\Crew  $crew
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function updateGuest(Crew $crew, Request $request)
    {
        // Validate
        $fields = ['guest_name'];
        $this->validate($request, Crew::getValidationRules($fields), Crew::getValidationMessages($fields));

        // Update
        $crew->update([
            'confirmed'  => $request->has('confirmed'),
            'guest_name' => clean($request->get('guest_name')),
        ]);

        Log::info("User {$request->user()->id} updated guest {$crew->id} for event {$crew->event()->id}");
        Notify::success('Guest updated');
        return $this->ajaxResponse('Guest updated');
    }

    /**
     * Update a member's role.
     *
     * @param \App\Models\Events\Crew  $crew
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function updateMember(Crew $crew, Request $request)
    {
        // Validate
        $fields = ['name'];
        $this->validate($request, Crew::getValidationRules($fields), Crew::getValidationMessages($fields));

        // Update
        $crew->update([
            'name'      => $request->get('core') ? clean($request->get('name')) : null,
            'em'        => $request->get('core') ? $request->has('em') : false,
            'confirmed' => $crew->event->isTracked() ? $request->has('confirmed') : false,
        ]);

        Log::info("User {$request->user()->id} updated crew {$crew->id} for event {$crew->event()->id}");
        Notify::success('Crew role updated');
        return $this->ajaxResponse('Crew role updated');
    }

    /**
     * Volunteer to crew an event.
     *
     * @param \App\Models\Events\Event $event
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function volunteer(Event $event, Request $request)
    {
        // Create the crew role
        $crew = $event->crew()->create([
            'name'    => null,
            'user_id' => $request->user()->id,
        ]);

        Log::debug("Crew role created for user {$request->user()->id} and event $event->id", ['crew' => $crew]);

        // Notify the user and EM
        if ($event->hasEM()) {
            Log::debug("Notifying the TEM ({$event->em->id}) that user has volunteered");
            $event->em->notify(new UserHasVolunteered($crew));
        }

        Log::debug("Notifying the user that they have volunteered");
        $request->user()->notify(new VolunteeredToCrew($event));

        Logger::log('event.volunteer', true, $crew->getAttributes());

        // Message
        Log::info("User {$request->user()->id} volunteered to event $event->id");
        Notify::success('You have volunteered');
        return $this->ajaxResponse('Volunteered');
    }

    /**
     * Unvolunteer from an event.
     *
     * @param \App\Models\Events\Event $event
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function unvolunteer(Event $event, Request $request)
    {
        // Disallow unvolunteering from socials
        if ($event->isSocial()) {
            Log::debug("Event {$event->id} is a social event so unvolunteering is disallowed");
            return $this->ajaxError(0, 401, 'You can\'t unvolunteer from a social.');
        }

        // Delete all crew entries
        $event->crew()
              ->where('user_id', $request->user()->id)
              ->delete();

        Logger::log('event.unvolunteer', true, ['event_id' => $event->id, 'user_id', $request->user()->id]);

        Log::info("User {$request->user()->id} unvolunteered from event {$event->id}.");
        Notify::success('You have unvolunteered');
        return $this->ajaxResponse('Unvolunteered');
    }

    /**
     * Test whether the action is for a member or a guest crew role.
     *
     * @param \App\Models\Events\Event $event
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    private function isGuestAction(Event $event, Request $request)
    {
        return $event->isSocial() && $request->has('guest') && $request->get('guest');
    }
}
