<h2>Event Details</h2>
<div class="form-static">
    <div class="form-entry">
        <label class="control-label">Event Manager:</label>
        <div class="form-control-static">{!! $event->em ? $event->em->name : '<em>&ndash; not yet assigned &ndash;</em>' !!}</div>
    </div>
    <div class="form-entry">
        <label for="type" class="control-label">Type:</label>
        <div class="form-control-static">
            <span class="event-entry tag upper {{ $event->type_class }}">{{ $event->type_string }}</span>
        </div>
    </div>
    @if($event->isEvent())
        <div class="form-entry">
            <label for="client" class="control-label">Client:</label>
            <div class="form-control-static">{{ $event->client }}</div>
        </div>
    @endif
    <div class="form-entry">
        <label for="venue" class="control-label">Venue:</label>
        <p class="form-control-static">{{ $event->venue }}</p>
    </div>
    @can('update', $event)
        @if($event->isEvent())
            <div class="form-entry">
                <label for="production_charge" class="control-label">Production Charge:</label>
                <div class="form-control-static">{{ $event->pretty_production_charge }}</div>
            </div>
        @endif
    @endcan
    <div class="form-entry">
        <label for="description" class="control-label">Description:</label>
        <div class="form-control-static description">
            {!! Markdown::convertToHtml($event->description) !!}
        </div>
    </div>

</div>