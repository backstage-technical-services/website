@extends('app.main')

@section('page-section', 'users')
@section('page-id', 'create-summary')
@section('header-main', 'User Accounts')
@section('header-sub', 'Bulk Create Summary')
@section('title', 'Create Users')

@section('content')
    <div class="container-fluid">
        @foreach ($results as $i => $result)
            <div class="row {{ $result['success'] ? 'success' : 'error' }}">
                <div class="status">
                    <span class="fa {{ $result['success'] ? 'fa-check' : 'fa-remove' }}"></span>
                </div>
                <div class="username">{{ is_int($result['username']) ? 'User ' : '' }}{{ $result['username'] }}</div>
                <div class="details">{!! nl2br($result['message']) !!}</div>
            </div>
        @endforeach
    </div>
    <div>
        <a class="btn btn-success" href="{{ route('user.create') }}">
            <span class="fa fa-user-plus"></span>
            <span>Add more users</span>
        </a>
        <span class="form-link">
            or {!! link_to_route('user.index', 'Cancel') !!}
        </span>
    </div>
@endsection
