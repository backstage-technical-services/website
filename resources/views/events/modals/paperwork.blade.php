<div data-type="modal-template" data-id="paperwork">
    {!! Form::open() !!}
    <div class="modal-body">
        <div class="form-group">
            {!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
            {!! Form::text('name', null, ['class' => 'form-control']) !!}
        </div>
        @InputError('title')
        <div class="form-group">
            {!! Form::label('template', 'Paperwork Template Link:', ['class' => 'control-label']) !!}
            {!! Form::text('template_link', null, ['class' => 'form-control', 'placeholder' => 'optional']) !!}
        </div>
        @InputError('template_link')
    </div>
    <div class="modal-footer">
        <button class="btn btn-success"
                data-type="submit-modal"
                data-action="save"
                data-redirect="true"
                data-redirect-location="{{ route('event.paperwork', ['id' => $paperwork->id]) }}">
            <span class="fa fa-check"></span>
            <span>Add Paperwork</span>
        </button>
    </div>
    {!! Form::close() !!}
</div>