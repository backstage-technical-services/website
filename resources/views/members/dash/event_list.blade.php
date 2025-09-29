<table class="table table-striped event-list">
    <tbody>
        @foreach($events as $event)
            <tr>
                <td class="col--event-type event-style fill {{ $event->type_class }}"></td>
                <td class="col--event dual-layer">
                    <div class="upper">
                        {!! link_to_route('event.view', $event->name, ['id' =>$event->id], ['class' => 'grey', 'target' => '_blank']) !!}
                    </div>
                    <div class="lower">{{ $event->start->format('H:i D jS M y') }} &ndash; {{ $event->end->format('H:i D jS M y') }}</div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>