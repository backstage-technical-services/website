@section('title', 'Create Election')
@section('page-id', 'create')
@section('header-sub', 'Create Election')

@section('buttons')
    <button class="btn btn-success" disable-submit="Saving ...">
        <span class="fa fa-plus"></span>
        <span>Create Election</span>
    </button>
    <span class="form-link">
        or {!! link_to_route('election.index', 'Cancel') !!}
    </span>
@endsection

@include('elections._form')
