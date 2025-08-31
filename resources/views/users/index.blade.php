@extends('app.main')

@section('page-section', 'users')
@section('page-id', 'index')
@section('header-main', 'User Accounts')
@section('title', 'User Accounts')

@section('content')
    <p>
        This table lists all user accounts entered into the database, including non-members and archived accounts. To ensure
        that all past events display properly it is not possible to delete users; instead use the archive function to
        disable their account and remove them from any signup lists.
    </p>
    <div>
        <a class="btn btn-success" href="{{ route('user.create') }}">
            <span class="fa fa-user-plus"></span>
            <span>Create a New User</span>
        </a>
        {!! SearchTools::render() !!}
    </div>
    {!! Form::open(['route' => 'user.update.bulk']) !!}
    <table class="table table-condensed">
        <thead>
            <tr>
                <th class="check">&nbsp;</th>
                <th class="id">&nbsp;</th>
                <th col="name">Name</th>
                <th class="hidden-xs" col="username">Username</th>
                <th col="membership">Account Type</th>
                <th class="hidden-xs" col="date">Registered</th>
                <th class="admin-tools admin-tools-icon"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td class="check">{!! Form::checkbox('users[]', $user->id) !!}</td>
                    <td class="id">{{ $user->id }}</td>
                    <td class="dual-layer" col="name">
                        <span class="upper">{{ $user->name }}</span>
                        <span class="lower hidden-md hidden-lg">{{ $user->username }}</span>
                    </td>
                    <td class="hidden-xs" col="username">{{ $user->username }}</td>
                    <td col="membership">{{ $user->account_type }}</td>
                    <td class="hidden-xs" col="date">{{ $user->created_at }}</td>
                    <td class="admin-tools admin-tools-icon">
                        <div class="dropdown pull-right">
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <span class="fa fa-cog"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="{{ route('user.edit', ['username' => $user->username]) }}">
                                        <span class="fa fa-pencil"></span> Edit
                                    </a>
                                </li>
                                @if ($user->status && $user->id != Auth::user()->id)
                                    <li>
                                        <button name="archive-user" title="Archive" value="{{ $user->id }}">
                                            <span class="fa fa-archive"></span> Archive
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No users matched your query</td>
                </tr>
            @endif
        </tbody>
    </table>
    <div class="form-group bulk-actions">
        {!! Form::select('bulk-action', ['' => '-- Select Action --'] + $bulkActions, null, [
            'class' => 'form-control input-sm',
        ]) !!}
        <button
            class="btn btn-success btn-sm"
            data-disable="click"
            data-disable-text="Applying..."
            name="bulk"
            value="1"
        >
            <span class="fa fa-check"></span>
            <span>Apply to selected users</span>
        </button>
    </div>
    {!! Form::close() !!}

    @if (get_class($users) == 'Illuminate\Pagination\LengthAwarePaginator')
        {{ $users }}
    @endif
@endsection
