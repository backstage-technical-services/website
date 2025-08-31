@extends('app.main')

@section('page-section', 'events')
@section('page-id', 'diary')
@section('title', 'Events Diary')
@section('header-main', 'Events Diary')

@section('javascripts')
    <script src="/js/partials/events.diary.js"></script>
@endsection

@section('content')
    @include('events.diary.date_header')
    @if (Auth::check() && Auth::user()->isMember())
        <div class="customise">
            <div class="btn-group">
                <button
                    class="btn btn-default"
                    data-toggle="modal"
                    data-target="#modal"
                    data-modal-template="select_date"
                    data-modal-class="modal-sm"
                    title="Select date"
                >
                    <span class="fa fa-calendar"></span>
                </button>
                <button
                    class="btn btn-default"
                    data-toggle="modal"
                    data-target="#modal"
                    data-modal-template="export"
                    title="Export"
                >
                    <span class="fa fa-download"></span>
                </button>
                <button class="btn btn-default" id="DiaryPreferences-edit" title="Customise">
                    <span class="fa fa-cogs"></span>
                </button>
                <button
                    class="btn btn-default"
                    data-toggle="modal"
                    data-target="#modal"
                    data-modal-template="help"
                    data-modal-class="modal-sm"
                    title="Help"
                >
                    <span class="fa fa-question"></span>
                </button>
            </div>
            @include('events.diary.preferences')
        </div>
    @endif
    <div class="diary">
        <div class="day-headers">
            <div class="cell">Mon</div>
            <div class="cell">Tue</div>
            <div class="cell">Wed</div>
            <div class="cell">Thu</div>
            <div class="cell">Fri</div>
            <div class="cell">Sat</div>
            <div class="cell">Sun</div>
        </div>
        <div class="calendar">
            @for ($i = 1; $i <= $blank_before; $i++)
                <span class="cell blank"></span>
            @endfor
            @for ($i = 1; $i <= $date->daysInMonth; $i++)
                <div class="cell day{{ $calendar[$i]->today ? ' today' : '' }}">
                    <span class="date">{{ $i }}</span>
                    <div class="event-list">
                        @foreach ($calendar[$i]->events as $event)
                            <div
                                class="event"
                                data-event-type="{{ $event->type_short }}"
                                data-crewing="{{ $event->userIsCrew(Auth::user()) ? 'true' : 'false' }}"
                                {{ $event->visibleInDiary() ? '' : 'style=display:none;' }}
                            >
                                <div class="event-style fill {{ $event->type_class }}"></div>
                                <div class="name{{ $event->type == \App\Models\Events\Event::TYPE_HIDDEN ? ' em' : '' }}">
                                    {!! link_to_route('event.view', $event->name, ['id' => $event->id], ['class' => 'grey']) !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endfor
            @for ($i = 1; $i <= $blank_after; $i++)
                <span class="cell blank"></span>
            @endfor
        </div>
    </div>
    <div class="visible-xs visible-md">
        @include('events.diary.date_header')
    </div>
    @if (Auth::check() && Auth::user()->hasExportToken())
        <input name="member-event-token" type="hidden" value="{{ Auth::user()->getExportToken() }}">
        <input name="member-id" type="hidden" value="{{ Auth::user()->username }}">
    @endif
@endsection

@section('modal')
    @if (Auth::check() && Auth::user()->isMember())
        @include('events.modals.diary.select_date')
        @include('events.modals.diary.export')
        @include('events.modals.diary.help')
    @endif
@endsection
