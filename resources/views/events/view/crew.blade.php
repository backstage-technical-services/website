<h2>Crew List</h2>

{{-- Crew count --}}
<p class="crew-count">
    @if ($event->isSocial())
        {{ $event->countCrew('members') }} members and {{ $event->countCrew('guests') }} guests
        ({{ $event->countCrew('confirmed') }} paid)
    @elseif($event->isTraining())
        {{ $event->countCrew('em') }} instructors and {{ $event->countCrew('members') - $event->countCrew('em') }}
        attendees
        ({{ $event->countCrew('confirmd') }} attended)
    @else
        {{ $event->countCrew('core') }} core crew and {{ $event->countCrew('general') }} general crew
    @endif
</p>

{{-- Buttons --}}
<div class="top-buttons">
    @can('volunteer', $event)
        <div class="btn-group">
            @if ($event->userIsCrew(Auth::user()))
                @if (!$event->isSocial())
                    <button
                        class="btn btn-danger"
                        data-submit-ajax="{{ route('event.volunteer', ['id' => $event->id]) }}"
                        data-redirect="true"
                        data-redirect-location="{{ route('event.view', ['id' => $event->id, 'tab' => 'crew']) }}"
                        type="button"
                    >
                        <span class="fa fa-user-times"></span>
                        <span>Unvolunteer</span>
                    </button>
                @endif
            @else
                <button
                    class="btn btn-success"
                    data-submit-ajax="{{ route('event.volunteer', ['id' => $event->id]) }}"
                    data-redirect="true"
                    data-redirect-location="{{ route('event.view', ['id' => $event->id, 'tab' => 'crew']) }}"
                    @if ($event->isSocial()) data-submit-confirm="Are you sure you want to sign-up for this social? By signing up you agree to pay any costs, even if you can no longer attend." @endif
                    type="button"
                >
                    <span class="fa fa-user-plus"></span>
                    <span>Volunteer</span>
                </button>
            @endif
        </div>
        @endif
        @can('update', $event)
            <div class="btn-group">
                <button
                    class="btn btn-success"
                    data-toggle="modal"
                    data-target="#modal"
                    data-modal-template="event_crew"
                    data-modal-class="modal-sm"
                    data-modal-title="Add Crew Role"
                    data-form-action="{{ route('event.crew.store', ['id' => $event->id]) }}"
                    data-mode="create"
                    type="button"
                >
                    <span class="fa fa-user-plus"></span>
                    <span>Add crew</span>
                </button>
                @if ($event->isSocial())
                    <button
                        class="btn btn-success dropdown-toggle"
                        data-toggle="dropdown"
                        type="button"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <button
                                data-toggle="modal"
                                data-target="#modal"
                                data-modal-template="event_guest"
                                data-modal-class="modal-sm"
                                data-modal-title="Add Guest"
                                data-form-action="{{ route('event.crew.store', ['id' => $event->id]) }}"
                                data-mode="create"
                                type="button"
                            >
                                <span class="fa fa-user-secret"></span>
                                <span>Add guest user</span>
                            </button>
                        </li>
                    </ul>
                @endif
            </div>
        @endcan
    </div>

    {{-- Crew List --}}
    <div class="crew-list">
        @if ($event->countCrew() > 0)
            <div class="form-static">
                @if ($event->hasEM())
                    <div class="form-entry">
                        <label class="control-label">TEM:</label>
                        <div class="editable">{{ $event->em->name }}</div>
                    </div>
                @endif
                @foreach ($event->crew as $crew_role => $crew_list)
                    @if (count($crew_list) > 0)
                        <div class="form-entry">
                            <label class="control-label">{{ $crew_role }}:</label>
                            <div class="form-control-static">
                                @foreach ($crew_list as $crew)
                                    @can('update', $event)
                                        <div
                                            class="editable"
                                            data-toggle="modal"
                                            data-target="#modal"
                                            data-modal-template="{{ $crew->isGuest() ? 'event_guest' : 'event_crew' }}"
                                            data-modal-title="Edit Crew"
                                            data-modal-class="modal-sm"
                                            data-save-action="{{ route('event.crew.update', ['id' => $event->id, 'crewId' => $crew->id]) }}"
                                            data-delete-action="{{ route('event.crew.destroy', ['id' => $event->id, 'crewId' => $crew->id]) }}"
                                            data-form-data="{{ json_encode($crew) }}"
                                            data-mode="edit"
                                            data-editable="true"
                                        >
                                            @if ($event->isTracked())
                                                <span
                                                    class="editable"
                                                    data-editable="toggle"
                                                    data-action="{{ route('event.crew.update', ['id' => $event->id, 'crewId' => $crew->id]) }}"
                                                    data-field="confirmed"
                                                >
                                                    <span class="fa fa-{{ $crew->confirmed ? 'check' : 'remove' }}"></span>
                                                </span>
                                            @endif
                                            <span>{{ $crew->crew_name }}</span>
                                        </div>
                                    @else
                                        <div class="editable">{{ $crew->crew_name }}</div>
                                    @endcan
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <p>No one is crewing this event yet</p>
        @endif
    </div>
