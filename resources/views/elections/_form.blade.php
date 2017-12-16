@extends('app.main')

@section('page-section', 'elections')
@section('header-main', 'Elections')

@section('scripts')
    $('select[name="type"]').on('change', function() {
        if($(this).val() == 1) {
            $('#position-list').addClass('hidden');
        } else {
            $('#position-list').removeClass('hidden');
        }
    });
@endsection

@section('content')
    {!! Form::model($election, ['url' => $route, 'class' => 'form-horizontal']) !!}
    {{-- Election type --}}
    <div class="form-group @InputClass('type')">
        {!! Form::label('type', 'Election Type:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            {!! Form::select('type', \App\Models\Elections\Election::$Types, null, ['class' => 'form-control']) !!}
            @InputError('type')
        </div>
    </div>

    {{-- BathStudent link --}}
    <div class="form-group @InputClass('bathstudent_id')">
        {!! Form::label('bathstudent_id', 'BathStudent Link:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            {!! Form::text('bathstudent_id', null, ['class' => 'form-control']) !!}
            @InputError('bathstudent_id')
        </div>
    </div>

    {{-- Hustings info --}}
    <div class="form-group @InputClass('hustings_time')">
        {!! Form::label('hustings_time', 'Hustings:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            <div class="input-group">
                {!! Form::datetime('hustings_time', null, ['class' => 'form-control']) !!}
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
            </div>
            @InputError('hustings_time')
        </div>
    </div>
    <div class="form-group @InputClass('hustings_location')">
        {!! Form::label('hustings_location', 'Hustings Location:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            {!! Form::text('hustings_location', null, ['class' => 'form-control']) !!}
            @InputError('hustings_location')
        </div>
    </div>

    {{-- Nominations --}}
    <div class="form-group @InputClass('nominations_start')">
        {!! Form::label('nominations_start', 'Nominations Open:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            <div class="input-group">
                {!! Form::datetime('nominations_start', null, ['class' => 'form-control']) !!}
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
            </div>
            @InputError('nominations_start')
        </div>
    </div>
    <div class="form-group @InputClass('nominations_end')">
        {!! Form::label('nominations_end', 'Nominations Close:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            <div class="input-group">
                {!! Form::datetime('nominations_end', null, ['class' => 'form-control']) !!}
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
            </div>
            @InputError('nominations_end')
        </div>
    </div>

    {{-- Voting --}}
    <div class="form-group @InputClass('voting_start')">
        {!! Form::label('voting_start', 'Voting Opens:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            <div class="input-group">
                {!! Form::datetime('voting_start', null, ['class' => 'form-control']) !!}
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
            </div>
            @InputError('voting_start')
        </div>
    </div>
    <div class="form-group @InputClass('voting_end')">
        {!! Form::label('voting_end', 'Voting Closes:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            <div class="input-group">
                {!! Form::datetime('voting_end', null, ['class' => 'form-control']) !!}
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
            </div>
            @InputError('voting_end')
        </div>
    </div>

    {{-- Positions --}}
    <div class="form-group @InputClass('positions_checked') @if($election->isFull()) hidden @endif " id="position-list">
        {!! Form::label('', 'Positions:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            <div class="container-fluid">
                @foreach($positions as $i => $position)
                    <div class="form-group ">
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('positions_checked[]', $i, true) !!}
                                {{ $position }}
                                {!! Form::hidden('positions['.$i.']', $position, ['class' => 'form-control form-control-inline', 'style' => '']) !!}
                            </label>
                        </div>
                    </div>
                @endforeach
                <div class="form-group">
                    @InputError('positions_checked')
                </div>
            </div>
        </div>
    </div>

    {{-- Buttons --}}
    <div class="form-group">
        <div class="col-md-4"></div>
        <div class="col-md-8">
            @yield('buttons')
        </div>
    </div>
    {!! Form::close() !!}
@endsection