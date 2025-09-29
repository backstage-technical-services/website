@extends('app.main')

@section('title', 'Edit a Page')
@section('page-section', 'pages')
@section('page-id', 'create')
@section('header-main', 'Page Editor')
@section('header-sub', 'Edit a Page')

@section('content')
    {!! Form::model($page, ['route' => ['page.update', $page->slug]]) !!}
    @include('pages.form')

    <div class="form-group">
        <button class="btn btn-success" disable-submit="Saving ..." type="submit">
            <span class="fa fa-check"></span>
            <span>Update Page</span>
        </button>
        <span class="form-link">
            or {!! link_to_route('page.index', 'Cancel') !!}
        </span>
    </div>
    {!! Form::close() !!}
@endsection