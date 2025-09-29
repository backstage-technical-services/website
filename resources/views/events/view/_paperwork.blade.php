@can('update', $event)
    <span class="editable"
          data-editable="toggle"
          data-action="{{ route('event.update', ['id' => $event->id]) }}"
          data-field="paperwork.{{ $paperwork }}">
        <span class="fa fa-{{ $event->paperwork[$paperwork] ? 'check' : 'remove' }}"></span>
    </span>
@else
    <span class="editable data-editable data-editable--toggle">
        <span class="fa fa-{{ $event->paperwork[$paperwork] ? 'check' : 'remove' }}"></span>
    </span>
@endcan
