<div data-type="modal-template" data-id="category">
    {!! Form::open() !!}
    <div class="modal-body">
        <div class="form-group">
            {!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
            {!! Form::text('name', null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('slug', 'Slug:', ['class' => 'control-label']) !!}
            {!! Form::text('slug', null, ['class' => 'form-control', 'placeholder' => 'Leave blank to use default']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('flag', 'Type:', ['class' => 'control-label']) !!}
            {!! Form::select('flag', \App\Models\Resources\Category::FLAGS, null, ['class' => 'form-control']) !!}
            <p class="help-block">
                This is used to determine whether the resource should be included in the paperwork section of the
                related event. If in doubt, select 'None'.
            </p>
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
            <span>Add Category</span>
        </button>
    </div>
    {!! Form::close() !!}
</div>
