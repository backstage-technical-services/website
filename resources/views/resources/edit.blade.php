@extends('app.main')

@section('page-section', 'resources')
@section('page-id', 'edit')
@section('title', 'Edit Resource')
@section('header-main', 'Resources')
@section('header-sub', 'Edit Resource')

@section('javascripts')
    <script src="/js/partials/resources.form.js"></script>
@endsection

@section('content')
    {!! Form::model($resource, ['route' => ['resource.update', $resource->id],'enctype' => 'multipart/form-data']) !!}
    @include('resources.forms.resource', ['mode' => 'edit'])
    {{-- Buttons --}}
    <div class="form-group">
        <button class="btn btn-success" data-disable="click" data-disable-text="Saving ...">
            <span class="fa fa-check"></span>
            <span>Save Changes</span>
        </button>
        <span class="form-link">
        or {!! link_to_route('resource.view', 'Cancel', ['id' => $resource->id], ['onclick' => 'return history.back();']) !!}
    </span>
    </div>
    {!! Form::close() !!}
@endsection