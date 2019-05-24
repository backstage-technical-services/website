<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use App\Models\Users\User;
use Carbon\Carbon;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event as CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;

class DiaryController extends Controller
{
    /**
     * View the diary within the user's timezone.
     * @param null                     $year
     * @param null                     $month
     * @param \Illuminate\Http\Request $request
     * @return $this
     */
    public function view($year = null, $month = null, Request $request)
    {
        // Get the month of the diary in the user's timezone
        $date       = ($year && $month ? Carbon::create($year, $month, 1) : Carbon::now())->tzUser();
        $month_prev = $date->copy()->subMonth();
        $month_next = $date->copy()->addMonth();

        // Set up the calendar
        $calendar = [];
        for($i = 1; $i <= $date->daysInMonth; $i++) {
            $date->day = $i;
            $events    = Event::onDate($date)
                              ->orderBy('event_times.start', 'ASC');

            // Limit to events if not a member
            if(!$request->user() || !$request->user()->isMember()) {
                $events = $events->where('type', Event::TYPE_EVENT);
            }

            $calendar[$i] = (object) [
                'today'  => $date->isToday(),
                'events' => $events->get(),
            ];
        }

        // Show the view
        return view('events.diary')->with([
            'date'         => $date,
            'calendar'     => $calendar,
            'month_prev'   => route('event.diary', ['year' => $month_prev->year, 'month' => $month_prev->month]),
            'month_next'   => route('event.diary', ['year' => $month_next->year, 'month' => $month_next->month]),
            'blank_before' => ($date->startOfMonth()->dayOfWeek ?: 7) - 1,
            'blank_after'  => 7 - ($date->endOfMonth()->dayOfWeek ?: 7),
        ]);
    }

    /**
     * Export the events diary as a .ics file.
     * @param \Illuminate\Http\Request $request
     * @return mixed
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function export(Request $request)
    {
        // Get the data from the request
        $types    = $request->has('types') ? explode(',', $request->get('types')) : ['event'];
        $crewing  = $request->has('crewing') ? $request->get('crewing') : '*';
        $username = $request->has('user') ? $request->get('user') : null;
        $token    = $request->has('token') ? $request->get('token') : null;

        // Convert the event types to their integer IDs
        $types = array_map(function ($value) {
            return array_search($value, Event::$TypesShort);
        }, $types);

        // Validate the token
        if(count($types) > 1 || $types[0] != Event::TYPE_EVENT || $crewing != '*') {
            $user = User::where('username', $username)->first();
            if(!$user || !$user->hasExportToken() || $token !== hash('sha256', $user->export_token)) {
                throw new TokenMismatchException();
            }
        }

        // Create the calendar
        $calendar = new Calendar('www.bts-crew.com');
        $calendar->setName('Backstage Diary');

        // Get the events
        $events = Event::whereIn('type', $types);
        if($crewing == 'true') {
            $events = $events->userOnCrew($user);
        }
        $events = $events->get();

        // Add each event time to the calendar
        foreach($events as $event) {
            foreach($event->times as $time) {
                $cal_event = (new CalendarEvent())->setDtStart($time->start)
                                                  ->setDtEnd($time->end)
                                                  ->setSummary($event->name . ' - ' . $time->name)
                                                  ->setLocation($event->venue);
                $calendar->addComponent($cal_event);
            }
        }

        // Return the response
        return (new Response($calendar->render(), 200))
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="bts_diary.ics"');
    }
}
