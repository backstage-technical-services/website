<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Events\Crew;
use App\Models\Events\Event;
use App\Models\Users\User;
use App\Notifications\Events\HasBeenVolunteered;
use App\Notifications\Events\UserHasVolunteered;
use App\Notifications\Events\VolunteeredToCrew;
use bnjns\LaravelNotifications\Facades\Notify;
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
        $event->crew()
              ->create([
                  'user_id'    => null,
                  'name'       => null,
                  'em'         => false,
                  'confirmed'  => $request->has('confirmed'),
                  'guest_name' => clean($request->get('guest_name')),
              ]);

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
            return $this->ajaxError(0, 422, $user->forename . ' already has that crew role.');
        }

        // Create
        $event->crew()
              ->create([
                  'user_id'   => $request->get('user_id'),
                  'name'      => $request->get('core') ? clean($request->get('name')) : null,
                  'em'        => $request->get('core') ? $request->has('em') : false,
                  'confirmed' => $event->isTracked() ? $request->has('confirmed') : false,
              ]);

        // Send an email to the user
        User::find($request->get('user_id'))->notify(new HasBeenVolunteered($event));

        Notify::success('Member added to crew');
        return $this->ajaxResponse('Member added to crew');
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

        Notify::success('Crew role updated');
        return $this->ajaxResponse('Crew role updated');
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

        // Notify the user and EM
        if ($event->hasEM()) {
            $request->user()->notify(new VolunteeredToCrew($event));
            $event->em->notify(new UserHasVolunteered($crew));
        }

        // Message
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
            return $this->ajaxError(0, 401, 'You can\'t unvolunteer from a social.');
        }

        // Delete all crew entries
        $event->crew()
              ->where('user_id', $request->user()->id)
              ->delete();

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