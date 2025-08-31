<div data-type="modal-template" data-id="event_crew">
    {!! Form::open() !!}
    <div class="modal-body">
        {{-- User --}}
        <div class="form-group">
            {!! Form::label('members', 'Member(s):', ['class' => 'control-label']) !!}
            {!! Form::memberList('user_id', null, ['class' => 'form-control', 'select2' => true, 'include_blank' => true]) !!}
        </div>
        {{-- Core --}}
        <div class="form-group">
            <div class="checkbox">
                <label>
                    {!! Form::checkbox('core', 1, null, ['data-type' => 'toggle-visibility']) !!}
                    Make this user core crew
                </label>
            </div>
        </div>
        <div class="form-group core-details" data-visibility-input="core" data-visibility-state="checked">
            {{-- Role name --}}
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Role name']) !!}
            {{-- EM --}}
            <div class="checkbox">
                <label>
                    {!! Form::checkbox('em', 1, null) !!}
                    This is an EM role
                </label>
            </div>
        </div>
        {{-- Confirmed --}}
        @if ($event->isTracked())
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('confirmed', 1, null) !!}
                        This member has {{ $event->isSocial() ? 'paid' : 'attended' }}
                    </label>
                </div>
            </div>
        @endif
    </div>
    <div class="modal-footer">
        <div class="btn-group">
            <button
                class="btn btn-success"
                data-action="save"
                data-type="submit-modal"
                data-redirect="true"
                data-redirect-location="{{ route('event.view', ['id' => $event->id, 'tab' => 'crew']) }}"
                type="button"
            >
                <span class="fa fa-check"></span>
                <span>Add Crew</span>
            </button>
            <button
                class="btn btn-danger"
                data-action="delete"
                data-type="submit-modal"
                data-submit-confirm="Are you sure you want to delete this crew role?"
                data-redirect="true"
                data-redirect-location="{{ route('event.view', ['id' => $event->id, 'tab' => 'crew']) }}"
                name="action"
                type="button"
                value="delete"
            >
                <span class="fa fa-trash"></span>
                <span>Delete</span>
            </button>
        </div>
    </div>
    {!! Form::close() !!}
</div>
