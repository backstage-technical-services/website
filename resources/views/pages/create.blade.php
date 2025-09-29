@extends('app.main')

@section('title', 'Create a Page')
@section('page-section', 'pages')
@section('page-id', 'create')
@section('header-main', 'Page Editor')
@section('header-sub', 'Create a New Page')

@section('content')
    {!! Form::model($page, ['route' => ['page.store']]) !!}
    @include('pages.form')

    <div class="form-group">
        <button class="btn btn-success" disable-submit="Processing ..." type="submit">
            <span class="fa fa-check"></span>
            <span>Create Page</span>
        </button>
        <span class="form-link">
            or {!! link_to_route('page.index', 'Cancel') !!}
        </span>
    </div>
    {!! Form::close() !!}
@endsection