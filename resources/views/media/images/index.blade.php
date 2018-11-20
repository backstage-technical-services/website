@extends('app.main')

@section('page-section', 'media')
@section('page-id', 'images-index')
@section('title', 'Image Gallery')
@section('header-main', 'Media')
@section('header-sub', 'Image Gallery')

@section('content')
    @forelse($albums as $album)
        <div class="box">
            <a class="grey" href="{{ route('media.image.album', $album->getId()) }}">
                <div class="photo" style="background-image:url({{ route('media.image', ['id' => $album->getChildren()->first()->getId()]) }});"></div>
                <div class="album-name">{{ $album->getName() }}</div>
                <div class="photo-count">{{ $album->getChildren()->count() }} photos</div>
            </a>
        </div>
    @empty
        <div class="no-entries">
            <h3>We don't have any images at the moment</h3>
            <h4>Check back soon</h4>
        </div>
    @endforelse
@endsection