@extends('app.main')

@section('title', 'Report an Accident')
@section('page-id', 'accident')
@section('header-main', 'Report an Accident')

@section('content')
    <p>Use this form to report an accident that occurred during a Backstage-supported event or activity. Please note that this form is automatically sent to the
        Students' Union as well as Backstage.</p>

    {!! Form::open(['class' => 'form-horizontal']) !!}
    <div class="row">
        <div class="col-md-6">
            <fieldset>
                <legend>Accident Details</legend>

                <!-- Location -->
                <div class="form-group @InputClass('location')">
                    {!! Form::label('location', 'Location:', ['class' => 'control-label col-sm-3']) !!}
                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="fa fa-home"></span></span>
                            {!! Form::text('location', null, ['class' => 'form-control', 'placeholder' => 'Where the accident occurred']) !!}
                        </div>
                        @InputError('location')
                    </div>
                </div>

                <!-- Date -->
                <div class="form-group @InputClass('date')">
                    {!! Form::label('date', 'Date:', ['class' => 'control-label col-sm-3']) !!}
                    <div class="col-sm-9">
                        {!! Form::date('date', null, ['class' => 'form-control']) !!}
                        @InputError('date')
                    </div>
                </div>

                <!-- Time -->
                <div class="form-group @InputClass('time')">
                    {!! Form::label('time', 'Time:', ['class' => 'control-label col-sm-3']) !!}
                    <div class="col-sm-9">
                        {!! Form::time('time', null, ['class' => 'form-control', 'data-date-format' => 'HH:mm']) !!}
                        @InputError('time')
                    </div>
                </div>

                <!-- Details -->
                <div class="form-group @InputClass('details')">
                    {!! Form::label('details', 'Details:', ['class' => 'control-label col-sm-3']) !!}
                    <div class="col-sm-9">
                        {!! Form::textarea('details', null, ['class' => 'form-control', 'rows' => 6, 'placeholder' => 'Brief description of the accident, include the location and any injuries caused']) !!}
                        @InputError('details')
                    </div>
                </div>

                <!-- Severity -->
                <div class="form-group @InputClass('severity')">
                    {!! Form::label('severity', 'Severity:', ['class' => 'control-label col-sm-3']) !!}
                    <div class="col-sm-9">
                        {!! Form::select('severity', $Severities, null, ['class' => 'form-control']) !!}
                        @InputError('severity')
                    </div>
                </div>

                <!-- Absence Details -->
                <div class="form-group @InputClass('absence_details')">
                    {!! Form::label('absence_details', 'Absence:', ['class' => 'control-label col-sm-3']) !!}
                    <div class="col-sm-9">
                        {!! Form::textarea('absence_details', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Details of any absence as a result of the accident']) !!}
                        @InputError('absence_details')
                    </div>
                </div>
            </fieldset>
        </div>

        <div class="col-md-6">
            <fieldset>
                <legend>Injured Party Details</legend>

                <!-- Name -->
                <div class="form-group @InputClass('injured_name')">
                    {!! Form::label('injured_name', 'Injured Party:', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="fa fa-user"></span></span>
                            {!! Form::text('injured_name', null, ['class' => 'form-control', 'placeholder' => 'The name of the injured person']) !!}
                        </div>
                        @InputError('injured_name')
                    </div>
                </div>

                <!-- Person Type -->
                <div class="form-group @InputClass('person_type') @InputClass('person_type_other')">
                    {!! Form::label('person_type', 'Category:', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::select('person_type', $PersonTypes, $PersonTypeDefault, ['class' => 'form-control', 'data-other-input' =>
                        'person_type_other']) !!}
                        {!! Form::text('person_type_other', null, [
                            'class' => 'form-control',
                            'placeholder' => 'If other, please specify'
                        ]) !!}
                        @InputError('person_type')
                        @InputError('person_type_other')
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>Contact Details</legend>

                <!-- Name -->
                <div class="form-group @InputClass('contact_name')">
                    {!! Form::label('contact_name', 'Name:', ['class' => 'control-label col-sm-3']) !!}
                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="fa fa-user"></span></span>
                            {!! Form::text('contact_name', null, ['class' => 'form-control', 'placeholder' => 'The name of the contact']) !!}
                        </div>
                        @InputError('contact_name')
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group @InputClass('contact_email')">
                    {!! Form::label('contact_email', 'Email:', ['class' => 'control-label col-sm-3']) !!}
                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="fa fa-at"></span></span>
                            {!! Form::text('contact_email', null, ['class' => 'form-control', 'placeholder' => 'Their email address']) !!}
                        </div>
                        @InputError('contact_email')
                    </div>
                </div>

                <!-- Phone -->
                <div class="form-group @InputClass('contact_phone')">
                    {!! Form::label('contact_phone', 'Phone:', ['class' => 'control-label col-sm-3']) !!}
                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-addon"><span class="fa fa-phone"></span></span>
                            {!! Form::text('contact_phone', null, ['class' => 'form-control', 'placeholder' => 'Their phone number']) !!}
                        </div>
                        @InputError('contact_phone')
                    </div>
                </div>
            </fieldset>
        </div>
    </div>

    <div class="text-center">
        <button class="btn btn-success" disable-submit="Sending report ..." type="submit">
            <span class="fa fa-send"></span>
            <span>Send accident report</span>
        </button>
        <span class="form-link">or {!! link_to_route('home', 'Cancel') !!}</span>
    </div>
    {!! Form::close() !!}
@endsection