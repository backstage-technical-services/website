<div data-type="modal-template" data-id="event_guest">
    {!! Form::open() !!}
    <div class="modal-body">
        {{-- Name --}}
        <div class="form-group">
            {!! Form::label('guest_name', 'Guest Name:', ['class' => 'control-label']) !!}
            {!! Form::text('guest_name', null, ['class' => 'form-control']) !!}
        </div>
        {{-- Paid --}}
        <div class="form-group">
            <div class="checkbox">
                <label>
                    {!! Form::checkbox('confirmed', 1, null) !!}
                    This guest has paid
                </label>
            </div>
        </div>
        {{-- Crew id --}}
        {!! Form::input('hidden', 'id', null) !!}
        {!! Form::input('hidden', 'guest', true) !!}
    </div>
    <div class="modal-footer">
        <div class="btn-group">
            <button class="btn btn-success"
                    data-type="submit-modal"
                    data-redirect="true"
                    data-redirect-location="{{ route('event.view', ['id' => $event->id, 'tab' => 'crew']) }}"
                    type="button">
                <span class="fa fa-check"></span>
                <span>Save</span>
            </button>
            <button class="btn btn-danger"
                    data-type="submit-modal"
                    data-submit-confirm="Are you sure you want to delete this guest?"
                    data-redirect="true"
                    data-redirect-location="{{ route('event.view', ['id' => $event->id, 'tab' => 'crew']) }}"
                    name="action"
                    type="button"
                    value="delete">
                <span class="fa fa-trash"></span>
                <span>Delete</span>
            </button>
        </div>
    </div>
    {!! Form::close() !!}
</div>