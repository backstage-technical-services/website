<div data-type="modal-template" data-id="contact">
    {!! Form::model($user, ['route' => ['member.update']]) !!}
    <div class="modal-header">
        <h1>Change Contact Details</h1>
    </div>
    <div class="modal-body">
        <div class="form-group">
            {!! Form::label('email', 'Email Address:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="fa fa-envelope"></span>
                </span>
                {!! Form::text('email', null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('phone', 'Phone Number:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="fa fa-phone"></span>
                </span>
                {!! Form::text('phone', null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('address', 'Term Address:', ['class' => 'control-label']) !!}
            <div class="input-group textarea">
                <span class="input-group-addon">
                    <span class="fa fa-home"></span>
                </span>
                {!! Form::textarea('address', null, ['class' => 'form-control', 'rows' => 4]) !!}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button
            class="btn btn-success"
            data-type="submit-modal"
            data-redirect="true"
            name="update"
            value="contact"
        >
            <span class="fa fa-check"></span>
            <span>Save changes</span>
        </button>
    </div>
    {!! Form::close() !!}
</div>
