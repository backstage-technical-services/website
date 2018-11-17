@extends('app.main')

@section('page-section', 'media')
@section('page-id', 'images-album')
@section('title', $album->getName())
@section('header-main', 'Media')
@section('header-sub', $album->getName())

@section('content')
    <h3>{{ count($album) }} photos</h3>
    @foreach($photos as $photo)
        <div class="box">
            <a data-lightbox="album" href="{{ route('media.image', ['id' => $photo->getId()]) }}" target="_blank">
                <div class="photo" style="background-image:url({{ route('media.image', ['id' => $photo->getId()]) }});"></div>
            </a>
        </div>
    @endforeach
    <div class="btn-group">
        <a class="btn btn-danger" href="{{ route('media.image.index') }}">
            <span class="fa fa-long-arrow-left"></span>
            <span>Back</span>
        </a>
    </div>
@endsection