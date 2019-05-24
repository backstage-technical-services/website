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
    <div class="form-group @InputClass('userTZ_hustings_time')">
        {!! Form::label('userTZ_hustings_time', 'Hustings:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            <div class="input-group">
                {!! Form::datetime('userTZ_hustings_time', null, ['class' => 'form-control', 'data-date-format' => 'YYYY-MM-DD HH:mm']) !!}
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
            </div>
            @InputError('userTZ_hustings_time')
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
    <div class="form-group @InputClass('userTZ_nominations_start')">
        {!! Form::label('userTZ_nominations_start', 'Nominations Open:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            <div class="input-group">
                {!! Form::datetime('userTZ_nominations_start', null, ['class' => 'form-control', 'data-date-format' => 'YYYY-MM-DD HH:mm']) !!}
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
            </div>
            @InputError('userTZ_nominations_start')
        </div>
    </div>
    <div class="form-group @InputClass('userTZ_nominations_end')">
        {!! Form::label('userTZ_nominations_end', 'Nominations Close:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            <div class="input-group">
                {!! Form::datetime('userTZ_nominations_end', null, ['class' => 'form-control', 'data-date-format' => 'YYYY-MM-DD HH:mm']) !!}
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
            </div>
            @InputError('userTZ_nominations_end')
        </div>
    </div>

    {{-- Voting --}}
    <div class="form-group @InputClass('userTZ_voting_start')">
        {!! Form::label('userTZ_voting_start', 'Voting Opens:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            <div class="input-group">
                {!! Form::datetime('userTZ_voting_start', null, ['class' => 'form-control', 'data-date-format' => 'YYYY-MM-DD HH:mm']) !!}
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
            </div>
            @InputError('userTZ_voting_start')
        </div>
    </div>
    <div class="form-group @InputClass('userTZ_voting_end')">
        {!! Form::label('userTZ_voting_end', 'Voting Closes:', ['class' => 'control-label col-md-4']) !!}
        <div class="col-md-8">
            <div class="input-group">
                {!! Form::datetime('userTZ_voting_end', null, ['class' => 'form-control', 'data-date-format' => 'YYYY-MM-DD HH:mm']) !!}
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
            </div>
            @InputError('userTZ_voting_end')
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