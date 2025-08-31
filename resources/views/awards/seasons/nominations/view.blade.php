@extends('app.main')

@section('page-section', 'awards')
@section('page-id', 'season-nominations')
@section('header-main', 'Backstage Awards')
@section('header-sub', $season->name)
@section('title', $season->name . ' Nominations')

@section('content')
    <h3>Manage Nominations</h3>
    <p>This system cannot detect multiple nominations for the same nominee and award - please only approve 1 nomination and
        delete the rest before opening voting.</p>
    <div class="table-wrapper">
        <table class="table table-striped">
            <thead>
                <th col="approved"></th>
                <th col="award">Award</th>
                <th col="nominee">Nominee</th>
                <th col="reason">Reason</th>
                <th class="admin-tools admin-tools-icon"></th>
            </thead>
            <tbody>
                @forelse($season->nominations()->ordered()->get() as $nomination)
                    <tr>
                        <td col="approved">
                            <span class="fa fa-{{ $nomination->isApproved() ? 'check success' : 'remove danger' }}"></span>
                        </td>
                        <td col="award">{{ $nomination->award->name }}</td>
                        <td class="dual-layer" col="nominee">
                            <div class="upper">{{ $nomination->nominee }}</div>
                            <div class="lower">Nominated by {{ $nomination->suggestor->name }}</div>
                        </td>
                        <td col="reason">{!! nl2br($nomination->reason) !!}</td>
                        <td class="admin-tools admin-tools-icon">
                            @can('edit', $nomination)
                                <div class="admin-tools">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button">
                                            <span class="fa fa-cog"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li>
                                                <button
                                                    data-submit-ajax="{{ route('award.season.nomination.approve', ['id' => $season->id, 'nominationId' => $nomination->id]) }}"
                                                    data-redirect="true" type="button"
                                                >
                                                    <span
                                                        class="fa fa-{{ $nomination->isApproved() ? 'remove' : 'check' }}"></span>
                                                    {{ $nomination->isApproved() ? 'Unapprove' : 'Approve' }}
                                                </button>
                                            </li>
                                            <li>
                                                <button
                                                    data-submit-ajax="{{ route('award.season.nomination.destroy', ['id' => $season->id, 'nominationId' => $nomination->id]) }}"
                                                    data-submit-confirm="Are you sure you want to delete this nomination?"
                                                    data-redirect="true"
                                                    type="button"
                                                >
                                                    <span class="fa fa-trash"></span> Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No nominations.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="back">
        <hr>
        <p>{!! link_to_route('award.season.view', 'Back', ['id' => $season->id]) !!}</p>
    </div>
@endsection
