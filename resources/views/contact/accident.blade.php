@extends('app.main')

@section('title', 'Report an Accident')
@section('page-section', 'contact')
@section('page-id', 'accident')
@section('header-main', 'Report an Accident')

@section('content')
    <p>
        Use this form to report an accident that occurred during a Backstage-supported event or activity. Please note that
        this form is automatically sent to the Students' Union as well as Backstage.
    </p>

    {!! Form::open() !!}
    <fieldset>
        <legend>Accident Details</legend>

        <!-- Location -->
        <div class="form-group @InputClass('location')">
            {!! Form::label('location', 'Location:') !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-home"></span></span>
                {!! Form::text('location', null, ['placeholder' => 'Where the accident occurred']) !!}
            </div>
            @InputError('location')
        </div>

        <!-- Date -->
        <div class="form-group @InputClass('date')">
            {!! Form::label('date', 'Date:') !!}
            {!! Form::date('date', null) !!}
            @InputError('date')
        </div>

        <!-- Time -->
        <div class="form-group @InputClass('time')">
            {!! Form::label('time', 'Time:') !!}
            {!! Form::time('time', null, ['data-date-format' => 'HH:mm']) !!}
            @InputError('time')
        </div>

        <!-- Details -->
        <div class="form-group @InputClass('details')">
            {!! Form::label('details', 'Details:') !!}
            {!! Form::textarea('details', null, [
                'rows' => 6,
                'placeholder' => 'Please provide a brief description of the injury or injuries and how they were obtained.',
            ]) !!}
            @InputError('details')
        </div>

        <!-- Severity -->
        <div class="form-group @InputClass('severity')">
            {!! Form::label('severity', 'Severity:') !!}
            {!! Form::select('severity', $Severities, null, ['data-type' => 'toggle-visibility']) !!}
            @InputError('severity')
        </div>

        <!-- Absence Details -->
        <div class="form-group @InputClass('absence_details')" data-visibility-input="severity" data-visibility-value="1 2 3">
            {!! Form::label('absence_details', 'Absence:') !!}
            {!! Form::textarea('absence_details', null, [
                'rows' => 4,
                'placeholder' => 'Details of any absence as a result of the accident',
            ]) !!}
            @InputError('absence_details')
        </div>
    </fieldset>

    <fieldset>
        <legend>Injured Party Details</legend>

        <!-- Name -->
        <div class="form-group @InputClass('injured_name')">
            {!! Form::label('injured_name', 'Injured Party:') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="fa fa-user"></span>
                </span>
                {!! Form::text('injured_name', null, ['placeholder' => 'The name of the injured person']) !!}
            </div>
            @InputError('injured_name')
        </div>

        <!-- Person Type -->
        <div class="form-group @InputClass('person_type') @InputClass('person_type_other')">
            {!! Form::label('person_type', 'Category:') !!}
            {!! Form::select('person_type', $PersonTypes, $PersonTypeDefault, ['data-other-input' => 'person_type_other']) !!}
            {!! Form::text('person_type_other', null, ['placeholder' => 'If other, please specify']) !!}
            @InputError('person_type')
            @InputError('person_type_other')
        </div>
    </fieldset>
    <fieldset>
        <legend>Contact Details</legend>

        <p>
            This should be someone who knows about the injury and is willing to be contacted by Backstage or the Students'
            Union for more information.
        </p>
        <!-- Name -->
        <div class="form-group @InputClass('contact_name')">
            {!! Form::label('contact_name', 'Name:') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="fa fa-user"></span>
                </span>
                {!! Form::text('contact_name', null, ['placeholder' => 'The name of the contact']) !!}
            </div>
            @InputError('contact_name')
        </div>

        <!-- Email -->
        <div class="form-group @InputClass('contact_email')">
            {!! Form::label('contact_email', 'Email:') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="fa fa-at"></span>
                </span>
                {!! Form::text('contact_email', null, ['placeholder' => 'Their email address']) !!}
            </div>
            @InputError('contact_email')
        </div>

        <!-- Phone -->
        <div class="form-group @InputClass('contact_phone')">
            {!! Form::label('contact_phone', 'Phone number:') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="fa fa-phone"></span>
                </span>
                {!! Form::text('contact_phone', null, ['placeholder' => 'Their phone number']) !!}
            </div>
            @InputError('contact_phone')
        </div>
    </fieldset>

    <div class="text-center">
        <button class="btn btn-success" disable-submit="Sending report ..." type="submit">
            <span class="fa fa-send"></span>
            <span>Send accident report</span>
        </button>
        <span class="form-link">or {!! link_to_route('home', 'Cancel') !!}</span>
    </div>
    {!! Form::close() !!}
@endsection
