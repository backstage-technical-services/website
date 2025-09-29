@extends('contact.shared')

@section('title', 'Book Us')
@section('page-id', 'book')
@section('header-sub', 'Book Us')

@section('scripts')
    $modal.modal.on('click', '#btnAcceptTerms', function() {
    $modal.modal.modal('hide');
    $('input[name="terms"]').attr('checked', 'checked');
    });
@endsection

@section('tab')
    <p>Please use the following form if you wish to request a quote or enquire about booking Backstage. You will receive an acknowledgement of your request but
        please note that this is not a confirmation that Backstage will crew your event. The Production Manager will get back to you with a definite answer as
        soon as possible.</p>

    {!! Form::open() !!}
    <fieldset class="row">
        <legend>Event Details</legend>

        <div class="form-group @InputClass('event_name')">
            {!! Form::label('event_name', 'Name:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-edit"></span></span>
                {!! Form::text('event_name', null, ['class' => 'form-control', 'placeholder' => 'What\'s it called?']) !!}
            </div>
            @InputError('event_name')
        </div>

        <div class="form-group @InputClass('event_venue')">
            {!! Form::label('event_venue', 'Venue:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-home"></span></span>
                {!! Form::text('event_venue', null, ['class' => 'form-control', 'placeholder' => 'Where is it?']) !!}
            </div>
            @InputError('event_venue')
        </div>

        <div class="form-group @InputClass('event_description')">
            {!! Form::label('event_description', 'Description:', ['class' => 'control-label']) !!}
            {!! Form::textarea('event_description', null, ['class' => 'form-control', 'placeholder' => 'Briefly describe what the event is about', 'rows' => 5]) !!}
            @InputError('event_description')
        </div>

        <div class="form-group @InputClass('event_dates')">
            {!! Form::label('event_dates', 'Event Date(s):', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                {!! Form::text('event_dates', null, ['class' => 'form-control', 'placeholder' => 'When are the shows?']) !!}
            </div>
            @InputError('event_event_dates')
        </div>

        <div class="form-group @InputClass('show_time')">
            {!! Form::label('show_time', 'Show Time:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-clock-o"></span></span>
                {!! Form::text('show_time', null, ['class' => 'form-control', 'placeholder' => '(this is optional)']) !!}
            </div>
            @InputError('show_time')
        </div>

        <div class="form-group no-highlight @InputClass('event_access')">
            {!! Form::label('event_access', 'When can we get into the venue?', ['class' => 'control-label']) !!}
            <div class="checkbox">
                <label class="checkbox-inline">
                    {!! Form::checkbox('event_access[]', 0, null) !!}
                    Morning
                </label>
                <label class="checkbox-inline">
                    {!! Form::checkbox('event_access[]', 1, null) !!}
                    Afternoon
                </label>
                <label class="checkbox-inline">
                    {!! Form::checkbox('event_access[]', 2, null) !!}
                    Evening
                </label>
            </div>
            @InputError('event_access')
        </div>
    </fieldset>
    <fieldset class="row">
        <legend>Contact Details</legend>

        <div class="form-group @InputClass('event_club')">
            {!! Form::label('event_club', 'Client:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-group"></span></span>
                {!! Form::text('event_club', null, ['class' => 'form-control', 'placeholder' => 'Who is this for?']) !!}
            </div>
            @InputError('event_club')
        </div>

        <div class="form-group @InputClass('contact_name')">
            {!! Form::label('contact_name', 'Contact Person:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-user"></span></span>
                {!! Form::text('contact_name', null, ['class' => 'form-control', 'placeholder' => 'Who we\'ll be talking to']) !!}
            </div>
            @InputError('contact_name')
        </div>

        <div class="form-group @InputClass('contact_email')">
            {!! Form::label('contact_email', 'Contact Email:', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-at"></span></span>
                {!! Form::input('email', 'contact_email', null, ['class' => 'form-control', 'placeholder' => 'We\'ll use this to discuss your booking']) !!}
            </div>
            @InputError('contact_email')
        </div>

        <div class="form-group @InputClass('contact_phone')">
            {!! Form::label('contact_phone', 'Contact Phone (optional):', ['class' => 'control-label']) !!}
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-phone"></span></span>
                {!! Form::text('contact_phone', null, ['class' => 'form-control', 'placeholder' => '(this is optional)']) !!}
            </div>
            @InputError('contact_phone')
        </div>
    </fieldset>

    <fieldset class="row" style="padding-bottom: 0.9em;">
        <legend>Additional Info</legend>
        <div class="form-group @InputClass('additional')">
            {!! Form::label('additional', 'Please include any additional requests you may have', ['class' => 'control-label']) !!}
            {!! Form::textarea('additional', null, ['class' => 'form-control', 'placeholder' => '', 'rows' => 5]) !!}
            @InputError('additional')
        </div>
    </fieldset>
    <div class="form-group @InputClass('g-recaptcha-response')">
        @InputError('g-recaptcha-response')
        <div class="g-recaptcha-center">
            {!! NoCaptcha::display() !!}
        </div>
    </div>
    <div class="form-group @InputClass('terms')">
        <div class="checkbox">
            <label>
                {!! Form::checkbox('terms', 1, null) !!}
                I agree to the <a href="{{ route('contact.book.terms') }}"
                                  id="show_terms"
                                  data-toggle="modal"
                                  data-target="#modal"
                                  data-modal-template="terms"
                                  data-modal-title="Terms and Conditions for the Provision of Services"
                                  target="_blank">Terms and Conditions</a>.
            </label>
        </div>
    </div>
    <div class="form-group">
        <button class="btn btn-success" disable-submit="Submitting booking ..." type="submit">
            <span class="fa fa-send"></span>
            <span>Submit booking request</span>
        </button>
        <span class="form-link">
            or {!! link_to_route('home', 'Cancel') !!}
        </span>
    </div>

    {!! Form::close() !!}
@endsection

@section('modal')
    <div data-type="modal-template" data-id="terms">
        @include('contact.book.modal')
    </div>
@endsection