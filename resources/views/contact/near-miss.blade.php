@extends('app.main')

@section('title', 'Report a Near Miss')
@section('page-section', 'contact')
@section('page-id', 'near-miss')
@section('header-main', 'Report a Near Miss')

@section('content')
    <p>Use this form to report an incident that occurred during a Backstage-supported event or activity which could have led to an injury. If an injury did
        occur, please use the {!! link_to_route('contact.accident', 'accident reporting form') !!} instead.</p>
    <p>This email is sent to the Committee and SU H&S inbox, and is only used to help improve the safety of the society and its
        members.</p>
    {!! Form::open() !!}
    <fieldset>
        <legend>Incident Details</legend>

        <!-- Location -->
        <div class="form-group @InputClass('location')">
            {!! Form::label('location', 'Location:') !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-home"></span></span>
                {!! Form::text('location', null, ['placeholder' => 'Where did the near miss occur?']) !!}
            </div>
            @InputError('location')
        </div>

        <!-- Date -->
        <div class="form-group @InputClass('date')">
            {!! Form::label('date', 'Date:') !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                {!! Form::date('date', null, ['placeholder' => 'Roughly when did it happen?']) !!}
            </div>
            @InputError('date')
        </div>

        <!-- Time -->
        <div class="form-group @InputClass('date')">
            {!! Form::label('time', 'Time:') !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                {!! Form::time('time', null, ['placeholder' => 'Roughly when did it happen?', 'data-date-format' => 'HH:mm']) !!}
            </div>
            @InputError('time')
        </div>

        <!-- Details -->
        <div class="form-group @InputClass('details')">
            {!! Form::label('details', 'Details:') !!}
            {!! Form::textarea('details', null, [
            'placeholder' => 'Please provide any details regarding the near miss, including the events leading up to it.',
            'rows' => 4
            ])!!}
            @InputError('details')
        </div>

        <!-- Safety Recommendation -->
        <div class="form-group @InputClass('safety_recommendations')">
            {!! Form::label('safety_recommendations', 'Safety Recommendations:') !!}
            {!! Form::textarea('safety_recommendations', null, [
            'placeholder' => 'Are there any safety steps you feel were overlooked, or should be implemented as a result?',
            'rows' => 4
            ])!!}
            @InputError('safety_recommendations')
        </div>
    </fieldset>

    <fieldset>
        <legend>Your Details</legend>
        <p>You do not need to provide your details, but we discourage anonymous reporting as it prevents the committee from collecting further information if
            needed.</p>

        <!-- Name -->
        <div class="form-group @InputClass('user_name')">
            {!! Form::label('user_name', 'Your Name:') !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-user"></span></span>
                {!! Form::text('user_name', $user_name) !!}
            </div>
            @InputError('user_name')
        </div>

        <!-- Email -->
        <div class="form-group @InputClass('user_email')">
            {!! Form::label('user_email', 'Your email address:') !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-envelope"></span></span>
                {!! Form::text('user_email', $user_email) !!}
            </div>
            @InputError('user_email')
        </div>
    </fieldset>

    <div class="text-center">
        <button class="btn btn-success" data-disable="click" data-disable-text="Sending report ..." type="submit">
            <span class="fa fa-send"></span>
            <span>Send Report</span>
        </button>
        <span class="form-link">or {!! link_to_route('home', 'Cancel') !!}</span>
    </div>
    {!! Form::close() !!}
@endsection
