@extends('app.main')

@section('page-section', 'media')
@section('page-id', 'videos-index')
@section('title', 'Videos')
@section('header-main', 'Media')
@section('header-sub', 'Videos')

@section('content')
    @forelse($videos as $video)
        <div class="video">
            <div class="thumbnail-wrapper">
                <a href="{{ route('media.videos.show', ['id' => $video->id]) }}">
                    <div class="thumb" style="background-image:url({{ $video->thumbnail }});"></div>
                    <div class="overlay"></div>
                    <div class="play-icon">
                        <span class="fa fa-play"></span>
                    </div>
                </a>
            </div>
            <div class="video-details">
                <div class="video-details-wrapper">
                    <a class="video-title"
                        href="{{ route('media.videos.show', ['id' => $video->id]) }}">{{ $video->title }}</a>
                    <div class="video-description">{!! nl2br(clean($video->description)) !!}</div>
                </div>
            </div>
        </div>
    @empty
        TEST
    @endforelse
@endsection
