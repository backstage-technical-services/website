@extends('app.main')

@section('page-section', 'resources')
@section('page-id', 'category-index')
@section('title', 'Manage Categories :: Resources')
@section('header-main', 'Resources')
@section('header-sub', 'Manage Categories')

@section('content')
    <div>
        <button class="btn btn-success"
                data-toggle="modal"
                data-target="#modal"
                data-modal-class="modal-sm"
                data-modal-template="category"
                data-modal-title="Create Category"
                data-form-action="{{ route('resource.category.store') }}"
                data-mode="create"
                type="button">
            <span class="fa fa-plus"></span>
            <span>Add a category</span>
        </button>
        <span class="form-link">
            or {!! link_to_route('resource.index', 'Cancel') !!}
        </span>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="id">ID</th>
                <th col="name">Name</th>
                <th col="slug">Slug</th>
                <th col="type">Type</th>
                <th class="admin-tools admin-tools-icon"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr>
                    <td class="id">{{ $category->id }}</td>
                    <td col="name">{{ $category->name }}</td>
                    <td col="slug">{{ $category->slug }}</td>
                    <td col="type">{{ $category->flag() }}</td>
                    <td class="admin-tools admin-tools-icon">
                        <div class="dropdown pull-right">
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <span class="fa fa-cog"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <button data-toggle="modal"
                                            data-target="#modal"
                                            data-modal-class="modal-sm"
                                            data-modal-template="category"
                                            data-modal-title="Edit Category"
                                            data-mode="edit"
                                            data-form-data="{{ json_encode($category) }}"
                                            data-form-action="{{ route('resource.category.update', ['id' => $category->id]) }}">
                                        <span class="fa fa-pencil"></span> Edit
                                    </button>
                                </li>
                                <li>
                                    <a data-submit-ajax="{{ route('resource.category.destroy', $category->id) }}"
                                       data-submit-confirm="Are you sure you want to delete this category?">
                                        <span class="fa fa-trash"></span> Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No categories.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $categories }}
@endsection

@section('modal')
    @include('resources.modals.category')
@endsection