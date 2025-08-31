<div data-type="modal-template" data-id="privacy">
    {!! Form::model($user, ['route' => ['member.update']]) !!}
    <div class="modal-header">
        <h1>Change Privacy Settings</h1>
    </div>
    <div class="modal-body">
        <p>Let other members see my:</p>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    {!! Form::checkbox('show_email', true) !!}
                    Email address
                </label>
            </div>
            <div class="checkbox">
                <label>
                    {!! Form::checkbox('show_phone', true) !!}
                    Phone number
                </label>
            </div>
            <div class="checkbox">
                <label>
                    {!! Form::checkbox('show_address', true) !!}
                    Address
                </label>
            </div>
            <div class="checkbox">
                <label>
                    {!! Form::checkbox('show_age', true) !!}
                    Age
                </label>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button
            class="btn btn-success"
            data-type="submit-modal"
            data-redirect="true"
            name="update"
            value="privacy"
        >
            <span class="fa fa-check"></span>
            <span>Save changes</span>
        </button>
    </div>
    {!! Form::close() !!}
</div>
