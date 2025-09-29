@extends('app.main')

@section('title', 'The Committee')
@section('page-section', 'committee')
@section('page-id', 'view')
@section('header-main', 'The Committee')

@section('scripts')
    $modal.onShow(function(event) {
        if($modal.mode == 'edit') {
            $modal.find('select[name=order]').find('option[value=' + ($modal.button.data('formData')['order'] + 1) + ']').attr('disabled', 'disabled');
        }
    });
@endsection

@section('content')
    @forelse($roles as $role)
        @include('committee._position', ['role' => $role])
    @empty
        <h4 class="no-committee">We don't seem to have any committee roles ...</h4>
    @endforelse
    @can('create', \App\Models\Committee\Role::class)
        <hr>
        <a class="btn btn-success"
           data-toggle="modal"
           data-target="#modal"
           data-modal-template="committee_add"
           data-modal-title="Add Committee Position"
           data-modal-class="modal-sm"
           data-form-action="{{ route('committee.add') }}"
           data-mode="create"
           href="#">
            <span class="fa fa-plus"></span>
            <span>Add a new role</span>
        </a>
    @endcan
@endsection

@section('modal')
    @can('create', \App\Models\Committee\Role::class)
        <div data-type="modal-template" data-id="committee_add">
            @include('committee.form')
        </div>
    @endcan
@endsection