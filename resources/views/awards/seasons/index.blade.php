@extends('app.main')

@section('page-section', 'awards')
@section('page-id', 'season-index')
@section('header-main', 'Backstage Awards')
@section('title', 'Backstage Awards')

@section('content')
    <div class="buttons">
        <div class="btn-group">
            @can('create', \App\Models\Awards\Season::class)
                <button class="btn btn-success"
                        data-toggle="modal"
                        data-target="#modal"
                        data-modal-class="sm"
                        data-modal-template="award_season"
                        data-modal-title="Create Award Season"
                        data-mode="create"
                        data-form-action="{{ route('award.season.store') }}"
                        type="button">
                    <span class="fa fa-plus"></span>
                    <span>Create Award Season</span>
                </button>
                @can('index', \App\Models\Awards\Award::class)
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{ route('award.index') }}">
                                <span class="fa fa-list"></span> Manage awards
                            </a>
                        </li>
                    </ul>
                @endcan
            @endcan
        </div>

        <div class="btn-group">
            @can('suggest', \App\Models\Awards\Award::class)
                <button class="btn btn-success"
                        data-toggle="modal"
                        data-target="#modal"
                        data-modal-template="award_suggest"
                        data-modal-title="Suggest Award"
                        data-modal-class="sm"
                        data-form-action="{{ route('award.suggest') }}"
                        type="button">
                    <span class="fa fa-plus"></span>
                    <span>Suggest Award</span>
                </button>
            @endcan
        </div>
    </div>

    <table class="table table-striped">
        <thead>
            <th col="name">Name</th>
            <th class="admin-tools admin-tools-icon"></th>
        </thead>
        <tbody>
            @forelse($seasons as $season)
                <tr>
                    <td class="dual-layer" col="name">
                        <div class="upper">
                            {!! link_to_route('award.season.view', $season->name, ['id' => $season->id], ['class' => 'grey']) !!}
                        </div>
                        @if($season->status !== null)
                            <div class="lower">
                                {{ $season->status_text }}
                            </div>
                        @endif
                    </td>
                    <td class="admin-tools admin-tools-icon">
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button">
                                <span class="fa fa-cog"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <button data-toggle="modal"
                                            data-target="#modal"
                                            data-modal-class="sm"
                                            data-modal-template="award_season"
                                            data-modal-title="Edit Award Season"
                                            data-mode="edit"
                                            data-form-data="{{ json_encode($season) }}"
                                            data-save-action="{{ route('award.season.update', ['id' => $season->id]) }}"
                                            type="button">
                                        <span class="fa fa-pencil"></span> Edit
                                    </button>
                                </li>
                                <li>
                                    <a href="{{ route('award.season.nomination.index', ['id' => $season->id]) }}">
                                        <span class="fa fa-list"></span> View nominations
                                    </a>
                                </li>
                                <li>
                                    <button data-submit-ajax="{{ route('award.season.destroy', ['id' => $season->id]) }}"
                                            data-submit-confirm="Are you sure you want to delete this award season?"
                                            type="button">
                                        <span class="fa fa-trash"></span> Delete
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No award seasons.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('modal')
    @include('awards.modals.seasons.season')
    @include('awards.modals.awards.suggest')
@endsection