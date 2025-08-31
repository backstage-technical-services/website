@extends('app.main')

@section('page-section', 'resources')
@section('page-id', 'issue')
@section('title', 'Issue New Version :: Resources')
@section('header-main', 'Resources')
@section('header-sub', 'Issue New Version')

@section('content')
    {!! Form::open(['route' => ['resource.issue.submit', $resource->id], 'enctype' => 'multipart/form-data']) !!}
    <div class="form-group">
        {!! Form::label('', 'Resource:', ['class' => 'control-label']) !!}
        <p class="form-control-static">{{ $resource->title }}</p>
    </div>
    <div class="form-group @InputClass('file')">
        {!! Form::label('file', 'Select File:', ['class' => 'control-label']) !!}
        {!! Form::file('file') !!}
        @InputError('file')
    </div>
    <div class="form-group @InputClass('reason')">
        {!! Form::label('reason', 'Reason for issue:', ['class' => 'control-label']) !!}
        {!! Form::textarea('reason', null, [
            'class' => 'form-control',
            'rows' => 6,
            'placeholder' => 'Please describe why a new version is being issued',
        ]) !!}
        @InputError('reason')
        <p class="help-block alt">This supports {!! link_to('https://simplemde.com/markdown-guide', 'markdown', ['target' => '_blank']) !!}.</p>
    </div>
    <div class="form-group">
        <button class="btn btn-success" data-disable="click" data-disable-text="Submitting issue ...">
            <span class="fa fa-check"></span>
            <span>Submit Issue</span>
        </button>
        <span class="form-link">
            or {!! link_to_route('resource.view', 'Cancel', ['id' => $resource->id]) !!}
        </span>
    </div>
    {!! Form::close() !!}
@endsection
