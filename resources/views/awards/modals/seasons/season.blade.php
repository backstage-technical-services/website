<div data-type="modal-template" data-id="award_season">
    {!! Form::open() !!}
    <div class="modal-body">
        <div class="form-group">
            {!! Form::label('name', 'Season Name:', ['class' => 'control-label']) !!}
            {!! Form::text('name', 'BTS Awards ' . date('Y'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('status', 'Status:', ['class' => 'control-label']) !!}
            {!! Form::select('status', ['' => 'Closed'] + \App\Models\Awards\Season::STATUSES, null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('awards', 'Select Awards:', ['class' => 'control-label']) !!}
            @if(count($awards))
                <div class="award-list-container">
                    @foreach($awards as $award)
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('awards[]', $award->id, $award->isRecurring()) !!} {{ $award->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="form-control-static">There are no awards for you to choose :/</p>
                {!! Form::input('hidden', 'awards[]') !!}
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <div class="btn-group">
            <button class="btn btn-success"
                    data-action="save"
                    data-type="submit-modal"
                    data-redirect="true"
                    type="button">
                <span class="fa fa-check"></span>
                <span>Create Award Season</span>
            </button>
        </div>
    </div>
    {!! Form::close() !!}
</div>