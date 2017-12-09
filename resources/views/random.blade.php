@extends('app.main')

@section('title', 'Random')

@section('content')
    {!! Lipsum::headers()->link()->ul()->html(5) !!}
@endsection