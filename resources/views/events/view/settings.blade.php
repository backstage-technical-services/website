@section('save_button')
    <button class="btn btn-success" data-disable="click" data-disable-text="Saving ..." name="action" type="submit" value="update">
        <span class="fa fa-check"></span>
        <span>Save Changes</span>
    </button>
@endsection

<h2>Event Settings</h2>
{!! Form::model($event, ['route' => ['event.update', $event->id]]) !!}
<div class="container-fluid">
    <div class="row">
        <div class="col-md-7">
            <fieldset>
                <legend>Event Details</legend>
                {{-- Event name --}}
                <div class="form-group @InputClass('name')">
                    {!! Form::label('name', 'Event Name:', ['class' => 'control-label']) !!}
                    {!! Form::text('name', null, ['class' => 'form-control']) !!}
                    @InputError('name')
                </div>
                {{-- Event manager --}}
                <div class="form-group @InputClass('em_id')">
                    {!! Form::label('em_id', 'Event Manager:',  ['class' => 'control-label']) !!}
                    @if($event->isTEM(Auth::user()) && !Auth::user()->isAdmin())
                        <p class="form-control-static">{!! $event->em->name !!}</p>
                    @else
                        {!! Form::memberList('em_id', null, ['class' => 'form-control', 'select2' => true, 'include_blank' => true, 'blank_text' => '-- No Event Manager --']) !!}
                        @InputError('em_id')
                    @endif
                </div>
                {{-- Event type --}}
                <div class="form-group @InputClass('type')">
                    {!! Form::label('type', 'Event Type:', ['class' => 'control-label']) !!}
                    {!! Form::select('type', \App\Models\Events\Event::$Types, null, ['class' => 'form-control', 'data-type' => 'toggle-visibility']) !!}
                    @InputError('type')
                </div>
                {{-- Event client --}}
                <div class="form-group @InputClass('client_type')"
                     data-visibility-input="type"
                     data-visibility-value="{{ \App\Models\Events\Event::TYPE_EVENT }}">
                    {!! Form::label('client_type', 'Client:', ['class' => 'control-label']) !!}
                    @can('create', \App\Models\Events\Event::class)
                        {!! Form::select('client_type', \App\Models\Events\Event::$Clients, null, ['class' => 'form-control']) !!}
                        @InputError('client_type')
                    @else
                        <p class="form-control-static">{{ $event->client }}</p>
                    @endcan
                </div>
                {{-- Venue type --}}
                <div class="form-group @InputClass('venue_type')"
                     data-visibility-input="type"
                     data-visibility-value="{{ \App\Models\Events\Event::TYPE_EVENT }}">
                    {!! Form::label('venue_type', 'Venue Type:', ['class' => 'control-label']) !!}
                    @can('create', \App\Models\Events\Event::class)
                        {!! Form::select('venue_type', \App\Models\Events\Event::$VenueTypes, null, ['class' => 'form-control']) !!}
                        @InputError('venue_type')
                    @else
                        <p class="form-control-static">{{ \App\Models\Events\Event::$VenueTypes[$event->venue_type ?: 1] }}</p>
                    @endcan
                </div>
                {{-- Venue --}}
                <div class="form-group @InputClass('venue')">
                    {!! Form::label('venue', 'Venue:', ['class' => 'control-label']) !!}
                    {!! Form::text('venue', null, ['class' => 'form-control']) !!}
                    @InputError('venue')
                </div>
                {{-- Production Charge --}}
                @can('create', \App\Models\Events\Event::class)
                    <div class="form-group @InputClass('production_charge')"
                        data-visibility-input="type"
                        data-visibility-value="{{ \App\Models\Events\Event::TYPE_EVENT }}">
                        {!! Form::label('production_charge', 'Production Charge:', ['class' => 'control-label']) !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <span class="fa fa-gbp"></span>
                            </span>
                            {!! Form::text('production_charge', null, ['class' => 'form-control']) !!}
                        </div>
                        @InputError('production_charge')
                    </div>
                @endcan
                {{-- Description --}}
                <div class="form-group @InputClass('description')">
                    {!! Form::label('description', 'Description:', ['class' => 'control-label']) !!}
                    {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 4]) !!}
                    @InputError('description')
                    <p class="help-block alt">This will be visible to the public. This also supports {!! link_to('https://simplemde.com/markdown-guide',
                    'markdown', ['target' => '_blank']) !!}.</p>
                </div>
                <div class="form-group">
                    @yield('save_button')
                </div>
            </fieldset>
        </div>
        <div class="col-md-5">
            <fieldset>
                <legend>Crew List</legend>
                {{-- Status --}}
                <div class="form-group @InputClass('crew_list_status')">
                    {!! Form::label('crew_list_status', 'List Status:', ['class' => 'control-label']) !!}
                    {!! Form::select('crew_list_status', \App\Models\Events\Event::$CrewListStatus, null, ['class' => 'form-control']) !!}
                    <p class="help-block small">
                        <a data-toggle="modal"
                           data-target="#modal"
                           data-modal-template="crew_list_help"
                           data-modal-class="modal-sm"
                           href="#">What does this mean?</a>
                    </p>
                    @InputError('crew_list_status')
                </div>
                {{-- Tools --}}
                <div class="form-group">
                    <div class="btn-group">
                        @yield('save_button')
                        <div class="btn-group">
                            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="fa fa-user-times"></span>
                                <span>Clear crew</span>
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button name="action"
                                            onclick="return confirm('Are you sure you want to clear the crew? This action is irreversible.');"
                                            value="clear-crew:all">
                                        <span>All crew</span>
                                    </button>
                                </li>
                                <li>
                                    <button name="action"
                                            onclick="return confirm('Are you sure you want to clear the general crew? This action is irreversible.');"
                                            value="clear-crew:general">
                                        <span>General crew only</span>
                                    </button>
                                </li>
                                <li>
                                    <button name="action"
                                            onclick="return confirm('Are you sure you want to clear the core crew? This action is irreversible.');"
                                            value="clear-crew:core">
                                        <span>Core crew only</span>
                                    </button>
                                </li>
                                @if($event->isSocial())
                                    <li>
                                        <button name="action"
                                                onclick="return confirm('Are you sure you want to clear the guests? This action is irreversible.');"
                                                value="clear-crew:guests">
                                            <span>Guests only</span>
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </fieldset>
            @can('delete', $event)
                <fieldset>
                    <legend>Delete Event</legend>
                    <div class="form-group">
                        <p>Deleting the event will remove any crew, time and email information. This process cannot be reversed.</p>
                        <button class="btn btn-danger"
                                data-submit-ajax="{{ route('event.destroy', ['id' => $event->id]) }}"
                                data-submit-confirm="Are you sure you want to delete this event? This process is irreversible."
                                data-success-url="{{ route('event.diary') }}"
                                type="button">
                            <span class="fa fa-trash"></span>
                            <span>Delete event</span>
                        </button>
                    </div>
                </fieldset>
            @endcan
        </div>
    </div>
</div>
{!! Form::close() !!}