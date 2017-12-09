@extends('app.main')

@section('page-section', 'resources')
@section('page-id', 'index')
@section('title', 'Resources')
@section('header-main', 'Resources')

@section('javascripts')
    <script src="/js/partials/resources.index.js"></script>
@endsection

@section('content')
    <div class="tools">
        <div class="links">
            <a class="btn btn-success" href="{{ route('resource.create') }}">
                <span class="fa fa-plus"></span>
                <span>Add resource</span>
            </a>
            <span class="form-link">
                or {!! link_to_route('resource.search', 'back to search') !!}
            </span>
        </div>
        <div class="filter">
            <div class="btn-group">
                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <span>Category</span>
                    <span class="fa fa-caret-down"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right dropdown-sm dropdown-scroll">
                    <li class="{{ SearchTools::filter() == null ? 'active' : '' }}">
                        <a href="{{ route('resource.index') }}">-- All Categories --</a>
                    </li>
                    @foreach($ResourceCategories as $category)
                        <li class="{{ SearchTools::filter() == 'category:'.$category->slug ? 'active' : '' }}">
                            <a href="{{ route('resource.index', ['filter' => 'category:'.$category->slug]) }}">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="btn-group">
                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <span>Access</span>
                    <span class="fa fa-caret-down"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right dropdown-sm dropdown-scroll">
                    <li class="{{ SearchTools::filter() == null ? 'active' : '' }}">
                        <a href="{{ route('resource.index') }}">-- All Access Levels --</a>
                    </li>
                    @foreach($AccessLevels as $access => $name)
                        <li class="{{ SearchTools::filter() == 'access:'.$access ? 'active' : '' }}">
                            <a href="{{ route('resource.index', ['filter' => 'access:'.$access]) }}">
                                {{ $name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="id">ID</th>
                <th col="name">Name</th>
                <th col="tags">Tags</th>
                <th col="access">Access</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($resources as $resource)
                <tr>
                    <td class="id">{{ $resource->id }}</td>
                    <td class="dual-layer" col="name">
                        <div class="upper">
                            <a class="grey" href="{{ route('resource.view', ['id' => $resource->id]) }}">{{ $resource->title }}</a>
                        </div>
                        <div class="lower">
                            {{ $resource->category_name }}
                        </div>
                    </td>
                    <td col="tags">
                        @if($resource->tags()->count())
                            <ul class="tag-list">
                                @foreach($resource->tags()->get() as $tag)
                                    <li>
                                        @include('resources.tags.partial')
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td col="access">{{ $resource->access_name }}</td>
                    <td class="admin-tools admin-tools-icon admin-tools-slim">
                        <div class="dropdown pull-right">
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <span class="fa fa-cog"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="{{ route('resource.edit', ['id' => $resource->id]) }}">
                                        <span class="fa fa-pencil"></span> Edit
                                    </a>
                                </li>
                                <li>
                                    <a data-submit-ajax="{{ route('resource.destroy', ['id' => $resource->id]) }}"
                                       data-submit-confirm="Are you sure you want to delete this resource?">
                                        <span class="fa fa-trash"></span> Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No resources.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if(get_class($resources) == 'Illuminate\Pagination\LengthAwarePaginator')
        {{ $resources }}
    @endif
@endsection