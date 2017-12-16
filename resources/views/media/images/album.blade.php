@extends('app.main')

@section('page-section', 'media')
@section('page-id', 'images-album')
@section('title', $album['name'])
@section('header-main', 'Media')
@section('header-sub', $album['name'])

@section('content')
    <h3>{{ $album['count'] }} photos</h3>
    @foreach($photos as $photo)
        <div class="box">
            <a data-lightbox="album" href="{{ $photo['images'][0]['source'] }}" target="_blank">
                <div class="photo" style="background-image:url({{ $photo['images'][0]['source'] }});"></div>
            </a>
        </div>
    @endforeach
    <div class="btn-group">
        <a class="btn btn-danger" href="{{ route('media.images.index') }}">
            <span class="fa fa-long-arrow-left"></span>
            <span>Back</span>
        </a>
    </div>
@endsection