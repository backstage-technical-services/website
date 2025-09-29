@extends('app.main')

@section('page-section', 'backups')
@section('page-id', 'view')
@section('title', 'Backups')
@section('header-main', 'Backups')

@section('content')
    <button type="button"
            class="btn btn-success"
            data-toggle="modal"
            data-target="#modal"
            data-modal-template="backups"
            data-modal-class="sm">
        <span class="fa fa-plus"></span>
        <span>Create Backup</span>
    </button>
    <table class="table table-striped">
        <thead>
            <th class="col--type"></th>
            <th class="col--date">Date</th>
            <th class="col--size">Size</th>
            <th class="admin-tools admin-tools-icon"></th>
        </thead>
        <tbody>
            @forelse($backups as $backup)
                <tr>
                    <td class="col--type">
                        @if($backup->getExtension() == 'sql')
                            <span class="fa fa-database" title="Database only"></span>
                        @elseif($backup->getExtension() == 'zip')
                            <span class="fa fa-sitemap" title="Database and resources"></span>
                        @endif
                    </td>
                    <td class="col--date dual-layer">
                        <div class="upper">
                            {{ \Carbon\Carbon::createFromTimestamp($backup->getCTime())->toDateTimeString() }}
                        </div>
                        <div class="lower">
                            {{ \Carbon\Carbon::createFromTimestamp($backup->getCTime())->diffForHumans() }}
                        </div>
                    </td>
                    <td class="col--size">
                        {{ $backup->getSizeForHumans() }}
                    </td>
                    <td class="admin-tools admin-tools-icon">
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <span class="fa fa-cog"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a href="{{ route('backup.download', ['filename' => $backup->getFilename()]) }}">
                                        <span class="fa fa-download"></span>
                                        <span>Download</span>
                                    </a>
                                    <button data-submit-ajax="{{ route('backup.destroy', ['filename' => $backup->getFilename()]) }}"
                                            data-submit-confirm="Are you sure you want to delete this backup?"
                                            data-redirect="true"
                                            type="button">
                                        <span class="fa fa-trash"></span>
                                        <span>Delete</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No backups</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('modal')
    @include('backups.modal')
@endsection