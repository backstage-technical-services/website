<div data-type="modal-template" data-id="select_date">
    {!! Form::open() !!}
    <div class="modal-header">
        <h1>Select Date</h1>
    </div>
    <div class="modal-body">
        <div class="form-horizontal">
            <div class="form-group">
                {!! Form::label('month', 'Month:', ['class' => 'control-label col-xs-5']) !!}
                <div class="col-xs-5">
                    {!! Form::selectMonth('month', $date->month, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('year', 'Year:', ['class' => 'control-label col-xs-5']) !!}
                <div class="col-xs-5">
                    {!! Form::selectRange('year', $date->year - 8, $date->year + 2, $date->year, ['class' => 'form-control']) !!}
                </div>
            </div>
            {!! Form::input('hidden', 'base_url', route('event.diary')) !!}
        </div>
    </div>
    <div class="modal-footer">
        <div class="btn-group">
            <button
                class="btn btn-success"
                name="action"
                type="button"
                value="select"
            >
                <span class="fa fa-check"></span>
                <span>Select date</span>
            </button>
            <button
                class="btn btn-primary"
                name="action"
                type="button"
                value="today"
            >
                <span class="fa fa-refresh"></span>
                <span>Go to this month</span>
            </button>
        </div>
    </div>
    {!! Form::close() !!}
</div>
