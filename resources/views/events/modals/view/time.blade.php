<div data-type="modal-template" data-id="event_time">
    {!! Form::open() !!}
    <div class="modal-body">
        {{-- Name --}}
        <div class="form-group">
            {!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
            {!! Form::text('name', null, ['class' => 'form-control']) !!}
        </div>
        {{-- Start --}}
        <div class="form-group">
            {!! Form::label('start', 'Start:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="fa fa-calendar"></span>
                </span>
                {!! Form::datetime('start', null, ['class' => 'form-control', 'data-date-format' => 'YYYY-MM-DD HH:mm']) !!}
            </div>
        </div>
        {{-- End --}}
        <div class="form-group">
            {!! Form::label('end', 'Finish:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="fa fa-calendar"></span>
                </span>
                {!! Form::datetime('end', null, ['class' => 'form-control', 'data-date-format' => 'YYYY-MM-DD HH:mm']) !!}
            </div>
        </div>
        {!! Form::input('hidden', 'id', null) !!}
    </div>
    <div class="modal-footer">
        <div class="btn-group">
            <button class="btn btn-success"
                    data-action="save"
                    data-type="submit-modal"
                    data-mode="create"
                    data-redirect="true"
                    data-redirect-location="{{ route('event.view', ['id' => $event->id, 'tab' => 'times']) }}"
                    type="button">
                <span class="fa fa-check"></span>
                <span>Add Time</span>
            </button>
            <button class="btn btn-danger"
                    data-action="delete"
                    data-type="submit-modal"
                    data-submit-confirm="Are you sure you want to delete this event time?"
                    data-redirect="true"
                    data-redirect-location="{{ route('event.view', ['id' => $event->id, 'tab' => 'times']) }}"
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