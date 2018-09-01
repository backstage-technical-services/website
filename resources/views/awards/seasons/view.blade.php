@extends('app.main')

@section('page-section', 'awards')
@section('page-id', 'season-view')
@section('header-main', 'Backstage Awards')
@section('header-sub',  $season->name)
@section('title', $season->name)

@section('status-selector')
    @can('update', $season)
        <div class="status-selector">
            <div class="btn-group pull-right">
                @if($season->areNominationsOpen() && Auth::user()->can('update', $season))
                    <a class="btn btn-default" href="{{ route('award.season.nomination.index', ['id' => $season->id]) }}">
                        Manage Nominations
                    </a>
                @endif
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <span>Edit Status</span>
                    <span class="fa fa-angle-down"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li>
                        <button data-submit-ajax="{{ route('award.season.status', ['id' => $season->id]) }}"
                                data-status=""
                                data-redirect="true"
                                title="Close the award"
                                type="button">
                            <span class="fa {{ $season->status === null ? 'fa-check' : '' }}"></span> Closed
                        </button>
                    </li>
                    <li>
                        <button data-submit-ajax="{{ route('award.season.status', ['id' => $season->id]) }}"
                                data-status="{{ \App\Models\Awards\Season::STATUS_NOMINATIONS }}"
                                data-redirect="true"
                                title="{{ $season->areNominationsOpen() ? 'Close' : 'Open' }} nominations"
                                type="button">
                            <span class="fa {{ $season->areNominationsOpen() ? 'fa-check' : '' }}"></span> Nominations Open
                        </button>
                    </li>
                    <li>
                        <button data-submit-ajax="{{ route('award.season.status', ['id' => $season->id]) }}"
                                data-status="{{ \App\Models\Awards\Season::STATUS_VOTING }}"
                                data-redirect="true"
                                title="{{ $season->isVotingOpen() ? 'Close' : 'Open' }} voting"
                                type="button">
                            <span class="fa {{ $season->isVotingOpen() ? 'fa-check' : '' }}"></span> Voting Open
                        </button>
                    </li>
                    <li>
                        <button data-submit-ajax="{{ route('award.season.status', ['id' => $season->id]) }}"
                                data-status="{{ \App\Models\Awards\Season::STATUS_RESULTS }}"
                                data-redirect="true"
                                title="{{ $season->areResultsReleased() ? 'Hide' : 'Release' }} results"
                                type="button">
                            <span class="fa {{ $season->areResultsReleased() ? 'fa-check' : '' }}"></span> Results Released
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    @endcan
@endsection

@section('content')
    @yield('status-selector')

    @if($season->areNominationsOpen())
        @include('awards.seasons.nominate')
    @elseif($season->isVotingOpen())
        @include('awards.seasons.vote')
    @elseif($season->areResultsReleased())
        @include('awards.seasons.award_nominations')
    @else
        <div class="closed-info">
            <h3>This award is currently closed</h3>
            <p>This means that only administrators can see this page, allowing you to manage the award without it affecting members' nominations or votes.</p>
            <p>Once the award is ready for nominations, voting or results to be opened, click the corresponding button below.</p>
        </div>
    @endif
@endsection