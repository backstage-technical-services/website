@extends('app.main')

@section('page-section', 'awards')
@section('page-id', 'award-index')
@section('header-main', 'Backstage Awards')
@section('header-sub', 'List of Awards')
@section('title', 'Backstage Awards')

@section('content')
    <div class="btn-group">
        <button class="btn btn-success"
                data-toggle="modal"
                data-target="#modal"
                data-modal-template="award"
                data-modal-title="Create Award"
                data-modal-class="sm"
                data-mode="create"
                data-form-action="{{ route('award.store') }}">
            <span class="fa fa-plus"></span>
            <span>Create Award</span>
        </button>
        @can('index', \App\Models\Awards\Season::class)
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="{{ route('award.season.index') }}">
                        <span class="fa fa-list"></span> Manage award seasons
                    </a>
                </li>
            </ul>
        @endcan
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th col="approved"></th>
                <th col="award">Award</th>
                <th col="recurring">Recurring</th>
                <th class="admin-tools admin-tools-icon"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($awards as $award)
                <tr>
                    <td col="approved">
                        <span class="fa fa-{{ $award->isApproved() ? 'check success' : 'remove danger' }}" title="{{ $award->isApproved() ? 'Approved' :
                        'Not Approved' }}"></span>
                    </td>
                    <td col="award">
                        <div class="name">{{ $award->name }}</div>
                        <div class="description">{{ $award->description }}</div>
                        @if(!$award->isApproved())
                            <div class="suggestor">
                                Suggested by {{ $award->suggestor->name }} ({{ $award->suggestor->username }}) {{ $award->created_at->diffForHumans() }}
                            </div>
                        @endif
                    </td>
                    <td col="recurring">
                        <span class="fa fa-{{ $award->isRecurring() ? 'check success' : 'remove danger' }}"></span>
                    </td>
                    <td class="admin-tools admin-tools-icon">
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <span class="fa fa-cog"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                @if(!$award->isApproved())
                                    <li>
                                        <button data-submit-ajax="{{ route('award.approve', ['id' => $award->id]) }}"
                                                data-redirect="true"
                                                type="button">
                                            <span class="fa fa-check"></span>
                                            <span>Approve</span>
                                        </button>
                                    </li>
                                @endif
                                <li>
                                    <button data-toggle="modal"
                                            data-target="#modal"
                                            data-modal-template="award"
                                            data-modal-title="Edit Award"
                                            data-modal-class="sm"
                                            data-mode="edit"
                                            data-form-data="{{ json_encode($award) }}"
                                            data-save-action="{{ route('award.update', ['id' => $award->id]) }}"
                                            type="button">
                                        <span class="fa fa-pencil"></span> Edit
                                    </button>
                                </li>
                                <li>
                                    <button data-submit-ajax="{{ route('award.destroy', ['id' => $award->id]) }}"
                                            data-submit-confirm="Are you sure you want to delete this award?"
                                            data-redirect="true"
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
                    <td colspan="6">We don't have any awards</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('modal')
    @include('awards.modals.awards.award')
@endsection