@extends('app.main')

@section('title', 'Reset Your Password')
@section('header-main', 'Reset Your Password')
@section('page-section', 'auth')
@section('page-id', 'password-email')

@section('content')
    <p>If you have forgotten the password for your Backstage account, simply enter your email address into the field below.
        You will be sent a code which will allow you to change your password.</p>
    {!! Form::open() !!}
    <div class="form-group @InputClass('email')">
        <div class="input-group">
            <span class="input-group-addon"><span class="fa fa-envelope"></span></span>
            {!! Form::text('email', null, ['placeholder' => 'Enter your email address', 'class' => 'form-control']) !!}
        </div>
        @InputError('email')
    </div>

    <div class="form-group">
        <button class="btn btn-success" disable-submit="Sending email ..." type="submit">
            <span class="fa fa-send"></span>
            <span>Send Reset Link</span>
        </button>
        <span class="form-link">
            or
            <a href="{{ route('auth.login') }}">Cancel</a>
        </span>
    </div>
    {!! Form::close() !!}
@endsection
