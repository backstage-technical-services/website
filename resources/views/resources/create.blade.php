@extends('app.main')

@section('page-section', 'resources')
@section('page-id', 'create')
@section('title', 'Create Resource')
@section('header-main', 'Resources')
@section('header-sub', 'Create Resource')

@section('javascripts')
    <script src="/js/partials/resources.form.js"></script>
@endsection

@section('content')
    {!! Form::open(['route' => 'resource.store', 'enctype' => 'multipart/form-data']) !!}
    @include('resources.forms.resource', ['mode' => 'create'])
    {{-- Buttons --}}
    <div class="form-group">
        <button class="btn btn-success" data-disable="click" data-disable-text="Saving ...">
            <span class="fa fa-check"></span>
            <span>Add Resource</span>
        </button>
        <span class="form-link">
        or {!! link_to_route('resource.index', 'Cancel', [], ['onclick' => 'return history.back();']) !!}
    </span>
    </div>
    {!! Form::close() !!}
@endsection