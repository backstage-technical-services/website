@extends('app.main')

@section('title', 'Elections')
@section('page-section', 'elections')
@section('page-id', 'list')
@section('header-main', 'Elections')

@section('content')
    @can('create', \App\Models\Elections\Election::class)
        <p>
            <a class="btn btn-success" href="{{ route('election.create') }}">
                <span class="fa fa-plus"></span>
                <span>Create Election</span>
            </a>
        </p>
    @endcan
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="title">Election</th>
                <th class="positions">Positions</th>
                <th class="admin-tools"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($elections as $election)
                <tr>
                    <td class="title">
                        {!! link_to_route('election.view', $election->title, ['id' => $election->id]) !!}
                    </td>
                    <td class="positions">
                        @if($election->isFull())
                            Entire Committee ({{ count($election->positions) }} positions)
                        @else
                            <ul class="position-list">
                                @foreach($election->positions as $position)
                                    <li>{{ $position }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td class="admin-tools admin-tools-icon">
                        @can('update', $election)
                            <div class="dropdown admin-tools">
                                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    <span class="fa fa-cog"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li>
                                        <a href="{{ route('election.edit', ['id' => $election->id]) }}">
                                            <span class="fa fa-pencil"></span> Edit
                                        </a>
                                    </li>
                                    @can('delete', $election)
                                        <li>
                                            <a data-submit-ajax="{{ route('election.destroy', ['id' => $election->id]) }}"
                                               data-submit-confirm="Are you sure you want to delete this election?">
                                                <span class="fa fa-trash"></span> Delete
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No elections</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $elections }}
@endsection