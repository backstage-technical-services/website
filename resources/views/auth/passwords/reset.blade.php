@extends('app.main')

@section('title', 'Reset Your Password')
@section('header-main', 'Reset Your Password')
@section('page-section', 'auth')
@section('page-id', 'password-reset')

@section('content')
    <p>To complete the process, confirm your email address and enter a new password.</p>
    {!! Form::open() !!}
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group @InputClass('email')">
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-envelope"></span></span>
                {!! Form::text('email', null, ['placeholder' => 'Confirm your email address', 'class' => 'form-control']) !!}
            </div>
            @InputError('email')
        </div>

        <div class="form-group @InputClass('password')">
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-key"></span></span>
                {!! Form::input('password', 'password', null, ['placeholder' => 'Enter a new password', 'class' => 'form-control']) !!}
            </div>
            @InputError('password')
        </div>

        <div class="form-group @InputClass('password_confirmation')">
            <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-key"></span></span>
                {!! Form::input('password', 'password_confirmation', null, ['placeholder' => 'Confirm your password', 'class' => 'form-control']) !!}
            </div>
            @InputError('password_confirmation')
        </div>

        <div class="form-group">
            <button class="btn btn-success" disable-submit="Resetting password ..." type="submit">
                <span class="fa fa-check"></span>
                <span>Reset Password</span>
            </button>
            <span class="form-link">
                or <a href="{{ route('auth.login') }}">Cancel</a>
            </span>
        </div>
    {!! Form::close() !!}
@endsection
