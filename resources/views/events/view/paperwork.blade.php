<h2>Event Paperwork</h2>
<div class="paperwork-list">
    {{-- Risk Assessment --}}
    <div class="paperwork">
        @include('events.view._paperwork', ['paperwork' => 'risk_assessment'])
        <div class="name">Risk assessment</div>

        @can('update', $event)
        <p class="link{{ $event->paperwork['risk_assessment'] ? ' hidden' : '' }}" data-show="incomplete">
            <span class="fa fa-link"></span>
            <a class="grey" href="{{ config('bts.links.risk_assessment') }}" target="_blank">Risk assessment form</a>
        </p>
        @endcan
    </div>
    {{-- Insurance --}}
    <div class="paperwork">
        @include('events.view._paperwork', ['paperwork' => 'insurance'])
        <div class="name">Insurance</div>
    </div>
    {{-- TEM Finance --}}
    <div class="paperwork">
        @include('events.view._paperwork', ['paperwork' => 'finance_em'])
        <div class="name">TEM finance</div>
    </div>
    {{-- Treasurer Finance --}}
    <div class="paperwork">
        @include('events.view._paperwork', ['paperwork' => 'finance_treas'])
        <div class="name">Treasurer finance</div>
    </div>
    {{-- Event Report --}}
    <div class="paperwork">
        @include('events.view._paperwork', ['paperwork' => 'event_report'])
        <div class="name">Event report</div>
        
        @can('update', $event)
        <p class="link{{ $event->paperwork['event_report'] ? ' hidden' : '' }}" data-show="incomplete">
            <span class="fa fa-link"></span>
            <a class="grey" href="{{ route('event.report', ['id' => $event->id]) }}">Event report form</a>
        </p>
        @endcan
    </div>
</div>
