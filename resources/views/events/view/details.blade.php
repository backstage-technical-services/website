<h2>Event Details</h2>
<div class="form-static">
    <div class="form-entry">
        <label class="control-label">Event Manager:</label>
        <div class="form-control-static">{!! $event->em ? $event->em->name : '<em>&ndash; not yet assigned &ndash;</em>' !!}</div>
    </div>
    <div class="form-entry">
        <label class="control-label" for="type">Type:</label>
        <div class="form-control-static">
            <span class="event-entry tag upper {{ $event->type_class }}">{{ $event->type_string }}</span>
        </div>
    </div>
    @if ($event->isEvent())
        <div class="form-entry">
            <label class="control-label" for="client">Client:</label>
            <div class="form-control-static">{{ $event->client }}</div>
        </div>
    @endif
    <div class="form-entry">
        <label class="control-label" for="venue">Venue:</label>
        <p class="form-control-static">{{ $event->venue }}</p>
    </div>
    @can('update', $event)
        @if ($event->isEvent())
            <div class="form-entry">
                <label class="control-label" for="production_charge">Production Charge:</label>
                <div class="form-control-static">{!! $event->pretty_production_charge ?: '<em>&ndash; none &ndash;</em>' !!}</div>
            </div>
        @endif
    @endcan
    <div class="form-entry">
        <label class="control-label" for="description">Description:</label>
        <div class="form-control-static description">
            {!! Markdown::convertToHtml($event->description) !!}
        </div>
    </div>

</div>
