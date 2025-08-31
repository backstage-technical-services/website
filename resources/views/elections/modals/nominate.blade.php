<div data-type="modal-template" data-id="nominate">
    {!! Form::open(['enctype' => 'multipart/form-data']) !!}
    <div class="modal-header">
        <h1>Add Nomination</h1>
    </div>
    <div class="modal-body">
        {{-- Member --}}
        <div class="form-group">
            {!! Form::label('user_id', 'Member:', ['class' => 'control-label']) !!}
            {!! Form::memberList('user_id', null, ['class' => 'form-control', 'include_blank' => true]) !!}
        </div>

        {{-- Position --}}
        <div class="form-group">
            {!! Form::label('position', 'Position:', ['class' => 'control-label']) !!}
            {!! Form::select('position', $election->positions, null, ['class' => 'form-control']) !!}
        </div>

        {{-- Manifesto --}}
        <div class="form-group">
            {!! Form::label('manifesto', 'Manifesto:', ['class' => 'control-label']) !!}
            {!! Form::file('manifesto', null, ['class' => 'form-control', 'accept' => 'application/pdf']) !!}
        </div>
    </div>
    <div class="modal-footer">
        <button
            class="btn btn-success"
            data-type="submit-modal"
            data-form-action="{{ route('election.nominate', ['id' => $election->id]) }}"
            data-redirect="true"
        >
            <span class="fa fa-check"></span>
            <span>Nominate</span>
        </button>
    </div>
    {!! Form::close() !!}
</div>
