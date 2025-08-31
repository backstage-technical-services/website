@extends('app.main')

@section('page-section', 'members')
@section('page-id', 'membership')
@section('header-main', 'The Membership')
@section('title', 'The Membership')

@section('content')
    <div>
        <div class="btn-group">
            @can('create', \App\Models\Users\User::class)
                <a class="btn btn-success" href="{{ route('user.create') }}">
                    <span class="fa fa-user-plus"></span>
                    <span>Add more users</span>
                </a>
            @endcan
            @can('index', \App\Models\Users\User::class)
                <a class="btn btn-primary" href="{{ route('user.index') }}">
                    <span class="fa fa-list"></span>
                    <span>View all users</span>
                </a>
            @endcan
        </div>
        {!! SearchTools::render() !!}
    </div>
    <table class="table">
        <thead class="hidden-xs hidden-sm">
            <tr>
                <th class="pic"></th>
                <th class="name">Name</th>
                <th class="email">Email Address</th>
                <th class="phone text-center">Phone</th>
                <th class="tool text-center">Tools</th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $member)
                <tr class="link">
                    <td class="pic">
                        <img class="img-circle" src="{{ $member->getAvatarUrl() }}">
                    </td>
                    <td class="name">
                        <div class="name">
                            <a href="{{ route('member.view', ['username' => $member->username]) }}">
                                {{ $member->name }}
                                @if ($member->nickname)
                                    <span class="nickname">({{ $member->nickname }})</span>
                                @endif
                            </a>
                        </div>
                        <div class="email visible-xs visible-sm">
                            @if ($member->show_email)
                                <a href="mailto:{{ $member->email }}">{{ $member->email }}</a>
                            @else
                                <em>- hidden -</em>
                            @endif
                        </div>
                    </td>
                    <td class="email hidden-xs hidden-sm">
                        @if ($member->show_email)
                            <a href="mailto:{{ $member->email }}">{{ $member->email }}</a>
                        @else
                            <em>- hidden -</em>
                        @endif
                    </td>
                    <td class="phone text-center hidden-xs hidden-sm">
                        @if ($member->show_phone)
                            {{ $member->phone }}
                        @else
                            <em>- hidden -</em>
                        @endif
                    </td>
                    <td class="tool text-center">
                        {!! $member->tool_colours_parsed !!}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No members matched your search query</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
