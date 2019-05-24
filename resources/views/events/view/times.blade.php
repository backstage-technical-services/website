<h2>Event Times</h2>
@can('update', $event)
<div class="top-buttons">
    <button class="btn btn-success"
            data-toggle="modal"
            data-target="#modal"
            data-modal-template="event_time"
            data-modal-title="Add Event Time"
            data-modal-class="modal-sm"
            data-form-action="{{ route('event.time.store', ['id' => $event->id]) }}"
            data-mode="create"
            type="button">
        <span class="fa fa-plus"></span>
        <span>Add event time</span>
    </button>
</div>
@endcan
<div class="event-time-wrapper">
    <div class="event-times">
        <?php $month = ''; ?>
        <?php $day = ''; ?>
        @foreach($event->times as $time)
            <?php $start = $time->start->tzUser(); ?>
            <?php $end = $time->end->tzUser(); ?>
            @can('update', $time)
                <div class="event-time editable"
                     data-toggle="modal"
                     data-target="#modal"
                     data-modal-template="event_time"
                     data-modal-title="Edit Event Time"
                     data-modal-class="modal-sm"
                     data-save-action="{{ route('event.time.update', ['id' => $event->id, 'timeId' => $time->id]) }}"
                     data-delete-action="{{ route('event.time.destroy', ['id' => $event->id, 'timeId' => $time->id]) }}"
                     data-form-data="{{ json_encode(array('id'    => $time->id,
                                                          'name'  => $time->name,
                                                          'start' => $start->toDateTimeString(),
                                                          'end'   => $end->toDateTimeString() )) }}"
                     data-mode="edit"
                     data-editable="true">
            @else
                <div class="event-time editable">
            @endcan
                <div class="date">
                    <div class="day">
                        @if($start->format('D j') != $day)
                            <span class="weekday">{{ $start->format('D') }}</span>
                            <span class="num">{{ $start->format('j') }}</span>
                            <?php $day = $start->format('D j'); ?>
                        @endif
                    </div>
                    <div class="month">
                        @if($start->format('M Y') != $month)
                            {{ $start->format('M Y') }}
                            <?php $month = $start->format('M Y'); ?>
                            <?php $day = ''; ?>
                        @endif
                    </div>
                </div>
                <div class="time">{{ $start->format('H:i') }} &mdash; {{ $end->format('H:i') }}</div>
                <div class="name">{{ $time->name }}</div>
        </div>
        @endforeach
    </div>
</div>