@extends('app.main')

@section('page-section', 'media')
@section('page-id', 'images-index')
@section('title', 'Image Gallery')
@section('header-main', 'Media')
@section('header-sub', 'Image Gallery')

@section('content')
    @if(count($albums) > 0)
        @foreach($albums as $album)
            <div class="box">
                <a class="grey" href="{{ route('media.images.album', $album['id']) }}">
                    <div class="photo" style="background-image:url(https://graph.facebook.com/{{ $album['id'] }}/picture);"></div>
                    <div class="album-name">{{ $album['name'] }}</div>
                    <div class="photo-count">({{ $album['count'] }} photos)</div>
                </a>
            </div>
        @endforeach
    @else
        <div class="no-entries">
            <h3>We don't have any images at the moment</h3>
            <h4>Check back soon</h4>
        </div>
    @endif
@endsection