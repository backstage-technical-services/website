@extends('app.main')

@section('title', 'Page Editor')
@section('page-section', 'pages')
@section('page-id', 'index')
@section('header-main', 'Page Editor')

@section('content')
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="id">ID</th>
                <th>Page</th>
                <th class="text-center hidden-xs">Author</th>
                <th></th>
                <th class="admin-tools"></th>
            </tr>
        </thead>
        <tbody>
            @if (count($pages))
                @foreach ($pages as $page)
                    <tr>
                        <td class="id hidden-xs">{{ $page->id }}</td>
                        <td class="page-title dual-layer">
                            <div class="upper">{{ $page->title }}</div>
                            <div class="lower">{{ route('page.show', ['slug' => $page->slug], false) }}</div>
                        </td>
                        <td class="text-center page-author hidden-xs">{{ $page->author->name }}</td>
                        <td class="text-center page-published">
                            @if ($page->published)
                                <span class="fa fa-check success" title="Published"></span>
                            @else
                                <span class="fa fa-remove danger" title="Not published"></span>
                            @endif
                        </td>
                        <td class="admin-tools admin-tools-icon">
                            <div class="dropdown admin-tools">
                                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    <span class="fa fa-cog"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li>
                                        <a href="{{ route('page.edit', $page->slug) }}">
                                            <span class="fa fa-pencil"></span> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('page.destroy', $page->slug) }}"
                                            onclick="return confirm('Are you sure you wish to delete this page?');"
                                        >
                                            <span class="fa fa-trash"></span> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5">No webpages</td>
                </tr>
            @endif
        </tbody>
    </table>
    <p class="text-right">
        <a class="btn btn-success" href="{{ route('page.create') }}">
            <span class="fa fa-plus"></span>
            <span>New Page</span>
        </a>
    </p>

    {{ $pages }}
@endsection
