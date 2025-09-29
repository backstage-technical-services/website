<div id="events">
    <div class="pagination-count">
        Showing {{ ($events->currentPage() - 1) * $events->perPage() + 1 }} - {{ ($events->currentPage() - 1) * $events->perPage() + $events->count() }} of {{
    $events->total() }} events
    </div>
    <table class="table table-striped event-list">
        <thead>
            <tr>
                <th col="event-type"></th>
                <th col="event">Event</th>
                <th col="role">Role</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
                <tr>
                    <td class="event-style fill {{ $event->type_class }}" col="event-type"></td>
                    <td class="dual-layer" col="event">
                        <span class="upper">{!! link_to_route('event.view', $event->name, ['id' => $event->id], ['class' => 'grey']) !!}</span>
                        <span class="lower">{{ $event->start_date }} &mdash; {{ $event->end_date }}</span>
                    </td>
                    <td col="role">
                        {{ $user->getCrewRole($event) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">{{ $user->forename }} doesn't have any events yet</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $events }}
</div>