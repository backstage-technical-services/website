@extends('app.main')

@section('page-section', 'media')
@section('page-id', 'videos-view')
@section('title', $video->title)
@section('header-main', 'Media')
@section('header-sub', $video->title)

@section('content')
    <div class="video">
        <iframe src="{{ $video->embed() }}" frameborder="0" allowfullscreen></iframe>
        <div class="video-details">
            <span class="duration">Duration: {{ $video->duration }}</span>
            <span class="date">Uploaded: {{ $video->created->diffForHumans() }}</span>
        </div>
        <div class="video-description">
            {!! nl2br(clean($video->description)) !!}
        </div>
        <div class="btn-group">
            <a class="btn btn-danger" href="{{ route('media.videos.index') }}">
                <span class="fa fa-long-arrow-left"></span>
                <span>Back</span>
            </a>
        </div>
    </div>
@endsection