@extends('app.main')

@section('page-section', 'training')
@section('page-id', 'category-index')
@section('header-main', 'Training Categories')
@section('title', 'Training Categories')

@section('content')
    <div>
        <button
            class="btn btn-success"
            data-toggle="modal"
            data-target="#modal"
            data-modal-class="modal-sm"
            data-modal-template="training_category"
            data-modal-title="Add a Category"
            data-mode="create"
            data-form-action="{{ route('training.category.store') }}"
            type="button"
        >
            <span class="fa fa-plus"></span>
            <span>Create Category</span>
        </button>
        <span class="form-link">
            or {!! link_to_route('training.skill.index', 'Back to the skills index') !!}
        </span>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="id">ID</th>
                <th>Name</th>
                <th class="admin-tools admin-tools-icon"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr>
                    <td class="id">{{ $category->id }}</td>
                    <td class="dual-layer">
                        <div class="upper">{{ $category->name }}</div>
                        <div class="lower">{{ $category->skills()->count() }} skills</div>
                    </td>
                    <td class="admin-tools admin-tools-icon">
                        <div class="dropdown admin-tools">
                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button">
                                <span class="fa fa-cog"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <button
                                        data-toggle="modal"
                                        data-target="#modal"
                                        data-modal-class="modal-sm"
                                        data-modal-template="training_category"
                                        data-modal-title="Edit Category"
                                        data-form-data="{{ json_encode(['name' => $category->name]) }}"
                                        data-form-action="{{ route('training.category.update', $category->id) }}"
                                        data-mode="edit"
                                        type="button"
                                    >
                                        <span class="fa fa-pencil"></span> Edit
                                    </button>
                                </li>
                                <li>
                                    <button
                                        data-submit-ajax="{{ route('training.category.destroy', $category->id) }}"
                                        data-submit-confirm="Are you sure you want to delete this category? This won't delete any skills."
                                        data-redirect="true"
                                        type="button"
                                    >
                                        <span class="fa fa-trash"></span> Delete
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No training categories</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('modal')
    @include('training.categories.modal')
@endsection
