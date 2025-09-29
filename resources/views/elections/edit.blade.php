@section('title', 'Edit Election')
@section('page-id', 'edit')
@section('header-sub', 'Edit Election')

@section('buttons')
    <button class="btn btn-success" disable-submit="Saving ...">
        <span class="fa fa-check"></span>
        <span>Save</span>
    </button>
    <span class="form-link">
        or {!! link_to_route('election.view', 'Back', ['id' => $election->id], ['onclick' => 'history.back();']) !!}
    </span>
@endsection

@include('elections._form')