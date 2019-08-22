<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\EventRequest;
use App\Mail\Events\AcceptedExternal;
use App\Mail\Events\FinanceEmail;
use App\Models\Events\Event;
use bnjns\LaravelNotifications\Facades\Notify;
use bnjns\SearchTools\SearchTools;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventController extends Controller
{
    /**
     * Set the basic authentication requirements.
     */
    public function __construct()
    {
        $this->middleware('auth')
             ->except(['view']);
    }

    /**
     * View a list of events.
     *
     * @param SearchTools $searchTools
     *
     * @return $this
     * @throws AuthorizationException
     */
    public function index(SearchTools $searchTools)
    {
        $this->authorize('index', Event::class);

        // Start the query
        $events = Event::newestFirst();

        // Add the search requirement
        $search = $searchTools->search();
        if (!is_null($search) && $search) {
            $events = $events->where(function ($query) use ($search) {
                $query->where('events.name', 'LIKE', '%' . $search . '%')
                      ->orWhere('events.venue', 'LIKE', '%' . $search . '%');
            });
        }

        // Paginate the results
        $events = $events->distinctPaginate(20);
        $this->checkPage($events);

        return view('events.index')->with('events', $events);
    }

    /**
     * View the form to create an event.
     *
     * @return Factory|View
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Event::class);
        return view('events.create');
    }

    /**
     * Process the form and add the event to the database.
     *
     * @param EventRequest $request
     *
     * @return RedirectResponse
     */
    public function store(EventRequest $request)
    {
        // Create the event
        $event = Event::create([
            'name'             => clean($request->get('name')),
            'venue'            => clean($request->get('venue')),
            'description'      => clean($request->get('description')),
            'type'             => clean($request->get('type')),
            'client_type'      => clean($request->get('type') == Event::TYPE_EVENT ? $request->get('client_type') : null),
            'venue_type'       => clean($request->get('type') == Event::TYPE_EVENT ? $request->get('venue_type') : null),
            'crew_list_status' => Event::CREW_LIST_OPEN,
            'em_id'            => clean($request->get('em_id') ?: null),
            'paperwork'        => [
                'risk_assessment' => false,
                'insurance'       => false,
                'finance_em'      => false,
                'finance_treas'   => false,
                'event_report'    => false,
            ],
            'production_charge' => clean($request->get('production_charge')),
        ]);

        // Set the event time limits
        $start_time = explode(':', $request->get('time_start'));
        $end_time   = explode(':', $request->get('time_end'));
        $date       = Carbon::createFromFormat('Y-m-d', $request->get('date_start'))
                            ->setTime(0, 0, 0);
        $date_end   = Carbon::createFromFormat('Y-m-d', $request->has('one_day') ? $request->get('date_start') : $request->get('date_end'))
                            ->setTime(23, 59, 59);

        // Create each event time
        while ($date->lte($date_end)) {
            $event->times()->create([
                'name'  => $event->name,
                'start' => $date->copy()->setTime($start_time[0], $start_time[1]),
                'end'   => $date->copy()->setTime($end_time[0], $end_time[1]),
            ]);
            $date->day++;
        }

        // Add to the finance database
        $event->addToFinanceDb();

        // If the event is external and off-campus email the SU
        if ($event->client_type > 1 && $event->venue_type == 2) {
            Mail::to(config('bts.emails.events.external_accepted.to'))
                ->queue(new AcceptedExternal($event, $request));
        }


        // Create a flash message and redirect
        Notify::success('Event created');

        if ($request->get('action') == 'create-another') {
            return redirect()->back();
        } else {
            return redirect()->route('event.view', ['id' => $event->id]);
        }
    }

    /**
     * View an event's details.
     *
     * @param                          $eventId
     * @param Request $request
     *
     * @return $this
     * @throws AuthorizationException
     */
    public function view($eventId, Request $request)
    {
        // Get the event
        $event = Event::findOrFail($eventId);

        // Check the user can view the event
        if ($event->type !== Event::TYPE_EVENT && (!Auth::check() || !$request->user()->isMember())) {
            throw new AuthorizationException();
        }

        return view('events.view')->with([
            'event' => $event,
            'tab'   => $request->has('tab') ? $request->get('tab') : 'details',
        ]);
    }

    /**
     * Update the event.
     *
     * @param                          $eventId
     * @param Request $request
     *
     * @return
     * @throws AuthorizationException
     */
    public function update($eventId, Request $request)
    {
        $event = Event::findOrFail($eventId);
        $this->authorize('update', $event);

        $action = $request->get('action');
        if ($action == 'update') {
            return $this->updateDetails($event, $request);
        } else if (preg_match('/^clear-crew:(.*)$/', $action, $matches)) {
            return $this->updateClearCrew($event, $matches[1]);
        } else if ($action == 'update-field' && $request->ajax() && preg_match('/^paperwork.(.*)$/', $request->get('field'), $matches)) {
            return $this->updatePaperwork($event, $matches[1], $request->get('value'));
        } else {
            return redirect()->route('event.view', ['id' => $eventId, 'tab' => 'settings']);
        }
    }

    /**
     * Clear the crew list
     *
     * @param Event $event
     * @param                          $mode
     *
     * @return RedirectResponse
     */
    private function updateClearCrew(Event $event, $mode)
    {
        if ($mode == 'all') {
            $event->crew()
                  ->delete();
            Notify::success('Crew list cleared');
        } else if ($mode == 'general') {
            $event->crew()
                  ->general()
                  ->delete();
            Notify::success('General crew cleared');
        } else if ($mode == 'core') {
            $event->crew()
                  ->core()
                  ->delete();
            Notify::success('Core crew cleared');
        } else if ($mode == 'guests' && $event->isSocial()) {
            $event->crew()
                  ->guest()
                  ->delete();
            Notify::success('Guests cleared');
        }

        return redirect()->route('event.view', ['id' => $event->id, 'tab' => 'crew']);
    }

    /**
     * Update the event's details.
     *
     * @param Event $event
     * @param Request $request
     *
     * @return $this|RedirectResponse
     */
    private function updateDetails(Event $event, Request $request)
    {
        // Determine the fields to update
        $fields = [
            'name',
            'type',
            'client_type',
            'venue_type',
            'venue',
            'description',
            'crew_list_status',
        ];
        if (!$event->isTEM($request->user()) || $request->user()->isAdmin()) {
            $fields[] = 'em_id';
        }
        if ($request->user()->can('create', Event::class)) {
            $fields[] = 'client_type';
            $fields[] = 'venue_type';
            $fields[] = 'production_charge';
        }

        // Set up the validation
        $rules     = Event::getValidationRules($fields);
        $messages  = Event::getValidationMessages($fields);
        $validator = validator($request->only($fields), $rules, $messages);

        // Test validation
        if ($validator->fails()) {
            return redirect()->route('event.view', ['id' => $event->id, 'tab' => 'settings'])
                             ->withInput($request->input())
                             ->withErrors($validator);
        }

        // Update the event
        $event->update(clean($request->only($fields)));

        // If the event is no longer a social, remove any guests
        if ($event->type != Event::TYPE_SOCIAL) {
            $event->crew()
                  ->guest()
                  ->delete();
        }

        Notify::success('Event updated');
        return redirect()->route('event.view', ['id' => $event->id, 'tab' => 'settings']);
    }

    /**
     * Update the event paperwork.
     *
     * @param Event $event
     * @param                          $paperwork
     * @param                          $value
     *
     * @return JsonResponse
     */
    private function updatePaperwork(Event $event, $paperwork, $value)
    {
        if (!isset(Event::$Paperwork[$paperwork])) {
            return $this->ajaxError(0, 404, 'Unknown paperwork');
        }

        $event->setPaperwork($paperwork, $value);
        return $this->ajaxResponse('Paperwork status updated');
    }

    /**
     * Delete an event.
     *
     * @param $eventId
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy($eventId)
    {
        $this->requireAjax();
        $this->authorize('delete', Event::class);

        Event::findOrFail($eventId)
             ->delete();

        Notify::success('Event deleted.');
        return $this->ajaxResponse('Event deleted.');
    }

    /**
     * Allow for simple searching through the events for using in select2 inputs.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function search(Request $request)
    {
        // Authorise
        $this->requireAjax();
        $this->authorizeGate('member');

        // Start the query
        $events = Event::select('events.*');

        // Search the name
        if ($request->has('name')) {
            $events = $events->where('events.name', 'LIKE', '%' . $request->get('name') . '%');
        }

        // Get and sort the results
        $events = $events->distinct()
                         ->join('event_times', 'events.id', '=', 'event_times.event_id')
                         ->orderBy('event_times.end', 'DESC')
                         ->get()
                         ->map(function ($event) {
                             return (object)[
                                 'id'   => $event->id,
                                 'name' => $event->name,
                                 'date' => $event->end->format('M Y'),
                             ];
                         })
                         ->toArray();

        return $this->ajaxResponse($events);
    }

    /**
     * View the event report form.
     *
     * @param null $eventId
     *
     * @return View
     * @throws AuthorizationException
     */
    public function report($eventId = null)
    {
        if (!is_null($eventId)) {
            $event = Event::findOrFail($eventId);
            $this->authorize('update', $event);

            if (!$event->isEvent()) {
                throw new NotFoundHttpException;
            }
        } else {
            $this->authorizeGate('member');
        }

        return view('events.report', [
            'event' => is_null($eventId) ? null : $event,
        ]);
    }

    /**
     * @param                          $eventId
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sendFinanceEmail($eventId, Request $request)
    {
        // Check the key is valid
        if (config('bts.finance_db.key') == $request->get('appKey')) {
            // Check the event exists
            $event = Event::find($eventId);
            if ($event && $event->hasEM()) {
                Mail::to($event->em->email, $event->em->name)
                    ->queue(new FinanceEmail($request, $event));

                return response()->json(['code' => 200, 'result' => 'success']);
            } else {
                return response()->json(['code' => '500', 'result' => 'Event not found or no EM assigned'], 500);
            }
        } else {
            return response()->json(['code' => 500, 'result' => 'Incorrect key'], 500);
        }
    }
}