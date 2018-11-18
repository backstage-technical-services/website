@extends('app.main')

@section('page-section', 'training')
@section('page-id', 'proposals-index')
@section('title', 'Skill Applications')
@section('header-main', 'Training Skills')
@section('header-sub', 'Skill Applications')

@section('content')
    <div class="mobile-only">
        <div class="alert alert-warning">
            <span class="fa fa-exclamation"></span>
            Please note that this page is currently not optimised for mobile displays.
        </div>
    </div>
    <div class="tabpanel">
        <div class="tab-links">
            <ul class="nav nav-tabs">
                <li{{ $tab == 'pending' ? ' class=active' : '' }}>
                    <a href="{{ route('training.skill.application.index', ['tab' => 'pending']) }}">Pending Review</a>
                </li>
                <li{{ $tab == 'reviewed' ? ' class=active' : '' }}>
                    <a href="{{ route('training.skill.application.index', ['tab' => 'reviewed']) }}">Reviewed</a>
                </li>
            </ul>
        </div>
        <div class="tab-content">
            <div class="tab-pane{{ $tab == 'pending' ? ' active' : '' }}">
                @include('training.skills.proposals.index.pending')
            </div>
            <div class="tab-pane{{ $tab == 'reviewed' ? ' active' : '' }}">
                @include('training.skills.proposals.index.reviewed')
                @Paginator($awarded)
            </div>
        </div>
@endsection