<div data-type="modal-template" data-id="nomination">
    {!! Form::open() !!}
    <div class="modal-body">
        @include('awards.seasons.nominations.form')
    </div>
    <div class="modal-footer">
        <div class="btn-group">
            <button
                class="btn btn-success"
                data-action="save"
                data-type="submit-modal"
                data-redirect="true"
                type="button"
            >
                <span class="fa fa-check"></span>
                <span>Save Changes</span>
            </button>
        </div>
    </div>
    {!! Form::close() !!}
</div>
