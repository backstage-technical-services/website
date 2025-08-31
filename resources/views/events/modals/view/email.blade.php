<div data-type="modal-template" data-id="event_email">
    {!! Form::open() !!}
    <div class="modal-header">
        <h1>Email Crew</h1>
    </div>
    <div class="modal-body">
        <div class="form-group">
            {!! Form::label('crew', 'Send to:', ['class' => 'control-label']) !!}
            {!! Form::select('crew', ['core' => 'Core crew only', 'all' => 'All crew'], 'all', ['class' => 'form-control']) !!}
            <p class="help-block">All crew will be able to see this in the Emails tab.</p>
        </div>
        <div class="form-group">
            {!! Form::label('header', 'Email Subject:', ['class' => 'control-label']) !!}
            {!! Form::text('header', null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('body', 'Email Body:', ['class' => 'control-label']) !!}
            {!! Form::textarea('body', null, ['class' => 'form-control', 'rows' => 10]) !!}
            <p class="help-block">This supports {!! link_to('https://simplemde.com/markdown-guide', 'markdown', ['target' => '_blank']) !!}.</p>
        </div>
    </div>
    <div class="modal-footer">
        <button
            class="btn btn-success"
            data-type="submit-modal"
            data-form-action="{{ route('event.email.store', ['id' => $event->id]) }}"
            data-redirect="true"
            data-redirect-location="{{ route('event.view', ['id' => $event->id, 'tab' => 'emails']) }}"
        >
            <span class="fa fa-check"></span>
            <span>Send Email</span>
        </button>
    </div>
    {!! Form::close() !!}
</div>
