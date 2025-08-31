@extends('app.main')

@section('page-section', 'users')
@section('page-id', 'create')
@section('header-main', 'User Accounts')
@section('header-sub', 'Create Users')
@section('title', 'Create Users')

@section('scripts')
    $('#modeTab').find('ul.nav > li').on('click', function() {
    var $this = $(this);
    $('input[name="mode"]').val($this.data('mode'));
    $('#submit-form').find('span:last').text($this.data('btnText'));
    });

    @if (request()->old('mode') == 'bulk')
        $('ul.nav > li[data-mode="bulk"]').trigger('click');
    @endif
@endsection

@section('content')
    {!! Form::open(['route' => 'user.store']) !!}
    <div class="tabpanel" id="modeTab">
        <div class="tab-links">
            <ul class="nav nav-tabs">
                <li class="active" data-mode="single" data-btn-text="Add User"><a href="#">Single User</a></li>
                <li data-mode="bulk" data-btn-text="Add Users"><a href="#">Multiple Users</a></li>
            </ul>
        </div>
        <div class="tab-content">
            <div class="tab-pane active">
                @include('users.create.single')
            </div>
            <div class="tab-pane">
                @include('users.create.bulk')
            </div>
        </div>
    </div>

    <div class="form-group @InputClass('type')">
        {!! Form::label('type', 'Account Type:', ['class' => 'control-label']) !!}
        {!! Form::select('type', \App\Models\Users\User::$AccountTypes, null, ['class' => 'form-control']) !!}
        @InputError('type')
    </div>

    <div class="form-group">
        <div class="btn-group">
            <button
                class="btn btn-success"
                id="submit-form"
                disable-submit="Adding user ..."
                type="submit"
            >
                <span class="fa fa-user-plus"></span>
                <span>Add User</span>
            </button>
            <a
                class="btn btn-primary"
                data-toggle="modal"
                data-target="#modal"
                data-modal-template="help"
                href="#"
            >
                <span class="fa fa-question-circle"></span>
                <span>Help</span>
            </a>
        </div>
        <span class="form-link">
            or {!! link_to_route('user.index', 'Cancel') !!}
        </span>
    </div>

    {!! Form::input('hidden', 'mode', 'single') !!}
    {!! Form::close() !!}
@endsection

@section('modal')
    <div data-type="modal-template" data-id="help">
        <div class="modal-header">
            <h1>Creating Users</h1>
        </div>
        <div class="modal-body">
            @HelpDoc('users.create')
        </div>
        <div class="modal-footer text-center">
            <button class="btn btn-success" data-toggle="modal" data-target="#modal">
                <span class="fa fa-thumbs-up"></span>
                <span>Got it!</span>
            </button>
        </div>
    </div>
@endsection
