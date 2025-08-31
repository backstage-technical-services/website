<div data-type="modal-template" data-id="training_category">
    {!! Form::open() !!}
    <div class="modal-body">
        <div class="form-group">
            {!! Form::label('name', 'Category Name:', ['class' => 'control-label']) !!}
            {!! Form::text('name', null, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="modal-footer">
        <button
            class="btn btn-success"
            data-action="save"
            data-type="submit-modal"
            data-redirect="true"
            type="button"
        >
            <span class="fa fa-check"></span>
            <span>Add category</span>
        </button>
    </div>
    {!! Form::close() !!}
</div>
