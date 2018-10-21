@extends('app.main')

@section('title', 'Logs')
@section('page-section', 'logs')
@section('page-id', 'index')
@section('header-main', 'Logs')

@section('content')
    <table class="table table-striped">
        <thead>
            <tr>
                <th col="status"></th>
                <th col="date">Date</th>
                <th col="user">User</th>
                <th col="action">Action</th>
                <th col="payload">Details</th>
                <th col="ip">IP Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $entry)
                <tr>
                    <td col="status">
                        <span class="fa fa-{{ $entry->status ? 'check' : 'remove' }} {{ $entry->status ? 'success' : 'danger' }}"
                              title="{{ $entry->status ? 'Success' : 'Failed' }}"></span>
                    </td>
                    <td class="dual-layer" col="date">
                        <div class="upper">{{ $entry->date }}</div>
                        <div class="lower">{{ $entry->time }}</div>
                    </td>
                    <td class="dual-layer" col="user">
                        @if($entry->isGuest())
                            <em>Guest</em>
                        @else
                            <div class="upper">{{ $entry->user->name }}</div>
                            <div class="lower">{{ $entry->user->username }}</div>
                        @endif
                    </td>
                    <td col="action">{{ $entry->action }}</td>
                    <td col="payload">
                        @if(empty($entry->payload))
                            <em>&ndash; none &ndash;</em>
                        @else
                            @if(is_array($entry->payload))
                                <ul>
                                    @foreach($entry->payload as $key => $value)
                                        <li><code>{{ $key }}:</code> {{ is_array($value) || is_object($value) ? json_encode($value) : $value }}</li>
                                    @endforeach
                                </ul>
                            @else
                                {{ $entry->payload }}
                            @endif
                        @endif
                    </td>
                    <td col="ip">{{ $entry->ip_address }}</td>
                </tr>
            @empty
                <tr class="link">
                    <td colspan="6">No log entries</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @Paginator($logs)
@endsection