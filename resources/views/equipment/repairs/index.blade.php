@extends('app.main')

@section('page-section', 'equipment')
@section('page-id', 'repairs-list')
@section('header-main', 'Repairs Database')
@section('title', 'Repairs Database')

@section('add_breakage_button')
    <div class="admin-tools admin-tools-sm">
        <div class="dropdown pull-right">
            <button class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                <span class="fa fa-plus"></span>
                <span>Add breakage</span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="{{ route('equipment.repairs.create') }}">
                        <span class="fa fa-plus"></span> Backstage kit
                    </a>
                </li>
                <li>
                    <a href="https://docs.google.com/forms/d/1iEeYXmItGGWwjsqbv1w1yRXKsvnfwBMdhAujCC5VKfI/viewform" target="_blank">
                        <span class="fa fa-external-link"></span> EMP Audio kit
                    </a>
                </li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
    @yield('add_breakage_button')
    <table class="table table-striped">
        <thead>
            <th col="item">Item</th>
            <th col="description">Description</th>
            <th col="comment">Comments</th>
            <th col="date">Reported</th>
            <th col="status">Status</th>
            <th col="button"></th>
        </thead>
        <tbody>
            @forelse($breakages as $breakage)
                <tr onclick="">
                    <td col="item" class="dual-layer">
                        <span class="upper">{{ $breakage->name }}</span>
                        <span class="lower">{{ $breakage->location }}</span>
                    </td>
                    <td col="description">
                        <div>{!! nl2br($breakage->description) !!}</div>
                    </td>
                    <td col="comment">
                        <div>{!! nl2br($breakage->comment) !!}</div>
                    </td>
                    <td col="date">{{ $breakage->created_at->diffForHumans() }}</td>
                    <td col="status">{{ \App\Models\Equipment\Breakage::$Status[$breakage->status] }}</td>
                    <td col="button">
                        <a class="btn btn-primary" href="{{ route('equipment.repairs.view', ['id' => $breakage->id]) }}" title="View breakage">
                            <span class="fa fa-angle-double-right"></span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">We seem to be breakage-free at the moment.<br>Let's keep it up!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {!! $breakages !!}
@endsection