@extends('app.main')

@section('page-section', 'events')
@section('page-id', 'index')
@section('header-main', 'Events')
@section('title', 'Event List')

@section('content')
    <div>
        <a class="btn btn-success" href="{{ route('event.create') }}">
            <span class="fa fa-plus"></span>
            <span>Create event</span>
        </a>
        {!! SearchTools::render() !!}
    </div>
    <table class="table table-striped event-list">
        <thead>
            <th col="event-type"></th>
            <th col="event">Event</th>
            <th col="venue">Venue</th>
            <th col="em">Event Manager</th>
            <th col="crew">Crew</th>
            <th class="admin-tools admin-tools-icon"></th>
        </thead>
        <tbody>
            @forelse($events as $event)
                <tr>
                    <td class="event-style fill {{ $event->type_class }}" col="event-type"></td>
                    <td class="dual-layer" col="event">
                        <span class="upper">{!! link_to_route('event.view', $event->name, ['id' => $event->id], ['class' => 'grey']) !!}</span>
                        <span class="lower">{{ $event->start_date }} &mdash; {{ $event->end_date }}</span>
                    </td>
                    <td col="venue">{{ $event->venue }}</td>
                    <td col="em">
                        @if ($event->hasEM())
                            {{ $event->em->name }}
                        @else
                            <em>- none -</em>
                        @endif
                    </td>
                    <td col="crew">{{ $event->crew()->count() }}</td>
                    <td class="admin-tools admin-tools-icon">
                        <div class="dropdown pull-right">
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <span class="fa fa-cog"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button
                                        data-submit-ajax="{{ route('event.destroy', ['id' => $event->id]) }}"
                                        data-submit-confirm="Are you sure you want to delete this event?"
                                        data-redirect="true"
                                        type="button"
                                    >
                                        <span class="fa fa-trash"></span>
                                        <span>Delete</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">We don't have any events :/</td>
                </tr>
            @endif
        </tbody>
    </table>

    {!! $events !!}
@endsection
