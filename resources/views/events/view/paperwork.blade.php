<h2>Event Paperwork</h2>

<div class="paperwork-list">
    @foreach($event->paperwork as $paperwork)
        <div class="paperwork">
            {{-- Complete / Missing icons--}}
            <span class="editable"
                  data-editable="toggle"
                  data-action="{{ route('event.update', ['id' => $event->id]) }}"
                  data-field="paperwork.{{ $paperwork->id }}">
                    <span class="fa fa-{{ $paperwork->pivot->completed ? 'check' : 'remove' }}"></span>
            </span>

            {{-- Paperwork Title--}}
            <div class="name">{{ $paperwork->name }}</div>

            {{-- Paperwork Link--}}
            @if ($paperwork->pivot->link)
                <p class="link">
                    <span class="fa fa-link"></span>
                    <a class="grey" href="{{ $paperwork->pivot->link }}" target="_blank">
                        {{$paperwork->name}}
                    </a>
                </p>
            @endif

            {{-- Template Link--}}
            @if ($paperwork->template_link)
                <p class="link {{ $paperwork->pivot->completed ? 'hidden' : '' }}" data-show="incomplete">
                    <span class="fa fa-link"></span>
                    <a class="grey" href="{{ $paperwork->template_link }}" target="_blank">
                        {{$paperwork->name}} form
                    </a>
                </p>
            @endif
        </div>
    @endforeach
</div>