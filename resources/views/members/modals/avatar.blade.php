<div data-type="modal-template" data-id="avatar">
    {!! Form::model($user, ['route' => ['member.update'], 'files' => true]) !!}
    <div class="modal-header">
        <h1>Change Profile Picture</h1>
    </div>
    <div class="modal-body">
        <div class="form-group avatar">
            <div class="col-xs-6">
                {!! Form::label('', 'Current Picture:', ['class' => 'control-label']) !!}
            </div>
            <div class="col-xs-6">
                <img src="{{ $user->getAvatarUrl() }}">
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('avatar', 'Select your new picture:', ['class' => 'control-label']) !!}
            {!! Form::file('avatar') !!}
            <p class="help-block small">
                This will be resized to 500px by 500px while maintaining the original aspect ratio. At the moment you
                don't have any ability to crop or zoom, but this may change in the future.
            </p>
        </div>
    </div>
    <div class="modal-footer">
        <div class="btn-group">
            <button
                class="btn btn-success"
                data-type="submit-modal"
                data-redirect="true"
                name="update"
                value="avatar"
            >
                <span class="fa fa-check"></span>
                <span>Change picture</span>
            </button>
            <button
                class="btn btn-danger"
                data-type="submit-modal"
                data-redirect="true"
                name="remove"
                value="avatar"
            >
                <span class="fa fa-remove"></span>
                <span>Remove picture</span>
            </button>
        </div>
    </div>
    {!! Form::close() !!}
</div>
