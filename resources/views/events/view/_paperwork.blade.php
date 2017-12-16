<span class="editable"
      data-editable="toggle"
      data-action="{{ route('event.update', ['id' => $event->id]) }}"
      data-field="paperwork.{{ $paperwork }}">
            <span class="fa fa-{{ $event->paperwork[$paperwork] ? 'check' : 'remove' }}"></span>
        </span>