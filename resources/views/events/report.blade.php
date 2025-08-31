@extends('app.main')

@section('page-section', 'events')
@section('page-id', 'report')
@section('title', 'Submit Event Report')

@section('header-main', 'Submit Event Report')
@if (!is_null($event))
    @section('header-sub', $event->name)
@endif

@section('content')
    <iframe
        width="100%"
        height="700"
        frameborder="0"
        src="{{ config('bts.links.event_report') . '?' . http_build_query(!is_null($event) ? $event->report_prefill : []) }}"
    ></iframe>
    @if (!is_null($event))
        <div class="back">
            <hr>
            <p>
                <a href="{{ route('event.view', ['id' => $event->id]) }}">Back to event page</a>
            </p>
        </div>
    @endif
@endsection
