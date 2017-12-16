<div data-type="modal-template" data-id="elect">
    {!! Form::open() !!}
    <div class="modal-header">
        <h1>Elect Committee</h1>
    </div>
    <div class="modal-body elect-body">
        <div class="container-fluid">
            @foreach($election->positions as $i => $position)
                <div class="row position">
                    <div class="col-xs-6 text-right">{{ $position }}:</div>
                    <div class="col-xs-6">
                        <div class="container-fluid">
                            @forelse($election->getNominations($i) as $nomination)
                                <div class="row nomination">
                                    <div class="checkbox">
                                        <label>
                                            {!! Form::checkbox('elected[]', $nomination->id, $nomination->elected) !!}
                                            {{ $nomination->user->name }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="row"><em>No nominations</em></div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-success"
                data-type="submit-modal"
                data-form-action="{{ route('election.elect', ['electionId' => $election->id]) }}"
                data-redirect="true">
            <span class="fa fa-check"></span>
            <span>Save</span>
        </button>
    </div>
    {!! Form::close() !!}
</div>