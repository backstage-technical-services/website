@extends('app.main')

@section('page-section', 'members')
@section('page-id', 'dash')
@section('title', 'Members\' Dashboard')
@section('header-main', 'Members\' Dashboard')

@section('content')
    <div id="dash--upcoming">
        <div class="event-list">
            <h2>Upcoming Events</h2>
            @if(count($events))
                @include('members.dash.event_list', ['events' => $events])
            @else
                <h3>There are no upcoming events</h3>
            @endif
        </div>
        <div class="event-list">
            <h2>Upcoming Training</h2>
            @if(count($events))
                @include('members.dash.event_list', ['events' => $training])
            @else
                <h3>There are no upcoming training sessions</h3>
            @endif
        </div>
        <div class="event-list">
            <h2>Upcoming Socials</h2>
            @if(count($events))
                @include('members.dash.event_list', ['events' => $socials])
            @else
                <h3>There are no upcoming socials</h3>
            @endif
        </div>
    </div>
    <div id="dash--central">
        @if(count($paperwork) > 0)
            <h2>Your events with outstanding paperwork</h2>
            <table class="table table-striped event-paperwork">
                <thead>
                    <th class="col--event"></th>
                    <th class="col--ra">Risk Assessment</th>
                    <th class="col--insurance">Insurance</th>
                    <th class="col--finance">Finance</th>
                    <th class="col--report">Event Report</th>
                </thead>
                <tbody>
                    @foreach($paperwork as $event)
                        <tr>
                            <td class="col--event">
                                {!! link_to_route('event.view', $event->name, ['id' => $event->id, 'tab' => 'paperwork'], ['class' => 'grey', 'target' =>
                            '_blank']) !!}
                                <div class="mobile-only">
                                    <ul>
                                        <li>@include('members.dash.paperwork', ['paperwork' => 'risk_assessment']) Risk Assessment</li>
                                        <li>@include('members.dash.paperwork', ['paperwork' => 'insurance']) Insurance</li>
                                        <li>@include('members.dash.paperwork', ['paperwork' => 'finance_em']) Finance</li>
                                        <li>@include('members.dash.paperwork', ['paperwork' => 'event_report']) Event Report</li>
                                    </ul>
                                </div>
                            </td>
                            <td class="col--ra paperwork-status">
                                @include('members.dash.paperwork', ['paperwork' => 'risk_assessment'])
                            </td>
                            <td class="col--insurance paperwork-status">
                                @include('members.dash.paperwork', ['paperwork' => 'insurance'])
                            </td>
                            <td class="col--finance paperwork-status">
                                @include('members.dash.paperwork', ['paperwork' => 'finance_em'])
                            </td>
                            <td class="col--report paperwork-status">
                                @include('members.dash.paperwork', ['paperwork' => 'event_report'])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        @if(count($need_tem))
            <h2>Events needing a TEM</h2>
            <table class="table table-striped event-tem">
                <tbody>
                    @foreach($need_tem as $event)
                        <tr>
                            <td class="col--event dual-layer">
                                <div class="upper">
                                    {!! link_to_route('event.view', $event->name, ['id' => $event->id, 'tab' => 'paperwork'], ['class' => 'grey', 'target' => '_blank']) !!}
                                </div>
                                <div class="lower">
                                    <div class="lower">{{ $event->start->format('H:i D jS M y') }} &ndash; {{ $event->end->format('H:i D jS M y') }}</div>
                                </div>
                            </td>
                            <td class="col--venue">
                                {!! $event->venue !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>




@endsection