<div class="modal-body">
    {!! Form::open() !!}
    {{-- Text field for the name --}}
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon"><span class="fa fa-user"></span></span>
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'What is the role called?']) !!}
        </div>
    </div>

    {{-- Text field for the email --}}
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon"><span class="fa fa-at"></span></span>
            {!! Form::input('email', 'email', null, ['class' => 'form-control', 'placeholder' => 'What is the role\'s email address?']) !!}
        </div>
    </div>

    {{-- Textarea for the description --}}
    <div class="form-group">
        <div class="input-group textarea">
            <span class="input-group-addon"><span class="fa fa-quote-left"></span></span>
            {!! Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => 'Describe the role', 'rows' => 5]) !!}
        </div>
        <p class="help-block small">Use the phrase <code>[name]</code> to insert the user's forename. This also
            supports {!! link_to('https://simplemde.com/markdown-guide', 'markdown', ['target' => '_blank']) !!}.</p>
    </div>

    {{-- Select field for the user --}}
    <div class="form-group">
        {!! Form::label('user_id', 'Assign user:') !!}
        {!! Form::memberList('user_id', null, ['class' => 'form-control', 'include_blank' => true]) !!}
    </div>

    {{-- Select field for order --}}
    <div class="form-group">
        {!! Form::label('order', 'Add this role:') !!}
        {!! Form::select('order', $order, null, ['class' => 'form-control']) !!}
    </div>

    {{-- Hidden input for the id --}}
    {!! Form::input('hidden', 'id', null) !!}
    {!! Form::close() !!}
</div>
<div class="modal-footer">
    <div class="btn-group">
        <button class="btn btn-success"
                data-action="save"
                data-type="submit-modal"
                data-redirect="true"
                name="action"
                value="save">
            <span class="fa fa-check"></span>
            <span>Add role</span>
        </button>
        <button class="btn btn-danger"
                data-action="delete"
                data-type="submit-modal"
                data-submit-confirm="Are you sure you want to delete this committee role?"
                data-form-action="{{ route('committee.delete') }}"
                data-redirect="true"
                name="action"
                value="delete">
            <span class="fa fa-trash"></span>
            <span>Delete</span>
        </button>
    </div>
</div>