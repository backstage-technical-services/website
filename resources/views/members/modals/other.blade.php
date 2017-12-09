<div data-type="modal-template" data-id="other">
    {!! Form::model($user, ['route' => ['member.update']]) !!}
    <div class="modal-header">
        <h1>Change Other Settings</h1>
    </div>
    <div class="modal-body">
        <div class="form-group">
            {!! Form::label('event_export', 'Exporting events:') !!}
            <div class="checkbox">
                <label>
                    {!! Form::checkbox('event_export', '1', $user->hasExportToken()) !!}
                    Allow customisation of events to export
                </label>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('tool_colours', 'Tool Colours:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="fa fa-wrench"></span>
                </span>
                {!! Form::text('tool_colours', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-success"
                data-type="submit-modal"
                data-redirect="true"
                name="update"
                value="other">
            <span class="fa fa-check"></span>
            <span>Save changes</span>
        </button>
    </div>
    {!! Form::close() !!}
</div>