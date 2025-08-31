@extends('contact.shared')

@section('title', 'Enquiries')
@section('page-id', 'enquiries')
@section('header-sub', 'General Enquiry')

@section('tab')
    <p>
        If you have a general question for Backstage then you can use the form below to send us an email; alternatively you
        are welcome to pop over to our office (<em class="bts">1E 3.4</em>) or call us on
        <em class="bts">(01225) 666076</em>.
    </p>
    <p>Please do not use this to submit a booking request; use the {!! link_to_route('contact.book', 'booking request form') !!} instead.</p>
    {!! Form::open() !!}
    <!-- Text field for 'name' -->
    <div class="form-group @InputClass('name')">
        {!! Form::label('name', 'Your name:', ['class' => 'control-label']) !!}
        <div class="input-group">
            <span class="input-group-addon"><span class="fa fa-user"></span></span>
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'John Smith']) !!}
        </div>
        @InputError('name')
    </div>

    <!-- Email field for 'email' -->
    <div class="form-group @InputClass('email')">
        {!! Form::label('email', 'Your email address:', ['class' => 'control-label']) !!}
        <div class="input-group">
            <span class="input-group-addon"><span class="fa fa-at"></span></span>
            {!! Form::input('email', 'email', null, ['class' => 'form-control', 'placeholder' => 'abc123@bath.ac.uk']) !!}
        </div>
        @InputError('email')
    </div>

    <!-- Text field for 'phone' -->
    <div class="form-group @InputClass('phone')">
        {!! Form::label('phone', 'Your phone number (optional):', ['class' => 'control-label']) !!}
        <div class="input-group">
            <span class="input-group-addon"><span class="fa fa-phone"></span></span>
            {!! Form::text('phone', null, [
                'class' => 'form-control',
                'placeholder' => 'Please use a full phone number, and not just your extension',
            ]) !!}
        </div>
        @InputError('phone')
    </div>

    <!-- Textarea for 'message' -->
    <div class="form-group @InputClass('message')">
        {!! Form::label('message', 'Your message or question:', ['class' => 'control-label']) !!}
        {!! Form::textarea('message', null, [
            'class' => 'form-control',
            'placeholder' => 'We\'ll make sure this gets forwarded to the appropriate person',
            'rows' => 6,
        ]) !!}
        @InputError('message')
    </div>

    <div class="form-group @InputClass('g-recaptcha-response')">
        {!! NoCaptcha::display() !!}
        @InputError('g-recaptcha-response')
    </div>

    <div class="form-group">
        <button class="btn btn-success" disable-submit="Sending enquiry ..." type="submit">
            <span class="fa fa-send"></span>
            <span>Send Enquiry</span>
        </button>
        <span class="form-link">
            or {!! link_to_route('home', 'Cancel') !!}
        </span>
    </div>
    {!! Form::close() !!}
@endsection
