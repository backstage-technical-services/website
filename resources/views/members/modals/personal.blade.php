<div data-type="modal-template" data-id="personal">
    {!! Form::model($user, ['route' => ['member.update']]) !!}
    <div class="modal-header">
        <h1>Change Personal Details</h1>
    </div>
    <div class="modal-body">
        <div class="form-group">
            {!! Form::label('name', 'Full Name:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="fa fa-user"></span>
                </span>
                {!! Form::text('name', null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('nickname', 'Nickname:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="fa fa-user-o"></span>
                </span>
                {!! Form::text('nickname', null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('dob', 'Date of Birth:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="fa fa-calendar"></span>
                </span>
                {!! Form::date('dob', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button
            class="btn btn-success"
            data-type="submit-modal"
            data-redirect="true"
            name="update"
            value="personal"
        >
            <span class="fa fa-check"></span>
            <span>Save changes</span>
        </button>
    </div>
    {!! Form::close() !!}
</div>
