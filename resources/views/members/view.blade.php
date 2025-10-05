@extends('app.main')

@section('page-section', 'members events training')
@section('page-id', 'view-profile')
@section('title', $user->isActiveUser() ? 'My Profile' : $user->getPossessiveName('Profile'))

@section('content')
    @if($user->isActiveUser())
        <div class="btn-group" id="edit-tools">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Edit Profile
                <span class="fa fa-caret-down"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                <li>
                    <button data-toggle="modal" data-target="#modal" data-modal-template="personal" data-modal-class="modal-sm" type="button">
                        <span class="fa fa-user"></span>
                        <span>Personal details</span>
                    </button>
                </li>
                <li>
                    <button data-toggle="modal" data-target="#modal" data-modal-template="contact" data-modal-class="modal-sm" type="button">
                        <span class="fa fa-envelope"></span>
                        <span>Contact details</span>
                    </button>
                </li>
                <li>
                    <button data-toggle="modal" data-target="#modal" data-modal-template="avatar" data-modal-class="modal-sm" type="button">
                        <span class="fa fa-user-circle"></span>
                        <span>Profile picture</span>
                    </button>
                </li>
                <li>
                    <a href="{{ config('services.keycloak.base_url') }}/realms/{{ config('services.keycloak.realms') }}/account/account-security/signing-in" target="_blank">
                        <span class="fa fa-key"></span>
                        <span>Password</span>
                    </a>
                </li>
                <li>
                    <button data-toggle="modal" data-target="#modal" data-modal-template="privacy" data-modal-class="modal-sm" type="button">
                        <span class="fa fa-user-secret"></span>
                        <span>Privacy settings</span>
                    </button>
                </li>
                <li>
                    <button data-toggle="modal" data-target="#modal" data-modal-template="other" data-modal-class="modal-sm" type="button">
                        <span class="fa fa-pencil"></span>
                        <span>Other settings</span>
                    </button>
                </li>
            </ul>
        </div>
    @endif
    <div>
        <div class="row header">
            <div class="col-sm-5 hidden-xs text-right">
                <img class="img-rounded" src="{{ $user->getAvatarUrl() }}">
            </div>
            <div class="col-sm-7">
                <h1>{{ $user->nickname ? sprintf('%s "%s" %s', $user->forename, $user->nickname, $user->surname) : $user->name }}</h1>
                <h3>{{ $user->username }}</h3>
            </div>
        </div>
        @if($user->isMember())
            <div class="tabpanel" id="profileTab">
                <div class="tab-links">
                    {!! $menu !!}
                </div>
                <div class="tab-content">
                    <div class="tab-pane{{ $tab == 'profile' ? ' active' : '' }}">
                        @include('members.profile.profile')
                    </div>
                    <div class="tab-pane{{ $tab == 'events' ? ' active' : '' }}">
                        @include('members.profile.events')
                    </div>
                    <div class="tab-pane{{ $tab == 'training' ? ' active' : '' }}">
                        @include('members.profile.training')
                    </div>
                </div>
            </div>
        @else
            @include('members.profile.profile')
        @endif
    </div>
@endsection

@section('modal')
    @include('members.modals.avatar')
    @include('members.modals.contact')
    @include('members.modals.other')
    @include('members.modals.personal')
    @include('members.modals.privacy')
@endsection
