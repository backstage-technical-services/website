@extends('contact.shared')

@section('title', 'Feedback')
@section('page-id', 'feedback')
@section('header-sub', 'Provide Feedback')

@section('tab')
    <p>We always strive to provide a professional service to all of our clients but we know that there is room for
        improvement. If you have recently been involved with an event at which we assisted and have feedback (either
        positive or negative) we would really appreciate it if you could fill in the form below.</p>
    <p>All feedback is anonymous and will only be used to improve the quality of the services we provide.</p>
    {!! Form::open() !!}
    <!-- Text field for 'event' -->
    <div class="form-group @InputClass('event')">
        {!! Form::label('event', 'Event Name:', ['class' => 'control-label']) !!}
        {!! Form::text('event', null, ['class' => 'form-control']) !!}
        @InputError('event')
    </div>

    <!-- Textarea for 'feedback' -->
    <div class="form-group @InputClass('feedback')">
        {!! Form::label('feedback', 'Your Feedback:', ['class' => 'control-label']) !!}
        {!! Form::textarea('feedback', null, ['class' => 'form-control', 'rows' => 6]) !!}
        @InputError('feedback')
    </div>

    <div class="form-group @InputClass('g-recaptcha-response')">
        {!! NoCaptcha::display() !!}
        @InputError('g-recaptcha-response')
    </div>

    <div class="form-group">
        <button class="btn btn-success" disable-submit="Sending feedback ..." type="submit">
            <span class="fa fa-send"></span>
            <span>Send Feedback</span>
        </button>
        <span class="form-link">
            or {!! link_to_route('home', 'Cancel') !!}
        </span>
    </div>
    {!! Form::close() !!}
@endsection
