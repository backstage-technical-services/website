<div data-type="modal-template" data-id="password">
    {!! Form::model($user, ['route' => ['member.update']]) !!}
    <div class="modal-header">
        <h1>Change Password</h1>
    </div>
    <div class="modal-body">
        <div class="form-group">
            {!! Form::label('password_new', 'New Password:', ['class' => 'control-label']) !!}
            {!! Form::input('password', 'password_new', null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('password_confirm', 'Confirm Password:', ['class' => 'control-label']) !!}
            {!! Form::input('password', 'password_confirm', null, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-success"
                data-type="submit-modal"
                data-redirect="true"
                name="update"
                value="password">
            <span class="fa fa-check"></span>
            <span>Change password</span>
        </button>
    </div>
    {!! Form::close() !!}
</div>
