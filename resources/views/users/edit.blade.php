@extends('app.main')

@section('page-section', 'users')
@section('page-id', 'edit')
@section('header-main', 'User Accounts')
@section('header-sub', 'Edit User')
@section('title', 'Edit User')

@section('buttons')
    <div class="buttons">
        <div class="btn-group">
            <button
                class="btn btn-success"
                data-disable="click"
                data-disable-text="Saving..."
                name="action"
                value="save"
            >
                <span class="fa fa-check"></span>
                <span>Save Changes</span>
            </button>
            <button
                class="btn btn-success dropdown-toggle"
                data-toggle="dropdown"
                type="button"
                aria-haspopup="true"
                aria-expanded="false"
            >
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <button name="action" value="reset-password">
                        <span class="fa fa-key"></span>
                        <span>Reset password</span>
                    </button>
                </li>
                @if (!$user->isActiveUser())
                    <li>
                        @if ($user->status)
                            <button name="action" value="archive">
                                <span class="fa fa-archive"></span>
                                <span>Archive</span>
                            </button>
                        @else
                            <button name="action" value="unarchive">
                                <span class="fa fa-archive"></span>
                                <span>Unarchive</span>
                            </button>
                        @endif
                    </li>
                @endif
            </ul>
        </div>
        <span class="form-link">
            or {!! link_to_route('user.index', 'Cancel') !!}
        </span>
    </div>
@endsection

@section('content')
    {!! Form::model($user, ['route' => ['user.update', $user->username]]) !!}
    <div id="account-status">
        Status:
        @if ($user->status)
            <span class="success">Active</span>
        @else
            <span class="warning">Archived</span>
        @endif
    </div>
    <div id="form-wrapper">
        <div class="left">
            {{-- Personal details --}}
            <fieldset id="personal-details">
                <legend>Personal Details</legend>
                <div class="form-group @InputClass('name')">
                    {!! Form::label('name', 'Full Name:', ['class' => 'control-label']) !!}
                    {!! Form::text('name', null, ['class' => 'form-control']) !!}
                    @InputError('name')
                </div>
                <div class="form-group @InputClass('username')">
                    {!! Form::label('username', 'BUCS Username:', ['class' => 'control-label']) !!}
                    @if ($user->isActiveUser())
                        <p class="form-control-static">{{ $user->username }}</p>
                    @else
                        {!! Form::text('username', null, ['class' => 'form-control']) !!}
                        @InputError('username')
                    @endif
                </div>
                <div class="form-group @InputClass('nickname')">
                    {!! Form::label('nickname', 'Nickname:', ['class' => 'control-label']) !!}
                    {!! Form::text('nickname', null, ['class' => 'form-control']) !!}
                    @InputError('nickname')
                </div>
                <div class="form-group @InputClass('email')">
                    {!! Form::label('email', 'Email Address:', ['class' => 'control-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <span class="fa fa-envelope"></span>
                        </span>
                        {!! Form::text('email', null, ['class' => 'form-control']) !!}
                    </div>
                    @InputError('email')
                </div>
                <div class="form-group @InputClass('phone')">
                    {!! Form::label('phone', 'Phone Number:', ['class' => 'control-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <span class="fa fa-phone"></span>
                        </span>
                        {!! Form::text('phone', null, ['class' => 'form-control']) !!}
                    </div>
                    @InputError('phone')
                </div>
                <div class="form-group @InputClass('dob')">
                    {!! Form::label('dob', 'Date of Birth:', ['class' => 'control-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <span class="fa fa-calendar"></span>
                        </span>
                        {!! Form::date('dob', null, ['class' => 'form-control']) !!}
                    </div>
                    @InputError('dob')
                </div>
                <div class="form-group @InputClass('address')">
                    {!! Form::label('address', 'Address:', ['class' => 'control-label']) !!}
                    <div class="input-group textarea">
                        <span class="input-group-addon">
                            <span class="fa fa-home"></span>
                        </span>
                        {!! Form::textarea('address', null, ['class' => 'form-control', 'rows' => '4']) !!}
                    </div>
                    @InputError('address')
                </div>
                <div class="form-group @InputClass('tool_colours')">
                    {!! Form::label('tool_colours', 'Tool Colours:', ['class' => 'control-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <span class="fa fa-wrench"></span>
                        </span>
                        {!! Form::text('tool_colours', null, ['class' => 'form-control']) !!}
                    </div>
                    @InputError('tool_colours')
                </div>
                <div class="form-group @InputClass('type')">
                    {!! Form::label('type', 'Account Type:', ['class' => 'control-label']) !!}
                    @if ($user->isActiveUser())
                        <p class="form-control-static">{{ $user->account_type }}</p>
                    @else
                        {!! Form::select('type', \App\Models\Users\User::$AccountTypes, null, ['class' => 'form-control']) !!}
                    @endif
                    @InputError('type')
                </div>
            </fieldset>
        </div>
        <div class="right">
            {{-- Privacy settings --}}
            <fieldset id="privacy">
                <legend>Privacy Settings</legend>
                <p>Show:</p>
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            {!! Form::checkbox('show_email', true) !!}
                            Email address
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            {!! Form::checkbox('show_phone', true) !!}
                            Phone number
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            {!! Form::checkbox('show_address', true) !!}
                            Address
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            {!! Form::checkbox('show_age', true) !!}
                            Age
                        </label>
                    </div>
                </div>
            </fieldset>
            {{-- Profile picture --}}
            @include('users._avatar')
        </div>
    </div>
    @yield('buttons')
    {!! Form::close() !!}
@endsection

@section('modal')
    @include('users.modals.avatar')
@endsection
