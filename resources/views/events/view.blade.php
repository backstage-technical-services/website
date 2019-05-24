@extends('app.main')

@section('page-section', 'events')
@section('page-id', 'view')
@section('title', $event->name)

@section('javascripts')
    <script src="/js/partials/events.view.js"></script>
@endsection

@section('content')
    <h1>{{ $event->name }}</h1>
    <h2>
        {{ $event->start->tzUser()->format('jS M Y') }}
        @if($event->start_date != $event->end_date)
        &mdash; {{ $event->end->tzUser()->format('jS M Y') }}
        @endif
    </h2>
    <div class="tab-vertical" role="tabpanel">
        <div class="tab-links icons">
            <nav>
                <ul class="nav nav-pills nav-stacked" role="tablist">
                    <li {{ $tab == 'details' ? ' class=active' : '' }} title="Event Details">
                        <a href="{{ route('event.view', ['id' => $event->id]) }}">
                            <span class="fa fa-info"></span>
                        </a>
                    </li>
                    @if(!$event->isCrewListHidden() || (Auth::check() && Auth::user()->isAdmin()))
                        <li {{ $tab == 'crew' ? ' class=active' : '' }} title="Event Crew">
                            <a href="{{ route('event.view', ['id' => $event->id, 'tab' => 'crew']) }}">
                                <span class="fa fa-users"></span>
                            </a>
                        </li>
                    @endif
                    <li {{ $tab == 'times' ? ' class=active' : '' }} title="Event Times">
                        <a href="{{ route('event.view', ['id' => $event->id, 'tab' => 'times']) }}">
                            <span class="fa fa-clock-o"></span>
                        </a>
                    </li>
                    @if(Auth::check() && Auth::user()->isCrew($event))
                        <li {{ $tab == 'emails' ? ' class=active' : '' }} title="Event Emails">
                            <a href="{{ route('event.view', ['id' => $event->id, 'tab' => 'emails']) }}">
                                <span class="fa fa-inbox"></span>
                            </a>
                        </li>
                    @endif
                    @if(Auth::check() && Auth::user()->isMember())
                        <li {{ $tab == 'paperwork' ? ' class=active' : '' }} title="Event Resources">
                            <a href="{{ route('event.view', ['id' => $event->id, 'tab' => 'paperwork']) }}">
                                <span class="fa fa-folder"></span>
                            </a>
                            @if(($incomplete = $event->countPaperwork(false)) > 0)
                                <span class="badge">{{ $incomplete }}</span>
                            @endif
                        </li>
                    @endif
                    @can('update', $event)
                        <li {{ $tab == 'settings' ? ' class=active' : '' }} title="Event Settings">
                            <a href="{{ route('event.view', ['id' => $event->id, 'tab' => 'settings']) }}">
                                <span class="fa fa-cogs"></span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </nav>
        </div>
        <div class="tab-content">
            {{-- Event details --}}
            <div class="tab-pane{{ $tab == 'details' ? ' active' : '' }}" id="event_details">
                @include('events.view.details')
            </div>
            {{-- Event crew --}}
            @if(!$event->isCrewListHidden() || (Auth::check() && Auth::user()->isAdmin()))
                <div class="tab-pane{{ $tab == 'crew' ? ' active' : '' }}" id="event_crew">
                    @include('events.view.crew')
                </div>
            @endif
            {{-- Event times--}}
            <div class="tab-pane{{ $tab == 'times' ? ' active' : '' }}" id="event_times">
                @include('events.view.times')
            </div>
            {{-- Event emails --}}
            @if(Auth::check() && Auth::user()->isCrew($event))
                <div class="tab-pane{{ $tab == 'emails' ? ' active' : '' }}" id="event_emails">
                    @include('events.view.emails')
                </div>
            @endif
            {{-- Event paperwork --}}
            @if(Auth::check() && Auth::user()->isMember())
                <div class="tab-pane{{ $tab == 'paperwork' ? ' active' : '' }}" id="event_paperwork">
                    @include('events.view.paperwork')
                </div>
            @endif
            {{-- Event settings --}}
            @can('update', $event)
                <div class="tab-pane{{ $tab == 'settings' ? ' active' : '' }}" id="event_settings">
                    @include('events.view.settings')
                </div>
            @endcan
        </div>
    </div>
@endsection

@section('modal')
    @can('update', $event)
        @include('events.modals.view.crew')
        @include('events.modals.view.time')
        @include('events.modals.view.email')
        @if($event->isSocial())
            @include('events.modals.view.guest')
        @endif
        @include('events.modals.view.crew_list_help')
    @endcan
@endsection